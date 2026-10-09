<?php
/**
 * Libère la place d'un créneau : commande qui échoue à la validation, ou commande annulée
 * (rendez-vous non honoré : Céline annule la commande, le créneau et le stock redeviennent libres).
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Observer;

use MadameAiguille\Checkout\Model\Pickup\BookingRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ReleasePickupBooking implements ObserverInterface
{
    public function __construct(
        private readonly BookingRepository $bookings
    ) {
    }

    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        if ($order && $order->getId()) {
            $this->bookings->releaseOrder((int) $order->getId());
        }

        $quote = $observer->getEvent()->getQuote();
        if ($quote && $quote->getId()) {
            $this->bookings->releaseQuote((int) $quote->getId());
        }
    }
}
