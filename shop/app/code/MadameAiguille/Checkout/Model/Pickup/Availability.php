<?php
/**
 * Créneaux proposés à la cliente : calendrier de Céline, moins les créneaux complets.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Pickup;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Availability
{
    public function __construct(
        private readonly Config $config,
        private readonly SlotCalendar $calendar,
        private readonly BookingRepository $bookings,
        private readonly TimezoneInterface $timezone
    ) {
    }

    /**
     * Créneaux du calendrier, réservés ou non.
     *
     * @return list<string>
     */
    public function getOfferedSlots(): array
    {
        return $this->calendar->generate(
            $this->config->getWeeklyRanges(),
            $this->config->getSlotDuration(),
            \DateTimeImmutable::createFromInterface($this->timezone->date()),
            $this->config->getNoticeHours(),
            $this->config->getHorizonDays(),
            $this->config->getClosedDates()
        );
    }

    /**
     * @return list<string>
     */
    public function getAvailableSlots(): array
    {
        $slots = $this->getOfferedSlots();
        if ($slots === []) {
            return [];
        }

        $taken = $this->bookings->countBySlot($slots[0], $slots[count($slots) - 1]);
        $capacity = $this->config->getCapacity();

        return array_values(array_filter(
            $slots,
            static fn (string $slot): bool => ($taken[$slot] ?? 0) < $capacity
        ));
    }

    public function isOffered(string $slot): bool
    {
        return in_array($slot, $this->getOfferedSlots(), true);
    }

    public function isAvailable(string $slot): bool
    {
        return in_array($slot, $this->getAvailableSlots(), true);
    }
}
