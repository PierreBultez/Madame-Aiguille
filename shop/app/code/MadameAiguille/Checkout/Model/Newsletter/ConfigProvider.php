<?php
/** Visibilité du consentement : respecte l'activation native de la newsletter. */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Newsletter;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Session $customerSession
    ) {
    }

    public function getConfig(): array
    {
        return ['madameaiguilleNewsletterEnabled' => $this->scopeConfig->isSetFlag(
            'newsletter/general/active',
            ScopeInterface::SCOPE_STORE
        ) && ($this->customerSession->isLoggedIn() || $this->scopeConfig->isSetFlag(
            'newsletter/subscription/allow_guest_subscribe',
            ScopeInterface::SCOPE_STORE
        ))];
    }
}
