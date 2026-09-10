<?php
/**
 * Statuts de commande propres au parcours Madame Aiguille.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Order;

use Magento\Sales\Model\Order;

class StatusConfig
{
    public const PENDING_PAYMENT = 'madameaiguille_pending_payment';
    public const PAYMENT_RECEIVED = 'madameaiguille_payment_received';
    public const PREPARING = 'madameaiguille_preparing';
    public const SHIPPED = 'madameaiguille_shipped';
    public const READY_FOR_PICKUP = 'madameaiguille_ready_for_pickup';
    public const DELIVERED = 'madameaiguille_delivered';

    /**
     * Return the status label and Magento state for every project status.
     *
     * @return array<string, array{label: string, state: string}>
     */
    public function getAll(): array
    {
        return [
            self::PENDING_PAYMENT => [
                'label' => 'En attente de paiement',
                'state' => Order::STATE_PENDING_PAYMENT,
            ],
            self::PAYMENT_RECEIVED => [
                'label' => 'Paiement reçu',
                'state' => Order::STATE_PROCESSING,
            ],
            self::PREPARING => [
                'label' => 'En préparation',
                'state' => Order::STATE_PROCESSING,
            ],
            self::SHIPPED => [
                'label' => 'Expédiée',
                'state' => Order::STATE_COMPLETE,
            ],
            self::READY_FOR_PICKUP => [
                'label' => 'Prête pour retrait',
                'state' => Order::STATE_PROCESSING,
            ],
            self::DELIVERED => [
                'label' => 'Livrée',
                'state' => Order::STATE_COMPLETE,
            ],
        ];
    }
}
