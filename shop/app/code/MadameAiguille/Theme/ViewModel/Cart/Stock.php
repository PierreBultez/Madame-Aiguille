<?php
/**
 * Madame Aiguille — plafond de quantité et disponibilité d'une ligne de panier
 *
 * Le renderer natif borne la quantité sur `max_sale_qty` (10 000 par défaut) :
 * une cliente peut donc saisir six exemplaires d'une série où il en reste trois
 * et ne l'apprendre qu'en validant. Ce ViewModel ramène le plafond au stock
 * réellement vendable, exactement comme le sélecteur de la fiche produit
 * (documentation-theme.md §10), en réutilisant LimitedSeries — seule autorité
 * du projet sur le stock et les séries limitées.
 *
 * Sur un configurable, le stock est celui de l'enfant réellement commandé
 * (option de quote `simple_product`), pas la somme des tailles.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Cart;

use MadameAiguille\Theme\ViewModel\Product\LimitedSeries;
use Magento\Catalog\Model\Product;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Stock implements ArgumentInterface
{
    public function __construct(
        private readonly LimitedSeries $limitedSeries
    ) {
    }

    /**
     * Produit qui porte le stock de la ligne : l'enfant simple d'un configurable,
     * sinon le produit de la ligne.
     */
    public function getStockProduct(AbstractItem $item): ?Product
    {
        if (method_exists($item, 'getOptionByCode')) {
            $option = $item->getOptionByCode('simple_product');
            $child = $option ? $option->getProduct() : null;

            if ($child instanceof Product) {
                return $child;
            }
        }

        $product = $item->getProduct();

        return $product instanceof Product ? $product : null;
    }

    /**
     * Plafond du sélecteur de quantité ; null quand le stock n'est pas géré.
     */
    public function getMaxQty(AbstractItem $item): ?int
    {
        $product = $this->getStockProduct($item);

        if ($product === null) {
            return null;
        }

        $max = $this->limitedSeries->getMaxQty($product);

        return $max === null ? null : max(0, $max);
    }

    /**
     * La ligne peut-elle encore être commandée telle quelle ? Faux dès qu'un
     * produit a été désactivé, épuisé ou que le stock est passé sous la
     * quantité déjà présente dans le panier.
     */
    public function isAvailable(AbstractItem $item): bool
    {
        $product = $this->getStockProduct($item);

        if ($product === null || !$product->isSalable()) {
            return false;
        }

        $max = $this->getMaxQty($item);

        return $max === null || $max >= (int) ceil((float) $item->getQty());
    }

    /**
     * Aide affichée sous le sélecteur : plafond atteint, ou stock restant.
     * Retourne null quand il n'y a rien d'utile à dire.
     */
    public function getQtyHint(AbstractItem $item): ?Phrase
    {
        $max = $this->getMaxQty($item);

        if ($max === null) {
            return null;
        }

        $product = $this->getStockProduct($item);
        $isLimited = $product !== null && $this->limitedSeries->isLimitedSeries($product);

        if ((int) ceil((float) $item->getQty()) >= $max) {
            return __('Stock maximum atteint');
        }

        return $isLimited
            ? __('Quantité plafonnée à %1 — le stock restant de la série.', $max)
            : __('Quantité plafonnée à %1 — le stock disponible.', $max);
    }

    /**
     * Mention de rareté reprise de la fiche produit (« Plus que 2 exemplaires »),
     * pour que le plafond ne soit pas une surprise au moment de valider.
     */
    public function getScarcityLabel(AbstractItem $item): ?Phrase
    {
        $product = $this->getStockProduct($item);

        if ($product === null || !$this->limitedSeries->isScarce($product)) {
            return null;
        }

        return $this->limitedSeries->getScarcityLabel($product);
    }

    /**
     * Mention de série limitée de la ligne (« Série limitée — 10 pièces »).
     */
    public function getSeriesLabel(AbstractItem $item): ?Phrase
    {
        $product = $this->getStockProduct($item);

        return $product === null ? null : $this->limitedSeries->getSeriesLabel($product);
    }
}
