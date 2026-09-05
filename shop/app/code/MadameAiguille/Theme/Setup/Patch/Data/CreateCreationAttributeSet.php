<?php
/**
 * Madame Aiguille — attribute set « Création »
 *
 * Dérivé du set Default (mêmes groupes, dont le poids obligatoire), complété
 * d'un groupe « Série limitée » regroupant serie_limitee, taille_serie et
 * taille. C'est le set à utiliser pour toutes les créations de l'atelier.
 * Idempotent : ne recrée pas le set s'il existe déjà.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateCreationAttributeSet implements DataPatchInterface
{
    public const ATTRIBUTE_SET_NAME = 'Création';

    private const GROUP_NAME = 'Série limitée';

    /** Après « Product Details » (sort_order 10), avant « Content » (15) */
    private const GROUP_SORT_ORDER = 11;

    /** code attribut => position dans le groupe */
    private const GROUP_ATTRIBUTES = [
        CreateCatalogAttributes::ATTRIBUTE_SERIE_LIMITEE => 10,
        CreateCatalogAttributes::ATTRIBUTE_TAILLE_SERIE => 20,
        CreateCatalogAttributes::ATTRIBUTE_TAILLE => 30,
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory,
        private readonly AttributeSetFactory $attributeSetFactory,
        private readonly AttributeSetCollectionFactory $attributeSetCollectionFactory
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = (int) $eavSetup->getEntityTypeId(Product::ENTITY);

        $attributeSetId = $this->findAttributeSetId($entityTypeId);

        if ($attributeSetId === null) {
            $attributeSetId = $this->createFromDefault($eavSetup, $entityTypeId);
        }

        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, self::GROUP_NAME, self::GROUP_SORT_ORDER);

        foreach (self::GROUP_ATTRIBUTES as $attributeCode => $sortOrder) {
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                self::GROUP_NAME,
                $attributeCode,
                $sortOrder
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    private function findAttributeSetId(int $entityTypeId): ?int
    {
        $collection = $this->attributeSetCollectionFactory->create()
            ->setEntityTypeFilter($entityTypeId)
            ->addFieldToFilter('attribute_set_name', self::ATTRIBUTE_SET_NAME)
            ->setPageSize(1);

        $attributeSet = $collection->getFirstItem();

        return $attributeSet->getId() ? (int) $attributeSet->getId() : null;
    }

    /**
     * Clone le squelette du set Default : groupes, attributs système, poids requis.
     */
    private function createFromDefault(EavSetup $eavSetup, int $entityTypeId): int
    {
        $defaultSetId = (int) $eavSetup->getDefaultAttributeSetId($entityTypeId);

        $attributeSet = $this->attributeSetFactory->create();
        $attributeSet->setData([
            'attribute_set_name' => self::ATTRIBUTE_SET_NAME,
            'entity_type_id' => $entityTypeId,
            'sort_order' => 50,
        ]);
        $attributeSet->validate();
        $attributeSet->save();
        $attributeSet->initFromSkeleton($defaultSetId)->save();

        return (int) $attributeSet->getId();
    }

    public static function getDependencies(): array
    {
        return [CreateCatalogAttributes::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
