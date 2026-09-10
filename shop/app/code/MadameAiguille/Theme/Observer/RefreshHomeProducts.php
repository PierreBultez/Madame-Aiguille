<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Observer;

use MadameAiguille\Theme\Model\Cache\FlushProductCacheBySkus;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/** Inclut les produits qui entrent dans une sélection auparavant vide. */
class RefreshHomeProducts implements ObserverInterface
{
    public function __construct(private readonly FlushProductCacheBySkus $cache)
    {
    }

    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getProduct();
        if ($product && $product->getId()) {
            $this->cache->flushByProductIds([(int) $product->getId()]);
        }
    }
}
