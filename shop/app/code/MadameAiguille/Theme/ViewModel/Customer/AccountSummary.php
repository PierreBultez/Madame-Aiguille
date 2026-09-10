<?php
/**
 * Identité résumée de la cliente connectée pour la navigation du compte.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Customer;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class AccountSummary implements ArgumentInterface
{
    public function __construct(
        private readonly CurrentCustomer $currentCustomer
    ) {
    }

    public function getName(): string
    {
        $customer = $this->currentCustomer->getCustomer();

        return trim($customer->getFirstname() . ' ' . $customer->getLastname());
    }

    public function getEmail(): string
    {
        return (string) $this->currentCustomer->getCustomer()->getEmail();
    }
}
