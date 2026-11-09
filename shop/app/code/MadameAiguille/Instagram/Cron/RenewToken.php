<?php
/**
 * Chaque nuit : jeton Instagram renouvelé s'il a une semaine. Un échec est journalisé ; env:check le signale
 * avant l'expiration.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Cron;

use MadameAiguille\Instagram\Model\Api\InstagramException;
use MadameAiguille\Instagram\Model\Feed\TokenRenewer;
use Psr\Log\LoggerInterface;

class RenewToken
{
    public function __construct(
        private readonly TokenRenewer $tokenRenewer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        try {
            if ($this->tokenRenewer->execute()) {
                $this->logger->info('Galerie Instagram : jeton renouvelé.');
            }
        } catch (InstagramException $exception) {
            $this->logger->warning('Galerie Instagram : renouvellement du jeton impossible. ' . $exception->getMessage());
        }
    }
}
