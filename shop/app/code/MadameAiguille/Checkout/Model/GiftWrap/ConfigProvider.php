<?php
/**
 * Expose au tunnel (window.checkoutConfig.madameaiguilleGiftWrap) l'option d'emballage cadeau
 * et le choix déjà enregistré sur le panier.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\GiftWrap;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class ConfigProvider implements ConfigProviderInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly Session $checkoutSession,
        private readonly PriceCurrencyInterface $priceCurrency
    ) {
    }

    public function getConfig(): array
    {
        $quote = $this->checkoutSession->getQuote();
        $storeId = (int) $quote->getStoreId();
        if (!$this->config->isEnabled($storeId) || $quote->isVirtual()) {
            return ['madameaiguilleGiftWrap' => ['enabled' => false]];
        }

        return [
            'madameaiguilleGiftWrap' => [
                'enabled' => true,
                'label' => $this->config->getLabel($storeId),
                'description' => $this->config->getDescription($storeId),
                'price' => $this->priceCurrency->convertAndFormat($this->config->getPrice($storeId), false),
                'requested' => (bool) $quote->getData(Config::QUOTE_FLAG),
                'totalCode' => Config::TOTAL_CODE,
            ],
        ];
    }
}
