<?php
/**
 * Liste des jours pour la grille des plages de retrait (ISO-8601 : 1 = lundi … 7 = dimanche).
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class WeekdayColumn extends Select
{
    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $days = [1 => __('Monday'), 2 => __('Tuesday'), 3 => __('Wednesday'), 4 => __('Thursday'),
                5 => __('Friday'), 6 => __('Saturday'), 7 => __('Sunday')];
            foreach ($days as $value => $label) {
                $this->addOption((string) $value, (string) $label);
            }
        }

        return parent::_toHtml();
    }
}
