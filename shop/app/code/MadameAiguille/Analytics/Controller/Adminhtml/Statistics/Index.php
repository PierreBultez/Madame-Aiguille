<?php
/**
 * Administration › Rapports › Statistiques du site : ouvre l'interface de Matomo, où l'on se connecte avec son
 * propre compte Matomo. Sans adresse configurée, renvoie vers la section de configuration (ou le tableau de bord
 * pour un rôle qui n'y a pas accès, comme « Atelier »).
 */

declare(strict_types=1);

namespace MadameAiguille\Analytics\Controller\Adminhtml\Statistics;

use MadameAiguille\Analytics\Model\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'MadameAiguille_Analytics::statistics';

    public function __construct(
        Context $context,
        private readonly Config $config
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $redirect = $this->resultRedirectFactory->create();
        $url = $this->config->getUrl();
        if ($url !== null) {
            return $redirect->setUrl($url);
        }

        $this->messageManager->addWarningMessage((string) __('L’adresse des statistiques n’est pas encore réglée.'));

        return $this->_authorization->isAllowed('MadameAiguille_Analytics::config')
            ? $redirect->setPath('adminhtml/system_config/edit', ['section' => 'madameaiguille_analytics'])
            : $redirect->setPath('adminhtml/dashboard');
    }
}
