<?php
/**
 * Madame Aiguille — purge des caches produit à partir de SKU
 *
 * Les badges « Plus que N exemplaires » dépendent de la quantité vendable,
 * mais Magento n'invalide les caches (HTML des vignettes, cache pleine page)
 * que lorsqu'un produit change de statut de stock. Ce service purge les tags
 * produit — enfants et parents configurables — dès qu'une quantité bouge, via
 * l'événement clean_cache_by_tags (cache applicatif, FPC intégré, Varnish).
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Cache;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Indexer\CacheContextFactory;
use MadameAiguille\Theme\ViewModel\Home\Products as HomeProducts;

class FlushProductCacheBySkus
{
    public function __construct(
        private readonly ProductResource $productResource,
        private readonly ConfigurableResource $configurableResource,
        private readonly CacheContextFactory $cacheContextFactory,
        private readonly EventManager $eventManager,
        private readonly CacheInterface $appCache
    ) {
    }

    /**
     * @param string[] $skus
     */
    public function execute(array $skus): void
    {
        $skus = array_values(array_unique(array_filter(array_map('strval', $skus))));

        if ($skus === []) {
            return;
        }

        $productIds = array_map('intval', $this->productResource->getProductsIdsBySkus($skus));

        if ($productIds === []) {
            return;
        }

        foreach ($productIds as $productId) {
            foreach ($this->configurableResource->getParentIdsByChild($productId) as $parentId) {
                $productIds[] = (int) $parentId;
            }
        }

        $this->flushByProductIds(array_values(array_unique($productIds)));
    }

    /**
     * @param int[] $productIds
     */
    public function flushByProductIds(array $productIds): void
    {
        if ($productIds === []) {
            return;
        }

        $cacheContext = $this->cacheContextFactory->create();
        $cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
        $cacheContext->registerTags([HomeProducts::CACHE_TAG]);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cacheContext]);

        $tags = $cacheContext->getIdentities();

        if ($tags) {
            $this->appCache->clean($tags);
        }
    }
}
