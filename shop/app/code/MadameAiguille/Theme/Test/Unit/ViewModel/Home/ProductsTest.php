<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\ViewModel\Home;

use MadameAiguille\Theme\ViewModel\Home\Products;
use MadameAiguille\Theme\ViewModel\Product\LimitedSeries;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ProductsTest extends TestCase
{
    public function testSoldOutProductsDoNotConsumePlacesAndNativeOrderIsPreserved(): void
    {
        $candidates = array_map(fn() => $this->createStub(Product::class), range(1, 7));
        $limited = $this->createStub(LimitedSeries::class);
        $limited->method('isSoldOut')->willReturnCallback(
            fn(Product $product): bool => in_array($product, [$candidates[0], $candidates[2]], true)
        );
        $viewModel = new Products($limited);
        self::assertSame(
            [$candidates[1], $candidates[3], $candidates[4], $candidates[5]],
            $viewModel->selectAvailable($candidates, 4)
        );
    }

    public function testEmptyOrEntirelySoldOutSelectionReturnsNoCards(): void
    {
        $limited = $this->createStub(LimitedSeries::class);
        $limited->method('isSoldOut')->willReturn(true);
        $viewModel = new Products($limited);
        self::assertSame([], $viewModel->selectAvailable([], 4));
        self::assertSame([], $viewModel->selectAvailable([$this->createStub(Product::class)], 4));
        self::assertSame([], $viewModel->selectAvailable([$this->createStub(Product::class)], 0));
    }

    public function testEditorialAttributeIsAppliedBeforeLimit(): void
    {
        $first = $this->createStub(Product::class);
        $second = $this->createStub(Product::class);
        $third = $this->createStub(Product::class);
        $first->method('getData')->willReturnMap([['home_featured', null, 0]]);
        $second->method('getData')->willReturnMap([['home_featured', null, 1]]);
        $third->method('getData')->willReturnMap([['home_featured', null, 1]]);
        $limited = $this->createStub(LimitedSeries::class);
        $limited->method('isSoldOut')->willReturn(false);

        self::assertSame(
            [$second, $third],
            (new Products($limited))->selectAvailable([$first, $second, $third], 4, 'home_featured')
        );
    }
}
