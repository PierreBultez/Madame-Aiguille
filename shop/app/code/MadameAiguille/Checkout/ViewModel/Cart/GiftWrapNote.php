<?php
/**
 * Panier : l'option « Emballage et message cadeau » se choisit à l'étape Livraison du tunnel (lot 11).
 * Le panier n'a plus de bouton « Options cadeau » ; une ligne y renvoie, tant que l'option est proposée.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\ViewModel\Cart;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class GiftWrapNote implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Phrase à afficher, ou chaîne vide si l'option n'est pas proposée.
     */
    public function getNote(): string
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        if (!$this->config->isEnabled($storeId)) {
            return '';
        }

        return (string) __('%1: to choose at the Delivery step.', $this->config->getLabel($storeId));
    }
}
