<?php
/** Détails de livraison de la dernière commande, sans exposer une commande par identifiant d'URL. */

declare(strict_types=1);

namespace MadameAiguille\Checkout\ViewModel;

use MadameAiguille\Checkout\Model\Pickup\EmailVariables;
use MadameAiguille\Checkout\Model\RelayPoint\Assignment;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;

class Confirmation implements ArgumentInterface
{
    public function __construct(
        private readonly Session $checkoutSession,
        private readonly EmailVariables $pickupVariables
    ) {
    }

    /** @return array<string, mixed> */
    public function getDelivery(): array
    {
        return $this->getDeliveryForOrder($this->checkoutSession->getLastRealOrder());
    }

    /** @return array<string, mixed> */
    public function getDeliveryForOrder(Order $order): array
    {
        $pickup = $this->pickupVariables->getVariables($order);
        if ($pickup !== []) {
            return ['type' => 'pickup'] + $pickup;
        }
        $address = $order->getShippingAddress();
        if (!$address || !$address->getData(Assignment::ADDRESS_FIELD)) {
            return [];
        }

        return [
            'type' => 'relay',
            'name' => (string) $address->getCompany(),
            'street' => $address->getStreet(),
            'city' => trim($address->getPostcode() . ' ' . $address->getCity()),
        ];
    }
}
