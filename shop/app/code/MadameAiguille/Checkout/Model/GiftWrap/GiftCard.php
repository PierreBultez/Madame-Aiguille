<?php
/**
 * La carte qui accompagne l'emballage cadeau : « De la part de », « Pour » et le message, tous obligatoires.
 * Saisie à l'étape Livraison, enregistrée comme message cadeau natif de la commande.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\GiftWrap;

use Magento\Framework\Exception\InputException;

class GiftCard
{
    private function __construct(
        public readonly string $sender,
        public readonly string $recipient,
        public readonly string $message
    ) {
    }

    /**
     * Valide la saisie de la cliente.
     *
     * @throws InputException
     */
    public static function fromInput(?string $sender, ?string $recipient, ?string $message, int $maxLength): self
    {
        $card = new self(self::clean($sender), self::clean($recipient), self::cleanMessage($message));

        if ($card->sender === '' || $card->recipient === '' || $card->message === '') {
            throw new InputException(
                __('To add gift wrapping, please fill in who it is from, who it is for and your message.')
            );
        }
        if (mb_strlen($card->message) > $maxLength) {
            throw new InputException(__('Your gift message is limited to %1 characters.', $maxLength));
        }
        if (mb_strlen($card->sender) > 255 || mb_strlen($card->recipient) > 255) {
            throw new InputException(__('The names on the gift card are too long.'));
        }

        return $card;
    }

    /**
     * Une carte enregistrée est-elle complète ? Sert au contrôle final, à la commande.
     */
    public static function isComplete(?string $sender, ?string $recipient, ?string $message): bool
    {
        return self::clean($sender) !== '' && self::clean($recipient) !== '' && self::cleanMessage($message) !== '';
    }

    private static function clean(?string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) $value));
    }

    /**
     * Les retours à la ligne du message sont gardés : la carte s'écrit comme la cliente l'a tapée.
     */
    private static function cleanMessage(?string $value): string
    {
        $lines = preg_split('/\R/u', str_replace("\r\n", "\n", (string) $value)) ?: [];

        return trim(implode("\n", array_map(static fn (string $line): string => self::clean($line), $lines)));
    }
}
