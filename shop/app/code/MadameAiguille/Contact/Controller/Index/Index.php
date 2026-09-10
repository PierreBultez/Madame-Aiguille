<?php
declare(strict_types=1);

namespace MadameAiguille\Contact\Controller\Index;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index extends \Magento\Contact\Controller\Index implements HttpGetActionInterface
{
    public function __construct(Context $context, ConfigInterface $contactsConfig)
    {
        parent::__construct($context, $contactsConfig);
    }

    public function execute(): ResultInterface
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
