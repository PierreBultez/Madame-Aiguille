<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Setup\Patch\Data;

use MadameAiguille\Theme\Setup\Patch\Data\StopPresettingTaxRegion;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StopPresettingTaxRegionTest extends TestCase
{
    public function testClearsTheRegionPresetByTheProject(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects(self::once())->method('save')->with('tax/defaults/region', '0');

        (new StopPresettingTaxRegion($this->configReturning('219'), $writer))->apply();
    }

    #[DataProvider('otherValues')]
    public function testLeavesAnyOtherChoiceUntouched(?string $value): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $writer->expects(self::never())->method('save');

        (new StopPresettingTaxRegion($this->configReturning($value), $writer))->apply();
    }

    public static function otherValues(): array
    {
        return [
            'déjà « * »' => ['0'],
            'absente' => [null],
            'choisie dans l’administration' => ['182'],
        ];
    }

    private function configReturning(?string $value): ScopeConfigInterface
    {
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->method('getValue')->with('tax/defaults/region')->willReturn($value);

        return $config;
    }
}
