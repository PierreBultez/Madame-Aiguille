<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\ViewModel\Order;

use MadameAiguille\Theme\Model\Order\StatusConfig;
use MadameAiguille\Theme\ViewModel\Order\Progress;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ProgressTest extends TestCase
{
    private function viewModel(): Progress
    {
        $timezone = $this->createStub(TimezoneInterface::class);
        $timezone->method('formatDateTime')->willReturnCallback(
            static fn(string $date): string => substr($date, 0, 10)
        );

        return new Progress($timezone, new StatusConfig());
    }

    /**
     * @param OrderStatusHistoryInterface[] $histories
     */
    private function order(string $status, array $histories = [], float $quantity = 2.0): OrderInterface
    {
        $order = $this->createStub(OrderInterface::class);
        $order->method('getStatus')->willReturn($status);
        $order->method('getStatusHistories')->willReturn($histories);
        $order->method('getUpdatedAt')->willReturn('2026-09-10 10:00:00');
        $order->method('getTotalItemCount')->willReturn($quantity);

        return $order;
    }

    private function history(string $status, string $date): OrderStatusHistoryInterface
    {
        $history = $this->createStub(OrderStatusHistoryInterface::class);
        $history->method('getStatus')->willReturn($status);
        $history->method('getCreatedAt')->willReturn($date);

        return $history;
    }

    public function testEveryProjectStatusHasACustomerSentenceAndBadgeVariant(): void
    {
        $expected = [
            StatusConfig::PENDING_PAYMENT => 'pending',
            StatusConfig::PAYMENT_RECEIVED => 'paid',
            StatusConfig::PREPARING => 'preparing',
            StatusConfig::SHIPPED => 'shipped',
            StatusConfig::READY_FOR_PICKUP => 'pickup',
            StatusConfig::DELIVERED => 'delivered',
        ];

        foreach ($expected as $status => $variant) {
            $order = $this->order($status);
            self::assertNotSame('', (string) $this->viewModel()->getPhrase($order));
            self::assertSame($variant, $this->viewModel()->getBadgeVariant($order));
        }
    }

    public function testDeliveryTimelineUsesHistoryDatesAndMarksTheCurrentStep(): void
    {
        $order = $this->order(StatusConfig::SHIPPED, [
            $this->history(StatusConfig::SHIPPED, '2026-09-04 12:00:00'),
            $this->history(StatusConfig::PREPARING, '2026-09-03 12:00:00'),
            $this->history(StatusConfig::PAYMENT_RECEIVED, '2026-09-02 12:00:00'),
        ]);

        $timeline = $this->viewModel()->getTimeline($order);

        self::assertSame(3, $this->viewModel()->getCurrentStep($order));
        self::assertSame('complete', $timeline[2]['state']);
        self::assertSame('current', $timeline[3]['state']);
        self::assertSame('2026-09-04', $timeline[3]['date']);
        self::assertSame('upcoming', $timeline[4]['state']);
    }

    public function testPickupHistorySelectsThePickupTimeline(): void
    {
        $order = $this->order(StatusConfig::READY_FOR_PICKUP, [
            $this->history(StatusConfig::READY_FOR_PICKUP, '2026-09-10 12:00:00'),
        ]);

        $timeline = $this->viewModel()->getTimeline($order);

        self::assertSame(StatusConfig::READY_FOR_PICKUP, $timeline[3]['code']);
        self::assertSame('current', $timeline[3]['state']);
    }

    public function testQuantityLabelHandlesSingularAndPlural(): void
    {
        self::assertSame('1 pièce', (string) $this->viewModel()->getQuantityLabel($this->order('unknown', [], 1)));
        self::assertSame('3 pièces', (string) $this->viewModel()->getQuantityLabel($this->order('unknown', [], 3)));
    }

    public function testNativeMagentoStatusesAreMappedToCustomerProgress(): void
    {
        $processing = $this->order('processing');
        $complete = $this->order('complete');

        self::assertSame('preparing', $this->viewModel()->getBadgeVariant($processing));
        self::assertSame(2, $this->viewModel()->getCurrentStep($processing));
        self::assertSame('shipped', $this->viewModel()->getBadgeVariant($complete));
        self::assertSame(3, $this->viewModel()->getCurrentStep($complete));
    }
}
