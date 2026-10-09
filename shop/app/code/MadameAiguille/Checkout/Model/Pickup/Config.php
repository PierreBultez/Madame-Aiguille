<?php
/**
 * Réglages du retrait à l'atelier (Général › Madame Aiguille › Retrait à l'atelier).
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Pickup;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    private const PREFIX = 'madameaiguille/pickup/';
    public const MAP_IMAGE_DIR = 'madameaiguille/pickup';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Json $json,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @return list<array{day: int, from: string, to: string}>
     */
    public function getWeeklyRanges(): array
    {
        $value = (string) $this->get('weekly_hours');
        $rows = $value === '' ? [] : (array) $this->json->unserialize($value);
        $ranges = [];
        foreach ($rows as $row) {
            if (is_array($row) && isset($row['day'], $row['from'], $row['to'])) {
                $ranges[] = ['day' => (int) $row['day'], 'from' => (string) $row['from'], 'to' => (string) $row['to']];
            }
        }

        return $ranges;
    }

    public function getSlotDuration(): int
    {
        return max(5, (int) $this->get('slot_duration'));
    }

    public function getCapacity(): int
    {
        return max(1, (int) $this->get('capacity'));
    }

    public function getNoticeHours(): int
    {
        return max(0, (int) $this->get('min_notice_hours'));
    }

    public function getHorizonDays(): int
    {
        return max(1, (int) $this->get('horizon_days'));
    }

    /**
     * Une date « AAAA-MM-JJ » par ligne, ou une période « AAAA-MM-JJ/AAAA-MM-JJ ».
     *
     * @return list<string>
     */
    public function getClosedDates(): array
    {
        $dates = [];
        foreach (preg_split('/\R/', (string) $this->get('closed_dates')) ?: [] as $line) {
            $bounds = array_map('trim', explode('/', trim($line)));
            $from = \DateTimeImmutable::createFromFormat('!Y-m-d', $bounds[0]);
            $to = \DateTimeImmutable::createFromFormat('!Y-m-d', $bounds[1] ?? $bounds[0]);
            if (!$from || !$to) {
                continue;
            }
            for ($day = $from; $day <= $to; $day = $day->modify('+1 day')) {
                $dates[] = $day->format('Y-m-d');
            }
        }

        return array_values(array_unique($dates));
    }

    public function getLocationName(): string
    {
        return trim((string) $this->get('location_name'));
    }

    public function getLocationAddress(): string
    {
        return trim((string) $this->get('location_address'));
    }

    public function getDirectionsUrl(): string
    {
        $latitude = trim((string) $this->get('latitude'));
        $longitude = trim((string) $this->get('longitude'));
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return '';
        }

        return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($latitude . ',' . $longitude);
    }

    public function getMapImageUrl(): string
    {
        $image = trim((string) $this->get('map_image'));
        if ($image === '') {
            return '';
        }

        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . self::MAP_IMAGE_DIR . '/' . ltrim($image, '/');
    }

    private function get(string $field): mixed
    {
        return $this->scopeConfig->getValue(self::PREFIX . $field, ScopeInterface::SCOPE_STORE);
    }
}
