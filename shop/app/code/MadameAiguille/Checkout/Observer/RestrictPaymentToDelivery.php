<?php
/**
 * Un moyen de paiement par mode de livraison (call Céline du 11/09/2026) :
 * le retrait à l'atelier se règle sur place, tout le reste se paie en ligne.
 *
 * Le paiement sur place est la méthode native « cashondelivery », renommée.
 * « free » (commande à 0 €) reste toujours possible.
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
    private const ALWAYS_ALLOWED = ['free'];

    public function execute(Observer $observer): void
    {
        /** @var DataObject $result */
        $result = $observer->getEvent()->getResult();
        $quote = $observer->getEvent()->getQuote();
        $code = (string) $observer->getEvent()->getMethodInstance()->getCode();

        if (!$result->getData('is_available') || !$quote instanceof CartInterface || in_array($code, self::ALWAYS_ALLOWED, true)) {
            return;
        }

        $isPickup = !$quote->isVirtual()
            && $quote->getShippingAddress()->getShippingMethod() === Pickup::SHIPPING_METHOD;

        if ($isPickup !== ($code === self::PAY_ON_SITE)) {
            $result->setData('is_available', false);
        }
    }
}
