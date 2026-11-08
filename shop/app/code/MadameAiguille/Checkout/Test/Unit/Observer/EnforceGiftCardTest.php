<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Observer;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use MadameAiguille\Checkout\Observer\EnforceGiftCard;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\GiftMessage\Model\Message;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\GiftMessage\Model\ResourceModel\Message as MessageResource;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class EnforceGiftCardTest extends TestCase
{
    public function testFreeMessageWithoutWrappingIsDroppedWithoutBlocking(): void
    {
        [$observer, $quote, $order] = $this->submission(0.0, 7);
        $order->setGiftMessageId(7);

        $this->guard([])->execute($observer);

        self::assertNull($quote->getGiftMessageId());
        self::assertNull($order->getGiftMessageId());
    }

    public function testWrappingWithACompleteCardPasses(): void
    {
        [$observer, $quote] = $this->submission(2.0, 7);

        $this->guard(['sender' => 'Pauline', 'recipient' => 'Marie', 'message' => 'Bonne fête'])->execute($observer);

        self::assertSame(7, $quote->getGiftMessageId());
    }

    public function testWrappingWithoutCardIsRefused(): void
    {
        [$observer] = $this->submission(2.0, 0);

        $this->expectException(LocalizedException::class);
        $this->guard([])->execute($observer);
    }

    public function testWrappingWithAnIncompleteCardIsRefused(): void
    {
        [$observer] = $this->submission(2.0, 7);

        $this->expectException(LocalizedException::class);
        $this->guard(['sender' => 'Pauline', 'recipient' => '', 'message' => 'Bonne fête'])->execute($observer);
    }

    /**
     * @return array{0: Observer, 1: Quote, 2: Order}
     */
    private function submission(float $amount, int $messageId): array
    {
        $address = new DataObject([Config::AMOUNT => $amount]);
        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()
            ->onlyMethods(['isVirtual', 'getShippingAddress'])->getMock();
        $quote->method('isVirtual')->willReturn(false);
        $quote->method('getShippingAddress')->willReturn($address);
        $quote->setData('gift_message_id', $messageId);
        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->onlyMethods([])->getMock();

        $observer = new Observer(['event' => new Event(['quote' => $quote, 'order' => $order])]);

        return [$observer, $quote, $order];
    }

    private function guard(array $stored): EnforceGiftCard
    {
        $message = $this->getMockBuilder(Message::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $factory = $this->createStub(MessageFactory::class);
        $factory->method('create')->willReturn($message);
        $resource = $this->createStub(MessageResource::class);
        $resource->method('load')->willReturnCallback(static function (Message $target) use ($stored, $resource) {
            $target->addData($stored);

            return $resource;
        });

        return new EnforceGiftCard($factory, $resource);
    }
}
