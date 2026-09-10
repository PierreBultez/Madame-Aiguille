<?php
/**
 * Madame Aiguille — badge « Nouveauté » à jour au changement de jour
 *
 * Le badge dépend des dates news_from_date / news_to_date : un produit qui
 * entre ou sort de sa période de nouveauté ne change pas en base, donc aucun
 * cache n'est invalidé. Chaque nuit, ce cron purge les caches des produits
 * dont une de ces deux dates est la veille ou le jour même (fuseau du magasin).
 * Le bloc Nouveautés de l'accueil (lot 3) en bénéficiera aussi.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Cron;

use MadameAiguille\Theme\Model\Cache\FlushProductCacheBySkus;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class RefreshNoveltyBadges
{
    public function __construct(
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly TimezoneInterface $timezone,
        private readonly FlushProductCacheBySkus $flushProductCache
    ) {
    }

    public function execute(): void
    {
        $today = $this->timezone->date()->format('Y-m-d');
        $yesterday = $this->timezone->date()->modify('-1 day')->format('Y-m-d');
        $from = $yesterday . ' 00:00:00';
        $to = $today . ' 23:59:59';

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToFilter([
                ['attribute' => 'news_from_date', 'from' => $from, 'to' => $to, 'date' => true],
                ['attribute' => 'news_to_date', 'from' => $from, 'to' => $to, 'date' => true],
            ]);

        $productIds = array_map('intval', $collection->getAllIds());

        $this->flushProductCache->flushByProductIds($productIds);
    }
}
