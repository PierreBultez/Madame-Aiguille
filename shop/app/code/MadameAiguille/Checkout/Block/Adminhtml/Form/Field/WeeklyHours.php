<?php
/**
 * Grille « jour · de · à » des disponibilités de retrait, dans la configuration.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class WeeklyHours extends AbstractFieldArray
{
    private ?WeekdayColumn $weekdayRenderer = null;

    protected function _prepareToRender(): void
    {
        $this->addColumn('day', ['label' => __('Day'), 'renderer' => $this->getWeekdayRenderer()]);
        $this->addColumn('from', ['label' => __('From (HH:MM)'), 'class' => 'required-entry', 'style' => 'width:80px']);
        $this->addColumn('to', ['label' => __('To (HH:MM)'), 'class' => 'required-entry', 'style' => 'width:80px']);
        $this->_addAfter = false;
        $this->_addButtonLabel = (string) __('Add a time range');
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $day = $row->getData('day');
        $options = [];
        if ($day !== null) {
            $options['option_' . $this->getWeekdayRenderer()->calcOptionHash($day)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @throws LocalizedException
     */
    private function getWeekdayRenderer(): WeekdayColumn
    {
        if ($this->weekdayRenderer === null) {
            $this->weekdayRenderer = $this->getLayout()->createBlock(
                WeekdayColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->weekdayRenderer;
    }
}
