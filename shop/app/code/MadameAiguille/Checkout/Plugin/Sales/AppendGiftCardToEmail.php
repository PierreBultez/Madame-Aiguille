<?php
/**
 * Emails de facture, d'avoir et d'expédition : ajoute l'encart « Emballage et message cadeau » sous les articles.
 *
 * La directive `{{layout}}` des emails ne rend que le bloc racine du handle (la liste des articles), dont le gabarit
 * natif n'affiche pas d'enfant libre. L'encart est déclaré enfant de ce bloc dans les layouts `sales_email_*_items`
 * et ajouté ici à son rendu. L'email de commande garde l'encart natif de Magento.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Sales;

use Magento\Framework\View\Element\AbstractBlock;

class AppendGiftCardToEmail
{
    public const CHILD = 'madameaiguille_gift_card';

    public function afterToHtml(AbstractBlock $subject, string $html): string
    {
        return $html . $subject->getChildHtml(self::CHILD);
    }
}
