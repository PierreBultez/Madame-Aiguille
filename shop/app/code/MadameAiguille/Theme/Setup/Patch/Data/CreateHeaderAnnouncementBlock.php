<?php
/**
 * Madame Aiguille — bloc CMS du bandeau d'annonce du header
 *
 * Texte de la maquette par défaut ; Céline le modifie (ou le vide pour masquer
 * le bandeau) depuis Contenu › Blocs. Idempotent.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;

class CreateHeaderAnnouncementBlock implements DataPatchInterface
{
    public const BLOCK_IDENTIFIER = 'header_announcement';

    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly BlockInterfaceFactory $blockFactory,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function apply(): self
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(BlockInterface::IDENTIFIER, self::BLOCK_IDENTIFIER)
            ->create();

        if ($this->blockRepository->getList($criteria)->getTotalCount() > 0) {
            return $this;
        }

        /** @var BlockInterface $block */
        $block = $this->blockFactory->create();
        $block->setIdentifier(self::BLOCK_IDENTIFIER)
            ->setTitle('Bandeau d’annonce (header)')
            ->setContent('Livraison offerte dès 49,00 € — séries limitées de 5 à 10 pièces')
            ->setIsActive(true)
            ->setStores([Store::DEFAULT_STORE_ID]);

        $this->blockRepository->save($block);

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
