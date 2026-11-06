<?php
declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Test\Unit\Model\Claude;

use Anthropic\Client;
use GuzzleHttp\Psr7\Response as HttpResponse;
use Http\Discovery\ClassDiscovery;
use MadameAiguille\AiAssistant\Model\AssistantException;
use MadameAiguille\AiAssistant\Model\Claude\SdkClient;
use MadameAiguille\AiAssistant\Model\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface as HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Le vrai SDK, branché sur un transport HTTP doublé : on vérifie la requête envoyée et la lecture de la réponse,
 * sans réseau ni coût.
 */
class SdkClientTest extends TestCase
{
    /** @var list<RequestInterface> */
    private array $sent = [];

    /** @var iterable<class-string> */
    private iterable $strategies = [];

    /**
     * L'autoloader des tests de Magento fabrique toute classe en « *Factory » demandée : la découverte PSR du SDK y
     * prendrait une fabrique Phalcon fictive. On lui impose Guzzle, comme dans Magento réel.
     */
    protected function setUp(): void
    {
        $this->strategies = ClassDiscovery::getStrategies();
        ClassDiscovery::setStrategies([GuzzleOnlyStrategy::class]);
    }

    protected function tearDown(): void
    {
        ClassDiscovery::setStrategies($this->strategies);
    }

    private function client(int $status, array $body, ?string $apiKey = 'cle-factice-de-test'): SdkClient
    {
        $config = $this->createStub(Config::class);
        $config->method('getApiKey')->willReturn($apiKey);
        $transport = new class ($status, $body, $this->sent) implements HttpClient {
            public function __construct(private int $status, private array $body, private array &$sent)
            {
            }

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->sent[] = $request;

                return new HttpResponse(
                    $this->status,
                    ['Content-Type' => 'application/json'],
                    (string) json_encode($this->body)
                );
            }
        };

        return new class ($config, $transport) extends SdkClient {
            public function __construct(Config $config, private HttpClient $transport)
            {
                parent::__construct($config);
            }

            protected function client(string $apiKey, ?HttpClient $transporter = null): Client
            {
                return parent::client($apiKey, $this->transport);
            }
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function message(string $text, string $stopReason = 'end_turn'): array
    {
        return [
            'id' => 'msg_test',
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'claude-haiku-5-5',
            'content' => [['type' => 'text', 'text' => $text]],
            'stop_reason' => $stopReason,
            'stop_sequence' => null,
            'usage' => [
                'input_tokens' => 1500,
                'output_tokens' => 400,
                'cache_read_input_tokens' => 2000,
                'cache_creation_input_tokens' => 0,
            ],
        ];
    }

    public function testSendsTheFixedModelEffortSchemaAndCachedSystemPrompt(): void
    {
        $schema = ['type' => 'object', 'properties' => [], 'required' => [], 'additionalProperties' => false];
        $response = $this->client(200, $this->message('{"a": 1}'))->request(
            'Consigne',
            [['type' => 'text', 'text' => 'Fiche']],
            $schema,
            8000
        );

        self::assertSame(['a' => 1], $response->data);
        self::assertSame([1500, 400, 2000], [$response->inputTokens, $response->outputTokens, $response->cacheReadTokens]);

        self::assertCount(1, $this->sent);
        $body = json_decode((string) $this->sent[0]->getBody(), true);
        self::assertSame('claude-haiku-5-5', $body['model']);
        self::assertSame(8000, $body['max_tokens']);
        self::assertSame(['effort' => 'medium', 'format' => ['type' => 'json_schema', 'schema' => $schema]], $body['output_config']);
        self::assertSame([['type' => 'text', 'text' => 'Consigne', 'cache_control' => ['type' => 'ephemeral']]], $body['system']);
        self::assertArrayNotHasKey('temperature', $body);
        // Le SDK 1.6 transmet la clé en « Authorization: Bearer »
        self::assertSame('Bearer cle-factice-de-test', $this->sent[0]->getHeaderLine('Authorization'));
        self::assertSame('https://api.anthropic.com/v1/messages', (string) $this->sent[0]->getUri());
    }

    /**
     * @return array<string, array{int, array<string, mixed>, string}>
     */
    public static function errors(): array
    {
        $error = fn (string $type, string $message): array => ['type' => 'error', 'error' => ['type' => $type, 'message' => $message]];

        return [
            'crédit épuisé (400)' => [400, $error('invalid_request_error', 'Your credit balance is too low to access the Anthropic API.'), AssistantException::CREDIT],
            'facturation (402)' => [402, $error('billing_error', 'Billing issue'), AssistantException::CREDIT],
            'clé refusée' => [401, $error('authentication_error', 'invalid x-api-key'), AssistantException::AUTHENTICATION],
            'modèle retiré' => [404, $error('not_found_error', 'model: claude-haiku-5-5'), AssistantException::MODEL_UNAVAILABLE],
            'limite de débit' => [429, $error('rate_limit_error', 'Rate limited'), AssistantException::OVERLOADED],
            'surcharge' => [529, $error('overloaded_error', 'Overloaded'), AssistantException::OVERLOADED],
            'demande invalide' => [400, $error('invalid_request_error', 'messages: bad'), AssistantException::INVALID_REQUEST],
        ];
    }

    #[DataProvider('errors')]
    public function testApiErrorsBecomeClearMessages(int $status, array $body, string $reason): void
    {
        try {
            $this->client($status, $body)->request('Consigne', [['type' => 'text', 'text' => 'x']], [], 100);
            self::fail('Une erreur était attendue');
        } catch (AssistantException $exception) {
            self::assertSame($reason, $exception->reason);
            self::assertStringNotContainsString('x-api-key', $exception->getMessage());
        }
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function unusableAnswers(): array
    {
        return [
            'refus' => [['stop_reason' => 'refusal', 'text' => ''], AssistantException::REFUSAL],
            'coupée' => [['stop_reason' => 'max_tokens', 'text' => '{"a":'], AssistantException::TRUNCATED],
            'pas du JSON' => [['stop_reason' => 'end_turn', 'text' => 'Bonjour'], AssistantException::INVALID_RESPONSE],
        ];
    }

    #[DataProvider('unusableAnswers')]
    public function testUnusableAnswersAreRefused(array $answer, string $reason): void
    {
        try {
            $this->client(200, $this->message($answer['text'], $answer['stop_reason']))
                ->request('Consigne', [['type' => 'text', 'text' => 'x']], [], 100);
            self::fail('Une erreur était attendue');
        } catch (AssistantException $exception) {
            self::assertSame($reason, $exception->reason);
        }
    }

    public function testAKeyWithAForbiddenCharacterIsRefusedWithoutBeingRepeated(): void
    {
        try {
            $this->client(200, $this->message('{}'), "cle-factice-de-test\u{0007}collé")->request('C', [], [], 100);
            self::fail('Une erreur était attendue');
        } catch (AssistantException $exception) {
            self::assertSame(AssistantException::AUTHENTICATION, $exception->reason);
            self::assertStringNotContainsString('cle-factice-de-test', $exception->getMessage());
            self::assertNull($exception->getPrevious(), 'L\'exception d\'origine recopie la clé : elle ne suit pas');
        }
        self::assertSame([], $this->sent);
    }

    public function testNoKeyMeansNoCall(): void
    {
        try {
            $this->client(200, $this->message('{}'), null)->request('C', [], [], 100);
            self::fail('Une erreur était attendue');
        } catch (AssistantException $exception) {
            self::assertSame(AssistantException::NOT_CONFIGURED, $exception->reason);
        }
        self::assertSame([], $this->sent);
    }
}
