<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateHomeFeaturedAttribute implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $setup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply(): self
    {
        $eav = $this->eavSetupFactory->create(['setup' => $this->setup]);
        if (!$eav->getAttributeId(Product::ENTITY, 'home_featured')) {
            $eav->addAttribute(Product::ENTITY, 'home_featured', [
                'type' => 'int', 'input' => 'boolean', 'source' => Boolean::class,
                'label' => 'Incontournable sur l’accueil', 'default' => '0',
                'note' => 'Les quatre produits disponibles les plus récemment créés sont affichés.',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'required' => false, 'user_defined' => true, 'visible' => true,
                'used_in_product_listing' => true, 'is_used_for_promo_rules' => true,
                'apply_to' => 'simple,virtual,configurable',
            ]);
        }
        $setId = $eav->getAttributeSetId(Product::ENTITY, CreateCreationAttributeSet::ATTRIBUTE_SET_NAME);
        $eav->addAttributeGroup(Product::ENTITY, $setId, 'Accueil', 12);
        $eav->addAttributeToGroup(Product::ENTITY, $setId, 'Accueil', 'home_featured', 10);
        return $this;
    }

    public static function getDependencies(): array
    {
        return [CreateCreationAttributeSet::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
