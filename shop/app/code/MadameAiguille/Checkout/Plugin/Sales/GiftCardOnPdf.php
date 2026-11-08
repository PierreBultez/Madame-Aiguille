<?php
/**
 * PDF de facture, d'avoir et de bon de livraison : imprime « Emballage et message cadeau » (De la part de, Pour,
 * message) sous l'en-tête de chaque document, avant le tableau des articles. Le bon de livraison est celui que
 * l'atelier a sous les yeux en préparant le colis.
 *
 * Seule méthode publique appelée une fois par document, après les blocs adresse et commande : insertDocumentNumber.
 * Les documents imprimés ensemble sont suivis dans l'ordre de getPdf() pour retrouver la commande de chacun.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Sales;

use MadameAiguille\Checkout\Model\GiftWrap\OrderGiftCard;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;

class GiftCardOnPdf
{
    private const LEFT = 35;
    private const LINE_HEIGHT = 12;
    private const WRAP = 110;
    private const FONT = 'lib/internal/GnuFreeFont/FreeSerif.ttf';
    private const FONT_BOLD = 'lib/internal/GnuFreeFont/FreeSerifBold.ttf';

    /** @var array<int, array{documents: array<int, object>, index: int}> */
    private array $runs = [];

    public function __construct(
        private readonly OrderGiftCard $orderGiftCard,
        private readonly DirectoryList $directoryList,
        private readonly StringUtils $string
    ) {
    }

    /**
     * @param AbstractPdf $subject
     * @param iterable<object> $documents
     * @return null
     */
    public function beforeGetPdf(AbstractPdf $subject, $documents = [])
    {
        $list = [];
        foreach ($documents as $document) {
            $list[] = $document;
        }
        $this->runs[spl_object_id($subject)] = ['documents' => $list, 'index' => 0];

        return null;
    }

    /**
     * @param AbstractPdf $subject
     * @param null $result
     * @param \Zend_Pdf_Page $page
     * @return null
     */
    public function afterInsertDocumentNumber(AbstractPdf $subject, $result, \Zend_Pdf_Page $page)
    {
        $run = &$this->runs[spl_object_id($subject)];
        $document = $run['documents'][$run['index'] ?? 0] ?? null;
        $run['index'] = ($run['index'] ?? 0) + 1;

        $card = $this->orderGiftCard->forOrder($document?->getOrder());
        if ($card === null) {
            return $result;
        }

        $root = $this->directoryList->getRoot() . '/';
        $regular = \Zend_Pdf_Font::fontWithPath($root . self::FONT);
        $bold = \Zend_Pdf_Font::fontWithPath($root . self::FONT_BOLD);

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $y = (float) $subject->y - 5;
        foreach ($this->lines($card) as [$text, $isTitle]) {
            $page->setFont($isTitle ? $bold : $regular, $isTitle ? 10 : 9);
            $page->drawText($text, self::LEFT, $y, 'UTF-8');
            $y -= self::LINE_HEIGHT;
        }
        $subject->y = $y - 8;

        return $result;
    }

    /**
     * Lignes à imprimer : titre, puis De la part de, Pour et le message coupé à la largeur de la page.
     *
     * @param array{title: string, sender: string, recipient: string, message: string} $card
     * @return array<int, array{0: string, 1: bool}>
     */
    public function lines(array $card): array
    {
        $lines = [[$card['title'], true]];
        $lines[] = [__('Gift from') . ' ' . $card['sender'], false];
        $lines[] = [__('Gift for') . ' ' . $card['recipient'], false];
        $lines[] = [(string) __('Gift message'), false];
        foreach (preg_split('/\R/u', $card['message']) ?: [] as $paragraph) {
            foreach ($this->string->split($paragraph, self::WRAP, true, true) ?: [''] as $line) {
                $lines[] = ['    ' . $line, false];
            }
        }

        return $lines;
    }
}
