<?php
/**
 * Un constat du contrôle d'environnement : un sujet, un niveau et une phrase lisible, sans jamais de secret.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Environment;

class Finding
{
    public const OK = 'ok';
    public const WARNING = 'avertissement';
    public const ERROR = 'erreur';

    public function __construct(
        public readonly string $topic,
        public readonly string $level,
        public readonly string $message
    ) {
    }

    public function isError(): bool
    {
        return $this->level === self::ERROR;
    }
}
