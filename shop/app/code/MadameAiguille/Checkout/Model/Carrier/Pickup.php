<?php
/**
 * Retrait à l'atelier (click & collect) : 0 €, payé sur place, sur rendez-vous.
 *
 * La restriction géographique éventuelle passe par les réglages natifs « pays autorisés » du transporteur.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

class Pickup extends AbstractCarrier implements CarrierInterface
{
    public const CODE = 'madameaiguille_pickup';
    public const METHOD = 'pickup';
    public const SHIPPING_METHOD = self::CODE . '_' . self::METHOD;

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var bool
     */
    protected $_isFixed = true;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        private readonly ResultFactory $rateResultFactory,
        private readonly MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $method = $this->rateMethodFactory->create();
        $method->setCarrier(self::CODE);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod(self::METHOD);
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice(0);
        $method->setCost(0);

        $result = $this->rateResultFactory->create();
        $result->append($method);

        return $result;
    }

    public function getAllowedMethods(): array
    {
        return [self::METHOD => $this->getConfigData('name')];
    }
}
