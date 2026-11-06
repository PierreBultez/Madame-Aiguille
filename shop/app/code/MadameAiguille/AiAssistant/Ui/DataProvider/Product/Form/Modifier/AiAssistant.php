<?php
/**
 * Ajoute le panneau « Rédiger avec l'IA » en tête de la section Contenu du formulaire produit, en création comme en
 * édition. Rien n'est ajouté si l'assistant n'est pas prêt (activé, avec clé) ou si le rôle n'a pas la ressource
 * `MadameAiguille_AiAssistant::assistant` : le formulaire reste alors exactement celui de Magento.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Ui\DataProvider\Product\Form\Modifier;

use MadameAiguille\AiAssistant\Controller\Adminhtml\Assistant\AbstractAction;
use MadameAiguille\AiAssistant\Model\Config;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;

class AiAssistant extends AbstractModifier
{
    public const GROUP = 'content';
    public const COMPONENT = 'madameaiguille_ai_assistant';

    public function __construct(
        private readonly Config $config,
        private readonly AuthorizationInterface $authorization,
        private readonly UrlInterface $urlBuilder,
        private readonly LocatorInterface $locator
    ) {
    }

    public function modifyData(array $data): array
    {
        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        if (!isset($meta[self::GROUP])
            || !$this->config->isReady()
            || !$this->authorization->isAllowed(AbstractAction::ADMIN_RESOURCE)
        ) {
            return $meta;
        }

        $meta[self::GROUP]['children'][self::COMPONENT] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'container',
                        'component' => 'MadameAiguille_AiAssistant/js/assistant',
                        'template' => 'MadameAiguille_AiAssistant/assistant',
                        'sortOrder' => -10,
                        'analyseUrl' => $this->urlBuilder->getUrl('madameaiguille_ai/assistant/analyser'),
                        'generateUrl' => $this->urlBuilder->getUrl('madameaiguille_ai/assistant/generer'),
                        'productId' => (int) $this->locator->getProduct()->getId(),
                        // Absent des données du formulaire en création : lu sur le produit du localisateur
                        'productType' => (string) $this->locator->getProduct()->getTypeId(),
                        'provider' => 'product_form.product_form_data_source',
                    ],
                ],
            ],
        ];

        return $meta;
    }
}
