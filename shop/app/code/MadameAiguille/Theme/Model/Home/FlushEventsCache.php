<?php
/**
 * Purge des pages qui affichent les rendez-vous de l'accueil (tag Events::CACHE_TAG) : cache applicatif,
 * cache pleine page intégré et Varnish, par l'événement natif clean_cache_by_tags.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Home;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Indexer\CacheContextFactory;

class FlushEventsCache
{
    public function __construct(
        private readonly CacheContextFactory $cacheContextFactory,
        private readonly EventManager $eventManager,
        private readonly CacheInterface $appCache
    ) {
    }

    public function execute(): void
    {
        $cacheContext = $this->cacheContextFactory->create();
        $cacheContext->registerTags([Events::CACHE_TAG]);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cacheContext]);
        $this->appCache->clean([Events::CACHE_TAG]);
    }
}
