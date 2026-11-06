<?php
declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Test\Unit\Setup\Patch\Data;

use MadameAiguille\AiAssistant\Setup\Patch\Data\RemovePageBuilderFromDescription as Patch;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\PageBuilder\Setup\Patch\Data\UpdateProductAttribute;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[AllowMockObjectsWithoutExpectations]
class RemovePageBuilderFromDescriptionTest extends TestCase
{
    private EavSetup&MockObject $eavSetup;
    private AdapterInterface&MockObject $connection;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->eavSetup = $this->createMock(EavSetup::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $select = $this->createStub(Select::class);
        foreach (['distinct', 'from', 'join', 'where', 'order'] as $method) {
            $select->method($method)->willReturnSelf();
        }
        $this->connection->method('select')->willReturn($select);
    }

    private function apply(): void
    {
        $setup = $this->createStub(ModuleDataSetupInterface::class);
        $setup->method('getConnection')->willReturn($this->connection);
        $setup->method('getTable')->willReturnArgument(0);
        $factory = $this->createStub(EavSetupFactory::class);
        $factory->method('create')->willReturn($this->eavSetup);

        (new Patch($setup, $factory, $this->logger))->apply();
    }

    public function testDisablesPageBuilderOnDescriptionOnlyAndEmptiesItsValuesEverywhere(): void
    {
        $this->eavSetup->method('getAttributeId')->with('catalog_product', 'description')->willReturn(73);
        $this->eavSetup->expects(self::once())->method('updateAttribute')
            ->with('catalog_product', 'description', ['is_pagebuilder_enabled' => 0]);
        $this->connection->method('fetchCol')->willReturn(['ADE1', 'CT-1', 'TC-1', 'TV-1']);
        $this->connection->expects(self::once())->method('delete')
            ->with('catalog_product_entity_text', ['attribute_id = ?' => 73])
            ->willReturn(4);
        $this->logger->expects(self::once())->method('info')->with(
            'Lot 10 : Page Builder retiré de « description » ; 4 valeur(s) supprimée(s), produits : ADE1, CT-1, TC-1, TV-1'
        );

        $this->apply();
    }

    public function testNoDescriptionAttributeMeansNothingIsTouched(): void
    {
        $this->eavSetup->method('getAttributeId')->willReturn(false);
        $this->eavSetup->expects(self::never())->method('updateAttribute');
        $this->connection->expects(self::never())->method('delete');

        $this->apply();
    }

    public function testRunsAfterPageBuilderHasEnabledItself(): void
    {
        self::assertSame([UpdateProductAttribute::class], Patch::getDependencies());
    }
}
