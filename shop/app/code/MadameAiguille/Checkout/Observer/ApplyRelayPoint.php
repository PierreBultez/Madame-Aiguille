<?php
/**
 * Validation de la commande en point relais : sans point mémorisé (appel d'API direct, panier modifié
 * entre deux onglets…), la commande est refusée ; sinon l'adresse du point devient l'adresse de
 * livraison de la commande. L'adresse du panier, elle, reste celle de la cliente.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Observer;

use MadameAiguille\Checkout\Model\RelayPoint\Assignment;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class ApplyRelayPoint implements ObserverInterface
{
    public function __construct(
        private readonly Assignment $assignment
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($quote->isVirtual() || $quote->getShippingAddress()->getShippingMethod() !== Assignment::SHIPPING_METHOD) {
            return;
        }

        $point = $this->assignment->recall($quote->getShippingAddress());
        if ($point === null) {
            throw new LocalizedException(__('Please choose your relay point.'));
        }

        $this->assignment->applyToOrderAddress($order->getShippingAddress(), $point);
    }
}
