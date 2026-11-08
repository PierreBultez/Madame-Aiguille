<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Model\Home;

use MadameAiguille\Theme\Model\Home\Events;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

class EventsTest extends TestCase
{
    private const TODAY = '2026-11-08';

    public function testKeepsUpcomingEventsInDateOrder(): void
    {
        $events = Events::select([
            ['date' => '2026-12-12', 'title' => 'Marché de Noël', 'place' => 'Saint-Épain'],
            ['date' => '2026-11-14', 'title' => 'Salon des créateurs', 'text' => 'Stand 12'],
            ['date' => '2026-11-07', 'title' => 'Hier : masqué'],
        ], $this->today(), 4);

        self::assertSame(['Salon des créateurs', 'Marché de Noël'], array_column($events, 'title'));
        self::assertSame('Saint-Épain', $events[1]['place']);
        self::assertSame('Stand 12', $events[0]['text']);
    }

    public function testEventOfTheDayAndRunningEventsStay(): void
    {
        $events = Events::select([
            ['date' => self::TODAY, 'title' => 'Aujourd’hui'],
            ['date' => '2026-11-06', 'end_date' => '2026-11-08', 'title' => 'Finit ce soir'],
            ['date' => '2026-11-05', 'end_date' => '2026-11-07', 'title' => 'Fini hier'],
        ], $this->today(), 4);

        self::assertSame(['Finit ce soir', 'Aujourd’hui'], array_column($events, 'title'));
    }

    public function testIgnoresIncompleteRowsAndIncoherentEndDates(): void
    {
        $events = Events::select([
            ['date' => '', 'title' => 'Sans date'],
            ['date' => '2026-02-30', 'title' => 'Date impossible'],
            ['date' => '2026-11-20', 'title' => '   '],
            ['date' => '2026-11-20', 'end_date' => '2026-11-01', 'title' => 'Fin avant le début'],
            'pas une ligne',
        ], $this->today(), 4);

        self::assertCount(1, $events);
        self::assertSame('2026-11-20', $events[0]['end']->format('Y-m-d'), 'Une fin antérieure vaut la date de début.');
    }

    public function testLimitsTheNumberOfEvents(): void
    {
        $rows = [];
        foreach (range(10, 16) as $day) {
            $rows[] = ['date' => '2026-11-' . $day, 'title' => 'Rendez-vous ' . $day];
        }

        self::assertCount(4, Events::select($rows, $this->today(), 4));
    }

    public function testReadsTheConfiguredGrid(): void
    {
        $json = new Json();
        $config = $this->createStub(ScopeConfigInterface::class);
        $config->method('getValue')->willReturnOnConsecutiveCalls(
            $json->serialize(['_1' => ['date' => '2026-11-14', 'title' => 'Salon']]),
            '',
            'pas du JSON'
        );
        $events = new Events($config, $json);

        self::assertCount(1, $events->upcoming($this->today()));
        self::assertSame([], $events->upcoming($this->today()));
        self::assertSame([], $events->upcoming($this->today()));
    }

    private function today(): \DateTimeImmutable
    {
        // Le jour du magasin, dans un autre fuseau que les dates saisies : seule la date compte.
        return new \DateTimeImmutable(self::TODAY . ' 23:30:00', new \DateTimeZone('Europe/Paris'));
    }
}
