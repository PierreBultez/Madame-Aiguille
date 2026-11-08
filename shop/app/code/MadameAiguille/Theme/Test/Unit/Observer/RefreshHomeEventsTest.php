<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Observer;

use MadameAiguille\Theme\Model\Home\FlushEventsCache;
use MadameAiguille\Theme\Observer\RefreshHomeEvents;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\TestCase;

class RefreshHomeEventsTest extends TestCase
{
    public function testFlushesWhenTheEventsChange(): void
    {
        $flush = $this->createMock(FlushEventsCache::class);
        $flush->expects(self::once())->method('execute');

        (new RefreshHomeEvents($flush))->execute($this->saved(['madameaiguille/home_events/list']));
    }

    public function testLeavesTheCacheAloneForOtherSettings(): void
    {
        $flush = $this->createMock(FlushEventsCache::class);
        $flush->expects(self::never())->method('execute');

        (new RefreshHomeEvents($flush))->execute($this->saved(['madameaiguille/cart/show_coupon']));
    }

    private function saved(array $paths): Observer
    {
        return new Observer(['event' => new Event(['changed_paths' => $paths])]);
    }
}
