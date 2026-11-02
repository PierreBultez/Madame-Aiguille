<?php
/**
 * Ouverture des ventes (lot 8, décision de Pierre du 02/11/2026) : la boutique s'ouvre aux moteurs de recherche.
 *
 * - robots INDEX,FOLLOW ; robots.txt sans le panier, le tunnel, le compte, la recherche ni les retours de paiement ;
 * - sitemap dans pub/media (partagé entre les versions : un fichier de pub/ disparaîtrait au déploiement suivant),
 *   soit https://madame-aiguille.fr/media/sitemap.xml, régénéré chaque nuit et annoncé dans robots.txt.
 *
 * L'en-tête X-Robots-Tag de nginx se retire à part (deploy/serveur/95-ouverture-ventes.sh). Une installation neuve
 * du serveur repasse en NOINDEX par 80-installer-magento.sh, le temps de sa recette.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory as SitemapCollectionFactory;
use Magento\Sitemap\Model\SitemapFactory;
use Magento\Store\Model\StoreManagerInterface;

class OpenStoreToSearchEngines implements DataPatchInterface
{
    public const SITEMAP_PATH = '/media/';
    public const SITEMAP_FILENAME = 'sitemap.xml';

    public const ROBOTS_INSTRUCTIONS = "User-agent: *\n"
        . "Disallow: /checkout/\n"
        . "Disallow: /customer/\n"
        . "Disallow: /sales/\n"
        . "Disallow: /wishlist/\n"
        . "Disallow: /catalogsearch/\n"
        . "Disallow: /newsletter/\n"
        . "Disallow: /stripe/\n"
        . "Disallow: /*?*SID=\n";

    private const VALUES = [
        'design/search_engine_robots/default_robots' => 'INDEX,FOLLOW',
        'design/search_engine_robots/custom_instructions' => self::ROBOTS_INSTRUCTIONS,
        'sitemap/generate/enabled' => '1',
        'sitemap/search_engines/submission_robots' => '1',
    ];

    public function __construct(
        private readonly WriterInterface $configWriter,
        private readonly SitemapFactory $sitemapFactory,
        private readonly SitemapCollectionFactory $sitemapCollectionFactory,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function apply(): self
    {
        foreach (self::VALUES as $path => $value) {
            $this->configWriter->save($path, $value);
        }

        $storeId = (int) $this->storeManager->getDefaultStoreView()?->getId();
        $existing = $this->sitemapCollectionFactory->create()->addStoreFilter([$storeId]);
        if ($storeId > 0 && $existing->getSize() === 0) {
            $this->sitemapFactory->create()
                ->setSitemapFilename(self::SITEMAP_FILENAME)
                ->setSitemapPath(self::SITEMAP_PATH)
                ->setStoreId($storeId)
                ->save();
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
