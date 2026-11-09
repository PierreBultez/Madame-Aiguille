<?php
declare(strict_types=1);

namespace MadameAiguille\Instagram\Test\Unit\Model\Api;

use MadameAiguille\Instagram\Model\Api\GraphClient;
use MadameAiguille\Instagram\Model\Api\InstagramException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;

class GraphClientTest extends TestCase
{
    public function testReadsTheMediaList(): void
    {
        $client = $this->client(200, '{"data":[{"id":"1","media_type":"IMAGE"}]}', $url);

        self::assertSame([['id' => '1', 'media_type' => 'IMAGE']], $client->fetchMedia('IGAA-secret', 4));
        self::assertStringStartsWith('https://graph.instagram.com/me/media?fields=', $url);
    }

    public function testErrorsNeverContainTheToken(): void
    {
        $client = $this->client(400, '{"error":{"message":"Invalid OAuth access token IGAA-secret"}}', $url);

        try {
            $client->refreshToken('IGAA-secret');
            self::fail('Exception attendue.');
        } catch (InstagramException $exception) {
            self::assertStringNotContainsString('IGAA-secret', $exception->getMessage());
            self::assertStringContainsString('HTTP 400', $exception->getMessage());
        }
    }

    private function client(int $status, string $body, ?string &$url): GraphClient
    {
        $curl = $this->createStub(Curl::class);
        $curl->method('get')->willReturnCallback(function (string $called) use (&$url): void {
            $url = $called;
        });
        $curl->method('getStatus')->willReturn($status);
        $curl->method('getBody')->willReturn($body);
        $factory = $this->createStub(CurlFactory::class);
        $factory->method('create')->willReturn($curl);

        return new GraphClient($factory, new Json());
    }
}
