<?php
/**
 * Nettoyage par liste blanche de la description longue, côté serveur, avant tout renvoi au navigateur.
 *
 * Seules `h2`, `h3`, `p`, `ul`, `li`, `strong`, `em`, `blockquote` et le tableau simple (`table`, `tr`, `th`, `td`,
 * rangés dans un `tbody`) survivent, **sans aucun attribut** : c'est la mise en page de l'exemple de référence de
 * Céline (sections `h2`, sous-parties `h3`, encadré, tableau des caractéristiques). Une balise inconnue est remplacée
 * par son texte ; `script`, `style` et consorts disparaissent avec leur contenu. Le rendu vient ensuite de la classe `prose` du thème, identique sur
 * toutes les fiches. Le modèle est tenu à ces balises par la consigne ; ce filtre ne lui fait pas confiance.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model;

class HtmlSanitizer
{
    public const ALLOWED = ['h2', 'h3', 'p', 'ul', 'li', 'strong', 'em', 'blockquote', 'tr', 'th', 'td'];

    /** Balises retirées avec tout leur contenu. */
    private const DROPPED = ['script', 'style', 'iframe', 'object', 'embed', 'template', 'noscript', 'svg', 'math',
        'head', 'title', 'form', 'textarea', 'select', 'button', 'colgroup', 'col', 'caption'];

    /** Blocs du premier niveau (après rendu : une `div` déballée en donne aussi) ; un texte nu va dans un `p`. */
    private const BLOCKS = ['h2', 'h3', 'p', 'ul', 'blockquote', 'table'];

    public function sanitize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><body>' . $html . '</body>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $document->getElementsByTagName('body')->item(0);
        if ($body === null) {
            return '';
        }

        $output = '';
        $loose = '';
        foreach ($body->childNodes as $node) {
            $fragment = $this->render($node);
            if (preg_match('#^<(' . implode('|', self::BLOCKS) . ')>#', $fragment)) {
                $output .= $this->paragraph($loose) . $fragment;
                $loose = '';
            } else {
                $loose .= $fragment;
            }
        }
        $output .= $this->paragraph($loose);

        return trim((string) preg_replace('#<(p|h2|h3|li|strong|em|blockquote)>\s*</\1>#u', '', $output));
    }

    private function render(\DOMNode $node): string
    {
        if ($node instanceof \DOMText) {
            return htmlspecialchars($node->textContent, ENT_NOQUOTES | ENT_HTML5, 'UTF-8');
        }
        if (!$node instanceof \DOMElement) {
            return '';
        }

        $tag = strtolower($node->tagName);
        if (in_array($tag, self::DROPPED, true)) {
            return '';
        }

        $inner = '';
        foreach ($node->childNodes as $child) {
            $inner .= $this->render($child);
        }

        return match (true) {
            $tag === 'b' => '<strong>' . $inner . '</strong>',
            $tag === 'i' => '<em>' . $inner . '</em>',
            in_array($tag, self::ALLOWED, true) => sprintf('<%1$s>%2$s</%1$s>', $tag, $inner),
            $tag === 'table' => '<table><tbody>' . $inner . '</tbody></table>',
            in_array($tag, ['thead', 'tbody', 'tfoot'], true) => $inner,
            $tag === 'h1' => '<h2>' . $inner . '</h2>',
            in_array($tag, ['h4', 'h5', 'h6'], true) => '<h3>' . $inner . '</h3>',
            $tag === 'ol' => '<ul>' . $inner . '</ul>',
            $tag === 'br' => ' ',
            default => $inner,
        };
    }

    private function paragraph(string $loose): string
    {
        return trim(strip_tags($loose)) === '' ? '' : '<p>' . trim($loose) . '</p>';
    }
}
