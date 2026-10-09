<?php
/**
 * Bandeau du header : le franco passe de 49 € à 60 € (décision du 09/10/2026), en point relais.
 * Le texte n'est réécrit que s'il est resté celui du lot 1 ; un bandeau modifié par Céline est conservé.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AlignHeaderAnnouncement implements DataPatchInterface
{
    private const LOT1_TEXT = 'Livraison offerte dès 49,00 € — séries limitées de 5 à 10 pièces';
    private const LOT5_TEXT = 'Livraison offerte en point relais dès 60 € — séries limitées de 5 à 10 pièces';

    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function apply(): self
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', 'header_announcement')
            ->create();

        foreach ($this->blockRepository->getList($criteria)->getItems() as $block) {
            if (trim((string) $block->getContent()) === self::LOT1_TEXT) {
                $this->blockRepository->save($block->setContent(self::LOT5_TEXT));
            }
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [CreateHeaderAnnouncementBlock::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
