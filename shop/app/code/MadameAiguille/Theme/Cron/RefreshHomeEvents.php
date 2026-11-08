<?php
/**
 * Chaque nuit, l'accueil oublie les rendez-vous passés : le contenu ne change pas en base, seule la date avance.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Cron;

use MadameAiguille\Theme\Model\Home\FlushEventsCache;

class RefreshHomeEvents
{
    public function __construct(
        private readonly FlushEventsCache $flushEventsCache
    ) {
    }

    public function execute(): void
    {
        $this->flushEventsCache->execute();
    }
}
