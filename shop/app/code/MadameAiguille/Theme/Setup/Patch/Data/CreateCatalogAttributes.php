<?php
/**
 * Madame Aiguille — attributs catalogue des séries limitées
 *
 * Modélisation (architecture-technique.md §4, spécification §2.1) :
 *   - taille        : liste déroulante à 2 valeurs, portée globale, rendue en
 *                     swatch texte (boutons) ; seul axe de variante des configurables
 *   - serie_limitee : booléen, déclenche la mention « Série limitée »
 *   - taille_serie  : entier, nombre de pièces de la série (« — 8 pièces »)
 *   - weight        : attribut natif rendu OBLIGATOIRE (frais de port au poids)
 *
 * Les trois attributs sont chargés dans les collections de listing pour que
 * les badges des vignettes n'exigent aucune requête supplémentaire. Idempotent.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogAttribute;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;
use Magento\Swatches\Model\Swatch;

class CreateCatalogAttributes implements DataPatchInterface
{
    public const ATTRIBUTE_TAILLE = 'taille';
    public const ATTRIBUTE_SERIE_LIMITEE = 'serie_limitee';
    public const ATTRIBUTE_TAILLE_SERIE = 'taille_serie';

    /** Valeurs de la taille, dans l'ordre d'affichage du sélecteur */
    private const TAILLE_OPTIONS = ['Petit', 'Grand'];

    /** Types de produit concernés : le simple (cas majoritaire) et le configurable (2 tailles) */
    private const APPLY_TO = 'simple,virtual,configurable';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory,
        private readonly EavConfig $eavConfig
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $this->createSerieLimitee($eavSetup);
        $this->createTailleSerie($eavSetup);
        $this->createTaille($eavSetup);
        $this->makeWeightRequired($eavSetup);

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    private function createSerieLimitee(EavSetup $eavSetup): void
    {
        if ($eavSetup->getAttributeId(Product::ENTITY, self::ATTRIBUTE_SERIE_LIMITEE)) {
            return;
        }

        $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_SERIE_LIMITEE, [
            'type' => 'int',
            'label' => 'Série limitée',
            'note' => 'Affiche la mention « Série limitée » sur la vignette et la fiche.',
            'input' => 'boolean',
            'source' => Boolean::class,
            'default' => '0',
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => self::APPLY_TO,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => true,
            'sort_order' => 10,
        ]);
    }

    private function createTailleSerie(EavSetup $eavSetup): void
    {
        if ($eavSetup->getAttributeId(Product::ENTITY, self::ATTRIBUTE_TAILLE_SERIE)) {
            return;
        }

        $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_TAILLE_SERIE, [
            'type' => 'int',
            'label' => 'Nombre de pièces de la série',
            'note' => 'Entier. Complète la mention : « Série limitée — 8 pièces ».',
            'input' => 'text',
            'frontend_class' => 'validate-digits validate-greater-than-zero',
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => self::APPLY_TO,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'sort_order' => 20,
        ]);
    }

    /**
     * Liste déroulante globale, utilisable comme axe de configurable, puis
     * convertie en swatch texte pour que Hyvä la rende en boutons (Design System §6.5).
     */
    private function createTaille(EavSetup $eavSetup): void
    {
        if (!$eavSetup->getAttributeId(Product::ENTITY, self::ATTRIBUTE_TAILLE)) {
            $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_TAILLE, [
                'type' => 'int',
                'label' => 'Taille',
                'input' => 'select',
                'option' => ['values' => self::TAILLE_OPTIONS],
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => self::APPLY_TO,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
                'sort_order' => 30,
            ]);
        }

        $this->eavConfig->clear();

        /** @var CatalogAttribute $attribute */
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, self::ATTRIBUTE_TAILLE);

        if ($this->isTextSwatch($attribute)) {
            return;
        }

        // Format attendu par Magento\Swatches\Model\Plugin\EavAttribute :
        // libellés et swatches indexés par identifiant d'option, puis par store.
        $optionLabels = [];
        $optionOrder = [];
        $position = 0;

        foreach ($attribute->getSource()->getAllOptions(false) as $option) {
            $optionId = (string) $option['value'];
            $optionLabels[$optionId] = [Store::DEFAULT_STORE_ID => (string) $option['label']];
            $optionOrder[$optionId] = (string) $position++;
        }

        $attribute->addData([
            Swatch::SWATCH_INPUT_TYPE_KEY => Swatch::SWATCH_INPUT_TYPE_TEXT,
            'update_product_preview_image' => 0,
            'use_product_image_for_swatch' => 0,
            'optiontext' => ['value' => $optionLabels, 'order' => $optionOrder],
            'swatchtext' => ['value' => $optionLabels],
        ]);
        $attribute->save();
    }

    private function isTextSwatch(CatalogAttribute $attribute): bool
    {
        $additionalData = $attribute->getData('additional_data');

        if (!is_string($additionalData) || $additionalData === '') {
            return false;
        }

        $decoded = json_decode($additionalData, true);

        return ($decoded[Swatch::SWATCH_INPUT_TYPE_KEY] ?? null) === Swatch::SWATCH_INPUT_TYPE_TEXT;
    }

    /**
     * Le poids conditionne le calcul des frais de port : un produit sans poids
     * fausserait silencieusement le tarif (spécification §2.1, §9).
     */
    private function makeWeightRequired(EavSetup $eavSetup): void
    {
        $eavSetup->updateAttribute(Product::ENTITY, 'weight', 'is_required', 1);
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
