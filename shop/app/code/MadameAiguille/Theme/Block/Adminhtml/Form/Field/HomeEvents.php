<?php
/**
 * Grille « Nouvelles de l'atelier » de la configuration (lot 11, C3) : un rendez-vous par ligne.
 *
 * Cinq colonnes ne tiennent pas dans la colonne « valeur » de la configuration : la grille occupe aussi celle de
 * l'intitulé, qui passe au-dessus. Case « Utiliser la valeur par défaut » et aide restent natives.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class HomeEvents extends AbstractFieldArray
{
    /**
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
    {
        $date = 'width:130px';
        $this->addColumn('date', [
            'label' => __('Date'),
            'class' => 'required-entry',
            'style' => $date,
            'renderer' => $this->dateColumn(),
        ]);
        $this->addColumn('end_date', ['label' => __('Jusqu’au'), 'style' => $date, 'renderer' => $this->dateColumn()]);
        $this->addColumn('title', ['label' => __('Titre'), 'class' => 'required-entry', 'style' => 'width:180px']);
        $this->addColumn('place', ['label' => __('Lieu'), 'style' => 'width:150px']);
        $this->addColumn('text', ['label' => __('Texte'), 'style' => 'width:220px']);
        $this->_addAfter = false;
        $this->_addButtonLabel = (string) __('Ajouter un rendez-vous');
    }

    /**
     * Une ligne enregistrée sans l'une des colonnes ferait planter le gabarit JavaScript de la grille native
     * (« title is not defined ») et bloquerait l'enregistrement de toute la section.
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        foreach (array_keys($this->getColumns()) as $column) {
            if (!$row->hasData($column)) {
                $row->setData($column, '');
            }
        }
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);
        if ($element->getInherit() == 1 && $isCheckboxRequired) {
            $element->setDisabled(true);
        }

        $label = '<p><strong>' . $this->escapeHtml((string) $element->getLabel()) . '</strong></p>';
        $html = preg_replace(
            '/^<td class="value">/',
            '<td class="value" colspan="2">' . $label,
            $this->_renderValue($element),
            1
        );
        if ($isCheckboxRequired) {
            $html .= $this->_renderInheritCheckbox($element);
        }
        $html .= $this->_renderHint($element);

        return $this->_decorateRowHtml($element, (string) $html);
    }

    /**
     * @throws LocalizedException
     */
    private function dateColumn(): DateColumn
    {
        /** @var DateColumn $renderer */
        $renderer = $this->getLayout()->createBlock(
            DateColumn::class,
            '',
            ['data' => ['is_render_to_js_template' => true]]
        );

        return $renderer;
    }
}
