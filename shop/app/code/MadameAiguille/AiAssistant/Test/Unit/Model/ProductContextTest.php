<?php
declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Test\Unit\Model;

use MadameAiguille\AiAssistant\Model\ProductContext;
use PHPUnit\Framework\TestCase;

class ProductContextTest extends TestCase
{
    public function testOnlyWhitelistedProductFieldsAreKeptAsPlainText(): void
    {
        $context = ProductContext::fromForm([
            'name' => '  Les tendres cotons ',
            'short_description' => '<p>Tout <strong>doux</strong></p><p>et lavables</p>',
            'serie_limitee' => '1',
            'taille_serie' => '8',
            'type_id' => 'configurable',
            'customer_email' => 'cliente@example.test',
            'entretien' => '',
            'price' => '0',
        ], true, []);

        self::assertSame([
            'produit_neuf' => true,
            'nom' => 'Les tendres cotons',
            'description_courte_actuelle' => "Tout doux\net lavables",
            'type' => 'modèle décliné en plusieurs tailles',
            'serie_limitee' => true,
            'pieces_dans_la_serie' => 8,
        ], $context->facts);
        self::assertTrue($context->isEmpty('entretien'));
        self::assertFalse($context->isEmpty('name'));
    }

    public function testArraysAndObjectsInTextFieldsAreIgnored(): void
    {
        $context = ProductContext::fromForm(['name' => ['injection'], 'sku' => 'CT-1'], false, []);

        self::assertSame(['produit_neuf' => false, 'reference' => 'CT-1'], $context->facts);
    }
}
