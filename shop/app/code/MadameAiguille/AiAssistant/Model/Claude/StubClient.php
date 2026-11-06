<?php
/**
 * Client doublé pour le poste local : réponses fixes, aucun appel réseau, aucun coût. Il sert à recetter le panneau
 * sans clé (lot 10). Il n'est jamais choisi en mode production (ClientRouter).
 *
 * Sa génération contient volontairement une balise interdite et un attribut : le nettoyeur doit les retirer.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model\Claude;

class StubClient implements ClientInterface
{
    public const MODEL = 'doublure-locale';

    /** Clé sentinelle à saisir sur le poste local pour obtenir ce client. */
    public const KEY = 'doublure-locale';

    public function request(string $system, array $content, array $schema, int $maxTokens): Response
    {
        $photos = count(array_filter($content, fn (array $block): bool => $block['type'] === 'image'));
        $data = isset($schema['properties']['questions']) ? $this->analysis() : $this->generation($photos);

        return new Response($data, self::MODEL, 1200 + 1600 * $photos, 600, 0, 0);
    }

    /**
     * @return array<string, mixed>
     */
    private function analysis(): array
    {
        return [
            'comprehension' => 'Réponse d\'essai du poste local : une pièce cousue main, d\'après la fiche.',
            'questions' => [
                ['id' => 'usage', 'question' => 'À quoi sert principalement cette pièce ?', 'type' => 'texte',
                    'choix' => []],
                ['id' => 'matiere', 'question' => 'Quelle est la matière de la doublure ?', 'type' => 'choix',
                    'choix' => ['Coton', 'Lin', 'Polyester']],
                ['id' => 'cadeau', 'question' => 'Est-elle pensée comme une idée cadeau ?', 'type' => 'oui_non',
                    'choix' => []],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function generation(int $photos): array
    {
        return [
            'description_courte' => 'Texte d\'essai du poste local, cousu main dans mon atelier de Saint-Épain.',
            'description_html' => '<p class="x">J\'ai cousu cette pièce d\'essai dans <strong>mon atelier</strong>.</p>'
                . '<h3>Au quotidien</h3><p>Paragraphe d\'essai.</p><script>alert(1)</script>'
                . '<ul><li>Premier point</li><li>Second point</li></ul>',
            'titre_meta' => 'Pièce d\'essai cousue main | Madame Aiguille',
            'description_meta' => 'Description méta d\'essai, produite par le client doublé du poste local.',
            'cle_url' => 'Pièce d\'essai cousue main',
            'textes_alternatifs' => array_map(
                fn (int $index): array => ['photo' => $index, 'texte' => sprintf('Photo d\'essai numéro %d.', $index)],
                range(1, max(1, $photos))
            ),
            'composition' => 'Coton (essai)',
            'dimensions' => '',
            'entretien' => 'Lavable à 30 °C (essai)',
            'points_a_confirmer' => ['Réponse d\'essai : aucun texte n\'a été rédigé par le modèle.'],
        ];
    }
}
