<?php
declare(strict_types=1);

namespace MadameAiguille\Instagram\Test\Unit\Model\Feed;

use MadameAiguille\Instagram\Model\Api\ClientInterface;
use MadameAiguille\Instagram\Model\Config;
use MadameAiguille\Instagram\Model\Feed\Storage;
use MadameAiguille\Instagram\Model\Feed\TokenRenewer;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\TestCase;

class TokenRenewerTest extends TestCase
{
    private const NOW = 1_794_200_000;

    public function testRenewsWeeklyAndAtFirstRun(): void
    {
        self::assertTrue(TokenRenewer::isDue(null, self::NOW));
        self::assertTrue(TokenRenewer::isDue(self::NOW - 8 * 86400, self::NOW));
        self::assertFalse(TokenRenewer::isDue(self::NOW - 2 * 86400, self::NOW));
    }

    public function testStoresTheRenewedTokenEncryptedAndItsDate(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isEnabled')->willReturn(true);
        $config->method('getAccessToken')->willReturn('IGAA-ancien');
        $config->expects(self::once())->method('saveAccessToken')->with('IGAA-neuf');

        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('refreshToken')->with('IGAA-ancien')
            ->willReturn(['access_token' => 'IGAA-neuf', 'expires_in' => 5184000]);

        $storage = $this->createMock(Storage::class);
        $storage->method('getTokenRefreshedAt')->willReturn(self::NOW - 9 * 86400);
        $storage->expects(self::once())->method('saveTokenRefresh')->with(self::NOW, 5184000);

        self::assertTrue((new TokenRenewer($config, $client, $storage, $this->clock()))->execute());
    }

    public function testDoesNothingWhenDisabledOrRecent(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::never())->method('refreshToken');

        $disabled = $this->createStub(Config::class);
        $disabled->method('isEnabled')->willReturn(false);
        $disabled->method('getAccessToken')->willReturn('IGAA');
        self::assertFalse((new TokenRenewer($disabled, $client, $this->createStub(Storage::class), $this->clock()))->execute());

        $enabled = $this->createStub(Config::class);
        $enabled->method('isEnabled')->willReturn(true);
        $enabled->method('getAccessToken')->willReturn('IGAA');
        $recent = $this->createStub(Storage::class);
        $recent->method('getTokenRefreshedAt')->willReturn(self::NOW - 86400);
        self::assertFalse((new TokenRenewer($enabled, $client, $recent, $this->clock()))->execute());
    }

    private function clock(): DateTime
    {
        $clock = $this->createStub(DateTime::class);
        $clock->method('gmtTimestamp')->willReturn(self::NOW);

        return $clock;
    }
}
