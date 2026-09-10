<?php
/**
 * Madame Aiguille — règles d'affichage des séries limitées
 *
 * Centralise tout ce qu'une vignette ou une fiche produit a besoin de savoir
 * sur la rareté d'une création : mention « Série limitée — N pièces », badge
 * unique par ordre de priorité (épuisé > rareté > nouveauté > série limitée),
 * stock restant, plafond de quantité, lien vers le formulaire de contact.
 * Les templates ne font qu'afficher ce que cette classe a calculé
 * (guide-bonnes-pratiques-hyva.md §3, Design System §6.3 et §6.4).
 *
 * Le stock est lu via les API MSI (quantité vendable) ; les résultats sont
 * mémorisés par produit pour la durée de la requête.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Product;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class LimitedSeries implements ArgumentInterface
{
    public const BADGE_SOLDOUT = 'soldout';
    public const BADGE_SCARCE = 'scarce';
    public const BADGE_NEW = 'new';
    public const BADGE_LIMITED = 'limited';

    private const XML_PATH_SCARCITY_THRESHOLD = 'madameaiguille/catalog/scarcity_threshold';
    private const XML_PATH_PRICE_NOTE = 'madameaiguille/catalog/price_note';

    /** Classes CSS des badges (components/badge.css) */
    private const BADGE_CSS = [
        self::BADGE_SOLDOUT => 'badge badge-soldout',
        self::BADGE_SCARCE => 'badge badge-scarce',
        self::BADGE_NEW => 'badge badge-new',
        self::BADGE_LIMITED => 'badge badge-limited',
    ];

    /** @var array<int, int|null> quantité vendable par identifiant produit */
    private array $remainingQty = [];

    /** @var array<int, array<int, int>> quantité vendable des enfants, par identifiant de configurable */
    private array $childrenQty = [];

    private ?int $stockId = null;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly TimezoneInterface $timezone,
        private readonly UrlInterface $urlBuilder,
        private readonly StoreManagerInterface $storeManager,
        private readonly StockResolverInterface $stockResolver,
        private readonly GetProductSalableQtyInterface $getProductSalableQty,
        private readonly GetStockItemConfigurationInterface $getStockItemConfiguration,
        private readonly IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowed
    ) {
    }

    // ── Attributs métier ─────────────────────────────────────────────────

    public function isLimitedSeries(Product $product): bool
    {
        return (bool) $product->getData('serie_limitee');
    }

    /**
     * Nombre de pièces de la série, null si non renseigné.
     */
    public function getSeriesSize(Product $product): ?int
    {
        $size = (int) $product->getData('taille_serie');

        return $size > 0 ? $size : null;
    }

    /**
     * « Série limitée — 8 pièces », ou « Série limitée » sans taille connue ; null hors série limitée.
     */
    public function getSeriesLabel(Product $product): ?Phrase
    {
        if (!$this->isLimitedSeries($product)) {
            return null;
        }

        $size = $this->getSeriesSize($product);

        return $size !== null
            ? __('Série limitée — %1 pièces', $size)
            : __('Série limitée');
    }

    /**
     * Nouveauté : dates news_from_date / news_to_date, évaluées dans le fuseau du magasin.
     */
    public function isNew(Product $product): bool
    {
        $from = $product->getData('news_from_date');
        $to = $product->getData('news_to_date');

        if (empty($from) && empty($to)) {
            return false;
        }

        return $this->timezone->isScopeDateInInterval($product->getStore(), $from, $to);
    }

    // ── Stock ────────────────────────────────────────────────────────────

    public function isSoldOut(Product $product): bool
    {
        return !$product->isSalable();
    }

    /**
     * Quantité vendable restante. Configurable : somme des enfants.
     * null quand le stock n'est pas géré (quantité illimitée).
     */
    public function getRemainingQty(Product $product): ?int
    {
        $productId = (int) $product->getId();

        if (array_key_exists($productId, $this->remainingQty)) {
            return $this->remainingQty[$productId];
        }

        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $children = $this->getChildrenRemainingQty($product);
            $qty = $children === [] ? 0 : array_sum($children);
        } else {
            $qty = $this->resolveSalableQty($product);
        }

        return $this->remainingQty[$productId] = $qty;
    }

    /**
     * Quantité vendable de chaque enfant d'un configurable, indexée par identifiant
     * d'enfant : c'est la clé que le sélecteur natif Hyvä émet (productIndex).
     *
     * @return array<int, int>
     */
    public function getChildrenRemainingQty(Product $product): array
    {
        $productId = (int) $product->getId();

        if (isset($this->childrenQty[$productId])) {
            return $this->childrenQty[$productId];
        }

        $result = [];

        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $typeInstance = $product->getTypeInstance();

            if ($typeInstance instanceof Configurable) {
                foreach ($typeInstance->getUsedProducts($product) as $child) {
                    /** @var Product $child */
                    $result[(int) $child->getId()] = $this->resolveSalableQty($child) ?? PHP_INT_MAX;
                }
            }
        }

        return $this->childrenQty[$productId] = $result;
    }

    public function getScarcityThreshold(): int
    {
        return max(0, (int) $this->scopeConfig->getValue(self::XML_PATH_SCARCITY_THRESHOLD, ScopeInterface::SCOPE_STORE));
    }

    /**
     * Rareté : stock restant connu, non nul, inférieur ou égal au seuil configuré.
     * Indépendante de l'attribut serie_limitee : c'est un fait de stock.
     */
    public function isScarce(Product $product): bool
    {
        $threshold = $this->getScarcityThreshold();

        if ($threshold === 0 || $this->isSoldOut($product)) {
            return false;
        }

        $remaining = $this->getRemainingQty($product);

        return $remaining !== null && $remaining <= $threshold;
    }

    /**
     * Plafond du sélecteur de quantité : le stock restant (null = pas de plafond).
     */
    public function getMaxQty(Product $product): ?int
    {
        return $this->getRemainingQty($product);
    }

    // ── Badge et libellés ────────────────────────────────────────────────

    /**
     * Un seul badge par produit, par priorité : épuisé > rareté > nouveauté > série limitée.
     * « short » est la forme abrégée des vignettes mobiles (« 8 pièces », « Plus que 2 »).
     *
     * @return array{type: string, label: Phrase, short: Phrase, css: string}|null
     */
    public function getBadge(Product $product): ?array
    {
        if ($this->isSoldOut($product)) {
            return $this->badge(self::BADGE_SOLDOUT, __('Épuisé'));
        }

        if ($this->isScarce($product)) {
            $remaining = (int) $this->getRemainingQty($product);

            return $this->badge(
                self::BADGE_SCARCE,
                $this->getScarcityLabel($product),
                $remaining === 1 ? __('Dernier exemplaire') : __('Plus que %1', $remaining)
            );
        }

        if ($this->isNew($product)) {
            return $this->badge(self::BADGE_NEW, __('Nouveauté'));
        }

        $seriesLabel = $this->getSeriesLabel($product);

        if ($seriesLabel === null) {
            return null;
        }

        $size = $this->getSeriesSize($product);

        return $this->badge(
            self::BADGE_LIMITED,
            $seriesLabel,
            $size !== null ? __('%1 pièces', $size) : __('Série limitée')
        );
    }

    /**
     * « Plus que 2 exemplaires » / « Plus qu'un exemplaire ».
     */
    public function getScarcityLabel(Product $product): Phrase
    {
        $remaining = (int) $this->getRemainingQty($product);

        return $remaining === 1
            ? __('Plus qu’un exemplaire')
            : __('Plus que %1 exemplaires', $remaining);
    }

    /**
     * Ligne secondaire de la carte produit (13 px, sous le nom) :
     * la mention de série si le badge dit autre chose, sinon la description
     * courte, sinon « Deux tailles disponibles » pour un configurable.
     */
    public function getCardSubtitle(Product $product): ?string
    {
        $badge = $this->getBadge($product);
        $seriesLabel = $this->getSeriesLabel($product);

        if ($seriesLabel !== null && ($badge === null || $badge['type'] !== self::BADGE_LIMITED)) {
            return (string) $seriesLabel;
        }

        $shortDescription = trim(strip_tags((string) $product->getShortDescription()));

        if ($shortDescription !== '') {
            return $shortDescription;
        }

        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            return (string) __('Deux tailles disponibles');
        }

        return null;
    }

    /**
     * Libellé du bouton inactif d'un produit épuisé.
     */
    public function getSoldOutLabel(Product $product): Phrase
    {
        return $this->isLimitedSeries($product) ? __('Série terminée') : __('Épuisé');
    }

    /**
     * Mention configurée sous le prix (TVA, frais de port…), vide si non renseignée.
     */
    public function getPriceNote(): string
    {
        return trim((string) $this->scopeConfig->getValue(self::XML_PATH_PRICE_NOTE, ScopeInterface::SCOPE_STORE));
    }

    /**
     * Formulaire de contact pré-rempli avec le produit concerné (page livrée au lot 3).
     */
    public function getContactUrl(Product $product): string
    {
        return $this->urlBuilder->getUrl('contact', ['_query' => ['product' => $product->getSku()]]);
    }

    // ── Internes ─────────────────────────────────────────────────────────

    /**
     * @return array{type: string, label: Phrase, short: Phrase, css: string}
     */
    private function badge(string $type, Phrase $label, ?Phrase $short = null): array
    {
        return ['type' => $type, 'label' => $label, 'short' => $short ?? $label, 'css' => self::BADGE_CSS[$type]];
    }

    /**
     * Quantité vendable MSI d'un produit simple ; null si le stock n'est pas géré
     * ou si le type ne gère pas de source.
     */
    private function resolveSalableQty(Product $product): ?int
    {
        if (!$this->isSourceItemManagementAllowed->execute((string) $product->getTypeId())) {
            return null;
        }

        try {
            $stockId = $this->getStockId();
            $sku = (string) $product->getSku();

            if (!$this->getStockItemConfiguration->execute($sku, $stockId)->isManageStock()) {
                return null;
            }

            return (int) floor($this->getProductSalableQty->execute($sku, $stockId));
        } catch (LocalizedException) {
            return null;
        }
    }

    private function getStockId(): int
    {
        if ($this->stockId === null) {
            $websiteCode = $this->storeManager->getWebsite()->getCode();
            $this->stockId = (int) $this->stockResolver
                ->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)
                ->getStockId();
        }

        return $this->stockId;
    }
}
