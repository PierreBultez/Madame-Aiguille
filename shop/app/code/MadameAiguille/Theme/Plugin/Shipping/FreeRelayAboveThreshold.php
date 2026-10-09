<?php
/**
 * Franco de port : le point relais devient gratuit dès que le sous-total atteint le seuil
 * de Général › Madame Aiguille › Panier — le même que celui de la barre du panier.
 *
 * Décision de Pierre du 09/10/2026 : une seule source de vérité. Le carrier natif freeshipping
 * est coupé ; il affichait le franco comme une méthode à part, sans point relais.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Plugin\Shipping;

use MadameAiguille\Theme\ViewModel\Cart\FreeShipping;
use Magento\OfflineShipping\Model\Carrier\Tablerate;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class FreeRelayAboveThreshold
{
    public function __construct(
        private readonly FreeShipping $freeShipping
    ) {
    }

    /**
     * @param Result|bool $result
     * @return Result|bool
     */
    public function afterCollectRates(Tablerate $subject, $result, RateRequest $request)
    {
        if (!$result instanceof Result || !$this->freeShipping->isReached((float) $request->getBaseSubtotalInclTax())) {
            return $result;
        }

        foreach ($result->getAllRates() as $rate) {
            $rate->setPrice(0);
        }

        return $result;
    }
}
