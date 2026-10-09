<?php
/**
 * Créneaux de retrait proposés, à partir des plages hebdomadaires de Céline.
 *
 * Calcul pur, sans état ni accès à la base : les réservations sont retranchées par Availability.
 * Les créneaux sont exprimés en heure locale de la boutique, au format « AAAA-MM-JJ HH:MM ».
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Pickup;

class SlotCalendar
{
    public const FORMAT = 'Y-m-d H:i';

    /**
     * @param list<array{day: int, from: string, to: string}> $ranges jour ISO-8601 (1 = lundi), heures « HH:MM »
     * @param list<string> $closedDates jours fermés, « AAAA-MM-JJ »
     * @return list<string>
     */
    public function generate(
        array $ranges,
        int $durationMinutes,
        \DateTimeImmutable $now,
        int $noticeHours,
        int $horizonDays,
        array $closedDates = []
    ): array {
        if ($durationMinutes <= 0 || $horizonDays <= 0) {
            return [];
        }

        $earliest = $now->modify(sprintf('+%d hours', max(0, $noticeHours)));
        $closed = array_flip($closedDates);
        $step = new \DateInterval('PT' . $durationMinutes . 'M');
        $slots = [];

        for ($offset = 0; $offset <= $horizonDays; $offset++) {
            $date = $now->setTime(0, 0)->modify(sprintf('+%d days', $offset));
            if (isset($closed[$date->format('Y-m-d')])) {
                continue;
            }

            foreach ($ranges as $range) {
                if ((int) $range['day'] !== (int) $date->format('N')) {
                    continue;
                }
                $start = $this->at($date, $range['from']);
                $end = $this->at($date, $range['to']);
                if ($start === null || $end === null) {
                    continue;
                }

                $slot = $start;
                while ($slot->add($step) <= $end) {
                    if ($slot >= $earliest) {
                        $slots[] = $slot->format(self::FORMAT);
                    }
                    $slot = $slot->add($step);
                }
            }
        }

        $slots = array_values(array_unique($slots));
        sort($slots);

        return $slots;
    }

    private function at(\DateTimeImmutable $date, string $time): ?\DateTimeImmutable
    {
        if (!preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', trim($time), $matches)) {
            return null;
        }

        return $date->setTime((int) $matches[1], (int) $matches[2]);
    }
}
