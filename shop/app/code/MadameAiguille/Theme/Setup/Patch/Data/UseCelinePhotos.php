<?php
/**
 * Photos réelles de l'atelier (23/11/2026) : la trousse en velours côtelé écru dans le héros de l'accueil, le portrait
 * de Céline dans « L'histoire de Madame Aiguille » et en tête de « À propos », à la place des images génériques.
 *
 * Remplace la seule balise <img> exacte écrite par CreateHomeBlocks / CreateFabricPageAndPrepareAbout (puis passée en
 * WebP par UseWebpHomeImages), adresse et texte alternatif compris. Sur « À propos », le cadrage du portrait vient
 * de page-cms.css (.cms-a-propos). Une image ou un texte alternatif changés dans
 * l'administration ne sont pas touchés : la photo reste alors à choisir à la main. Une page enregistrée depuis Page
 * Builder garde l'ancien HTML encodé dans un bloc « Code HTML » : il est cherché sous cette forme aussi.
 * Les anciens fichiers restent dans le thème.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UseCelinePhotos implements DataPatchInterface
{
    public const HERO_OLD = '<img src="{{view url=\'images/home/hero.webp\'}}" alt="Trousse fleurie posée sur du linge'
        . ' clair" width="1024" height="1024" fetchpriority="high">';
    public const HERO_NEW = '<img src="{{view url=\'images/home/trousse-velours-ecru.webp\'}}" alt="Trousse en velours'
        . ' côtelé écru, ouverte sur sa doublure rouille à pois" width="1086" height="760" fetchpriority="high">';

    public const STORY_OLD = '<img src="{{view url=\'images/home/atelier.webp\'}}" alt="Tissus et accessoires de couture'
        . ' à l’atelier" width="1024" height="1024" loading="lazy">';
    public const STORY_NEW = '<img src="{{view url=\'images/home/celine.webp\'}}" alt="Céline, créatrice de Madame'
        . ' Aiguille" width="1206" height="860" loading="lazy">';

    public const ABOUT_OLD = '<img src="{{view url=\'images/home/atelier.webp\'}}" alt="Création textile en cours à'
        . ' l’atelier" width="930" height="591">';
    public const ABOUT_NEW = '<img src="{{view url=\'images/home/celine.webp\'}}" alt="Céline, créatrice de Madame'
        . ' Aiguille" width="1206" height="860">';

    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly PageRepositoryInterface $pageRepository,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function apply(): self
    {
        $blocks = ['home_hero' => [self::HERO_OLD, self::HERO_NEW], 'home_story' => [self::STORY_OLD, self::STORY_NEW]];
        foreach ($blocks as $identifier => [$old, $new]) {
            $criteria = $this->criteriaBuilder->addFilter(BlockInterface::IDENTIFIER, $identifier)->create();
            foreach ($this->blockRepository->getList($criteria)->getItems() as $block) {
                $content = (string) $block->getContent();
                $updated = self::replaced($content, $old, $new);
                if ($updated !== $content) {
                    $block->setContent($updated);
                    $this->blockRepository->save($block);
                }
            }
        }

        $criteria = $this->criteriaBuilder->addFilter(PageInterface::IDENTIFIER, 'a-propos')->create();
        foreach ($this->pageRepository->getList($criteria)->getItems() as $page) {
            $content = (string) $page->getContent();
            $updated = self::replaced($content, self::ABOUT_OLD, self::ABOUT_NEW);
            if ($updated !== $content) {
                $page->setContent($updated);
                $this->pageRepository->save($page);
            }
        }

        return $this;
    }

    /**
     * Le contenu avec la nouvelle balise, sous forme brute ou encodée (bloc « Code HTML » de Page Builder).
     *
     * @param string $content
     * @param string $old
     * @param string $new
     * @return string
     */
    public static function replaced(string $content, string $old, string $new): string
    {
        $updated = str_replace($old, $new, $content);
        if ($updated !== $content) {
            return $updated;
        }

        return str_replace(self::encoded($old), self::encoded($new), $content);
    }

    /**
     * Forme d'un fragment dans le bloc « Code HTML » de Page Builder.
     *
     * @param string $html
     * @return string
     */
    public static function encoded(string $html): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return htmlspecialchars($html, ENT_NOQUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function getDependencies(): array
    {
        return [UseWebpHomeImages::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
