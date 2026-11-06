<?php
/**
 * Consigne permanente de l'assistant : charte de rédaction et règles HTML, versionnées dans `prompt/consigne.md`.
 *
 * Elle est envoyée en tête de chaque requête, identique octet pour octet, pour profiter de la mise en cache du prompt
 * côté API. L'exemple de référence (`prompt/exemple-reference.html`), une fois validé par Céline, y est ajouté tel
 * quel ; tant que le fichier n'existe pas, la consigne part sans exemple.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir;

class Prompt
{
    public const CHARTER = 'consigne.md';
    public const EXAMPLE = 'exemple-reference.html';

    private ?string $system = null;

    public function __construct(
        private readonly Dir $moduleDir,
        private readonly File $file
    ) {
    }

    public function system(): string
    {
        if ($this->system !== null) {
            return $this->system;
        }

        $directory = $this->moduleDir->getDir('MadameAiguille_AiAssistant') . '/prompt/';
        $system = trim($this->file->fileGetContents($directory . self::CHARTER));
        if ($this->file->isExists($directory . self::EXAMPLE)) {
            $system .= "\n\n## Exemple de référence validé par Céline\n\n"
                . "Pour le ton, la longueur et la mise en page seulement : n'en reprends aucune phrase.\n\n"
                . trim($this->file->fileGetContents($directory . self::EXAMPLE));
        }

        return $this->system = $system;
    }
}
