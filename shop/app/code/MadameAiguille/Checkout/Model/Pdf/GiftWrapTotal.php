<?php
/**
 * Ligne « Emballage et message cadeau » des PDF de facture et d'avoir : même libellé que partout ailleurs, celui de
 * Madame Aiguille › Emballage cadeau, et non un titre fixe traduit par un paquet de langue.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Pdf;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;

class GiftWrapTotal extends DefaultTotal
{
    public function __construct(
        Data $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        private readonly Config $config,
        array $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->config->getLabel((int) $this->getOrder()->getStoreId());
    }
}
