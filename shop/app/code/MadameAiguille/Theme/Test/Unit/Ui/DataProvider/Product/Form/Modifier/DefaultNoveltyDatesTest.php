<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use MadameAiguille\Theme\Ui\DataProvider\Product\Form\Modifier\DefaultNoveltyDates;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class DefaultNoveltyDatesTest extends TestCase
{
    private function modifier(?int $productId, string $today = '2026-11-23 10:00:00'): DefaultNoveltyDates
    {
        $product = $this->createConfiguredStub(Product::class, ['getId' => $productId]);
        $locator = $this->createConfiguredStub(LocatorInterface::class, ['getProduct' => $product]);
        $timezone = $this->createStub(TimezoneInterface::class);
        $timezone->method('date')->willReturnCallback(fn () => new \DateTime($today));

        return new DefaultNoveltyDates($locator, $timezone);
    }

    public function testANewProductIsProposedOneMonthOfNovelty(): void
    {
        $data = $this->modifier(null)->modifyData(['' => ['product' => ['name' => 'Trousse']]]);

        self::assertSame('2026-11-23', $data['']['product']['news_from_date']);
        self::assertSame('2026-12-23', $data['']['product']['news_to_date']);
        self::assertSame('Trousse', $data['']['product']['name']);
    }

    public function testANewProductWithoutAnyDataStillGetsTheDates(): void
    {
        $data = $this->modifier(null, '2026-01-15 08:00:00')->modifyData([]);

        self::assertSame('2026-01-15', $data['']['product']['news_from_date']);
        self::assertSame('2026-02-15', $data['']['product']['news_to_date']);
    }

    public function testDatesAlreadyChosenAreKept(): void
    {
        $input = ['' => ['product' => ['news_from_date' => '', 'news_to_date' => '2027-01-01']]];

        self::assertSame($input, $this->modifier(null)->modifyData($input));
    }

    public function testAnExistingProductIsNeverTouched(): void
    {
        $input = [12 => ['product' => ['news_from_date' => null, 'news_to_date' => null]]];

        self::assertSame($input, $this->modifier(12)->modifyData($input));
    }
}
