<?php
declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Test\Unit\Model;

use MadameAiguille\AiAssistant\Model\AssistantException;
use MadameAiguille\AiAssistant\Model\Config;
use MadameAiguille\AiAssistant\Model\DailyQuota;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class DailyQuotaTest extends TestCase
{
    private FlagManager&MockObject $flags;
    private mixed $stored = null;

    private function quota(int $limit = 3): DailyQuota
    {
        $this->flags = $this->createMock(FlagManager::class);
        $this->flags->method('getFlagData')->willReturnCallback(fn () => $this->stored);
        $this->flags->method('saveFlag')->willReturnCallback(function (string $code, mixed $value): bool {
            $this->stored = $value;
            return true;
        });
        $timezone = $this->createStub(TimezoneInterface::class);
        $timezone->method('date')->willReturnCallback(fn () => new \DateTime('2026-11-06 23:59:00'));
        $config = $this->createStub(Config::class);
        $config->method('getDailyLimit')->willReturn($limit);

        return new DailyQuota($this->flags, $timezone, $config);
    }

    public function testCountsEachCallAndStopsAtTheLimit(): void
    {
        $quota = $this->quota(2);
        $quota->consume();
        $quota->consume();
        self::assertSame(0, $quota->remaining());

        try {
            $quota->consume();
            self::fail('Le troisième appel aurait dû être refusé');
        } catch (AssistantException $exception) {
            self::assertSame(AssistantException::QUOTA, $exception->reason);
            self::assertStringContainsString('2 demandes par jour', $exception->getMessage());
        }
        self::assertSame(['date' => '2026-11-06', 'count' => 2], $this->stored);
    }

    public function testTheCounterStartsAgainTheNextDay(): void
    {
        $this->stored = ['date' => '2026-11-05', 'count' => 50];
        $quota = $this->quota(50);

        self::assertSame(50, $quota->remaining());
        $quota->consume();
        self::assertSame(['date' => '2026-11-06', 'count' => 1], $this->stored);
    }
}
