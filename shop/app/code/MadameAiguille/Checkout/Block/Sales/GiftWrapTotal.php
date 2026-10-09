<?php
/**
 * Ajoute la ligne « Emballage cadeau » aux totaux d'une commande, d'une facture ou d'un avoir :
 * compte client, emails et administration. Déclaré comme enfant des blocs de totaux natifs.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Block\Sales;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;

class GiftWrapTotal extends AbstractBlock
{
    public function __construct(
        Context $context,
        private readonly Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Appelé par le bloc de totaux parent.
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $source = $parent?->getSource();
        if (!$source instanceof DataObject || (float) $source->getData(Config::AMOUNT) <= 0) {
            return $this;
        }

        $parent->addTotal(new DataObject([
            'code' => Config::TOTAL_CODE,
            'label' => $this->config->getLabel((int) $source->getStoreId()),
            'value' => (float) $source->getData(Config::AMOUNT),
            'base_value' => (float) $source->getData(Config::BASE_AMOUNT),
        ]), 'shipping');

        return $this;
    }
}
