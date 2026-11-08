<?php
/**
 * Connexion depuis le tunnel (lot 11, A2) : le CAPTCHA natif étant coupé sur la vitrine, la fenêtre de connexion
 * n'envoie pas de `captcha_form_id`. Le plugin natif `Captcha\Model\Customer\Plugin\AjaxLogin` appelle alors
 * `getCaptcha(null)`, et `array_key_exists(null, …)` est déprécié en PHP 8.5 : le gestionnaire d'erreurs de Magento
 * en fait une exception et `customer/ajax/login` répond 500. Un identifiant vide garde le comportement prévu
 * (aucun formulaire, donc aucun CAPTCHA exigé).
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Customer;

use Magento\Captcha\Helper\Data;

class TolerateMissingCaptchaFormId
{
    /**
     * @param Data $subject
     * @param mixed $formId
     * @return array{0: mixed}
     */
    public function beforeGetCaptcha(Data $subject, $formId): array
    {
        return [$formId ?? ''];
    }
}
