<?php
declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Test\Unit\Model;

use MadameAiguille\AiAssistant\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @param array<string, string|null> $values
     */
    private function config(array $values): Config
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(fn (string $path) => $values[$path] ?? null);
        $scopeConfig->method('isSetFlag')->willReturnCallback(fn (string $path) => (bool) ($values[$path] ?? false));
        return new Config($scopeConfig);
    }

    public function testReadyOnlyWhenEnabledWithAKey(): void
    {
        self::assertFalse($this->config([])->isReady());
        self::assertFalse($this->config([Config::XML_PATH_ENABLED => '1'])->isReady());
        self::assertFalse($this->config([Config::XML_PATH_API_KEY => 'sk-test'])->isReady());
        self::assertTrue($this->config([
            Config::XML_PATH_ENABLED => '1',
            Config::XML_PATH_API_KEY => 'sk-test',
        ])->isReady());
    }

    public function testTheKeyArrivesDecryptedByMagentoAndAnEmptyOneIsAbsent(): void
    {
        self::assertSame('sk-test', $this->config([Config::XML_PATH_API_KEY => 'sk-test'])->getApiKey());
        self::assertNull($this->config([Config::XML_PATH_API_KEY => ' '])->getApiKey());
    }

    public function testAnInvalidDailyLimitFallsBackToFifty(): void
    {
        self::assertSame(20, $this->config([Config::XML_PATH_DAILY_LIMIT => '20'])->getDailyLimit());
        self::assertSame(50, $this->config([Config::XML_PATH_DAILY_LIMIT => '0'])->getDailyLimit());
        self::assertSame(50, $this->config([Config::XML_PATH_DAILY_LIMIT => 'beaucoup'])->getDailyLimit());
    }
}
