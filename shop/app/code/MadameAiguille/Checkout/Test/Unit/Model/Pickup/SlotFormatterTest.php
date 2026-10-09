<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Model\Pickup;

use MadameAiguille\Checkout\Model\Pickup\SlotFormatter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class SlotFormatterTest extends TestCase
{
    public function testWritesTheAppointmentInTheStoreLanguage(): void
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn('fr_FR');
        $formatter = new SlotFormatter($scopeConfig);

        self::assertSame('jeudi 15 octobre 2026 à 9 h 00', $formatter->format('2026-10-15 09:00'));
        self::assertSame('n’importe quoi', $formatter->format('n’importe quoi'));
    }
}
