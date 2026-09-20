<?php

declare(strict_types=1);

namespace App\Modules\Forum\Domain\Services;

/**
 * Чистит HTML из редактора: разрешённые теги без обработчиков событий
 * и опасных URL. Посты пишут игроки — сырой HTML отдавать нельзя.
 */
final class ForumHtmlSanitizer
{
    private const ALLOWED_TAGS = ['p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'a', 'ul', 'ol', 'li', 'blockquote', 'img', 'span', 'font', 'h3', 'h4'];

    private const ALLOWED_ATTRS = [
        'p' => ['style'],
        'a' => ['href', 'title'],
        'img' => ['src', 'alt', 'width', 'height'],
        'span' => ['style'],
        'font' => ['color', 'size'],
    ];

    public function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

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
        return mb_strlen(trim(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8')));
    }

    private function cleanNode(\DOMNode $node): void
    {
        if (! $node->hasChildNodes()) {
            return;
        }

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
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                $this->unwrap($node, $child);

                continue;
            }

            $this->cleanAttributes($child, $tag);
            $this->cleanNode($child);
        }
    }

    private function unwrap(\DOMNode $parent, \DOMElement $element): void
    {
        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }
        $parent->removeChild($element);
        $this->cleanNode($parent);
    }

    private function cleanAttributes(\DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRS[$tag] ?? [];

        /** @var \DOMAttr[] $attrs */
        $attrs = iterator_to_array($element->attributes);
        foreach ($attrs as $attr) {
            $name = strtolower($attr->name);
            if (str_starts_with($name, 'on') || ! in_array($name, $allowed, true)) {
                $element->removeAttribute($attr->name);

                continue;
            }

            if ($tag === 'p' && $name === 'style') {
                if (preg_match('/(?:^|;)\s*text-align\s*:\s*(left|center|right|justify)\s*(?:;|$)/i', $attr->value, $matches) !== 1) {
                    $element->removeAttribute($attr->name);
                } else {
                    $element->setAttribute('style', 'text-align: '.strtolower($matches[1]).';');
                }

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! $this->isSafeUrl($attr->value)) {
                $element->removeAttribute($attr->name);
            }
        }

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $element->setAttribute('target', '_blank');
            $element->setAttribute('rel', 'noopener nofollow');
        }
    }

    private function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '#')) {
            return false;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto'], true);
    }
}
