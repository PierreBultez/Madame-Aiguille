<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\ViewModel\Styleguide;

use MadameAiguille\Theme\Model\Order\StatusConfig;
use MadameAiguille\Theme\ViewModel\Order\Progress;
use MadameAiguille\Theme\ViewModel\Styleguide\OrderStates;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status\History;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class OrderStatesTest extends TestCase
{
    private function viewModel(): OrderStates
    {
        $timezone = $this->createStub(TimezoneInterface::class);
        $timezone->method('formatDateTime')->willReturnCallback(
            static fn(string $date): string => substr($date, 0, 10)
        );

        // Objets nus : la démonstration ne doit toucher ni la base ni le registre de statuts.
        $orderFactory = $this->createStub(OrderInterfaceFactory::class);
        $orderFactory->method('create')->willReturnCallback(
            fn(): Order => $this->createOrderDouble()
        );

        $historyFactory = $this->createStub(OrderStatusHistoryInterfaceFactory::class);
        $historyFactory->method('create')->willReturnCallback(
            fn(): History => $this->createHistoryDouble()
        );

        return new OrderStates(
            $orderFactory,
            $historyFactory,
            new Progress($timezone, new StatusConfig()),
            new StatusConfig()
        );
    }

    private function createOrderDouble(): Order
    {
        return new class extends Order {
            private array $values = [];

            public function __construct()
            {
            }

            public function setStatus($status)
            {
                $this->values['status'] = $status;

                return $this;
            }

            public function getStatus()
            {
                return $this->values['status'] ?? null;
            }

            public function setStatusHistories(?array $statusHistories = null)
            {
                $this->values['histories'] = $statusHistories;

                return $this;
            }

            public function getStatusHistories()
            {
                return $this->values['histories'] ?? [];
            }

            public function setTotalItemCount($totalItemCount)
            {
                $this->values['count'] = $totalItemCount;

                return $this;
            }

            public function getTotalItemCount()
            {
                return $this->values['count'] ?? 0;
            }

            public function setUpdatedAt($timestamp)
            {
                $this->values['updated_at'] = $timestamp;

                return $this;
            }

            public function getUpdatedAt()
            {
                return $this->values['updated_at'] ?? null;
            }
        };
    }

    private function createHistoryDouble(): History
    {
        return new class extends History {
            private array $values = [];

            public function __construct()
            {
            }

            public function setStatus($status)
            {
                $this->values['status'] = $status;

                return $this;
            }

            public function getStatus()
            {
                return $this->values['status'] ?? null;
            }

            public function setCreatedAt($createdAt)
            {
                $this->values['created_at'] = $createdAt;

                return $this;
            }

            public function getCreatedAt()
            {
                return $this->values['created_at'] ?? null;
            }
        };
    }

    public function testOneCardPerProjectStatusWithoutDuplicate(): void
    {
        $cards = $this->viewModel()->getCards();

        $codes = array_column($cards, 'status');
        self::assertSame(array_keys((new StatusConfig())->getAll()), $codes);
        self::assertSame($codes, array_unique($codes));
    }

    public function testEveryCardCarriesTheRealProgressSentenceAndBadge(): void
    {
        foreach ($this->viewModel()->getCards() as $card) {
            self::assertNotSame('', (string) $card['phrase']);
            self::assertNotSame('neutral', $card['badge']);
            self::assertSame('2 pièces', (string) $card['quantity']);
        }
    }

    public function testDeliveryTimelineStopsAtShippedAndPickupTimelineReplacesIt(): void
    {
        [$delivery, $pickup] = $this->viewModel()->getTimelines();

        $deliveryCodes = array_column($delivery['timeline'], 'code');
        self::assertContains(StatusConfig::SHIPPED, $deliveryCodes);
        self::assertNotContains(StatusConfig::READY_FOR_PICKUP, $deliveryCodes);
        self::assertSame(
            'current',
            $delivery['timeline'][array_search(StatusConfig::SHIPPED, $deliveryCodes, true)]['state']
        );

        $pickupCodes = array_column($pickup['timeline'], 'code');
        self::assertContains(StatusConfig::READY_FOR_PICKUP, $pickupCodes);
        self::assertNotContains(StatusConfig::SHIPPED, $pickupCodes);
    }

    public function testCompletedStepsAreDated(): void
    {
        [$delivery] = $this->viewModel()->getTimelines();

        foreach ($delivery['timeline'] as $step) {
            if ($step['state'] === 'upcoming') {
                self::assertNull($step['date']);
                continue;
            }

            self::assertNotNull($step['date']);
        }
    }

    public function testStatusTableExposesTheMagentoStateOfEveryStatus(): void
    {
        $statuses = $this->viewModel()->getStatuses();

        self::assertCount(6, $statuses);
        foreach ($statuses as $status) {
            self::assertNotSame('', $status['state']);
            self::assertNotSame('', (string) $status['label']);
        }
    }
}
