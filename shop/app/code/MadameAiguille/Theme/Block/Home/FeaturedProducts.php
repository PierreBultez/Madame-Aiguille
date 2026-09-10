<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Block\Home;

use MadameAiguille\Theme\ViewModel\Home\Products;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogWidget\Block\Product\ProductsList;

/** Sélection éditoriale sur le widget catalogue natif. */
class FeaturedProducts extends ProductsList
{
    public function createCollection(): Collection
    {
        return parent::createCollection()->addAttributeToSelect('home_featured');
    }

    public function getCacheLifetime(): ?int
    {
        return null;
    }

    public function getIdentities(): array
    {
        return [Products::CACHE_TAG];
    }
}
