<?php
/**
 * Madame Aiguille — après une réservation MSI (commande passée, annulée,
 * remboursée, expédiée…), la quantité vendable change sans que la source
 * bouge : on purge les caches des produits concernés pour que les badges
 * de rareté restent exacts.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Plugin\Inventory;

use MadameAiguille\Theme\Model\Cache\FlushProductCacheBySkus;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;

class FlushCacheAfterReservations
{
    public function __construct(
        private readonly FlushProductCacheBySkus $flushProductCache
    ) {
    }

    /**
     * @param ItemToSellInterface[] $items
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        PlaceReservationsForSalesEventInterface $subject,
        $result,
        array $items,
        SalesChannelInterface $salesChannel,
        SalesEventInterface $salesEvent
    ): void {
        $this->flushProductCache->execute(
            array_map(static fn(ItemToSellInterface $item): string => $item->getSku(), $items)
        );
    }
}
