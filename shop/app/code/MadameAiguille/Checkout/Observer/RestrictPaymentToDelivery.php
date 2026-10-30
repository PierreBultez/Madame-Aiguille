<?php
/**
 * Moyens de paiement selon le mode de livraison : le point relais se paie en ligne ; le retrait à l'atelier
 * se règle au choix en ligne ou sur place, par carte au TPE ou en espèces (décision de Pierre du 30/10/2026,
 * le call Céline du 11/09/2026 ne prévoyait que le paiement sur place).
 *
 * Le paiement sur place est la méthode native « cashondelivery », renommée : c'est la seule que l'on retire.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Observer;

use MadameAiguille\Checkout\Model\Carrier\Pickup;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;

class RestrictPaymentToDelivery implements ObserverInterface
{
    public const PAY_ON_SITE = 'cashondelivery';

    public function execute(Observer $observer): void
    {
        /** @var DataObject $result */
        $result = $observer->getEvent()->getResult();
        $quote = $observer->getEvent()->getQuote();
        $code = (string) $observer->getEvent()->getMethodInstance()->getCode();

        if ($code !== self::PAY_ON_SITE || !$result->getData('is_available') || !$quote instanceof CartInterface) {
            return;
        }

        $isPickup = !$quote->isVirtual()
            && $quote->getShippingAddress()->getShippingMethod() === Pickup::SHIPPING_METHOD;

        if (!$isPickup) {
            $result->setData('is_available', false);
        }
    }
}
