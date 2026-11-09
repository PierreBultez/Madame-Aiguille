<?php
/**
 * Échec d'un appel à l'API Instagram. Le message ne contient jamais le jeton ni l'URL appelée.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Api;

class InstagramException extends \RuntimeException
{
}
