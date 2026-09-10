<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Observer;

use MadameAiguille\Theme\Model\Order\Email\StatusEmailSender;
use MadameAiguille\Theme\Observer\SendOrderStatusEmail;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class SendOrderStatusEmailTest extends TestCase
{
    public function testChangedStatusIsForwardedToTheSender(): void
    {
        $order = $this->createMock(Order::class);
        $order->expects(self::once())->method('dataHasChangedFor')->with('status')->willReturn(true);
        $sender = $this->createMock(StatusEmailSender::class);
        $sender->expects(self::once())->method('send')->with($order);

        $observer = new Observer(['event' => new DataObject(['order' => $order])]);
        (new SendOrderStatusEmail($sender))->execute($observer);
    }

    public function testUnchangedStatusDoesNotSendAnEmail(): void
    {
        $order = $this->createMock(Order::class);
        $order->method('dataHasChangedFor')->with('status')->willReturn(false);
        $sender = $this->createMock(StatusEmailSender::class);
        $sender->expects(self::never())->method('send');

        $observer = new Observer(['event' => new DataObject(['order' => $order])]);
        (new SendOrderStatusEmail($sender))->execute($observer);
    }
}
