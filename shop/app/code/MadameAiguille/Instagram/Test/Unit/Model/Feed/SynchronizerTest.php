<?php
declare(strict_types=1);

namespace MadameAiguille\Instagram\Test\Unit\Model\Feed;

use MadameAiguille\Instagram\Model\Api\ClientInterface;
use MadameAiguille\Instagram\Model\Api\InstagramException;
use MadameAiguille\Instagram\Model\Config;
use MadameAiguille\Instagram\Model\Feed\FlushGalleryCache;
use MadameAiguille\Instagram\Model\Feed\ImageStore;
use MadameAiguille\Instagram\Model\Feed\Storage;
use MadameAiguille\Instagram\Model\Feed\Synchronizer;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\TestCase;

class SynchronizerTest extends TestCase
{
    public function testStoresTheGalleryAndFlushesTheHomeWhenItChanges(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $client->method('fetchMedia')->willReturn([
            ['id' => '7', 'media_type' => 'IMAGE', 'media_url' => 'https://cdn/7.jpg', 'permalink' => 'https://ig/p/7',
                'caption' => 'Pochette céleste.', 'timestamp' => '2026-11-08T10:00:00+0000'],
        ]);
        $images = $this->createMock(ImageStore::class);
        $images->method('store')->with('7', 'https://cdn/7.jpg')->willReturn('madameaiguille/instagram/7.jpg');
        $images->expects(self::once())->method('prune')->with(['madameaiguille/instagram/7.jpg']);
        $storage = $this->createMock(Storage::class);
        $storage->method('getItems')->willReturn([]);
        $storage->expects(self::once())->method('saveFeed')->with([[
            'id' => '7',
            'file' => 'madameaiguille/instagram/7.jpg',
            'permalink' => 'https://ig/p/7',
            'alt' => 'Pochette céleste.',
            'width' => 640,
            'height' => 640,
        ]], 1_794_200_000);
        $flush = $this->createMock(FlushGalleryCache::class);
        $flush->expects(self::once())->method('execute');

        self::assertSame(1, $this->synchronizer($client, $images, $storage, $flush)->execute());
    }

    public function testKeepsThePreviousGalleryWhenNothingIsUsable(): void
    {
        $client = $this->createStub(ClientInterface::class);
        $client->method('fetchMedia')->willReturn([['id' => '8', 'media_type' => 'VIDEO']]);
        $storage = $this->createMock(Storage::class);
        $storage->expects(self::never())->method('saveFeed');

        $this->expectException(InstagramException::class);
        $this->synchronizer($client, $this->createStub(ImageStore::class), $storage, $this->createStub(FlushGalleryCache::class))
            ->execute();
    }

    private function synchronizer(
        ClientInterface $client,
        ImageStore $images,
        Storage $storage,
        FlushGalleryCache $flush
    ): Synchronizer {
        $config = $this->createStub(Config::class);
        $config->method('isEnabled')->willReturn(true);
        $config->method('getAccessToken')->willReturn('IGAA');
        $config->method('getCount')->willReturn(4);
        $clock = $this->createStub(DateTime::class);
        $clock->method('gmtTimestamp')->willReturn(1_794_200_000);

        return new Synchronizer($config, $client, $images, $storage, $flush, $clock);
    }
}
