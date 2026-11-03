<?php
declare(strict_types=1);

namespace MadameAiguille\Analytics\Test\Unit\Model\Csp;

use MadameAiguille\Analytics\Model\Config;
use MadameAiguille\Analytics\Model\Csp\MatomoPolicyCollector;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;

class MatomoPolicyCollectorTest extends TestCase
{
    /** @param array<string, string> $values */
    private function collector(array $values): MatomoPolicyCollector
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(fn (string $path) => $values[$path] ?? null);
        $scopeConfig->method('isSetFlag')->willReturnCallback(fn (string $path) => (bool) ($values[$path] ?? false));

        return new MatomoPolicyCollector(new Config($scopeConfig));
    }

    public function testAllowsOnlyTheConfiguredOriginForScriptsConnectionsAndImages(): void
    {
        $policies = $this->collector([
            Config::XML_PATH_ENABLED => '1',
            Config::XML_PATH_URL => 'https://stats.example.test/matomo/',
            Config::XML_PATH_SITE_ID => '1',
        ])->collect();

        self::assertSame(
            ['script-src', 'connect-src', 'img-src'],
            array_map(fn (FetchPolicy $policy): string => $policy->getId(), $policies)
        );
        foreach ($policies as $policy) {
            self::assertSame(['https://stats.example.test'], $policy->getHostSources());
            self::assertFalse($policy->isInlineAllowed());
        }
    }

    public function testAddsNothingWhileTheMeasurementIsNotReady(): void
    {
        $existing = [new FetchPolicy('script-src', false, ['https://widget.example.test'])];

        self::assertSame($existing, $this->collector([
            Config::XML_PATH_ENABLED => '0',
            Config::XML_PATH_URL => 'https://stats.example.test/',
            Config::XML_PATH_SITE_ID => '1',
        ])->collect($existing));
    }
}
