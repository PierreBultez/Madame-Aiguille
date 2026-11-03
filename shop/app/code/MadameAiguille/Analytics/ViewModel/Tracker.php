<?php
/**
 * Attributs de la balise de suivi Matomo, rendue dans le <head> de la vitrine Hyvä et du tunnel Luma.
 *
 * Rien de propre au visiteur : le mot-clé et le nombre de résultats d'une recherche dépendent de l'adresse seule,
 * si bien que Varnish et le cache de pages servent la même page à tous. Le type de page vient du layout
 * (catalogsearch_result_index, cms_noroute_index).
 */

declare(strict_types=1);

namespace MadameAiguille\Analytics\ViewModel;

use MadameAiguille\Analytics\Model\Config;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Search\Model\QueryFactory;

class Tracker implements ArgumentInterface
{
    public const PAGE_SEARCH = 'search';
    public const PAGE_NOT_FOUND = 'not-found';

    public function __construct(
        private readonly Config $config,
        private readonly QueryFactory $queryFactory,
        private readonly LayerResolver $layerResolver
    ) {
    }

    public function isActive(): bool
    {
        return $this->config->isReady();
    }

    /**
     * @return array<string, string> attributs data-* de la balise <script>, vides si la mesure n'est pas prête
     */
    public function getAttributes(?string $pageType = null): array
    {
        if (!$this->isActive()) {
            return [];
        }

        $attributes = [
            'data-url' => (string) $this->config->getUrl(),
            'data-site-id' => (string) $this->config->getSiteId(),
        ];

        if ($pageType === self::PAGE_NOT_FOUND) {
            $attributes['data-page'] = self::PAGE_NOT_FOUND;
        } elseif ($pageType === self::PAGE_SEARCH) {
            $attributes['data-page'] = self::PAGE_SEARCH;
            $attributes['data-search-keyword'] = (string) $this->queryFactory->get()->getQueryText();
            $count = $this->getSearchResultCount();
            if ($count !== null) {
                $attributes['data-search-count'] = (string) $count;
            }
        }

        return $attributes;
    }

    /** Taille de la collection de la couche de recherche, déjà construite par le contrôleur de résultats. */
    private function getSearchResultCount(): ?int
    {
        try {
            return (int) $this->layerResolver->get()->getProductCollection()->getSize();
        } catch (\Throwable) {
            return null;
        }
    }
}
