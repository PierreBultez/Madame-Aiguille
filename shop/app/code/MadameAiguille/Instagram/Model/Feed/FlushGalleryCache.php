<?php
/**
 * Purge des pages qui affichent la galerie (tag GALLERY_TAG) : cache applicatif, FPC intégré et Varnish.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Feed;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Indexer\CacheContextFactory;

class FlushGalleryCache
{
    public const GALLERY_TAG = 'madameaiguille_home_instagram';

    public function __construct(
        private readonly CacheContextFactory $cacheContextFactory,
        private readonly EventManager $eventManager,
        private readonly CacheInterface $appCache
    ) {
    }

    public function execute(): void
    {
        $cacheContext = $this->cacheContextFactory->create();
        $cacheContext->registerTags([self::GALLERY_TAG]);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cacheContext]);
        $this->appCache->clean([self::GALLERY_TAG]);
    }
}
