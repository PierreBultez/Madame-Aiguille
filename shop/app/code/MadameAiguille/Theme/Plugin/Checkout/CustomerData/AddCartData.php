<?php
/**
 * Madame Aiguille — données du mini-panier : plafond de stock et franco
 *
 * La section customer-data `cart` ne transporte ni le stock vendable d'une
 * ligne, ni l'état du franco de port. Le tiroir en a besoin pour plafonner ses
 * boutons de quantité et afficher sa barre de progression sans jamais relire un
 * prix dans le DOM (plan de développement, lot 4).
 *
 * Tout est calculé ici, côté serveur, à partir du quote de la session ; le
 * template Alpine ne fait qu'afficher les valeurs reçues.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Plugin\Checkout\CustomerData;

use MadameAiguille\Theme\ViewModel\Cart\FreeShipping;
use MadameAiguille\Theme\ViewModel\Cart\Stock;
use Magento\Checkout\CustomerData\Cart as CartSection;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class AddCartData
{
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly Stock $stock,
        private readonly FreeShipping $freeShipping
    ) {
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    public function afterGetSectionData(CartSection $subject, array $result): array
    {
        $result['free_shipping'] = $this->freeShipping->getState((float) ($result['subtotalAmount'] ?? 0));

        if (empty($result['items']) || !is_array($result['items'])) {
            return $result;
        }

        $itemsById = $this->getQuoteItemsById();

        foreach ($result['items'] as $index => $item) {
            $quoteItem = $itemsById[(int) ($item['item_id'] ?? 0)] ?? null;

            if ($quoteItem === null) {
                continue;
            }

            $maxQty = $this->stock->getMaxQty($quoteItem);
            $hint = $this->stock->getQtyHint($quoteItem);

            $result['items'][$index]['max_qty'] = $maxQty;
            $result['items'][$index]['qty_hint'] = $hint === null ? '' : (string) $hint;
            $result['items'][$index]['is_available'] = $this->stock->isAvailable($quoteItem);
        }

        return $result;
    }

    /**
     * @return array<int, AbstractItem>
     */
    private function getQuoteItemsById(): array
    {
        $items = [];

        foreach ($this->checkoutSession->getQuote()->getAllVisibleItems() as $item) {
            $items[(int) $item->getId()] = $item;
        }

        return $items;
    }
}
