<?php
/**
 * Consigne permanente de l'assistant : charte de rédaction et règles HTML, versionnées dans `prompt/consigne.md`.
 *
 * Elle est envoyée en tête de chaque requête, identique octet pour octet, pour profiter de la mise en cache du prompt
 * côté API. L'exemple de référence (`prompt/exemple-reference.html`, la description du cabas Adèle
 * fournie le 07/11/2026) y est ajouté tel quel ; sans le fichier, la consigne part sans exemple.
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
                . "La description longue du cabas Adèle (référence ADE-FU), telle que Céline la veut : repère de ton, "
                . "de longueur et de mise en page. Ses faits (coloris, dimensions, entretien, usages) sont propres à ce "
                . "cabas : n'en reprends aucun pour une autre pièce, ni aucune phrase.\n\n"
                . trim($this->file->fileGetContents($directory . self::EXAMPLE));
        }

        return $this->system = $system;
    }
}
