<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\ViewModel\Cart;

use MadameAiguille\Theme\ViewModel\Cart\FreeShipping;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class FreeShippingTest extends TestCase
{
    private function viewModel(string|float|null $threshold): FreeShipping
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn($threshold);

        $priceCurrency = $this->createStub(PriceCurrencyInterface::class);
        $priceCurrency->method('format')->willReturnCallback(
            static fn(float $amount): string => number_format($amount, 2, ',', ' ') . ' €'
        );

        return new FreeShipping($scopeConfig, $priceCurrency);
    }

    public function testThresholdAtZeroDisablesTheBarEntirely(): void
    {
        $viewModel = $this->viewModel('0');

        self::assertFalse($viewModel->isEnabled());
        self::assertNull($viewModel->getMessage(20.0));
        self::assertNull($viewModel->getProgressLabel(20.0));
        self::assertNull($viewModel->getThresholdLabel());
        self::assertSame(0, $viewModel->getProgress(20.0));
        self::assertFalse($viewModel->isReached(1000.0));
    }

    public function testRemainingAmountDrivesTheMessageUntilTheThresholdIsCrossed(): void
    {
        $viewModel = $this->viewModel('49');

        self::assertSame(7.0, $viewModel->getRemaining(42.0));
        self::assertSame('Plus que 7,00 € pour la livraison offerte', (string) $viewModel->getMessage(42.0));
        self::assertFalse($viewModel->isReached(42.0));

        self::assertSame(0.0, $viewModel->getRemaining(49.0));
        self::assertTrue($viewModel->isReached(49.0));
        self::assertSame('Livraison offerte : c’est acquis.', (string) $viewModel->getMessage(49.0));
        self::assertSame('Livraison offerte : c’est acquis.', (string) $viewModel->getMessage(60.0));
    }

    public function testProgressIsBoundedBetweenZeroAndOneHundred(): void
    {
        $viewModel = $this->viewModel('49');

        self::assertSame(0, $viewModel->getProgress(0.0));
        self::assertSame(86, $viewModel->getProgress(42.0));
        self::assertSame(100, $viewModel->getProgress(49.0));
        self::assertSame(100, $viewModel->getProgress(120.0));
    }

    public function testStateExposesEverythingTheDrawerNeeds(): void
    {
        $state = $this->viewModel('49')->getState(42.0);

        self::assertSame(
            [
                'enabled' => true,
                'reached' => false,
                'progress' => 86,
                'message' => 'Plus que 7,00 € pour la livraison offerte',
                'progress_label' => '42,00 € / 49,00 €',
            ],
            $state
        );
    }

    public function testNegativeOrMissingThresholdIsTreatedAsDisabled(): void
    {
        self::assertFalse($this->viewModel(null)->isEnabled());
        self::assertFalse($this->viewModel('-10')->isEnabled());
    }
}
