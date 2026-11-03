<?php
/**
 * Module d'opposition de Matomo sur la page Confidentialité, appelé par le contenu CMS :
 * {{block class="MadameAiguille\Analytics\Block\OptOut" template="MadameAiguille_Analytics::opt-out.phtml"}}
 *
 * Script externe servi par Matomo (optOutJS), sans iframe ni script inline. Il retrouve le suivi de la page
 * (_paq) et, à l'opposition, dépose le seul cookie mtm_consent_removed, que Matomo écrit même avec disableCookies.
 */

declare(strict_types=1);

namespace MadameAiguille\Analytics\Block;

use MadameAiguille\Analytics\Model\Config;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class OptOut extends Template
{
    public const DIV_ID = 'matomo-opt-out';

    public function __construct(
        Context $context,
        private readonly Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isActive(): bool
    {
        return $this->config->isReady();
    }

    public function getScriptUrl(): string
    {
        return $this->config->getUrl() . 'index.php?' . http_build_query([
            'module' => 'CoreAdminHome',
            'action' => 'optOutJS',
            'divId' => self::DIV_ID,
            'language' => 'fr',
            'showIntro' => 1,
        ]);
    }
}
