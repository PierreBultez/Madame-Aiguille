<?php
/**
 * Produits expédiables sans poids : un poids absent ou nul fausse la grille au poids sans aucune erreur visible
 * (plan de tests §2). Seuls les produits simples portent un poids — un configurable est expédié via son enfant.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Catalog;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class MissingWeightFinder
{
    public function __construct(
        private readonly CollectionFactory $collectionFactory
    ) {
    }

    /**
     * @return list<array{sku: string, name: string}>
     */
    public function find(): array
    {
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('type_id', Type::TYPE_SIMPLE)
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter('weight', [['null' => true], ['lteq' => 0]], 'left')
            ->setOrder('sku', 'ASC');

        $products = [];
        foreach ($collection as $product) {
            $products[] = ['sku' => (string) $product->getSku(), 'name' => (string) $product->getName()];
        }

        return $products;
    }
}
