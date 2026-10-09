<?php
/** Conserver le choix explicite dans le paiement du panier, puis de la commande. */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Checkout;

use Magento\Quote\Model\Quote\Payment;

class CaptureNewsletterConsent
{
    public const CONSENT_KEY = 'madameaiguille_newsletter';

    public function afterImportData(Payment $subject, Payment $result, array $data): Payment
    {
        $value = $data['additional_data'][self::CONSENT_KEY] ?? false;
        $subject->setAdditionalInformation(self::CONSENT_KEY, in_array($value, [true, 1, '1'], true));

        return $result;
    }
}
