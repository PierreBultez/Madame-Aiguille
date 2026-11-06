<?php
/**
 * Madame Aiguille — données structurées `Product` de la fiche (JSON-LD, lot 10)
 *
 * Nom, photos, description, référence, marque, prix et disponibilité, pour les résultats enrichis des moteurs de
 * recherche. **Rien de propre au visiteur** : Varnish et le cache de pages servent la même fiche à tous ; le prix
 * est celui de la vitrine (minimum d'un configurable, fourchette s'il varie), la disponibilité suit le stock, dont
 * les changements purgent déjà la fiche (§8, fraîcheur des badges).
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class StructuredData implements ArgumentInterface
{
    public const BRAND = 'Madame Aiguille';
    public const MAX_IMAGES = 4;
    private const DESCRIPTION_LENGTH = 300;

    public function __construct(
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(Product $product): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => (string) $product->getName(),
            'sku' => (string) $product->getSku(),
            'url' => (string) $product->getProductUrl(),
            'brand' => ['@type' => 'Brand', 'name' => self::BRAND],
        ];

        $images = $this->getImages($product);
        if ($images !== []) {
            $data['image'] = $images;
        }
        $description = $this->getDescription($product);
        if ($description !== '') {
            $data['description'] = $description;
        }
        $offer = $this->getOffer($product);
        if ($offer !== null) {
            $data['offers'] = $offer;
        }

        return $data;
    }

    /** JSON prêt à placer dans un `<script type="application/ld+json">` (`<` et `>` encodés). */
    public function getJson(Product $product): string
    {
        return (string) json_encode(
            $this->getData($product),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
        );
    }

    /**
     * @return list<string>
     */
    private function getImages(Product $product): array
    {
        $images = [];
        foreach ($product->getMediaGalleryImages() ?? [] as $image) {
            if ($image->getData('disabled') || ($image->getData('media_type') ?: 'image') !== 'image') {
                continue;
            }
            $images[] = (string) $image->getData('url');
            if (count($images) === self::MAX_IMAGES) {
                break;
            }
        }

        return array_values(array_filter($images));
    }

    /**
     * Description méta, sinon accroche, sinon début de la description longue : du texte brut. Une valeur qui répète
     * le nom est sautée (Magento remplit la description méta avec le nom quand elle est vide à l'enregistrement).
     */
    private function getDescription(Product $product): string
    {
        $name = mb_strtolower(trim((string) $product->getName()));
        foreach (['meta_description', 'short_description', 'description'] as $code) {
            $text = trim((string) preg_replace(
                '/\s+/u',
                ' ',
                html_entity_decode(strip_tags((string) $product->getData($code)), ENT_QUOTES | ENT_HTML5, 'UTF-8')
            ));
            if ($text !== '' && mb_strtolower($text) !== $name) {
                return mb_strlen($text) > self::DESCRIPTION_LENGTH
                    ? rtrim(mb_substr($text, 0, self::DESCRIPTION_LENGTH - 1)) . '…'
                    : $text;
            }
        }

        return '';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getOffer(Product $product): ?array
    {
        $finalPrice = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE);
        $low = (float) $finalPrice->getMinimalPrice()->getValue();
        $high = (float) $finalPrice->getMaximalPrice()->getValue();
        if ($low <= 0) {
            return null;
        }

        $offer = [
            'priceCurrency' => (string) $this->storeManager->getStore()->getCurrentCurrencyCode(),
            'availability' => $product->isSalable() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'url' => (string) $product->getProductUrl(),
        ];

        return $high > $low
            ? ['@type' => 'AggregateOffer', 'lowPrice' => round($low, 2), 'highPrice' => round($high, 2)] + $offer
            : ['@type' => 'Offer', 'price' => round($low, 2)] + $offer;
    }
}
