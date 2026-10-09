<?php
/**
 * Au passage de l'étape Livraison : enregistre sur le panier le choix de l'emballage cadeau.
 * Les totaux sont recalculés par Magento dans la foulée et renvoyés au tunnel.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Checkout;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class AssignGiftWrap
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly Config $config
    ) {
    }

    /**
     * Le dépôt de paniers garde l'instance chargée : c'est elle que la gestion native enregistre ensuite.
     *
     * @param int $cartId
     * @return array{0: int, 1: ShippingInformationInterface}
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ): array {
        $requested = (bool) $addressInformation->getExtensionAttributes()?->getMadameaiguilleGiftWrap();
        $quote = $this->cartRepository->getActive($cartId);
        $quote->setData(Config::QUOTE_FLAG, $requested && $this->config->isEnabled((int) $quote->getStoreId()) ? 1 : 0);

        return [$cartId, $addressInformation];
    }
}
