<?php
/**
 * Madame Aiguille — options d'affichage du panier réglables sans code
 *
 * Décision de Pierre du 10/09/2026 : le champ code promo reste masqué tant
 * qu'aucune règle de panier n'existe. Le bloc natif reste déclaré en layout ;
 * seul son rendu est conditionné, pour que l'activation soit une case à cocher
 * dans l'admin et non une modification de layout.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Cart;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class Options implements ArgumentInterface
{
    private const XML_PATH_SHOW_COUPON = 'madameaiguille/cart/show_coupon';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isCouponVisible(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SHOW_COUPON, ScopeInterface::SCOPE_STORE);
    }
}
