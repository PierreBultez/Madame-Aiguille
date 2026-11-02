<?php
/**
 * Qualité (lot 8, Lighthouse du 02/11/2026) : les images de l'accueil et de « Nos tissus » passent du PNG au WebP
 * (3,6 Mo → 129 Ko ; l'accueil mobile affichait son image principale en 9,6 s).
 *
 * Remplace, dans les blocs et les pages CMS, les seules références exactes {{view url='images/home/X.png'}} écrites
 * par CreateHomeBlocks et CreateFabricPageAndPrepareAbout. Une image changée dans l'administration n'est pas touchée ;
 * les PNG restent dans le thème.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UseWebpHomeImages implements DataPatchInterface
{
    public const IMAGES = ['hero', 'atelier', 'ig-1', 'ig-2', 'ig-3', 'ig-4'];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        foreach (['cms_block', 'cms_page'] as $table) {
            foreach (self::IMAGES as $image) {
                $png = "{{view url='images/home/{$image}.png'}}";
                $webp = "{{view url='images/home/{$image}.webp'}}";
                $connection->update(
                    $this->moduleDataSetup->getTable($table),
                    ['content' => new \Zend_Db_Expr($connection->quoteInto('REPLACE(content, ?, ', $png)
                        . $connection->quote($webp) . ')')],
                    $connection->quoteInto('content LIKE ?', '%' . $png . '%')
                );
            }
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [CreateHomeBlocks::class, CreateFabricPageAndPrepareAbout::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
