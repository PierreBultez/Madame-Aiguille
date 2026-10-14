<?php
/**
 * Moyens de paiement Mollie **définitifs** (lot 8, décision de Pierre et Céline du 14/10/2026) : carte bancaire,
 * Apple Pay et Google Pay. Les moyens provisoires du 8a (Bancontact, iDEAL, Wero, Klarna) sont coupés.
 *
 * Passe après KeepProvisionalMollieMethods : une installation neuve finit dans le même état que le serveur.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ApplyFinalMollieMethods implements DataPatchInterface
{
    public const ENABLED = ['creditcard', 'applepay', 'googlepay'];
    public const DISABLED = ['bancontact', 'ideal', 'wero', 'klarna'];

    public function __construct(
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): self
    {
        foreach (self::ENABLED as $method) {
            $this->configWriter->save('payment/mollie_methods_' . $method . '/active', '1');
        }
        foreach (self::DISABLED as $method) {
            $this->configWriter->save('payment/mollie_methods_' . $method . '/active', '0');
        }
        $this->configWriter->save('payment/mollie_general/default_selected_method', 'mollie_methods_creditcard');

        return $this;
    }

    public static function getDependencies(): array
    {
        return [KeepProvisionalMollieMethods::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
