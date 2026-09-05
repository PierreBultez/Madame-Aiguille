<?php
/**
 * Madame Aiguille — page de contrôle du socle graphique (/styleguide)
 *
 * Affiche tokens, échelle typographique et états des composants pour valider
 * visuellement le thème. Indisponible en mode production.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly State $appState
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function execute(): Page
    {
        if ($this->appState->getMode() === State::MODE_PRODUCTION) {
            throw new NotFoundException(__('Page not found.'));
        }

        return $this->pageFactory->create();
    }
}
