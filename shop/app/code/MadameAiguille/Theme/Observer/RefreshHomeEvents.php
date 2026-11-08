<?php
/**
 * Enregistrement de Général › Madame Aiguille : les rendez-vous de l'accueil se voient tout de suite,
 * sans vider les caches à la main.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Observer;

use MadameAiguille\Theme\Model\Home\Events;
use MadameAiguille\Theme\Model\Home\FlushEventsCache;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RefreshHomeEvents implements ObserverInterface
{
    public function __construct(
        private readonly FlushEventsCache $flushEventsCache
    ) {
    }

    public function execute(Observer $observer): void
    {
        $changed = (array) $observer->getEvent()->getData('changed_paths');
        if ($changed === [] || in_array(Events::XML_PATH, $changed, true)) {
            $this->flushEventsCache->execute();
        }
    }
}
