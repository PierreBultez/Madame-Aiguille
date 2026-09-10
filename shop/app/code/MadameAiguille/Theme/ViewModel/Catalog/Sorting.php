<?php
/**
 * Madame Aiguille — options de tri de la liste produits
 *
 * Un seul sélecteur (maquette Page catégorie), chaque entrée combinant un
 * champ et une direction : Pertinence (recherche seulement), Nouveautés (date
 * de mise en ligne décroissante), Prix croissant, Prix décroissant. Une entrée n'est proposée
 * que si son champ figure dans les ordres autorisés par Magento pour la
 * catégorie courante (attribut « available_sort_by »).
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Catalog;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Sorting implements ArgumentInterface
{
    /** champ, direction, libellé — « relevance » n'existe que sur la page de résultats de recherche */
    private const OPTIONS = [
        ['relevance', 'desc', 'Pertinence'],
        ['created_at', 'desc', 'Nouveautés'],
        ['price', 'asc', 'Prix croissant'],
        ['price', 'desc', 'Prix décroissant'],
    ];

    /**
     * @return array<int, array{value: string, field: string, direction: string, label: Phrase, current: bool}>
     */
    public function getOptions(Toolbar $toolbar): array
    {
        $available = $toolbar->getAvailableOrders();
        $current = $this->getCurrentValue($toolbar);
        $options = [];

        foreach (self::OPTIONS as [$field, $direction, $label]) {
            // array_key_exists : un ordre peut être présent avec un libellé vide (created_at)
            if (!array_key_exists($field, $available)) {
                continue;
            }

            $value = $this->value($field, $direction);
            $options[] = [
                'value' => $value,
                'field' => $field,
                'direction' => $direction,
                'label' => __($label),
                'current' => $value === $current,
            ];
        }

        return $options;
    }

    public function getCurrentValue(Toolbar $toolbar): string
    {
        return $this->value((string) $toolbar->getCurrentOrder(), (string) $toolbar->getCurrentDirection());
    }

    private function value(string $field, string $direction): string
    {
        return $field . ':' . $direction;
    }
}
