<?php
/**
 * CSP : autorise l'hôte Matomo configuré pour charger matomo.js (script-src) et envoyer les mesures (connect-src
 * pour sendBeacon et XHR, img-src pour le pixel de repli). Rien n'est ajouté tant que la mesure n'est pas prête.
 *
 * Indispensable dans le tunnel, où la CSP est bloquante depuis Magento 2.4.7 ; la vitrine est en report-only.
 */

declare(strict_types=1);

namespace MadameAiguille\Analytics\Model\Csp;

use MadameAiguille\Analytics\Model\Config;
use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Csp\Model\Policy\FetchPolicy;

class MatomoPolicyCollector implements PolicyCollectorInterface
{
    public const DIRECTIVES = ['script-src', 'connect-src', 'img-src'];

    public function __construct(
        private readonly Config $config
    ) {
    }

    public function collect(array $defaultPolicies = []): array
    {
        $origin = $this->config->getOrigin();
        if (!$this->config->isReady() || $origin === null) {
            return $defaultPolicies;
        }

        $policies = $defaultPolicies;
        foreach (self::DIRECTIVES as $directive) {
            $policies[] = new FetchPolicy($directive, false, [$origin]);
        }

        return $policies;
    }
}
