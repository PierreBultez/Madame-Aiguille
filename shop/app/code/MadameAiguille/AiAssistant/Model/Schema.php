<?php
/**
 * Schémas JSON imposés aux deux réponses du modèle (sorties structurées de l'API, `output_config.format`).
 *
 * Tous les champs sont obligatoires et sans propriété supplémentaire : un champ « sans proposition » vaut la chaîne
 * vide. Les bornes (huit questions, longueurs) sont rappelées dans la consigne et appliquées par Assistant.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model;

class Schema
{
    public const QUESTION_TYPES = ['texte', 'choix', 'oui_non'];

    /**
     * @return array<string, mixed>
     */
    public static function analysis(): array
    {
        return self::object([
            'comprehension' => ['type' => 'string', 'description' => 'Ce que le modèle a compris de la pièce'],
            'questions' => [
                'type' => 'array',
                'description' => 'Huit questions au plus, seulement celles dont la réponse manque',
                'items' => self::object([
                    'id' => ['type' => 'string'],
                    'question' => ['type' => 'string'],
                    'type' => ['type' => 'string', 'enum' => self::QUESTION_TYPES],
                    'choix' => ['type' => 'array', 'items' => ['type' => 'string']],
                ]),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function generation(): array
    {
        $text = ['type' => 'string'];

        return self::object([
            'description_courte' => $text,
            'description_html' => $text,
            'titre_meta' => $text,
            'description_meta' => $text,
            'cle_url' => $text,
            'textes_alternatifs' => [
                'type' => 'array',
                'items' => self::object(['photo' => ['type' => 'integer'], 'texte' => $text]),
            ],
            'composition' => $text,
            'dimensions' => $text,
            'entretien' => $text,
            'points_a_confirmer' => ['type' => 'array', 'items' => $text],
        ]);
    }

    /**
     * @param array<string, array<string, mixed>> $properties
     * @return array<string, mixed>
     */
    private static function object(array $properties): array
    {
        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => array_keys($properties),
            'additionalProperties' => false,
        ];
    }
}
