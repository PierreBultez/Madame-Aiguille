<?php
/**
 * Coupe les suggestions orthographiques d'OpenSearch (« Vouliez-vous dire » et liste de la recherche rapide).
 * Sur un catalogue d'une poignée de fiches, le correcteur ne trouve rien de juste : « adele » donnait les
 * références « ade-v » et « ade-t », « trousse » donnait « trouve ». La recherche rapide retombe alors sur
 * les recherches passées qui ont abouti (comportement natif de Magento).
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DisableSearchSpellingSuggestions implements DataPatchInterface
{
    public function __construct(
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): self
    {
        $this->configWriter->save(SuggestedQueriesInterface::SEARCH_SUGGESTION_ENABLED, '0');

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
