<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\ViewModel\Product;

use MadameAiguille\Theme\ViewModel\Product\ConfigurableOptions;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as ProductAttribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ConfigurableOptionsTest extends TestCase
{
    /**
     * Super-attribut du configurable : getProductAttribute() y est magique, d'où l'objet de données.
     */
    private function superAttribute(int $id, ?string $storeLabel): DataObject
    {
        $productAttribute = $storeLabel === null
            ? null
            : $this->createConfiguredStub(ProductAttribute::class, ['getStoreLabel' => $storeLabel]);

        return new DataObject(['attribute_id' => $id, 'product_attribute' => $productAttribute]);
    }

    private function product(object $type): Product
    {
        return $this->createConfiguredStub(Product::class, ['getTypeInstance' => $type]);
    }

    public function testAConfigurableGivesItsStorefrontLabelsInLowerCaseByAttributeId(): void
    {
        $type = $this->createStub(Configurable::class);
        $type->method('getConfigurableAttributes')->willReturn([
            $this->superAttribute(93, 'Couleur'),
            $this->superAttribute(145, 'Taille'),
            $this->superAttribute(150, null),
            $this->superAttribute(151, '  '),
        ]);

        self::assertSame(
            ['93' => 'couleur', '145' => 'taille'],
            (new ConfigurableOptions())->getAttributeLabels($this->product($type))
        );
    }

    public function testASimpleProductHasNothingToChoose(): void
    {
        self::assertSame(
            [],
            (new ConfigurableOptions())->getAttributeLabels($this->product($this->createStub(Simple::class)))
        );
    }
}
