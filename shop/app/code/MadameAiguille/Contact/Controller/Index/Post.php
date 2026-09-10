<?php
declare(strict_types=1);

namespace MadameAiguille\Contact\Controller\Index;

use MadameAiguille\Contact\Model\Attachment;
use MadameAiguille\Contact\Model\AttachmentStorage;
use MadameAiguille\Contact\Model\FormDataValidator;
use MadameAiguille\Contact\Model\Mailer;
use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Post extends \Magento\Contact\Controller\Index implements HttpPostActionInterface
{
    private const PERSISTOR_KEY = 'contact_us';

    public function __construct(
        Context $context,
        ConfigInterface $contactsConfig,
        private readonly FormKeyValidator $formKeyValidator,
        private readonly FormDataValidator $formDataValidator,
        private readonly AttachmentStorage $attachmentStorage,
        private readonly Mailer $mailer,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context, $contactsConfig);
    }

    public function execute(): Redirect
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $attachment = null;
        try {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(__('Votre session a expiré. Rechargez la page puis réessayez.'));
            }
            $data = $this->formDataValidator->validate($this->getRequest()->getParams());
            $file = $this->getRequest()->getFiles('attachment');
            $attachment = $this->attachmentStorage->store(is_array($file) ? $file : null);
            $this->mailer->send($data, $attachment);

            $this->messageManager->addSuccessMessage(
                __('Merci, votre message a bien été envoyé. Un accusé de réception vient de vous être adressé.')
            );
            $this->dataPersistor->clear(self::PERSISTOR_KEY);
        } catch (LocalizedException $exception) {
            $this->attachmentStorage->delete($attachment instanceof Attachment ? $attachment : null);
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->persistFormData();
        } catch (\Throwable $exception) {
            $this->attachmentStorage->delete($attachment instanceof Attachment ? $attachment : null);
            $this->logger->critical($exception);
            $this->messageManager->addErrorMessage(
                __('Le formulaire n’a pas pu être envoyé. Réessayez dans quelques instants.')
            );
            $this->persistFormData();
        }

        return $this->resultRedirectFactory->create()->setPath('contact/index');
    }

    private function persistFormData(): void
    {
        $data = array_intersect_key($this->getRequest()->getParams(), array_flip([
            'name', 'email', 'subject', 'comment', 'product', 'consent',
        ]));
        $this->dataPersistor->set(self::PERSISTOR_KEY, $data);
    }
}
