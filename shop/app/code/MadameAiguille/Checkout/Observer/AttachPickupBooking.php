<?php
/**
 * Commande créée : la place réservée sur le créneau lui est rattachée.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Observer;

use MadameAiguille\Checkout\Model\Pickup\BookingRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AttachPickupBooking implements ObserverInterface
{
    public function __construct(
        private readonly BookingRepository $bookings
    ) {
    }

    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();
        if ($quote && $order && $order->getId()) {
            $this->bookings->attachOrder((int) $quote->getId(), (int) $order->getId());
        }
    }
}
