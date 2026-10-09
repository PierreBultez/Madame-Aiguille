<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Model\Pickup;

use MadameAiguille\Checkout\Model\Pickup\SlotCalendar;
use PHPUnit\Framework\TestCase;

class SlotCalendarTest extends TestCase
{
    private const CELINE = [
        ['day' => 4, 'from' => '09:00', 'to' => '18:00'],
        ['day' => 5, 'from' => '09:00', 'to' => '11:30'],
    ];

    /** Lundi 12 octobre 2026, 12 h */
    private function monday(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('2026-10-12 12:00', new \DateTimeZone('Europe/Paris'));
    }

    public function testOffersHalfHourSlotsOnThursdayAndFridayMorning(): void
    {
        $slots = (new SlotCalendar())->generate(self::CELINE, 30, $this->monday(), 48, 6);

        self::assertCount(18 + 5, $slots);
        self::assertSame('2026-10-15 09:00', $slots[0]);
        self::assertSame('2026-10-15 17:30', $slots[17]);
        self::assertSame('2026-10-16 11:00', $slots[22]);
    }

    public function testNoticeRemovesTooCloseSlots(): void
    {
        $wednesdayEvening = new \DateTimeImmutable('2026-10-14 20:00', new \DateTimeZone('Europe/Paris'));
        $slots = (new SlotCalendar())->generate(self::CELINE, 30, $wednesdayEvening, 24, 2);

        // Mercredi 20 h + 24 h : tout le jeudi est trop proche, le premier créneau est vendredi 9 h
        self::assertSame('2026-10-16 09:00', $slots[0]);
        self::assertNotContains('2026-10-15 17:30', $slots);
    }

    public function testClosedDatesAreSkipped(): void
    {
        $slots = (new SlotCalendar())->generate(self::CELINE, 30, $this->monday(), 0, 6, ['2026-10-15']);

        self::assertSame('2026-10-16 09:00', $slots[0]);
        self::assertCount(5, $slots);
    }

    public function testHorizonLimitsTheCalendar(): void
    {
        $slots = (new SlotCalendar())->generate(self::CELINE, 30, $this->monday(), 0, 2);

        self::assertSame([], $slots);
    }

    public function testARangeShorterThanASlotOffersNothingAndBadHoursAreIgnored(): void
    {
        $slots = (new SlotCalendar())->generate(
            [['day' => 4, 'from' => '09:00', 'to' => '09:20'], ['day' => 5, 'from' => '9h', 'to' => '11:30']],
            30,
            $this->monday(),
            0,
            6
        );

        self::assertSame([], $slots);
    }
}
