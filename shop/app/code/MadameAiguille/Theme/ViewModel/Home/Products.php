<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Home;

use MadameAiguille\Theme\ViewModel\Product\LimitedSeries;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Products implements ArgumentInterface
{
    public const CACHE_TAG = 'madameaiguille_home_products';

    public function __construct(private readonly LimitedSeries $limitedSeries)
    {
    }

    /** @return Product[] */
    public function getItems(Collection $collection, int $limit = 4, ?string $requiredAttribute = null): array
    {
        // Retirer les limites des deux widgets AVANT chargement : un épuisé ne
        // doit pas consommer une place. Le petit catalogue reste trié par le natif.
        $collection->setPageSize(0)->setCurPage(1)
            ->addAttributeToSelect(['serie_limitee', 'taille_serie', 'news_from_date', 'news_to_date']);
        $collection->getSelect()->reset(Select::LIMIT_COUNT)->reset(Select::LIMIT_OFFSET);

        return $this->selectAvailable($collection, $limit, $requiredAttribute);
    }

    /** @param iterable<Product> $products
     *  @return Product[]
     */
    public function selectAvailable(iterable $products, int $limit, ?string $requiredAttribute = null): array
    {
        $items = [];
        if ($limit < 1) {
            return $items;
        }
        foreach ($products as $product) {
            if ($requiredAttribute !== null && !(bool) $product->getData($requiredAttribute)) {
                continue;
            }
            if ($this->limitedSeries->isSoldOut($product)) {
                continue;
            }
            $items[] = $product;
            if (count($items) === $limit) {
                break;
            }
        }
        return $items;
    }
}
