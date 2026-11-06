<?php
/**
 * Client de l'API Claude par le SDK PHP officiel (anthropic-ai/sdk 1.6).
 *
 * Le modèle et l'effort sont des constantes du code, décision de Pierre du 06/11/2026 : aucun réglage ne peut faire
 * changer la facture. En changer passe par un commit, après un essai comparatif (guide d'exploitation §8).
 * Chaque erreur de l'API devient une AssistantException au message clair ; aucune n'atteint l'écran de Céline brute.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model\Claude;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\InternalServerException;
use Anthropic\Core\Exceptions\NotFoundException;
use Anthropic\Core\Exceptions\PermissionDeniedException;
use Anthropic\Core\Exceptions\RateLimitException;
use Anthropic\ErrorType;
use Anthropic\Messages\Message;
use Anthropic\RequestOptions;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use MadameAiguille\AiAssistant\Model\AssistantException;
use MadameAiguille\AiAssistant\Model\Config;
use Psr\Http\Client\ClientInterface as HttpClientInterface;

class SdkClient implements ClientInterface
{
    public const MODEL = 'claude-haiku-5-5';

    /** Effort d'analyse explicite : `medium` est le défaut de Haiku 5.5, on le fige pour qu'il ne bouge pas seul. */
    public const EFFORT = 'medium';

    /** Une génération prend de 10 à 30 s ; PHP-FPM, nginx et Varnish laissent 600 s sur le serveur. */
    private const TIMEOUT_SECONDS = 120.0;
    private const MAX_RETRIES = 1;

    public function __construct(
        private readonly Config $config
    ) {
    }

    public function request(string $system, array $content, array $schema, int $maxTokens): Response
    {
        $apiKey = $this->config->getApiKey();
        if ($apiKey === null) {
            throw new AssistantException(
                __('L\'assistant n\'a pas de clé d\'API : demandez à Pierre de la saisir.'),
                AssistantException::NOT_CONFIGURED
            );
        }

        try {
            $message = $this->client($apiKey)->messages->create(
                maxTokens: $maxTokens,
                messages: [['role' => 'user', 'content' => $content]],
                model: self::MODEL,
                outputConfig: [
                    'effort' => self::EFFORT,
                    'format' => ['type' => 'json_schema', 'schema' => $schema],
                ],
                system: [['type' => 'text', 'text' => $system, 'cacheControl' => ['type' => 'ephemeral']]],
            );
        } catch (APIStatusException $exception) {
            throw $this->translate($exception);
        } catch (APIConnectionException $exception) {
            throw new AssistantException(
                __('Le service de rédaction ne répond pas. Vérifiez la connexion, puis réessayez dans quelques minutes.'),
                AssistantException::UNREACHABLE,
                $exception
            );
        } catch (\InvalidArgumentException $exception) {
            // Guzzle refuse un en-tête contenant un caractère interdit (clé mal collée) et recopie sa valeur dans le
            // message : ni ce message ni l'exception d'origine ne sont transmis, pour que la clé n'atteigne aucun journal
            throw str_contains(strtolower($exception->getMessage()), 'header')
                ? new AssistantException(
                    __('La clé d\'API est illisible : demandez à Pierre de la ressaisir.'),
                    AssistantException::AUTHENTICATION
                )
                : new AssistantException(
                    __('Le service de rédaction a refusé la demande. Réessayez ; si cela se répète, prévenez Pierre.'),
                    AssistantException::INVALID_REQUEST
                );
        }

        return $this->read($message);
    }

    /**
     * Client HTTP et fabriques PSR fixés sur Guzzle (dépendance de Magento) plutôt que laissés à php-http/discovery :
     * le même code en local, dans les tests et sur le serveur. Les tests y branchent un transport doublé.
     */
    protected function client(string $apiKey, ?HttpClientInterface $transporter = null): Client
    {
        $factory = new HttpFactory();

        return new Client(
            apiKey: $apiKey,
            requestOptions: RequestOptions::with(
                timeout: self::TIMEOUT_SECONDS,
                maxRetries: $transporter === null ? self::MAX_RETRIES : 0,
                transporter: $transporter ?? new GuzzleClient(['timeout' => self::TIMEOUT_SECONDS]),
                uriFactory: $factory,
                streamFactory: $factory,
                requestFactory: $factory
            )
        );
    }

    private function read(Message $message): Response
    {
        if ($message->stopReason === 'refusal') {
            throw new AssistantException(
                __('Le service de rédaction a refusé cette demande. Reformulez vos réponses, puis réessayez.'),
                AssistantException::REFUSAL
            );
        }
        if ($message->stopReason === 'max_tokens') {
            throw new AssistantException(
                __('La réponse a été coupée avant la fin. Réessayez, ou demandez un texte plus court.'),
                AssistantException::TRUNCATED
            );
        }

        $data = null;
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $data = json_decode($block->text, true);
                break;
            }
        }
        if (!is_array($data)) {
            throw new AssistantException(
                __('La réponse reçue est illisible. Réessayez.'),
                AssistantException::INVALID_RESPONSE
            );
        }

        return new Response(
            $data,
            $message->model,
            $message->usage->inputTokens,
            $message->usage->outputTokens,
            (int) $message->usage->cacheReadInputTokens,
            (int) $message->usage->cacheCreationInputTokens
        );
    }

    private function translate(APIStatusException $exception): AssistantException
    {
        $type = $exception->type;

        return match (true) {
            $type === ErrorType::BILLING_ERROR,
            $type === ErrorType::INVALID_REQUEST_ERROR && $this->mentionsCredit($exception) => new AssistantException(
                __('Le crédit du service de rédaction est épuisé ou la limite de dépenses est atteinte : prévenez Pierre.'),
                AssistantException::CREDIT,
                $exception
            ),
            $exception instanceof AuthenticationException,
            $exception instanceof PermissionDeniedException => new AssistantException(
                __('La clé d\'API est refusée : prévenez Pierre.'),
                AssistantException::AUTHENTICATION,
                $exception
            ),
            $exception instanceof NotFoundException => new AssistantException(
                __('Le modèle de rédaction n\'est plus disponible : prévenez Pierre.'),
                AssistantException::MODEL_UNAVAILABLE,
                $exception
            ),
            $exception instanceof RateLimitException,
            $exception instanceof InternalServerException => new AssistantException(
                __('Le service de rédaction est surchargé. Réessayez dans quelques minutes.'),
                AssistantException::OVERLOADED,
                $exception
            ),
            default => new AssistantException(
                __('Le service de rédaction a refusé la demande. Réessayez ; si cela se répète, prévenez Pierre.'),
                AssistantException::INVALID_REQUEST,
                $exception
            ),
        };
    }

    /** « Your credit balance is too low… » est une erreur 400 ordinaire : seul son texte la distingue. */
    private function mentionsCredit(APIStatusException $exception): bool
    {
        return (bool) preg_match('/credit|billing|spend limit/i', (string) $exception->getAPIMessage());
    }
}
