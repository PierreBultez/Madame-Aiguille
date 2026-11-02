<?php
/**
 * Poids de l'emballage type (Madame Aiguille › Mondial Relay, 0,2 kg depuis le 02/11/2026) : ajouté une fois par
 * colis au poids des créations pour choisir le palier de la grille Mondial Relay. Tablerate choisit son palier sur
 * free_method_weight (poids hors articles en livraison gratuite), recopié dans package_weight : les deux sont
 * augmentés, puis remis après le calcul, la demande de tarif étant partagée avec les autres transporteurs.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Shipping;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\OfflineShipping\Model\Carrier\Tablerate;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Store\Model\ScopeInterface;

class AddPackagingWeight
{
    public const XML_PATH = 'madameaiguille/mondial_relay/packaging_weight';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @return mixed
     */
    public function aroundCollectRates(Tablerate $subject, callable $proceed, RateRequest $request)
    {
        $weight = (float) $request->getPackageWeight();
        $billedWeight = (float) $request->getFreeMethodWeight();
        $packaging = max(0.0, (float) $this->scopeConfig->getValue(
            self::XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $request->getStoreId()
        ));
        if ($billedWeight <= 0.0 || $packaging <= 0.0) {
            return $proceed($request);
        }

        $request->setPackageWeight($weight + $packaging);
        $request->setFreeMethodWeight($billedWeight + $packaging);
        try {
            return $proceed($request);
        } finally {
            $request->setPackageWeight($weight);
            $request->setFreeMethodWeight($billedWeight);
        }
    }
}
