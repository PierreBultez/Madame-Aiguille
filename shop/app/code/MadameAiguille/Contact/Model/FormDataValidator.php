<?php
declare(strict_types=1);

namespace MadameAiguille\Contact\Model;

use Magento\Framework\Exception\LocalizedException;

class FormDataValidator
{
    public const SUBJECTS = [
        'product' => 'Une création ou un tissu',
        'order' => 'Une commande',
        'custom' => 'Un projet personnalisé',
        'market' => 'Un marché ou un événement',
        'other' => 'Autre demande',
    ];

    /** @return array{name: string, email: string, subject: string, subject_label: string, comment: string, product: string, consent: string} */
    public function validate(array $params): array
    {
        if (trim((string) ($params['hideit'] ?? '')) !== '') {
            throw new LocalizedException(__('Le formulaire ne peut pas être envoyé.'));
        }

        $name = trim((string) ($params['name'] ?? ''));
        $email = trim((string) ($params['email'] ?? ''));
        $subject = trim((string) ($params['subject'] ?? ''));
        $comment = trim((string) ($params['comment'] ?? ''));
        $product = trim((string) ($params['product'] ?? ''));

        if ($name === '' || mb_strlen($name) > 100) {
            throw new LocalizedException(__('Indiquez un nom de 100 caractères maximum.'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
            throw new LocalizedException(__('Vérifiez votre adresse email.'));
        }
        if (!isset(self::SUBJECTS[$subject])) {
            throw new LocalizedException(__('Choisissez l’objet de votre message.'));
        }
        if ($comment === '' || mb_strlen($comment) > 1000) {
            throw new LocalizedException(__('Votre message doit contenir entre 1 et 1 000 caractères.'));
        }
        if (mb_strlen($product) > 64) {
            throw new LocalizedException(__('La référence du produit est trop longue.'));
        }
        if (($params['consent'] ?? null) !== '1') {
            throw new LocalizedException(__('Votre consentement est nécessaire pour envoyer le formulaire.'));
        }

        return [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'subject_label' => self::SUBJECTS[$subject],
            'comment' => $comment,
            'product' => $product,
            'consent' => 'Oui',
        ];
    }
}
