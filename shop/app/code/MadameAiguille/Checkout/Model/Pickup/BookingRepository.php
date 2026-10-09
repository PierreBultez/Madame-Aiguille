<?php
/**
 * Places réservées sur les créneaux de retrait.
 *
 * Une ligne par place : la clé unique (slot, seat) garantit qu'un créneau ne dépasse jamais sa capacité,
 * même si deux clientes valident leur commande au même instant.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Pickup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\DuplicateException;

class BookingRepository
{
    private const TABLE = 'madameaiguille_pickup_booking';

    public function __construct(
        private readonly ResourceConnection $resource
    ) {
    }

    /**
     * @return array<string, int> nombre de places prises par créneau, entre deux créneaux inclus
     */
    public function countBySlot(string $from, string $to): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->table(), ['slot', 'taken' => new \Zend_Db_Expr('COUNT(*)')])
            ->where('slot >= ?', $from)
            ->where('slot <= ?', $to)
            ->group('slot');

        return array_map('intval', $connection->fetchPairs($select));
    }

    /**
     * Prend une place sur le créneau pour ce panier. Rejouable : un panier garde sa place.
     */
    public function reserve(string $slot, int $capacity, int $quoteId): bool
    {
        $connection = $this->resource->getConnection();
        $connection->delete($this->table(), ['quote_id = ?' => $quoteId, 'slot <> ?' => $slot, 'order_id IS NULL']);

        $alreadyHeld = $connection->fetchOne(
            $connection->select()->from($this->table(), 'booking_id')
                ->where('quote_id = ?', $quoteId)->where('slot = ?', $slot)
        );
        if ($alreadyHeld) {
            return true;
        }

        for ($seat = 1; $seat <= $capacity; $seat++) {
            try {
                $connection->insert($this->table(), ['slot' => $slot, 'seat' => $seat, 'quote_id' => $quoteId]);
                return true;
            } catch (DuplicateException) {
                continue;
            }
        }

        return false;
    }

    public function attachOrder(int $quoteId, int $orderId): void
    {
        $this->resource->getConnection()->update(
            $this->table(),
            ['order_id' => $orderId],
            ['quote_id = ?' => $quoteId, 'order_id IS NULL']
        );
    }

    public function releaseQuote(int $quoteId): void
    {
        $this->resource->getConnection()->delete($this->table(), ['quote_id = ?' => $quoteId, 'order_id IS NULL']);
    }

    public function releaseOrder(int $orderId): void
    {
        $this->resource->getConnection()->delete($this->table(), ['order_id = ?' => $orderId]);
    }

    private function table(): string
    {
        return $this->resource->getTableName(self::TABLE);
    }
}
