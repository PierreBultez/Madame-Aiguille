<?php
/**
 * Madame Aiguille — une page catégorie sans méta-description (champ vide dans
 * *Catalogue › Catégories › Optimisation pour les moteurs de recherche*) n'en
 * affichait aucune : Lighthouse SEO 92 au 02/11/2026. On compose alors un
 * résumé depuis la description de la catégorie, ou à défaut depuis son nom.
 * Une méta-description saisie dans l'administration reste prioritaire.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Plugin\Catalog;

use Magento\Catalog\Block\Category\View;
use Magento\Framework\View\Page\Config as PageConfig;

class CategoryMetaDescriptionFallback
{
    public const MAX_LENGTH = 160;

    public function __construct(
        private readonly PageConfig $pageConfig
    ) {
    }

    /**
     * Pose la méta-description de repli quand la catégorie n'en a pas.
     *
     * @param View $subject
     * @param View $result
     * @return View
     */
    public function afterSetLayout(View $subject, $result)
    {
        $category = $subject->getCurrentCategory();
        if (!$category || trim((string) $category->getData('meta_description')) !== '') {
            return $result;
        }

        $this->pageConfig->setDescription(
            $this->compose((string) $category->getName(), (string) $category->getData('description'))
        );

        return $result;
    }

    /**
     * Description sans balises, ou phrase type autour du nom, ramenée à 160 caractères.
     */
    public function compose(string $name, string $description): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($description))));
        if ($text === '') {
            $text = (string) __(
                '%1 : créations en tissu cousues main à Saint-Épain, en séries limitées de 5 à 10 pièces.',
                $name
            );
        }
        if (mb_strlen($text) <= self::MAX_LENGTH) {
            return $text;
        }

        $cut = mb_substr($text, 0, self::MAX_LENGTH - 1);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space ? mb_substr($cut, 0, $space) : $cut, " ,;:.") . '…';
    }
}
