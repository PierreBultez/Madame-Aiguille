<?php
/**
 * Madame Aiguille — barre de progression vers la livraison offerte
 *
 * Le seuil est une valeur de configuration (Général › Madame Aiguille › Panier) ;
 * 0 masque complètement la barre. Le montant restant est toujours calculé à
 * partir d'un sous-total fourni par le serveur — le quote sur la page panier,
 * la section customer-data dans le mini-panier — jamais depuis un montant lu
 * dans le DOM (plan de développement, lot 4).
 *
 * Ce seuil est aussi celui qui rend le point relais gratuit au checkout
 * (Plugin\Shipping\FreeRelayAboveThreshold, lot 5) : la barre et le tarif
 * ne peuvent pas diverger.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Cart;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class FreeShipping implements ArgumentInterface
{
    private const XML_PATH_THRESHOLD = 'madameaiguille/cart/free_shipping_threshold';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly PriceCurrencyInterface $priceCurrency
    ) {
    }

    /**
     * Seuil configuré, 0.0 quand la barre est désactivée.
     */
    public function getThreshold(): float
    {
        return max(0.0, (float) $this->scopeConfig->getValue(self::XML_PATH_THRESHOLD, ScopeInterface::SCOPE_STORE));
    }

    public function isEnabled(): bool
    {
        return $this->getThreshold() > 0.0;
    }

    /**
     * Montant restant à ajouter, 0.0 une fois le seuil franchi.
     */
    public function getRemaining(float $subtotal): float
    {
        return max(0.0, round($this->getThreshold() - $subtotal, 2));
    }

    public function isReached(float $subtotal): bool
    {
        return $this->isEnabled() && $this->getRemaining($subtotal) <= 0.0;
    }

    /**
     * Progression en pourcentage entier, bornée à 0–100 : ne sert qu'à la largeur
     * de la barre, jamais à un libellé (« 12,00 € » est actionnable, « 75 % » ne l'est pas).
     */
    public function getProgress(float $subtotal): int
    {
        $threshold = $this->getThreshold();

        if ($threshold <= 0.0) {
            return 0;
        }

        return (int) min(100, max(0, round($subtotal / $threshold * 100)));
    }

    /**
     * Phrase lue en premier dans le tiroir et sur la page panier.
     */
    public function getMessage(float $subtotal): ?Phrase
    {
        if (!$this->isEnabled()) {
            return null;
        }

        if ($this->isReached($subtotal)) {
            return __('Livraison offerte : c’est acquis.');
        }

        return __('Plus que %1 pour la livraison offerte', $this->formatPrice($this->getRemaining($subtotal)));
    }

    /**
     * « 42,00 € / 49,00 € » — repère chiffré sous la barre.
     */
    public function getProgressLabel(float $subtotal): ?Phrase
    {
        if (!$this->isEnabled()) {
            return null;
        }

        return __('%1 / %2', $this->formatPrice($subtotal), $this->formatPrice($this->getThreshold()));
    }

    /**
     * Annonce du bandeau d'en-tête : « Livraison offerte dès 49,00 € ».
     */
    public function getThresholdLabel(): ?Phrase
    {
        return $this->isEnabled()
            ? __('Livraison offerte dès %1', $this->formatPrice($this->getThreshold()))
            : null;
    }

    /**
     * État complet destiné au mini-panier : le tiroir n'affiche que ces valeurs,
     * il ne recalcule rien à partir des prix déjà rendus.
     *
     * @return array{enabled: bool, reached: bool, progress: int, message: string, progress_label: string}
     */
    public function getState(float $subtotal): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'reached' => $this->isReached($subtotal),
            'progress' => $this->getProgress($subtotal),
            'message' => (string) $this->getMessage($subtotal),
            'progress_label' => (string) $this->getProgressLabel($subtotal),
        ];
    }

    public function formatPrice(float $amount): string
    {
        return $this->priceCurrency->format($amount, false);
    }
}
