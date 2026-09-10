<?php
/**
 * Madame Aiguille — état du panier pour l'en-tête et la barre de franco
 *
 * Le layout du lot 3 retire `page.main.title` : le titre « Mon panier — 3
 * articles » est donc rendu par le gabarit du panier, qui a besoin du nombre
 * d'articles et du sous-total. Les deux viennent du quote de la session, seule
 * source de vérité — la barre de progression ne doit jamais être alimentée par
 * un montant relu dans le DOM (plan de développement, lot 4).
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Cart;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Summary implements ArgumentInterface
{
    public function __construct(
        private readonly CheckoutSession $checkoutSession
    ) {
    }

    /**
     * Nombre d'exemplaires, toutes lignes confondues : « 3 articles » compte
     * deux exemplaires d'une même création comme deux articles.
     */
    public function getItemsQty(): int
    {
        return (int) round((float) $this->checkoutSession->getQuote()->getItemsQty());
    }

    /**
     * Nombre de lignes distinctes.
     */
    public function getItemsCount(): int
    {
        return count($this->checkoutSession->getQuote()->getAllVisibleItems());
    }

    /**
     * Sous-total du quote, base de calcul du franco.
     */
    public function getSubtotal(): float
    {
        $totals = $this->checkoutSession->getQuote()->getTotals();

        return isset($totals['subtotal']) ? (float) $totals['subtotal']->getValue() : 0.0;
    }

    /**
     * « Mon panier » seul quand le compte n'apporte rien, « — 3 articles » sinon.
     */
    public function getItemsLabel(): ?Phrase
    {
        $qty = $this->getItemsQty();

        if ($qty <= 0) {
            return null;
        }

        return $qty === 1 ? __('1 article') : __('%1 articles', $qty);
    }
}
