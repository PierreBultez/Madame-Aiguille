<?php
/**
 * Imprime la mention de TVA (Général › Madame Aiguille › Mentions légales) en pied de chaque page
 * des factures PDF. Franchise en base : « TVA non applicable, art. 293 B du CGI. », obligatoire sur les factures.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Plugin\Sales;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Store\Model\ScopeInterface;

class VatMentionOnInvoicePdf
{
    private const XML_PATH = 'madameaiguille/legal/vat_mention';
    private const FONT_SIZE = 8;
    private const LEFT = 25;
    private const BOTTOM = 20;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param array<\Magento\Sales\Model\Order\Invoice> $invoices
     */
    public function afterGetPdf(Invoice $subject, \Zend_Pdf $pdf, $invoices = []): \Zend_Pdf
    {
        $first = is_array($invoices) ? reset($invoices) : null;
        $storeId = $first ? (int) $first->getStoreId() : null;
        $mention = trim((string) $this->scopeConfig->getValue(self::XML_PATH, ScopeInterface::SCOPE_STORE, $storeId));
        if ($mention === '') {
            return $pdf;
        }

        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA);
        foreach ($pdf->pages as $page) {
            $page->setFont($font, self::FONT_SIZE);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.2));
            $page->drawText($mention, self::LEFT, self::BOTTOM, 'UTF-8');
        }

        return $pdf;
    }
}
