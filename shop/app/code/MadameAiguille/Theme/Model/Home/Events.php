<?php
/**
 * « Les nouvelles de l'atelier » de l'accueil (lot 11, C3) : marchés, salons, congés… saisis par l'atelier dans
 * Général › Madame Aiguille › Nouvelles de l'atelier, une ligne par rendez-vous (date, jusqu'au, titre, lieu, texte).
 *
 * Seuls les rendez-vous à venir sont gardés, du plus proche au plus lointain. Un rendez-vous disparaît le lendemain
 * de sa date de fin (ou de sa date, s'il ne dure qu'un jour). Une ligne sans date valide ni titre est ignorée.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Home;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class Events
{
    public const XML_PATH = 'madameaiguille/home_events/list';
    public const CACHE_TAG = 'madameaiguille_home_events';
    public const LIMIT = 4;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * @return list<array{start: \DateTimeImmutable, end: \DateTimeImmutable, title: string, place: string, text: string}>
     */
    public function upcoming(\DateTimeImmutable $today, ?int $storeId = null): array
    {
        $raw = (string) $this->scopeConfig->getValue(self::XML_PATH, ScopeInterface::SCOPE_STORE, $storeId);
        if (trim($raw) === '') {
            return [];
        }

        try {
            $rows = $this->serializer->unserialize($raw);
        } catch (\InvalidArgumentException) {
            return [];
        }

        return self::select(is_array($rows) ? $rows : [], $today, self::LIMIT);
    }

    /**
     * @param array<mixed> $rows lignes de la grille de configuration
     * @return list<array{start: \DateTimeImmutable, end: \DateTimeImmutable, title: string, place: string, text: string}>
     */
    public static function select(array $rows, \DateTimeImmutable $today, int $limit): array
    {
        // Comparaison de jours, pas d'instants : le jour du magasin, ramené au même fuseau que les dates saisies.
        $today = self::date($today->format('Y-m-d'));
        $events = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $start = self::date($row['date'] ?? null);
            $title = trim((string) ($row['title'] ?? ''));
            if ($start === null || $title === '') {
                continue;
            }
            $end = self::date($row['end_date'] ?? null);
            $end = $end !== null && $end > $start ? $end : $start;
            if ($end < $today) {
                continue;
            }
            $events[] = [
                'start' => $start,
                'end' => $end,
                'title' => $title,
                'place' => trim((string) ($row['place'] ?? '')),
                'text' => trim((string) ($row['text'] ?? '')),
            ];
        }

        usort($events, static fn (array $a, array $b): int => [$a['start'], $a['end']] <=> [$b['start'], $b['end']]);

        return array_slice($events, 0, $limit);
    }

    private static function date(mixed $value): ?\DateTimeImmutable
    {
        $value = trim((string) $value);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value ? $date : null;
    }
}
