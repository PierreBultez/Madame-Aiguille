<?php
/**
 * Colonne date d'une grille de configuration : sélecteur de date du navigateur, valeur enregistrée en AAAA-MM-JJ.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\AbstractBlock;

class DateColumn extends AbstractBlock
{
    /**
     * Gabarit JavaScript de la cellule : `<%- … %>` est rempli par la grille native, ligne par ligne.
     */
    protected function _toHtml(): string
    {
        $column = (array) $this->getData('column');
        $class = trim((string) ($column['class'] ?? ''));

        return sprintf(
            '<input type="date" id="%s" name="%s" value="<%%- %s %%>" class="input-text admin__control-text %s" style="%s"/>',
            // Identifiant et nom portent les marqueurs `<%- _id %>` de la grille native : ils ne s'échappent pas,
            // comme dans les sélecteurs natifs (Html\Select). Ils viennent de la grille, jamais d'une saisie.
            (string) $this->getData('input_id'),
            (string) $this->getData('input_name'),
            $this->escapeHtmlAttr((string) $this->getData('column_name')),
            $this->escapeHtmlAttr($class),
            $this->escapeHtmlAttr((string) ($column['style'] ?? ''))
        );
    }
}
