<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Api;

interface PickupSlotsInterface
{
    /**
     * Créneaux de retrait encore libres, « AAAA-MM-JJ HH:MM » en heure de la boutique.
     *
     * @return string[]
     */
    public function getAvailable(): array;
}
