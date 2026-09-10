<?php
/**
 * Madame Aiguille — bloc CMS de rassurance du panier
 *
 * Deux lignes sous le bouton « Passer commande » (maquette Panier, artboard
 * 07). Les moyens de paiement et le délai d'expédition y sont des textes
 * éditables, jamais du code : ils devront être alignés sur les prestataires
 * réellement activés au lot 5. Céline les modifie dans Contenu › Blocs.
 * Les classes utilisées sont déclarées dans @source inline de
 * tailwind-source.css. Idempotent.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;

class CreateCartReassuranceBlock implements DataPatchInterface
{
    public const BLOCK_IDENTIFIER = 'cart_reassurance';

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
            ->setTitle('Rassurance (panier)')
            ->setContent($this->getContent())
            ->setIsActive(true)
            ->setStores([Store::DEFAULT_STORE_ID]);

        $this->blockRepository->save($block);

        return $this;
    }

    private function getContent(): string
    {
        $items = [
            [
                '<path d="M12 2.5 20 6v6.5c0 5-3.4 7.8-8 9-4.6-1.2-8-4-8-9V6l8-3.5Z"/><path d="M8.5 12l2.6 2.6L16 9.5"/>',
                'Paiement sécurisé — carte, virement ou main propre',
            ],
            [
                '<path d="M2.5 8.5h13v9h-13v-9Z"/><path d="M15.5 11.5h4l2 3v3h-6v-6Z"/><circle cx="6.5" cy="18.5" r="1.8"/><circle cx="17.5" cy="18.5" r="1.8"/>',
                'Expédition sous 2 à 3 jours ouvrés',
            ],
        ];

        $html = '<div class="mt-4 space-y-2 border-t border-brand-line pt-4">' . PHP_EOL;

        foreach ($items as [$paths, $text]) {
            $html .= '<p class="flex gap-2 text-caption text-brand-muted">'
                . '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mt-0.5 shrink-0 text-brand-rose">' . $paths . '</svg>'
                . '<span>' . $text . '</span>'
                . '</p>' . PHP_EOL;
        }

        return $html . '</div>';
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
