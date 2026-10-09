<?php
/**
 * Emballage cadeau (Général › Madame Aiguille › Emballage cadeau) : option payante de la commande.
 * Magento Open Source n'a pas d'emballage cadeau natif (le Gift Wrapping est réservé à Adobe Commerce).
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\GiftWrap;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const TOTAL_CODE = 'madameaiguille_gift_wrap';
    /** Choix de la cliente, sur le panier */
    public const QUOTE_FLAG = 'madameaiguille_gift_wrap';
    /** Montants, sur l'adresse du panier, la commande, la facture et l'avoir */
    public const AMOUNT = 'madameaiguille_gift_wrap_amount';
    public const BASE_AMOUNT = 'base_madameaiguille_gift_wrap_amount';

    private const PREFIX = 'madameaiguille/gift_wrap/';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::PREFIX . 'enabled', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Prix en devise de base.
     */
    public function getPrice(?int $storeId = null): float
    {
        return max(0.0, round((float) $this->scopeConfig->getValue(self::PREFIX . 'price', ScopeInterface::SCOPE_STORE, $storeId), 2));
    }

    public function getLabel(?int $storeId = null): string
    {
        return trim((string) $this->scopeConfig->getValue(self::PREFIX . 'label', ScopeInterface::SCOPE_STORE, $storeId))
            ?: (string) __('Gift wrapping');
    }

    public function getDescription(?int $storeId = null): string
    {
        return trim((string) $this->scopeConfig->getValue(self::PREFIX . 'description', ScopeInterface::SCOPE_STORE, $storeId));
    }
}
