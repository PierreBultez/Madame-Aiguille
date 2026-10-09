<?php
/**
 * Paiement sur place (carte au TPE ou espèces) pour le retrait à l'atelier : la méthode native
 * « cashondelivery » est activée et renommée. Elle n'est proposée qu'avec le retrait
 * (Observer\RestrictPaymentToDelivery).
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class EnablePayOnSite implements DataPatchInterface
{
    private const VALUES = [
        'payment/cashondelivery/active' => '1',
        'payment/cashondelivery/title' => 'Paiement sur place',
        'payment/cashondelivery/instructions' => 'Vous réglez votre commande au moment du retrait, par carte bancaire ou en espèces.',
        'payment/cashondelivery/allowspecific' => '0',
    ];

    public function __construct(
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): self
    {
        foreach (self::VALUES as $path => $value) {
            $this->configWriter->save($path, $value);
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
