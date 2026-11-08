<?php
/**
 * Expose au tunnel (window.checkoutConfig.madameaiguilleGiftWrap) l'option « Emballage et message cadeau »,
 * le choix déjà enregistré sur le panier et la carte déjà saisie, pour la retrouver en revenant à l'étape Livraison.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\GiftWrap;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\GiftMessage\Model\ResourceModel\Message as MessageResource;

class ConfigProvider implements ConfigProviderInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly Session $checkoutSession,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly MessageFactory $messageFactory,
        private readonly MessageResource $messageResource
    ) {
    }

    public function getConfig(): array
    {
        $quote = $this->checkoutSession->getQuote();
        $storeId = (int) $quote->getStoreId();
        if (!$this->config->isEnabled($storeId) || $quote->isVirtual()) {
            return ['madameaiguilleGiftWrap' => ['enabled' => false]];
        }

        return [
            'madameaiguilleGiftWrap' => [
                'enabled' => true,
                'label' => $this->config->getLabel($storeId),
                'description' => $this->config->getDescription($storeId),
                'price' => $this->priceCurrency->convertAndFormat($this->config->getPrice($storeId), false),
                'requested' => (bool) $quote->getData(Config::QUOTE_FLAG),
                'totalCode' => Config::TOTAL_CODE,
                'messageMaxLength' => $this->config->getMessageMaxLength($storeId),
                'card' => $this->getCard((int) $quote->getGiftMessageId()),
            ],
        ];
    }

    /**
     * @return array{sender: string, recipient: string, message: string}
     */
    private function getCard(int $messageId): array
    {
        $message = $this->messageFactory->create();
        if ($messageId) {
            $this->messageResource->load($message, $messageId);
        }

        return [
            'sender' => (string) $message->getSender(),
            'recipient' => (string) $message->getRecipient(),
            'message' => (string) $message->getMessage(),
        ];
    }
}
