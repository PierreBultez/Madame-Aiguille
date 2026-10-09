<?php
/**
 * Lot 5 : les textes affichés ne promettent plus que ce qui existe — Mondial Relay en point relais,
 * retrait à l'atelier payé sur place, expédition sous 4 à 5 jours ouvrés, franco à 60 €, emballage cadeau.
 *
 * Remplacement phrase par phrase : seule une phrase restée identique à celle des lots précédents est
 * réécrite ; tout ce que Céline a modifié est conservé. Même principe pour les valeurs entre crochets
 * des pages juridiques et pour la mention sous le prix, posée seulement si elle est vide.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AlignDisplayWithDelivery implements DataPatchInterface
{
    private const BLOCK_SENTENCES = [
        'product_reassurance' => [
            '>Expédition sous 2 à 3 jours<' => '>Expédition sous 4 à 5 jours ouvrés<',
            '>Du lundi au vendredi, depuis l’atelier.<' => '>Depuis l’atelier de Saint-Épain.<',
            '>Carte bancaire, virement ou en main propre.<' => '>Carte bancaire en ligne, ou sur place au retrait.<',
            '>Colissimo, Mondial Relay, Chronopost<' => '>Mondial Relay, offert dès 60 €<',
            '>Ou remise en main propre, sans frais.<' => '>Ou retrait à l’atelier sur rendez-vous, sans frais.<',
            '>Emballage soigné<' => '>Emballage cadeau<',
            '>Papier de soie et mot manuscrit sur demande.<' => '>En option, avec votre message écrit à la main.<',
        ],
        'cart_reassurance' => [
            '>Paiement sécurisé — carte, virement ou main propre<' => '>Paiement sécurisé — carte bancaire en ligne, ou sur place au retrait<',
            '>Expédition sous 2 à 3 jours ouvrés<' => '>Expédition sous 4 à 5 jours ouvrés<',
        ],
    ];

    private const LEGAL_PAGES = ['cgv', 'mentions-legales', 'livraison-retours', 'confidentialite'];
    private const CONTACT_PLACEHOLDER = '[adresse e-mail de contact]';
    private const PRICE_NOTE_PATH = 'madameaiguille/catalog/price_note';

    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly PageRepositoryInterface $pageRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): self
    {
        $this->alignBlocks();
        $this->fillContactEmail();

        if (trim((string) $this->scopeConfig->getValue(self::PRICE_NOTE_PATH)) === '') {
            $this->configWriter->save(self::PRICE_NOTE_PATH, ConfigureSalesFoundations::VAT_MENTION);
        }

        return $this;
    }

    private function alignBlocks(): void
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', array_keys(self::BLOCK_SENTENCES), 'in')
            ->create();

        foreach ($this->blockRepository->getList($criteria)->getItems() as $block) {
            $content = (string) $block->getContent();
            $aligned = strtr($content, self::BLOCK_SENTENCES[$block->getIdentifier()]);
            if ($aligned !== $content) {
                $this->blockRepository->save($block->setContent($aligned));
            }
        }
    }

    private function fillContactEmail(): void
    {
        $email = trim((string) $this->scopeConfig->getValue('trans_email/ident_general/email'));
        if ($email === '') {
            return;
        }

        $criteria = $this->searchCriteriaBuilder
            ->addFilter('identifier', self::LEGAL_PAGES, 'in')
            ->create();

        foreach ($this->pageRepository->getList($criteria)->getItems() as $page) {
            $content = (string) $page->getContent();
            if (str_contains($content, self::CONTACT_PLACEHOLDER)) {
                $this->pageRepository->save($page->setContent(str_replace(self::CONTACT_PLACEHOLDER, $email, $content)));
            }
        }
    }

    public static function getDependencies(): array
    {
        return [
            CreateProductReassuranceBlock::class,
            CreateCartReassuranceBlock::class,
            FillLegalPages::class,
            ConfigureSalesFoundations::class,
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
