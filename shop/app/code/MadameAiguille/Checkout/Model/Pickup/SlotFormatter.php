<?php
/**
 * « 2026-10-15 10:00 » → « jeudi 15 octobre 2026 à 10 h 00 ».
 *
 * Langue de la boutique, pas celle de l'utilisateur de l'administration : le texte part chez la cliente
 * (description de livraison, emails), même quand Céline change un statut depuis un back-office en anglais.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Pickup;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class SlotFormatter
{
    private const PATTERN = "EEEE d MMMM y 'à' H 'h' mm";

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function format(string $slot): string
    {
        $date = \DateTimeImmutable::createFromFormat(SlotCalendar::FORMAT, $slot);
        if (!$date) {
            return $slot;
        }

        $locale = (string) $this->scopeConfig->getValue(
            DirectoryHelper::XML_PATH_DEFAULT_LOCALE,
            ScopeInterface::SCOPE_STORE
        );
        $formatter = new \IntlDateFormatter(
            $locale ?: 'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            $date->getTimezone(),
            null,
            self::PATTERN
        );

        return (string) $formatter->format($date);
    }
}
