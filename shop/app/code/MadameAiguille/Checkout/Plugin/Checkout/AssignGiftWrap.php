<?php
/**
 * Au passage de l'étape Livraison : enregistre sur le panier le choix « Emballage et message cadeau ».
 *
 * L'option cochée exige une carte complète (De la part de, Pour, message), enregistrée comme message cadeau natif
 * du panier : Magento la reporte ensuite sur la commande. Option décochée : un message éventuel (saisi avant le
 * lot 11 dans le panier) est retiré, sans bloquer la commande. Les totaux sont recalculés par Magento dans la foulée.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Checkout;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use MadameAiguille\Checkout\Model\GiftWrap\GiftCard;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\GiftMessage\Api\Data\MessageInterfaceFactory;
use Magento\GiftMessage\Model\GiftMessageManager;
use Magento\Quote\Api\CartRepositoryInterface;

class AssignGiftWrap
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly Config $config,
        private readonly GiftMessageManager $giftMessageManager,
        private readonly MessageInterfaceFactory $messageFactory
    ) {
    }

    /**
     * Le dépôt de paniers garde l'instance chargée : c'est elle que la gestion native enregistre ensuite.
     *
     * @param ShippingInformationManagementInterface $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return array{0: int, 1: ShippingInformationInterface}
     * @throws InputException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ): array {
        $attributes = $addressInformation->getExtensionAttributes();
        $quote = $this->cartRepository->getActive($cartId);
        $storeId = (int) $quote->getStoreId();
        $requested = (bool) $attributes?->getMadameaiguilleGiftWrap() && $this->config->isEnabled($storeId);

        $message = $this->messageFactory->create();
        if ($requested) {
            $card = GiftCard::fromInput(
                $attributes->getMadameaiguilleGiftSender(),
                $attributes->getMadameaiguilleGiftRecipient(),
                $attributes->getMadameaiguilleGiftMessage(),
                $this->config->getMessageMaxLength($storeId)
            );
            $message->setSender($card->sender)->setRecipient($card->recipient)->setMessage($card->message);
            $this->giftMessageManager->setMessage($quote, 'quote', $message);
        } elseif ($quote->getGiftMessageId()) {
            // Un message vide fait supprimer le message natif et le détacher du panier.
            $this->giftMessageManager->setMessage($quote, 'quote', $message->setMessage(null));
        }

        $quote->setData(Config::QUOTE_FLAG, $requested ? 1 : 0);

        return [$cartId, $addressInformation];
    }
}
