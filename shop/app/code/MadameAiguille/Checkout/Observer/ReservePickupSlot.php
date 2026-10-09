<?php
/**
 * Validation de la commande : la place est prise sur le créneau, de façon atomique.
 * Si une autre cliente vient de prendre la dernière place, la commande est refusée avec un message clair.
 * Le créneau est ajouté à la description de livraison : il apparaît ainsi dans l'administration,
 * les emails, la facture et le compte client sans autre gabarit.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Observer;

use MadameAiguille\Checkout\Model\Carrier\Pickup;
use MadameAiguille\Checkout\Model\Pickup\Availability;
use MadameAiguille\Checkout\Model\Pickup\BookingRepository;
use MadameAiguille\Checkout\Model\Pickup\Config;
use MadameAiguille\Checkout\Model\Pickup\SlotFormatter;
use MadameAiguille\Checkout\Plugin\Checkout\AssignPickupSlot;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class ReservePickupSlot implements ObserverInterface
{
    public function __construct(
        private readonly Availability $availability,
        private readonly BookingRepository $bookings,
        private readonly Config $config,
        private readonly SlotFormatter $formatter
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
        if ($quote->isVirtual() || $quote->getShippingAddress()->getShippingMethod() !== Pickup::SHIPPING_METHOD) {
            return;
        }

        $slot = (string) $quote->getShippingAddress()->getData(AssignPickupSlot::ADDRESS_FIELD);
        if ($slot === '') {
            throw new LocalizedException(__('Please choose your pickup time.'));
        }
        if (!$this->availability->isOffered($slot)
            || !$this->bookings->reserve($slot, $this->config->getCapacity(), (int) $quote->getId())
        ) {
            throw new LocalizedException(__(
                'This pickup time has just been booked. Please go back to the delivery step and choose another one.'
            ));
        }

        $order->setShippingDescription(
            trim((string) $order->getShippingDescription()) . ' — ' . $this->formatter->format($slot)
        );
    }
}
