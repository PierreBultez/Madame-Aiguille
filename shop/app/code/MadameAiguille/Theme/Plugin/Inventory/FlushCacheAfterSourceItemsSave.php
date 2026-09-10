<?php
/**
 * Madame Aiguille — après l'enregistrement d'articles de source MSI (saisie
 * de stock en admin, déduction à l'expédition, import), on purge les caches
 * des produits concernés : Magento ne le fait que si le statut « en stock »
 * change, pas quand seule la quantité varie.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Plugin\Inventory;

use MadameAiguille\Theme\Model\Cache\FlushProductCacheBySkus;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;

class FlushCacheAfterSourceItemsSave
{
    public function __construct(
        private readonly FlushProductCacheBySkus $flushProductCache
    ) {
    }

    /**
     * @param SourceItemInterface[] $sourceItems
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(SourceItemsSaveInterface $subject, $result, array $sourceItems): void
    {
        $this->flushProductCache->execute(
            array_map(static fn(SourceItemInterface $item): string => (string) $item->getSku(), $sourceItems)
        );
    }
}
