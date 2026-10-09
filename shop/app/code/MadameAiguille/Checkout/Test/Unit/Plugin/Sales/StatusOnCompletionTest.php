<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Plugin\Sales;

use MadameAiguille\Checkout\Model\Carrier\Pickup;
use MadameAiguille\Checkout\Plugin\Sales\StatusOnCompletion;
use MadameAiguille\Theme\Model\Order\StatusConfig;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Handler\State;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class StatusOnCompletionTest extends TestCase
{
    private function statusAfterCheck(string $state, string $status, string $shippingMethod): string
    {
        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()
            ->onlyMethods(['getShippingMethod'])->getMock();
        $order->method('getShippingMethod')->willReturn($shippingMethod);
        $order->setState($state)->setStatus($status);

        $state = $this->createStub(State::class);
        (new StatusOnCompletion())->afterCheck($state, $state, $order);

        return (string) $order->getStatus();
    }

    public function testAShippedParcelBecomesShipped(): void
    {
        self::assertSame(StatusConfig::SHIPPED, $this->statusAfterCheck('complete', 'complete', 'tablerate_bestway'));
    }

    public function testAPickupHandedOverBecomesDelivered(): void
    {
        self::assertSame(StatusConfig::DELIVERED, $this->statusAfterCheck('complete', 'complete', Pickup::SHIPPING_METHOD));
    }

    public function testAStatusSetByHandIsKept(): void
    {
        self::assertSame(StatusConfig::DELIVERED, $this->statusAfterCheck('complete', StatusConfig::DELIVERED, 'tablerate_bestway'));
    }

    public function testOtherStatesAreLeftAlone(): void
    {
        self::assertSame('processing', $this->statusAfterCheck('processing', 'processing', 'tablerate_bestway'));
    }
}
