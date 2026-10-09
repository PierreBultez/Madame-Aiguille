<?php
/** Visibilité du consentement : respecte l'activation native de la newsletter. */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Newsletter;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function getConfig(): array
    {
        return ['madameaiguilleNewsletterEnabled' => $this->scopeConfig->isSetFlag(
            'newsletter/general/active',
            ScopeInterface::SCOPE_STORE
        )];
    }
}
