<?php
/**
 * Madame Aiguille — tri « Nouveautés » en page catégorie
 *
 * Le tri natif ne propose que position, nom et prix. Pour trier par date de
 * mise en ligne (spécification §1.2), on rend l'attribut système created_at
 * utilisable en tri : il est alors ajouté à l'index OpenSearch et aux ordres
 * disponibles du bloc Toolbar. Les libellés proposés à la cliente sont portés
 * par ViewModel\Catalog\Sorting. Idempotent.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MakeCreatedAtSortable implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply(): self
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->updateAttribute(Product::ENTITY, 'created_at', 'used_for_sort_by', 1);
        $eavSetup->updateAttribute(Product::ENTITY, 'created_at', 'frontend_label', 'Date de mise en ligne');

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
