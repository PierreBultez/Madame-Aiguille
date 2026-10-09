<?php
/** Accord natif obligatoire, éditable dans Magasins > Conditions générales de ventes. */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Setup\Patch\Data;

use Magento\CheckoutAgreements\Model\AgreementFactory;
use Magento\CheckoutAgreements\Model\AgreementModeOptions;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement as AgreementResource;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class EnableCheckoutAgreement implements DataPatchInterface
{
    public function __construct(
        private readonly AgreementFactory $agreementFactory,
        private readonly AgreementResource $agreementResource,
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): self
    {
        $agreement = $this->agreementFactory->create();
        $existing = $agreement->getCollection()
            ->addFieldToFilter('name', 'Conditions générales de vente')
            ->getFirstItem();

        // Ne jamais écraser les textes ni les réglages d'un accord déjà édité par Céline.
        if (!$existing->getId()) {
            $agreement->setData([
                'name' => 'Conditions générales de vente',
                'content' => 'Consultez les conditions générales de vente en suivant le lien sous la case '
                    . 'd’acceptation. Leur acceptation est obligatoire pour passer commande.',
                'checkbox_text' => 'J’accepte les conditions générales de vente',
                'is_active' => 1,
                'is_html' => 0,
                'mode' => AgreementModeOptions::MODE_MANUAL,
                'stores' => [0],
            ]);
            $this->agreementResource->save($agreement);
        }

        $this->configWriter->save('checkout/options/enable_agreements', '1');

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
