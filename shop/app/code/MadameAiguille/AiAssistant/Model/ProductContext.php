<?php
/**
 * Ce que le modèle reçoit de la fiche : une liste blanche de champs du formulaire produit, lue côté serveur.
 *
 * Le navigateur envoie l'état courant du formulaire (y compris ce qui n'est pas encore enregistré) ; seuls les champs
 * ci-dessous en sont retenus, réduits à du texte brut et bornés en longueur. **Aucune donnée client** ne peut passer :
 * il n'y en a pas dans un formulaire produit, et rien d'autre que ces champs n'est lu.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model;

class ProductContext
{
    /** Champs texte retenus, avec le nom donné au modèle. */
    public const TEXT_FIELDS = [
        'name' => 'nom',
        'sku' => 'reference',
        'short_description' => 'description_courte_actuelle',
        'description' => 'description_actuelle',
        'composition' => 'composition',
        'dimensions' => 'dimensions',
        'entretien' => 'entretien',
        'meta_title' => 'titre_meta_actuel',
        'meta_description' => 'description_meta_actuelle',
    ];

    /** Champs que l'assistant ne propose de remplir que s'ils sont vides. */
    public const FILL_IF_EMPTY = ['composition', 'dimensions', 'entretien'];

    private const MAX_LENGTH = 3000;

    /**
     * @param array<string, mixed> $facts
     * @param array<string, string> $values Champs texte de la fiche, par code d'attribut
     */
    public function __construct(
        public readonly array $facts,
        public readonly bool $isNew,
        private readonly array $values
    ) {
    }

    /**
     * @param array<string, mixed> $form Données `product` du formulaire
     * @param list<string> $categoryNames
     */
    public static function fromForm(array $form, bool $isNew, array $categoryNames): self
    {
        $values = [];
        $facts = ['produit_neuf' => $isNew];
        foreach (self::TEXT_FIELDS as $code => $label) {
            $value = self::text($form[$code] ?? '');
            $values[$code] = $value;
            if ($value !== '') {
                $facts[$label] = $value;
            }
        }
        if ($categoryNames !== []) {
            $facts['categories'] = $categoryNames;
        }
        if (in_array($form['type_id'] ?? '', ['simple', 'configurable'], true)) {
            $facts['type'] = $form['type_id'] === 'configurable' ? 'modèle décliné en plusieurs tailles' : 'pièce simple';
        }
        if (is_numeric($form['price'] ?? null) && (float) $form['price'] > 0) {
            $facts['prix_euros'] = round((float) $form['price'], 2);
        }
        if (is_numeric($form['weight'] ?? null) && (float) $form['weight'] > 0) {
            $facts['poids_grammes'] = (int) round((float) $form['weight'] * 1000);
        }
        if ((string) ($form['serie_limitee'] ?? '') === '1') {
            $facts['serie_limitee'] = true;
            if (ctype_digit((string) ($form['taille_serie'] ?? '')) && (int) $form['taille_serie'] > 0) {
                $facts['pieces_dans_la_serie'] = (int) $form['taille_serie'];
            }
        }

        return new self($facts, $isNew, $values);
    }

    public function isEmpty(string $code): bool
    {
        return ($this->values[$code] ?? '') === '';
    }

    public function name(): string
    {
        return $this->values['name'] ?? '';
    }

    /** Texte brut d'un champ (le WYSIWYG renvoie du HTML), espaces resserrés, longueur bornée. */
    private static function text(mixed $value): string
    {
        if (!is_scalar($value)) {
            return '';
        }
        $text = html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '</li>'], "\n", (string) $value)));
        $text = trim((string) preg_replace(['/[ \t]+/u', '/\s*\n\s*/u'], [' ', "\n"], $text));

        return mb_substr($text, 0, self::MAX_LENGTH);
    }
}
