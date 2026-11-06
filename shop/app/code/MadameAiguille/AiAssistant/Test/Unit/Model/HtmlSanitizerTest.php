<?php
declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Test\Unit\Model;

use MadameAiguille\AiAssistant\Model\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    private function sanitize(string $html): string
    {
        return (new HtmlSanitizer())->sanitize($html);
    }

    public function testAllowedTagsPassUnchanged(): void
    {
        $html = '<p>Je vous propose une <strong>trousse</strong> <em>cousue main</em>.</p>'
            . '<h3>Les matières</h3><ul><li>Velours côtelé</li><li>Coton</li></ul>';

        self::assertSame($html, $this->sanitize($html));
    }

    public function testAttributesAndClassesAreRemoved(): void
    {
        self::assertSame(
            '<p>Texte <strong>fort</strong></p>',
            $this->sanitize('<p class="x" style="color:red" onclick="alert(1)">Texte <strong id="a">fort</strong></p>')
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function forbidden(): array
    {
        return [
            'script et son contenu' => ['<p>A</p><script>alert(1)</script>', '<p>A</p>'],
            'style et son contenu' => ['<style>p{}</style><p>A</p>', '<p>A</p>'],
            'iframe' => ['<p>A<iframe src="https://example.test"></iframe></p>', '<p>A</p>'],
            'lien réduit à son texte' => ['<p>Voir <a href="javascript:alert(1)">ici</a></p>', '<p>Voir ici</p>'],
            'image supprimée' => ['<p>A<img src=x onerror=alert(1)></p>', '<p>A</p>'],
            'h2 ramené à h3' => ['<h2>Titre</h2>', '<h3>Titre</h3>'],
            'liste numérotée en liste' => ['<ol><li>Un</li></ol>', '<ul><li>Un</li></ul>'],
            'b et i normalisés' => ['<p><b>A</b> <i>B</i></p>', '<p><strong>A</strong> <em>B</em></p>'],
            'div déballée' => ['<div><p>A</p></div>', '<p>A</p>'],
            'texte nu rangé dans un p' => ['Une phrase seule.', '<p>Une phrase seule.</p>'],
            'paragraphes vides retirés' => ['<p> </p><p>A</p><h3></h3>', '<p>A</p>'],
            'commentaire HTML' => ['<p>A<!-- note --></p>', '<p>A</p>'],
        ];
    }

    #[DataProvider('forbidden')]
    public function testForbiddenMarkupIsNeutralised(string $html, string $expected): void
    {
        self::assertSame($expected, $this->sanitize($html));
    }

    public function testTextIsEscapedAndAccentsKept(): void
    {
        self::assertSame(
            '<p>Coton &amp; bambou, 10 × 10 cm, l’atelier &lt;script&gt;</p>',
            $this->sanitize('<p>Coton &amp; bambou, 10 × 10 cm, l’atelier &lt;script&gt;</p>')
        );
    }

    public function testEmptyInputStaysEmpty(): void
    {
        self::assertSame('', $this->sanitize('   '));
        self::assertSame('', $this->sanitize('<script>x</script>'));
    }
}
