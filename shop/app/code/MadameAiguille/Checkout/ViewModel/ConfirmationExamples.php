<?php
/** Commandes non persistées pour contrôler les cartes réelles dans le styleguide. */
declare(strict_types=1);

namespace MadameAiguille\Checkout\ViewModel;

use MadameAiguille\Checkout\Plugin\Checkout\AssignPickupSlot;
use MadameAiguille\Checkout\Model\RelayPoint\Assignment;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\AddressFactory;

class ConfirmationExamples implements ArgumentInterface
{
    public function __construct(
        private readonly OrderFactory $orderFactory,
        private readonly AddressFactory $addressFactory,
        private readonly Confirmation $confirmation
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function getDeliveries(): array
    {
        $pickup = $this->orderFactory->create();
        $pickup->setShippingAddress($this->addressFactory->create()->setData([
            'address_type' => 'shipping',
            AssignPickupSlot::ADDRESS_FIELD => '2026-10-15 10:00',
        ]));
        $relay = $this->orderFactory->create();
        $relay->setShippingAddress($this->addressFactory->create()->setData([
            'address_type' => 'shipping',
            Assignment::ADDRESS_FIELD => 'FR-DEMO',
            'company' => 'Point relais de démonstration',
            'street' => '12 rue des Créations',
            'postcode' => '37000',
            'city' => 'Tours',
        ]));

        return [
            $this->confirmation->getDeliveryForOrder($pickup),
            $this->confirmation->getDeliveryForOrder($relay),
        ];
    }
}
