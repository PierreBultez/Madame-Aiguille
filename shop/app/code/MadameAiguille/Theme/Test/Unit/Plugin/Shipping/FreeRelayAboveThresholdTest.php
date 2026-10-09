<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Plugin\Shipping;

use MadameAiguille\Theme\Plugin\Shipping\FreeRelayAboveThreshold;
use MadameAiguille\Theme\ViewModel\Cart\FreeShipping;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\OfflineShipping\Model\Carrier\Tablerate;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\Result;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class FreeRelayAboveThresholdTest extends TestCase
{
    private function plugin(string $threshold): FreeRelayAboveThreshold
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn($threshold);

        return new FreeRelayAboveThreshold(
            new FreeShipping($scopeConfig, $this->createStub(PriceCurrencyInterface::class))
        );
    }

    private function rateResult(Method $rate): Result
    {
        $result = $this->createStub(Result::class);
        $result->method('getAllRates')->willReturn([$rate]);

        return $result;
    }

    public function testRelayBecomesFreeOnceTheSubtotalReachesTheThreshold(): void
    {
        $rate = $this->createMock(Method::class);
        $rate->expects(self::once())->method('setPrice')->with(0);

        $this->plugin('60')->afterCollectRates(
            $this->createStub(Tablerate::class),
            $this->rateResult($rate),
            new RateRequest(['base_subtotal_incl_tax' => 60.0])
        );
    }

    public function testRelayKeepsItsPriceBelowTheThreshold(): void
    {
        $rate = $this->createMock(Method::class);
        $rate->expects(self::never())->method('setPrice');

        $this->plugin('60')->afterCollectRates(
            $this->createStub(Tablerate::class),
            $this->rateResult($rate),
            new RateRequest(['base_subtotal_incl_tax' => 59.99])
        );
    }

    public function testThresholdAtZeroNeverMakesTheRelayFree(): void
    {
        $rate = $this->createMock(Method::class);
        $rate->expects(self::never())->method('setPrice');

        $this->plugin('0')->afterCollectRates(
            $this->createStub(Tablerate::class),
            $this->rateResult($rate),
            new RateRequest(['base_subtotal_incl_tax' => 500.0])
        );
    }

    public function testUnavailableCarrierIsLeftUntouched(): void
    {
        self::assertFalse($this->plugin('60')->afterCollectRates(
            $this->createStub(Tablerate::class),
            false,
            new RateRequest(['base_subtotal_incl_tax' => 100.0])
        ));
    }
}
