<?php
/**
 * Madame Aiguille — bloc CMS de rassurance de la fiche produit
 *
 * Quatre arguments (expédition, paiement, transporteurs, emballage) affichés
 * sous le bouton d'achat. Textes de la maquette par défaut ; Céline les
 * modifie dans Contenu › Blocs (les modes réels seront alignés au lot 5).
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

class CreateProductReassuranceBlock implements DataPatchInterface
{
    public const BLOCK_IDENTIFIER = 'product_reassurance';

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
            ->setTitle('Rassurance (fiche produit)')
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
                '<path d="M2.5 8.5h13v9h-13v-9Z"/><path d="M15.5 11.5h4l2 3v3h-6v-6Z"/><circle cx="6.5" cy="18.5" r="1.8"/><circle cx="17.5" cy="18.5" r="1.8"/>',
                'Expédition sous 2 à 3 jours',
                'Du lundi au vendredi, depuis l’atelier.',
            ],
            [
                '<path d="M12 2.5 20 6v6.5c0 5-3.4 7.8-8 9-4.6-1.2-8-4-8-9V6l8-3.5Z"/><path d="M8.5 12l2.6 2.6L16 9.5"/>',
                'Paiement sécurisé',
                'Carte bancaire, virement ou en main propre.',
            ],
            [
                '<path d="M4 8h16l-1 12H5L4 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
                'Colissimo, Mondial Relay, Chronopost',
                'Ou remise en main propre, sans frais.',
            ],
            [
                '<path d="M12 21S3.5 15.8 3.5 9.9A4.9 4.9 0 0 1 12 7.4 4.9 4.9 0 0 1 20.5 9.9C20.5 15.8 12 21 12 21Z"/>',
                'Emballage soigné',
                'Papier de soie et mot manuscrit sur demande.',
            ],
        ];

        $html = '<div class="grid grid-cols-1 gap-x-6 gap-y-4 border-t border-brand-nude pt-6 sm:grid-cols-2">' . PHP_EOL;

        foreach ($items as [$paths, $title, $text]) {
            $html .= '<div class="flex gap-3">'
                . '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mt-0.5 shrink-0 text-brand-rose">' . $paths . '</svg>'
                . '<div><p class="font-medium text-label text-brand-ink">' . $title . '</p>'
                . '<p class="text-label text-brand-muted">' . $text . '</p></div>'
                . '</div>' . PHP_EOL;
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
