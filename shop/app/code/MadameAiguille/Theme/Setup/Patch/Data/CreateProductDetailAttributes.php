<?php
/**
 * Madame Aiguille — attributs du tableau « Caractéristiques » de la fiche produit
 *
 * composition (texte), dimensions (texte), entretien (texte long) : saisis
 * librement par Céline, affichés avec le poids et la taille de série
 * (ViewModel\Product\Characteristics). Regroupés dans un groupe
 * « Caractéristiques » de l'attribute set « Création ». Idempotent.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateProductDetailAttributes implements DataPatchInterface
{
    public const ATTRIBUTE_COMPOSITION = 'composition';
    public const ATTRIBUTE_DIMENSIONS = 'dimensions';
    public const ATTRIBUTE_ENTRETIEN = 'entretien';

    private const GROUP_NAME = 'Caractéristiques';
    private const GROUP_SORT_ORDER = 12;

    /** code => [libellé, type de saisie, backend, note] */
    private const ATTRIBUTES = [
        self::ATTRIBUTE_COMPOSITION => ['Composition', 'text', 'varchar', 'Ex. : extérieur 100 % coton, doublure coton enduit, ouatine polyester.'],
        self::ATTRIBUTE_DIMENSIONS => ['Dimensions', 'text', 'varchar', 'Ex. : 18 × 12 × 8 cm. Pour un modèle à deux tailles, préciser chaque taille.'],
        self::ATTRIBUTE_ENTRETIEN => ['Conseils d’entretien', 'textarea', 'text', 'Lavage, séchage, repassage.'],
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory,
        private readonly AttributeSetCollectionFactory $attributeSetCollectionFactory
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = (int) $eavSetup->getEntityTypeId(Product::ENTITY);
        $sortOrder = 10;

        foreach (self::ATTRIBUTES as $code => [$label, $input, $backendType, $note]) {
            if (!$eavSetup->getAttributeId(Product::ENTITY, $code)) {
                $eavSetup->addAttribute(Product::ENTITY, $code, [
                    'type' => $backendType,
                    'label' => $label,
                    'note' => $note,
                    'input' => $input,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => $code !== self::ATTRIBUTE_ENTRETIEN,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => 'simple,virtual,configurable',
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'sort_order' => $sortOrder,
                ]);
            }
            $sortOrder += 10;
        }

        $attributeSetId = $this->findCreationAttributeSetId($entityTypeId);

        if ($attributeSetId !== null) {
            $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, self::GROUP_NAME, self::GROUP_SORT_ORDER);
            $position = 10;

            foreach (array_keys(self::ATTRIBUTES) as $code) {
                $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, self::GROUP_NAME, $code, $position);
                $position += 10;
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    private function findCreationAttributeSetId(int $entityTypeId): ?int
    {
        $attributeSet = $this->attributeSetCollectionFactory->create()
            ->setEntityTypeFilter($entityTypeId)
            ->addFieldToFilter('attribute_set_name', CreateCreationAttributeSet::ATTRIBUTE_SET_NAME)
            ->setPageSize(1)
            ->getFirstItem();

        return $attributeSet->getId() ? (int) $attributeSet->getId() : null;
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
