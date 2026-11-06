<?php
/**
 * Journal minimal de chaque appel (`var/log/madameaiguille_assistant_ia.log`) : date, utilisateur, action, modèle,
 * jetons, durée et issue. **Jamais le contenu** : ni la fiche, ni les réponses de Céline, ni le texte produit.
 * C'est la source du suivi de consommation mensuel (guide d'exploitation §3).
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model;

use MadameAiguille\AiAssistant\Model\Claude\Response;
use MadameAiguille\AiAssistant\Model\Claude\SdkClient;
use Psr\Log\LoggerInterface;

class UsageLogger
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function log(string $action, string $user, int $durationMs, ?Response $response, ?string $error = null): void
    {
        $this->logger->info('appel', [
            'action' => $action,
            'utilisateur' => $user,
            'modele' => $response->model ?? SdkClient::MODEL,
            'entree' => $response->inputTokens ?? 0,
            'sortie' => $response->outputTokens ?? 0,
            'cache_lu' => $response->cacheReadTokens ?? 0,
            'cache_ecrit' => $response->cacheWriteTokens ?? 0,
            'duree_ms' => $durationMs,
            'issue' => $error ?? 'ok',
        ]);
    }
}
