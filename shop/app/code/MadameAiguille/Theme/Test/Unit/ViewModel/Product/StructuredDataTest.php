<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\ViewModel\Product;

use MadameAiguille\Theme\ViewModel\Product\StructuredData;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Catalog\Pricing\Price\FinalPriceInterface;
use Magento\Framework\Pricing\PriceInfo\Base as PriceInfo;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class StructuredDataTest extends TestCase
{
    /**
     * @param array<string, mixed> $data
     */
    private function product(array $data, float $low, float $high, bool $salable = true, array $images = []): Product
    {
        $amount = fn (float $value): AmountInterface => $this->createConfiguredStub(AmountInterface::class, ['getValue' => $value]);
        $price = $this->createStub(FinalPriceInterface::class);
        $price->method('getMinimalPrice')->willReturn($amount($low));
        $price->method('getMaximalPrice')->willReturn($amount($high));
        $priceInfo = $this->createStub(PriceInfo::class);
        $priceInfo->method('getPrice')->willReturn($price);

        $product = $this->getMockBuilder(Product::class)->disableOriginalConstructor()
            ->onlyMethods(['getPriceInfo', 'isSalable', 'getProductUrl', 'getMediaGalleryImages', 'getData', 'getName', 'getSku'])
            ->getMock();
        $product->method('getData')->willReturnCallback(fn ($key = '') => $data[$key] ?? null);
        $product->method('getName')->willReturn($data['name'] ?? null);
        $product->method('getSku')->willReturn($data['sku'] ?? null);
        $product->method('getPriceInfo')->willReturn($priceInfo);
        $product->method('isSalable')->willReturn($salable);
        $product->method('getProductUrl')->willReturn('https://madame-aiguille.fr/coffret-tendresse.html');
        $gallery = new Collection($this->createStub(EntityFactoryInterface::class));
        foreach ($images as $image) {
            $gallery->addItem(new DataObject($image));
        }
        $product->method('getMediaGalleryImages')->willReturn($gallery);

        return $product;
    }

    private function viewModel(): StructuredData
    {
        $store = $this->createConfiguredStub(Store::class, ['getCurrentCurrencyCode' => 'EUR']);

        return new StructuredData($this->createConfiguredStub(StoreManagerInterface::class, ['getStore' => $store]));
    }

    public function testASimpleProductHasAnOfferWithItsPriceStockAndPhotos(): void
    {
        $data = $this->viewModel()->getData($this->product([
            'name' => 'Le coffret tendresse',
            'sku' => 'CT-1',
            'meta_description' => 'Le coffret tendresse',
            'short_description' => '<p>Une panière &amp; quatre cotons.</p>',
        ], 34.0, 34.0, true, [
            ['url' => 'https://madame-aiguille.fr/media/a.jpg', 'media_type' => 'image', 'id' => 1],
            ['url' => 'https://madame-aiguille.fr/media/b.jpg', 'disabled' => '1', 'id' => 2],
        ]));

        self::assertSame('Product', $data['@type']);
        self::assertSame(['@type' => 'Brand', 'name' => 'Madame Aiguille'], $data['brand']);
        self::assertSame(['https://madame-aiguille.fr/media/a.jpg'], $data['image']);
        self::assertSame('Une panière & quatre cotons.', $data['description']);
        self::assertSame([
            '@type' => 'Offer',
            'price' => 34.0,
            'priceCurrency' => 'EUR',
            'availability' => 'https://schema.org/InStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'url' => 'https://madame-aiguille.fr/coffret-tendresse.html',
        ], $data['offers']);
    }

    public function testASizedModelWithTwoPricesIsAnAggregateOfferAndSoldOutIsOutOfStock(): void
    {
        $offers = $this->viewModel()->getData($this->product(['name' => 'Trousse'], 24.0, 29.0, false))['offers'];

        self::assertSame('AggregateOffer', $offers['@type']);
        self::assertSame([24.0, 29.0], [$offers['lowPrice'], $offers['highPrice']]);
        self::assertSame('https://schema.org/OutOfStock', $offers['availability']);
    }

    public function testTheJsonCannotCloseItsScriptTag(): void
    {
        $json = $this->viewModel()->getJson($this->product(['name' => 'Trousse </script><script>alert(1)'], 0.0, 0.0));

        self::assertStringNotContainsString('</script>', $json);
        self::assertArrayNotHasKey('offers', json_decode($json, true), 'Sans prix, pas d\'offre');
    }
}
