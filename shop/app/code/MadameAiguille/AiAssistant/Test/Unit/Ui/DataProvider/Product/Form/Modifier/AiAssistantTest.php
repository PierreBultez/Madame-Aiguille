<?php
declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use MadameAiguille\AiAssistant\Model\Config;
use MadameAiguille\AiAssistant\Ui\DataProvider\Product\Form\Modifier\AiAssistant;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;

class AiAssistantTest extends TestCase
{
    private const META = ['content' => ['children' => ['container_description' => []]]];

    private function modify(bool $ready, bool $allowed, array $meta = self::META): array
    {
        $config = $this->createStub(Config::class);
        $config->method('isReady')->willReturn($ready);
        $authorization = $this->createStub(AuthorizationInterface::class);
        $authorization->method('isAllowed')->willReturnCallback(
            fn (string $resource): bool => $allowed && $resource === 'MadameAiguille_AiAssistant::assistant'
        );
        $url = $this->createStub(UrlInterface::class);
        $url->method('getUrl')->willReturnCallback(fn (string $route): string => 'https://admin.test/' . $route);
        $product = $this->createStub(ProductInterface::class);
        $product->method('getId')->willReturn(null);
        $product->method('getTypeId')->willReturn('configurable');
        $locator = $this->createStub(LocatorInterface::class);
        $locator->method('getProduct')->willReturn($product);

        return (new AiAssistant($config, $authorization, $url, $locator))->modifyMeta($meta);
    }

    public function testAddsThePanelAboveTheDescriptionForANewProduct(): void
    {
        $config = $this->modify(true, true)['content']['children']['madameaiguille_ai_assistant']['arguments']['data']['config'];

        self::assertSame('MadameAiguille_AiAssistant/js/assistant', $config['component']);
        self::assertSame(5, $config['sortOrder'], 'Entre la description courte et la description');
        self::assertSame(0, $config['productId']);
        self::assertSame('configurable', $config['productType']);
        self::assertSame('https://admin.test/madameaiguille_ai/assistant/analyser', $config['analyseUrl']);
        self::assertSame('https://admin.test/madameaiguille_ai/assistant/generer', $config['generateUrl']);
    }

    public function testTheFormIsUntouchedWhenNotReadyNotAllowedOrWithoutContent(): void
    {
        self::assertSame(self::META, $this->modify(false, true));
        self::assertSame(self::META, $this->modify(true, false));
        self::assertSame(['product-details' => []], $this->modify(true, true, ['product-details' => []]));
    }
}
