<?php
/**
 * Erreur présentable à Céline : le message dit quoi faire, jamais un détail technique. La raison (`reason`) sert au
 * journal et aux tests.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class AssistantException extends LocalizedException
{
    public const NOT_CONFIGURED = 'non_configure';
    public const QUOTA = 'plafond';
    public const CREDIT = 'credit';
    public const AUTHENTICATION = 'cle';
    public const OVERLOADED = 'surcharge';
    public const UNREACHABLE = 'injoignable';
    public const REFUSAL = 'refus';
    public const TRUNCATED = 'tronque';
    public const INVALID_RESPONSE = 'reponse_invalide';
    public const INVALID_REQUEST = 'demande_invalide';
    public const MODEL_UNAVAILABLE = 'modele_indisponible';

    public function __construct(
        Phrase $phrase,
        public readonly string $reason,
        ?\Exception $cause = null
    ) {
        parent::__construct($phrase, $cause);
    }
}
