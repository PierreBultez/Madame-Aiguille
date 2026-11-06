<?php
/**
 * Lot 10 (décision de Pierre du 06/11/2026) : Page Builder quitte le champ Description des produits, et les
 * descriptions actuelles sont vidées, sans migration.
 *
 * - `is_pagebuilder_enabled` passe à 0 sur `description` seul : le champ repasse à l'éditeur WYSIWYG classique, où
 *   le HTML de l'assistant s'insère et se relit tel qu'il sera affiché. Les autres attributs et les blocs CMS gardent
 *   Page Builder ;
 * - toutes les valeurs de `description` sont supprimées, sur toutes les vues ; les SKU touchés vont dans
 *   `system.log`. Céline réécrit ses fiches avec l'assistant.
 *
 * Joué une seule fois (data patch) : une description écrite après le déploiement n'est jamais effacée.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\PageBuilder\Setup\Patch\Data\UpdateProductAttribute;
use Psr\Log\LoggerInterface;

class RemovePageBuilderFromDescription implements DataPatchInterface
{
    public const ATTRIBUTE = 'description';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function apply(): self
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeId = (int) $eavSetup->getAttributeId(Product::ENTITY, self::ATTRIBUTE);
        if ($attributeId === 0) {
            return $this;
        }
        $eavSetup->updateAttribute(Product::ENTITY, self::ATTRIBUTE, ['is_pagebuilder_enabled' => 0]);

        $connection = $this->moduleDataSetup->getConnection();
        $values = $this->moduleDataSetup->getTable('catalog_product_entity_text');
        $skus = $connection->fetchCol(
            $connection->select()
                ->distinct()
                ->from(['product' => $this->moduleDataSetup->getTable('catalog_product_entity')], ['sku'])
                ->join(['value' => $values], 'value.entity_id = product.entity_id', [])
                ->where('value.attribute_id = ?', $attributeId)
                ->order('product.sku')
        );
        $deleted = $connection->delete($values, ['attribute_id = ?' => $attributeId]);

        $this->logger->info(sprintf(
            'Lot 10 : Page Builder retiré de « description » ; %d valeur(s) supprimée(s), produits : %s',
            $deleted,
            $skus === [] ? 'aucun' : implode(', ', $skus)
        ));

        return $this;
    }

    public static function getDependencies(): array
    {
        return [UpdateProductAttribute::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
