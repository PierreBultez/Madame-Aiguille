<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Pickup;

use MadameAiguille\Checkout\Api\PickupSlotsInterface;

class PickupSlots implements PickupSlotsInterface
{
    public function __construct(
        private readonly Availability $availability
    ) {
    }

    public function getAvailable(): array
    {
        return $this->availability->getAvailableSlots();
    }
}
