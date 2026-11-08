<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\ViewModel\Home;

use MadameAiguille\Theme\ViewModel\Home\Events;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EventsTest extends TestCase
{
    #[DataProvider('dates')]
    public function testWritesTheDateInFrench(string $start, string $end, string $expected): void
    {
        self::assertSame($expected, Events::when(
            new \DateTimeImmutable($start),
            new \DateTimeImmutable($end),
            new \DateTimeImmutable('2026-11-08'),
            'fr_FR'
        ));
    }

    public static function dates(): array
    {
        return [
            'un jour' => ['2026-11-14', '2026-11-14', 'Samedi 14 novembre'],
            'l’an prochain' => ['2027-01-09', '2027-01-09', 'Samedi 9 janvier 2027'],
            'même mois' => ['2026-12-12', '2026-12-14', 'Event from 12 to 14 décembre'],
            'deux mois' => ['2026-11-30', '2026-12-02', 'Event from 30 novembre to 2 décembre'],
        ];
    }
}
