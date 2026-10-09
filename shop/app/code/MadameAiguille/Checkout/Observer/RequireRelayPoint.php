<?php
/**
 * Dernier garde-fou à la création de la commande : une livraison en point relais sans point
 * (appel d'API direct, panier modifié entre deux onglets…) n'est pas acceptée.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Observer;

use MadameAiguille\Checkout\Model\RelayPoint\Assignment;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class RequireRelayPoint implements ObserverInterface
{
    /**
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        if ($quote->isVirtual()) {
            return;
        }

        $address = $quote->getShippingAddress();
        if ($address->getShippingMethod() === Assignment::SHIPPING_METHOD
            && !$address->getData(Assignment::ADDRESS_FIELD)
        ) {
            throw new LocalizedException(__('Please choose your relay point.'));
        }
    }
}
