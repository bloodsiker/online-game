<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Services;

final class AdminChatMessageFormatter
{
    private const ALLOWED_TAGS = ['br', 'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'span', 'font'];

    private const DANGEROUS_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math'];

    public function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previousErrors = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8"?><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $wrapper = $dom->getElementsByTagName('div')->item(0);
        if ($wrapper === null) {
            return '';
        }

        $this->cleanNode($wrapper);

        $result = '';
        foreach ($wrapper->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return trim($result);
    }

    public function textLength(string $html): int
    {
        return mb_strlen(trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function cleanNode(\DOMNode $node): void
    {
        /** @var \DOMNode[] $children */
        $children = iterator_to_array($node->childNodes);

        foreach ($children as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);

                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            /** @var \DOMElement $child */
            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DANGEROUS_TAGS, true)) {
                $node->removeChild($child);

                continue;
            }

            if (in_array($tag, ['div', 'p'], true)) {
                $this->replaceBlockWithContentAndBreak($node, $child);

                continue;
            }

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                $this->unwrap($node, $child);

                continue;
            }

            $this->cleanAttributes($child, $tag);
            $this->cleanNode($child);
        }
    }

    private function replaceBlockWithContentAndBreak(\DOMNode $parent, \DOMElement $element): void
    {
        $this->cleanNode($element);

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->insertBefore($element->ownerDocument->createElement('br'), $element);
        $parent->removeChild($element);
    }

    private function unwrap(\DOMNode $parent, \DOMElement $element): void
    {
        $this->cleanNode($element);

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private function cleanAttributes(\DOMElement $element, string $tag): void
    {
        $color = null;

        if ($tag === 'font' && $element->hasAttribute('color')) {
            $color = $this->normalizeColor($element->getAttribute('color'));
        }

        if ($tag === 'span' && $element->hasAttribute('style')) {
            preg_match('/(?:^|;)\s*color\s*:\s*([^;]+)(?:;|$)/i', $element->getAttribute('style'), $matches);
            $color = $this->normalizeColor($matches[1] ?? '');
        }

        /** @var \DOMAttr[] $attributes */
        $attributes = iterator_to_array($element->attributes);
        foreach ($attributes as $attribute) {
            $element->removeAttribute($attribute->name);
        }

        if ($color !== null) {
            $element->setAttribute('style', 'color: '.$color.';');
        }
    }

    private function normalizeColor(string $color): ?string
    {
        $color = trim(strtolower($color));

        if (preg_match('/^#[0-9a-f]{6}$/', $color) === 1) {
            return $color;
        }

        if (preg_match('/^#[0-9a-f]{3}$/', $color) === 1) {
            return sprintf('#%1$s%1$s%2$s%2$s%3$s%3$s', $color[1], $color[2], $color[3]);
        }

        if (preg_match('/^rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/', $color, $matches) !== 1) {
            return null;
        }

        $components = array_map('intval', array_slice($matches, 1));
        if (max($components) > 255) {
            return null;
        }

        return sprintf('#%02x%02x%02x', ...$components);
    }
}
