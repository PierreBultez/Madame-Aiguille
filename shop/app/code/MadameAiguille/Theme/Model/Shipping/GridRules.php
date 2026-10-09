<?php
/**
 * Règles métier d'une grille au poids, au-delà de la validation native du CSV :
 * chaque pays de la grille doit être un pays de vente, et commencer à 0 kg — sinon un colis
 * plus léger que le premier palier n'obtiendrait aucun tarif et le point relais disparaîtrait du tunnel.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Shipping;

use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Exception\LocalizedException;

class GridRules
{
    public function __construct(
        private readonly AllowedCountries $allowedCountries
    ) {
    }

    /**
     * @param list<array{0: string, 1: float}> $tiers couples [pays ISO 2, poids minimal en kg]
     * @return array<string, int> nombre de paliers par pays
     * @throws LocalizedException
     */
    public function check(array $tiers): array
    {
        if ($tiers === []) {
            throw new LocalizedException(__('The rate grid is empty.'));
        }

        $allowed = $this->allowedCountries->getAllowedCountries();
        $count = [];
        $startsAtZero = [];
        foreach ($tiers as [$country, $weight]) {
            $count[$country] = ($count[$country] ?? 0) + 1;
            $startsAtZero[$country] = ($startsAtZero[$country] ?? false) || $weight === 0.0;
        }

        $errors = [];
        foreach (array_keys($count) as $country) {
            if (!in_array($country, $allowed, true)) {
                $errors[] = __('%1 is not an allowed country of the store.', $country);
            }
            if (!$startsAtZero[$country]) {
                $errors[] = __('The %1 grid must start at 0 kg.', $country);
            }
        }
        foreach (array_diff($allowed, array_keys($count)) as $country) {
            $errors[] = __('Allowed country %1 has no rate.', $country);
        }

        if ($errors !== []) {
            throw new LocalizedException(__(implode(PHP_EOL, array_map('strval', $errors))));
        }

        ksort($count);
        return $count;
    }
}
