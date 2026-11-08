<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Model\GiftWrap;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use MadameAiguille\Checkout\Model\GiftWrap\OrderGiftCard;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GiftMessage\Model\Message;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\GiftMessage\Model\ResourceModel\Message as MessageResource;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class OrderGiftCardTest extends TestCase
{
    public function testNoCardWithoutMessage(): void
    {
        self::assertNull($this->service([])->forOrder($this->order(0, 0.0)));
        self::assertNull($this->service([])->forOrder(null));
    }

    public function testWrappedOrderUsesTheOptionLabel(): void
    {
        $card = $this->service(['sender' => 'Pauline', 'recipient' => 'Marie', 'message' => 'Bonne fête'])
            ->forOrder($this->order(7, 2.0));

        self::assertSame('Emballage et message cadeau', $card['title']);
        self::assertTrue($card['wrapped']);
        self::assertSame(['Pauline', 'Marie', 'Bonne fête'], [$card['sender'], $card['recipient'], $card['message']]);
    }

    public function testOlderFreeMessageKeepsItsOwnTitle(): void
    {
        $card = $this->service(['sender' => 'Pauline', 'recipient' => 'Marie', 'message' => 'Bonne fête'])
            ->forOrder($this->order(7, 0.0));

        self::assertFalse($card['wrapped']);
        self::assertSame('Gift Message', $card['title']);
    }

    private function order(int $messageId, float $amount): Order
    {
        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $order->setData('gift_message_id', $messageId);
        $order->setData(Config::AMOUNT, $amount);
        $order->setData('store_id', 1);

        return $order;
    }

    private function service(array $stored): OrderGiftCard
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn('Emballage et message cadeau');

        $message = $this->getMockBuilder(Message::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $factory = $this->createStub(MessageFactory::class);
        $factory->method('create')->willReturn($message);
        $resource = $this->createStub(MessageResource::class);
        $resource->method('load')->willReturnCallback(static function (Message $target) use ($stored, $resource) {
            $target->addData($stored);

            return $resource;
        });

        return new OrderGiftCard(new Config($scopeConfig), $factory, $resource);
    }
}
