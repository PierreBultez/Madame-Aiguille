<?php
/**
 * Variables de l'email « Prête pour retrait » : rendez-vous choisi dans le tunnel, lieu et itinéraire.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Pickup;

use MadameAiguille\Checkout\Plugin\Checkout\AssignPickupSlot;
use MadameAiguille\Theme\Model\Order\Email\TemplateVariablesProviderInterface;
use Magento\Sales\Model\Order;

class EmailVariables implements TemplateVariablesProviderInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly SlotFormatter $formatter
    ) {
    }

    public function getVariables(Order $order): array
    {
        $slot = (string) $order->getShippingAddress()?->getData(AssignPickupSlot::ADDRESS_FIELD);
        if ($slot === '') {
            return [];
        }

        return [
            'pickup_slot' => $this->formatter->format($slot),
            'pickup_location_name' => $this->config->getLocationName(),
            'pickup_location_address' => $this->config->getLocationAddress(),
            'pickup_directions_url' => $this->config->getDirectionsUrl(),
        ];
    }
}
