<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Model\Shipping;

use MadameAiguille\Theme\Model\Shipping\GridRules;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class GridRulesTest extends TestCase
{
    private function rules(array $allowed = ['FR', 'BE']): GridRules
    {
        $allowedCountries = $this->createStub(AllowedCountries::class);
        $allowedCountries->method('getAllowedCountries')->willReturn($allowed);

        return new GridRules($allowedCountries);
    }

    public function testCountsTiersPerCountryWhenEveryAllowedCountryStartsAtZero(): void
    {
        $tiers = $this->rules()->check([['FR', 0.0], ['FR', 0.5], ['BE', 0.0], ['FR', 1.0]]);

        self::assertSame(['BE' => 1, 'FR' => 3], $tiers);
    }

    public function testRejectsAGridThatDoesNotStartAtZero(): void
    {
        $this->expectException(LocalizedException::class);
        $this->rules()->check([['FR', 0.0], ['BE', 0.25]]);
    }

    public function testRejectsACountryOutsideTheSalesArea(): void
    {
        $this->expectException(LocalizedException::class);
        $this->rules()->check([['FR', 0.0], ['BE', 0.0], ['CH', 0.0]]);
    }

    public function testRejectsAnAllowedCountryWithoutRate(): void
    {
        $this->expectException(LocalizedException::class);
        $this->rules()->check([['FR', 0.0]]);
    }

    public function testRejectsAnEmptyGrid(): void
    {
        $this->expectException(LocalizedException::class);
        $this->rules()->check([]);
    }
}
