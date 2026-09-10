<?php
/**
 * Madame Aiguille — test unitaire du ViewModel LimitedSeries
 *
 * Lancement, depuis shop/ :
 *   vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/MadameAiguille/Theme/Test/Unit
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\ViewModel\Product;

use MadameAiguille\Theme\ViewModel\Product\LimitedSeries;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

// Le produit est un mock partiel (getMockBuilder) sans attente d'appel : PHPUnit le signale sinon
#[AllowMockObjectsWithoutExpectations]
class LimitedSeriesTest extends TestCase
{
    private ScopeConfigInterface|Stub $scopeConfig;
    private TimezoneInterface|Stub $timezone;
    private GetProductSalableQtyInterface|Stub $salableQty;
    private StockItemConfigurationInterface|Stub $stockItemConfiguration;
    private LimitedSeries $viewModel;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $this->scopeConfig->method('getValue')->willReturnCallback(fn(string $path) => match ($path) {
            'madameaiguille/catalog/scarcity_threshold' => '3',
            'madameaiguille/catalog/price_note' => '  TVA non applicable  ',
            default => null,
        });

        $this->timezone = $this->createStub(TimezoneInterface::class);

        $website = $this->createStub(WebsiteInterface::class);
        $website->method('getCode')->willReturn('base');
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getWebsite')->willReturn($website);

        $stock = $this->createStub(StockInterface::class);
        $stock->method('getStockId')->willReturn(1);
        $stockResolver = $this->createStub(StockResolverInterface::class);
        $stockResolver->method('execute')->willReturn($stock);

        $this->salableQty = $this->createStub(GetProductSalableQtyInterface::class);

        $this->stockItemConfiguration = $this->createStub(StockItemConfigurationInterface::class);
        $this->stockItemConfiguration->method('isManageStock')->willReturn(true);
        $getStockItemConfiguration = $this->createStub(GetStockItemConfigurationInterface::class);
        $getStockItemConfiguration->method('execute')->willReturn($this->stockItemConfiguration);

        $isAllowed = $this->createStub(IsSourceItemManagementAllowedForProductTypeInterface::class);
        $isAllowed->method('execute')->willReturnCallback(fn(string $type) => $type === 'simple');

        $urlBuilder = $this->createStub(UrlInterface::class);
        $urlBuilder->method('getUrl')->willReturnCallback(
            fn(string $route, array $params) => 'http://localhost/' . $route . '?product=' . $params['_query']['product']
        );

        $this->viewModel = new LimitedSeries(
            $this->scopeConfig,
            $this->timezone,
            $urlBuilder,
            $storeManager,
            $stockResolver,
            $this->salableQty,
            $getStockItemConfiguration,
            $isAllowed
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function product(array $data, bool $salable, float $qty = 10.0): Product|Stub
    {
        // getSku() et getTypeInstance() passent par la factory de type, absente hors DI : on les simule
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isSalable', 'getStore', 'getSku', 'getTypeInstance'])
            ->getMock();
        $product->method('isSalable')->willReturn($salable);
        $product->method('getStore')->willReturn(null);
        $product->method('getSku')->willReturn('MA-TEST');
        $typeInstance = $this->createStub(Configurable::class);
        $typeInstance->method('getUsedProducts')->willReturn([]);
        $product->method('getTypeInstance')->willReturn($typeInstance);
        $product->setData($data + ['entity_id' => 42, 'type_id' => 'simple']);

        $this->salableQty->method('execute')->willReturn($qty);

        return $product;
    }

    public function testSoldOutOverridesEveryOtherBadge(): void
    {
        $this->timezone->method('isScopeDateInInterval')->willReturn(true);
        $product = $this->product(['serie_limitee' => 1, 'taille_serie' => 8, 'news_from_date' => '2026-09-01'], false, 0.0);

        $badge = $this->viewModel->getBadge($product);

        self::assertSame(LimitedSeries::BADGE_SOLDOUT, $badge['type']);
        self::assertSame('Épuisé', (string) $badge['label']);
        self::assertSame('badge badge-soldout', $badge['css']);
        self::assertSame('Série terminée', (string) $this->viewModel->getSoldOutLabel($product));
        self::assertFalse($this->viewModel->isScarce($product));
    }

    public function testScarcityBeatsNoveltyAndUsesRemainingQty(): void
    {
        $this->timezone->method('isScopeDateInInterval')->willReturn(true);
        $product = $this->product(['serie_limitee' => 1, 'taille_serie' => 10, 'news_from_date' => '2026-09-01'], true, 2.0);

        $badge = $this->viewModel->getBadge($product);

        self::assertSame(LimitedSeries::BADGE_SCARCE, $badge['type']);
        self::assertSame('Plus que 2 exemplaires', (string) $badge['label']);
        self::assertSame(2, $this->viewModel->getMaxQty($product));
        // La ligne secondaire rappelle la série puisque le badge dit autre chose
        self::assertSame('Série limitée — 10 pièces', $this->viewModel->getCardSubtitle($product));
    }

    public function testSingularScarcityLabel(): void
    {
        $product = $this->product(['serie_limitee' => 0], true, 1.0);

        self::assertSame('Plus qu’un exemplaire', (string) $this->viewModel->getBadge($product)['label']);
    }

    public function testNoveltyBeatsLimitedSeries(): void
    {
        $this->timezone->method('isScopeDateInInterval')->willReturn(true);
        $product = $this->product(['serie_limitee' => 1, 'taille_serie' => 8, 'news_from_date' => '2026-09-01'], true, 8.0);

        self::assertSame(LimitedSeries::BADGE_NEW, $this->viewModel->getBadge($product)['type']);
    }

    public function testLimitedSeriesBadgeAndShortDescriptionSubtitle(): void
    {
        $this->timezone->method('isScopeDateInInterval')->willReturn(false);
        $product = $this->product(
            ['serie_limitee' => 1, 'taille_serie' => 6, 'short_description' => '<p>Doublée coton, fermeture aimantée.</p>'],
            true,
            4.0
        );

        $badge = $this->viewModel->getBadge($product);

        self::assertSame(LimitedSeries::BADGE_LIMITED, $badge['type']);
        self::assertSame('Série limitée — 6 pièces', (string) $badge['label']);
        self::assertSame('Doublée coton, fermeture aimantée.', $this->viewModel->getCardSubtitle($product));
    }

    public function testLimitedSeriesWithoutSize(): void
    {
        $product = $this->product(['serie_limitee' => 1, 'taille_serie' => null], true, 15.0);

        self::assertSame('Série limitée', (string) $this->viewModel->getSeriesLabel($product));
        self::assertNull($this->viewModel->getSeriesSize($product));
    }

    public function testNoBadgeForRegularProductWithComfortableStock(): void
    {
        $product = $this->product(['serie_limitee' => 0], true, 15.0);

        self::assertNull($this->viewModel->getBadge($product));
        self::assertNull($this->viewModel->getSeriesLabel($product));
        self::assertNull($this->viewModel->getCardSubtitle($product));
        self::assertSame('Épuisé', (string) $this->viewModel->getSoldOutLabel($product));
    }

    public function testThresholdZeroDisablesScarcity(): void
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn('0');
        $viewModel = new LimitedSeries(
            $scopeConfig,
            $this->timezone,
            $this->createStub(UrlInterface::class),
            $this->createStub(StoreManagerInterface::class),
            $this->createStub(StockResolverInterface::class),
            $this->salableQty,
            $this->createStub(GetStockItemConfigurationInterface::class),
            $this->createStub(IsSourceItemManagementAllowedForProductTypeInterface::class)
        );
        $product = $this->product([], true, 1.0);

        self::assertSame(0, $viewModel->getScarcityThreshold());
        self::assertFalse($viewModel->isScarce($product));
    }

    public function testConfigurableSubtitleWithoutShortDescription(): void
    {
        $product = $this->product(['type_id' => 'configurable', 'serie_limitee' => 0], true);

        self::assertSame('Deux tailles disponibles', $this->viewModel->getCardSubtitle($product));
    }

    public function testPriceNoteIsTrimmedAndContactUrlCarriesSku(): void
    {
        $product = $this->product([], true);

        self::assertSame('TVA non applicable', $this->viewModel->getPriceNote());
        self::assertSame('http://localhost/contact?product=MA-TEST', $this->viewModel->getContactUrl($product));
    }
}
