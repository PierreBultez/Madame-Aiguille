<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Block\Home;

use MadameAiguille\Theme\ViewModel\Home\Products;
use Magento\Catalog\Block\Product\Widget\NewWidget;

/** Widget natif : règles de dates conservées, cache porté par la page entière. */
class NewProducts extends NewWidget
{
    public function getCacheLifetime(): ?int
    {
        return null;
    }

    public function getIdentities(): array
    {
        return [Products::CACHE_TAG];
    }
}
