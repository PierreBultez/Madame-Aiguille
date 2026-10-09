<?php
/**
 * Expose au tunnel (window.checkoutConfig.madameaiguilleRelayPoint) ce qu'il faut au widget Mondial Relay.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\RelayPoint;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    private const XML_PATH_BRAND_CODE = 'madameaiguille/mondial_relay/brand_code';

    /** Mondial Relay attend un code enseigne de huit caractères, complété par des espaces (« BDTEST␣␣ »). */
    private const BRAND_CODE_LENGTH = 8;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getConfig(): array
    {
        $brand = trim((string) $this->scopeConfig->getValue(self::XML_PATH_BRAND_CODE, ScopeInterface::SCOPE_WEBSITE));

        return [
            'madameaiguilleRelayPoint' => [
                'carrierCode' => Assignment::CARRIER_CODE,
                'brand' => str_pad($brand, self::BRAND_CODE_LENGTH),
                // Monaco est desservi par le réseau français
                'searchCountries' => ['MC' => 'FR'],
            ],
        ];
    }
}
