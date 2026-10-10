<?php
/**
 * Moyens de paiement Mollie **provisoires** (lot 8a) : après la restriction à la carte bancaire du lot 5
 * (Theme ConfigureSalesFoundations), Pierre a réactivé dans l'administration Apple Pay, Google Pay, Bancontact,
 * iDEAL, Wero et Klarna le 09/10/2026, à valider avec Céline. Ce patch reproduit ce choix sur une installation neuve.
 *
 * Il ne désactive rien : quand Céline aura tranché, l'activation se règle dans l'administration.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Setup\Patch\Data;

use MadameAiguille\Theme\Setup\Patch\Data\ConfigureSalesFoundations;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class KeepProvisionalMollieMethods implements DataPatchInterface
{
    public const METHODS = ['applepay', 'googlepay', 'bancontact', 'ideal', 'wero', 'klarna'];

    public function __construct(
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): self
    {
        foreach (self::METHODS as $method) {
            $this->configWriter->save('payment/mollie_methods_' . $method . '/active', '1');
        }
        $this->configWriter->save('payment/mollie_general/default_selected_method', 'mollie_methods_creditcard');

        return $this;
    }

    public static function getDependencies(): array
    {
        return [ConfigureSalesFoundations::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
