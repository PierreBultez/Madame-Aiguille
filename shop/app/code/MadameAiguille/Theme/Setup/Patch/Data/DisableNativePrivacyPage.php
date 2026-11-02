<?php
/**
 * Page native « privacy-policy-cookie-restriction-mode » (en anglais) : remplacée par « confidentialite », reliée
 * nulle part, mais active et donc publiée dans le sitemap à l'ouverture des ventes. Elle est désactivée.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DisableNativePrivacyPage implements DataPatchInterface
{
    public const IDENTIFIER = 'privacy-policy-cookie-restriction-mode';

    public function __construct(
        private readonly PageRepositoryInterface $repository,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function apply(): self
    {
        $criteria = $this->criteriaBuilder->addFilter(PageInterface::IDENTIFIER, self::IDENTIFIER)->create();
        foreach ($this->repository->getList($criteria)->getItems() as $page) {
            if ($page->isActive()) {
                $page->setIsActive(false);
                $this->repository->save($page);
            }
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
