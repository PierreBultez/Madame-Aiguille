<?php
/**
 * Réponse d'un appel, réduite à ce dont le module a besoin : le JSON imposé par le schéma et la consommation.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model\Claude;

class Response
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public readonly array $data,
        public readonly string $model,
        public readonly int $inputTokens,
        public readonly int $outputTokens,
        public readonly int $cacheReadTokens = 0,
        public readonly int $cacheWriteTokens = 0
    ) {
    }
}
