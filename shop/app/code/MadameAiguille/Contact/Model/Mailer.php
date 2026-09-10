<?php
declare(strict_types=1);

namespace MadameAiguille\Contact\Model;

use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;

class Mailer
{
    private const ADMIN_TEMPLATE = 'madameaiguille_contact_admin';
    private const ACKNOWLEDGEMENT_TEMPLATE = 'madameaiguille_contact_acknowledgement';

    public function __construct(
        private readonly ConfigInterface $contactsConfig,
        private readonly TransportBuilder $transportBuilder,
        private readonly StateInterface $inlineTranslation,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function send(array $data, ?Attachment $attachment): void
    {
        $variables = [
            'data' => new DataObject($data),
            'attachment_name' => $attachment?->getOriginalName() ?: '',
        ];
        $templateOptions = [
            'area' => Area::AREA_FRONTEND,
            'store' => $this->storeManager->getStore()->getId(),
        ];

        $this->inlineTranslation->suspend();
        try {
            $adminTransport = $this->transportBuilder
                ->setTemplateIdentifier(self::ADMIN_TEMPLATE)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($variables)
                ->setFrom($this->contactsConfig->emailSender())
                ->addTo($this->contactsConfig->emailRecipient())
                ->setReplyTo($data['email'], $data['name'])
                ->getTransport();

            if ($attachment) {
                $message = $adminTransport->getMessage();
                if (!method_exists($message, 'getSymfonyMessage')) {
                    throw new LocalizedException(__('La pièce jointe ne peut pas être ajoutée au message.'));
                }
                $symfonyMessage = $message->getSymfonyMessage();
                $body = $symfonyMessage->getBody();
                if (!$body) {
                    throw new LocalizedException(__('Le message ne peut pas être préparé.'));
                }
                $symfonyMessage->setBody(new MixedPart(
                    $body,
                    DataPart::fromPath(
                        $attachment->getAbsolutePath(),
                        $attachment->getOriginalName(),
                        $attachment->getMimeType()
                    )
                ));
            }
            $adminTransport->sendMessage();

            try {
                $this->transportBuilder
                    ->setTemplateIdentifier(self::ACKNOWLEDGEMENT_TEMPLATE)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($variables)
                    ->setFrom($this->contactsConfig->emailSender())
                    ->addTo($data['email'], $data['name'])
                    ->getTransport()
                    ->sendMessage();
            } catch (\Throwable $exception) {
                $this->logger->error('L’accusé de réception du formulaire de contact n’a pas été envoyé.', [
                    'exception' => $exception,
                ]);
            }
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
