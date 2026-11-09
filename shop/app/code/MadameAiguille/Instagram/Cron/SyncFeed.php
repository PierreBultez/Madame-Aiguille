<?php
/**
 * Toutes les trois heures : galerie de l'accueil à jour. Un échec est journalisé, la galerie précédente reste.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Cron;

use MadameAiguille\Instagram\Model\Api\InstagramException;
use MadameAiguille\Instagram\Model\Feed\Synchronizer;
use Psr\Log\LoggerInterface;

class SyncFeed
{
    public function __construct(
        private readonly Synchronizer $synchronizer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        try {
            $this->synchronizer->execute();
        } catch (InstagramException $exception) {
            $this->logger->warning('Galerie Instagram : ' . $exception->getMessage());
        }
    }
}
