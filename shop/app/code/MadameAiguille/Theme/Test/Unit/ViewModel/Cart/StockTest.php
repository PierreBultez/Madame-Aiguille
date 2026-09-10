<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\ViewModel\Cart;

use MadameAiguille\Theme\ViewModel\Cart\Stock;
use MadameAiguille\Theme\ViewModel\Product\LimitedSeries;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class StockTest extends TestCase
{
    private function item(Product $product, float $qty, ?Product $child = null): Item
    {
        $item = $this->createStub(Item::class);
        $item->method('getProduct')->willReturn($product);
        $item->method('getQty')->willReturn($qty);

        if ($child !== null) {
            $option = $this->createStub(Option::class);
            $option->method('getProduct')->willReturn($child);
            $item->method('getOptionByCode')->willReturn($option);
        }

        return $item;
    }

    public function testConfigurableLinesUseTheStockOfTheOrderedChildNotTheParentTotal(): void
    {
        $parent = $this->createStub(Product::class);
        $child = $this->createStub(Product::class);
        $child->method('isSalable')->willReturn(true);

        $limited = $this->createStub(LimitedSeries::class);
        $limited->method('getMaxQty')->willReturnCallback(
            static fn(Product $product): ?int => $product === $child ? 2 : 9
        );

        $viewModel = new Stock($limited);
        $item = $this->item($parent, 1.0, $child);

        self::assertSame($child, $viewModel->getStockProduct($item));
        self::assertSame(2, $viewModel->getMaxQty($item));
    }

    public function testUnmanagedStockLeavesTheQuantityUncappedAndSilent(): void
    {
        $product = $this->createStub(Product::class);
        $product->method('isSalable')->willReturn(true);

        $limited = $this->createStub(LimitedSeries::class);
        $limited->method('getMaxQty')->willReturn(null);

        $viewModel = new Stock($limited);
        $item = $this->item($product, 3.0);

        self::assertNull($viewModel->getMaxQty($item));
        self::assertNull($viewModel->getQtyHint($item));
        self::assertTrue($viewModel->isAvailable($item));
    }

    public function testALineBecomesUnavailableWhenStockFallsBelowTheQuantityAlreadyInTheCart(): void
    {
        $product = $this->createStub(Product::class);
        $product->method('isSalable')->willReturn(true);

        $limited = $this->createStub(LimitedSeries::class);
        $limited->method('getMaxQty')->willReturn(1);

        $viewModel = new Stock($limited);

        self::assertTrue($viewModel->isAvailable($this->item($product, 1.0)));
        self::assertFalse($viewModel->isAvailable($this->item($product, 2.0)));
    }

    public function testADeactivatedOrSoldOutProductIsNeverAvailable(): void
    {
        $product = $this->createStub(Product::class);
        $product->method('isSalable')->willReturn(false);

        $limited = $this->createStub(LimitedSeries::class);
        $limited->method('getMaxQty')->willReturn(5);

        self::assertFalse((new Stock($limited))->isAvailable($this->item($product, 1.0)));
    }

    public function testTheHintAnnouncesTheCapBeforeItIsReachedAndTheCeilingOnceItIs(): void
    {
        $product = $this->createStub(Product::class);
        $product->method('isSalable')->willReturn(true);

        $limited = $this->createStub(LimitedSeries::class);
        $limited->method('getMaxQty')->willReturn(3);
        $limited->method('isLimitedSeries')->willReturn(true);

        $viewModel = new Stock($limited);

        self::assertSame(
            'Quantité plafonnée à 3 — le stock restant de la série.',
            (string) $viewModel->getQtyHint($this->item($product, 1.0))
        );
        self::assertSame('Stock maximum atteint', (string) $viewModel->getQtyHint($this->item($product, 3.0)));
    }
}
