<?php
/**
 * Expose au tunnel (window.checkoutConfig.madameaiguillePickup) le lieu de retrait.
 * Les créneaux, eux, sont lus à la demande par l'API pickup-slots pour rester à jour.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Pickup;

use MadameAiguille\Checkout\Model\Carrier\Pickup;
use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    public function __construct(
        private readonly Config $config
    ) {
    }

    public function getConfig(): array
    {
        return [
            'madameaiguillePickup' => [
                'carrierCode' => Pickup::CODE,
                'location' => [
                    'name' => $this->config->getLocationName(),
                    'address' => $this->config->getLocationAddress(),
                    'directionsUrl' => $this->config->getDirectionsUrl(),
                    'mapImageUrl' => $this->config->getMapImageUrl(),
                ],
            ],
        ];
    }
}
