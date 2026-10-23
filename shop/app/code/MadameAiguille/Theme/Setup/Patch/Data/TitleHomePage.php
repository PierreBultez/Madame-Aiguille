<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Titre et description de l'accueil (onglet du navigateur, résultats des moteurs), repris des textes du hero.
 * Remplace seulement le titre par défaut de Magento : un titre saisi par Céline n'est jamais réécrit.
 */
class TitleHomePage implements DataPatchInterface
{
    private const DEFAULT_TITLE = 'Home page';
    private const TITLE = 'Accueil';
    private const META_TITLE = 'Madame Aiguille — De petites créations, de grands bonheurs';
    private const META_DESCRIPTION = 'Des accessoires en tissu pour embellir les petits instants du quotidien.';

    public function __construct(
        private readonly PageRepositoryInterface $repository,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function apply(): self
    {
        $criteria = $this->criteriaBuilder->addFilter(PageInterface::IDENTIFIER, 'home')->create();
        foreach ($this->repository->getList($criteria)->getItems() as $page) {
            if (trim((string) $page->getTitle()) !== self::DEFAULT_TITLE) {
                continue;
            }
            $page->setTitle(self::TITLE);
            if (trim((string) $page->getMetaTitle()) === '') {
                $page->setMetaTitle(self::META_TITLE);
            }
            if (trim((string) $page->getMetaDescription()) === '') {
                $page->setMetaDescription(self::META_DESCRIPTION);
            }
            $this->repository->save($page);
        }

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
