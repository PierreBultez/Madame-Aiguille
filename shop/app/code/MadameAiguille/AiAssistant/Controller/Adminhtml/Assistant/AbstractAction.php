<?php
/**
 * Socle des deux actions de l'assistant : POST seulement, clé de formulaire vérifiée par Magento (CsrfValidator),
 * ressource ACL `MadameAiguille_AiAssistant::assistant`, assistant prêt. La réponse est toujours un JSON
 * `{ok: true, …}` ou `{ok: false, message}` : jamais une page d'erreur dans le formulaire produit. Une erreur inattendue
 * n'est journalisée que par sa classe et sa ligne, jamais par son message (§31, la clé ne doit atteindre aucun journal).
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Controller\Adminhtml\Assistant;

use MadameAiguille\AiAssistant\Model\Assistant;
use MadameAiguille\AiAssistant\Model\AssistantException;
use MadameAiguille\AiAssistant\Model\Config;
use MadameAiguille\AiAssistant\Model\GalleryImages;
use MadameAiguille\AiAssistant\Model\ProductContext;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

abstract class AbstractAction extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'MadameAiguille_AiAssistant::assistant';

    public function __construct(
        Context $context,
        protected readonly Config $config,
        protected readonly Assistant $assistant,
        protected readonly GalleryImages $galleryImages,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly JsonFactory $jsonFactory,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     * @throws AssistantException
     */
    abstract protected function handle(array $payload): array;

    public function execute(): Json
    {
        $result = $this->jsonFactory->create();
        try {
            if (!$this->config->isReady()) {
                throw new AssistantException(
                    __('L\'assistant n\'est pas activé ou n\'a pas de clé : demandez à Pierre.'),
                    AssistantException::NOT_CONFIGURED
                );
            }
            $payload = json_decode((string) $this->getRequest()->getParam('payload'), true);

            return $result->setData(['ok' => true] + $this->handle(is_array($payload) ? $payload : []));
        } catch (AssistantException $exception) {
            return $result->setData(['ok' => false, 'message' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            // Ni le message ni la trace : ils peuvent contenir la clé d'API (en-tête refusé) ou un texte de la fiche
            $this->logger->error(sprintf(
                'Assistant IA : erreur inattendue %s (%s:%d)',
                $exception::class,
                basename($exception->getFile()),
                $exception->getLine()
            ));

            return $result->setData([
                'ok' => false,
                'message' => (string) __('Une erreur inattendue est survenue. Votre fiche n\'a pas été modifiée.'),
            ]);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function product(array $payload): ProductContext
    {
        $form = is_array($payload['product'] ?? null) ? $payload['product'] : [];
        $categoryIds = array_values(array_filter(
            array_map('intval', is_array($form['category_ids'] ?? null) ? $form['category_ids'] : []),
            fn (int $id): bool => $id > 0
        ));
        $names = [];
        if ($categoryIds !== []) {
            $collection = $this->categoryCollectionFactory->create()
                ->addAttributeToSelect('name')
                ->addIdFilter($categoryIds);
            foreach ($collection as $category) {
                $names[] = (string) $category->getName();
            }
        }

        return ProductContext::fromForm($form, empty($payload['product_id']), $names);
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<array{file: string, mediaType: string, data: string}>
     */
    protected function images(array $payload): array
    {
        return $this->galleryImages->prepare(is_array($payload['images'] ?? null) ? $payload['images'] : []);
    }

    protected function user(): string
    {
        return (string) $this->_auth->getUser()?->getUserName();
    }
}
