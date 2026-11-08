<?php
/**
 * Encart « Emballage et message cadeau » d'une commande ou de l'un de ses documents (facture, avoir, expédition),
 * dans le compte, les emails et l'administration. La commande vient, dans l'ordre : de la donnée `order` du bloc
 * (emails), du bloc parent (administration), puis du registre natif des pages de commande (compte).
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Block\Sales;

use MadameAiguille\Checkout\Model\GiftWrap\OrderGiftCard;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;

class GiftCard extends Template
{
    public function __construct(
        Context $context,
        private readonly OrderGiftCard $orderGiftCard,
        private readonly Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return array{title: string, wrapped: bool, sender: string, recipient: string, message: string}|null
     */
    public function getCard(): ?array
    {
        return $this->orderGiftCard->forOrder($this->resolveOrder());
    }

    private function resolveOrder(): ?Order
    {
        $candidates = [
            $this->getData('order'),
            $this->getParentBlock()?->getData('order'),
            $this->getParentBlock() && method_exists($this->getParentBlock(), 'getOrder')
                ? $this->getParentBlock()->getOrder()
                : null,
            $this->registry->registry('current_order'),
        ];
        foreach ($candidates as $order) {
            if ($order instanceof Order) {
                return $order;
            }
        }

        return null;
    }
}
