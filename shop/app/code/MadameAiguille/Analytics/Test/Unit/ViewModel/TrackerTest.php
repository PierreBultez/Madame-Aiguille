<?php
declare(strict_types=1);

namespace MadameAiguille\Analytics\Test\Unit\ViewModel;

use MadameAiguille\Analytics\Model\Config;
use MadameAiguille\Analytics\ViewModel\Tracker;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactory;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TrackerTest extends TestCase
{
    private const READY = [
        Config::XML_PATH_ENABLED => '1',
        Config::XML_PATH_URL => 'https://stats.example.test',
        Config::XML_PATH_SITE_ID => '1',
    ];

    /** @param array<string, string> $config */
    private function tracker(array $config = [], ?\Throwable $layerFailure = null): Tracker
    {
        $values = array_merge(self::READY, $config);
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(fn (string $path) => $values[$path] ?? null);
        $scopeConfig->method('isSetFlag')->willReturnCallback(fn (string $path) => (bool) ($values[$path] ?? false));

        $query = $this->createStub(Query::class);
        $query->method('getQueryText')->willReturn('pochette "lin"');
        $queryFactory = $this->createStub(QueryFactory::class);
        $queryFactory->method('get')->willReturn($query);

        $collection = $this->createStub(Collection::class);
        $collection->method('getSize')->willReturn(3);
        $layer = $this->createStub(Layer::class);
        $layerFailure === null
            ? $layer->method('getProductCollection')->willReturn($collection)
            : $layer->method('getProductCollection')->willThrowException($layerFailure);
        $resolver = $this->createStub(LayerResolver::class);
        $resolver->method('get')->willReturn($layer);

        return new Tracker(new Config($scopeConfig), $queryFactory, $resolver);
    }

    public function testRendersNothingUntilTheMeasurementIsEnabledAndComplete(): void
    {
        foreach ([
            [Config::XML_PATH_ENABLED => '0'],
            [Config::XML_PATH_URL => ''],
            [Config::XML_PATH_URL => 'http://stats.example.test/'],
            [Config::XML_PATH_SITE_ID => ''],
            [Config::XML_PATH_SITE_ID => '0'],
            [Config::XML_PATH_SITE_ID => 'abc'],
        ] as $config) {
            self::assertSame([], $this->tracker($config)->getAttributes(), json_encode($config));
        }
    }

    public function testAnOrdinaryPageCarriesOnlyTheAddressAndTheSite(): void
    {
        self::assertSame(
            ['data-url' => 'https://stats.example.test/', 'data-site-id' => '1'],
            $this->tracker()->getAttributes()
        );
    }

    public function testTheSearchPageCarriesTheKeywordAndTheResultCount(): void
    {
        self::assertSame(
            [
                'data-url' => 'https://stats.example.test/',
                'data-site-id' => '1',
                'data-page' => 'search',
                'data-search-keyword' => 'pochette "lin"',
                'data-search-count' => '3',
            ],
            $this->tracker()->getAttributes(Tracker::PAGE_SEARCH)
        );
    }

    public function testAnUnavailableResultCountIsLeftOut(): void
    {
        $attributes = $this->tracker([], new \RuntimeException('couche absente'))->getAttributes(Tracker::PAGE_SEARCH);

        self::assertArrayNotHasKey('data-search-count', $attributes);
        self::assertSame('pochette "lin"', $attributes['data-search-keyword']);
    }

    public function testTheNotFoundPageIsMarked(): void
    {
        self::assertSame('not-found', $this->tracker()->getAttributes(Tracker::PAGE_NOT_FOUND)['data-page']);
    }
}
