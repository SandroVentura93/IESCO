<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlContentSanitizer
{
    private const ALLOWED_ELEMENTS = [
        'a', 'blockquote', 'br', 'div', 'em', 'figcaption', 'figure', 'h2', 'h3', 'h4',
        'hr', 'img', 'li', 'ol', 'p', 'span', 'strong', 'table', 'tbody', 'td', 'th',
        'thead', 'tr', 'u', 'ul',
    ];

    public function clean(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<!DOCTYPE html><html><body><div id="page-content">'.$html.'</div></body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);

        $wrapper = $document->getElementById('page-content');
        if (! $wrapper) {
            return '';
        }

        $this->cleanChildren($wrapper);

        $content = '';
        foreach ($wrapper->childNodes as $child) {
            $content .= $document->saveHTML($child);
        }

        return $content;
    }

    private function cleanChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (! in_array($tag, self::ALLOWED_ELEMENTS, true)) {
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math'], true)) {
                    $parent->removeChild($child);
                    continue;
                }

                $this->cleanChildren($child);
                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);
                continue;
            }

            $this->cleanAttributes($child, $tag);
            $this->cleanChildren($child);
        }
    }

    private function cleanAttributes(DOMElement $element, string $tag): void
    {
        $allowed = match ($tag) {
            'a' => ['href', 'title', 'target'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            'td', 'th' => ['colspan', 'rowspan'],
            default => [],
        };

        foreach (iterator_to_array($element->attributes) as $attribute) {
            if (! in_array(strtolower($attribute->name), $allowed, true)) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if (in_array(strtolower($attribute->name), ['href', 'src'], true)
                && ! $this->isSafeUrl($attribute->value, $tag === 'a')) {
                $element->removeAttributeNode($attribute);
            }
        }

        if ($tag === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function isSafeUrl(string $url, bool $allowMailto): bool
    {
        $url = trim($url);
        if ($url === '' || preg_match('/[\x00-\x20\\\\]/', $url)) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme !== null) {
            $allowedSchemes = $allowMailto ? ['http', 'https', 'mailto', 'tel'] : ['http', 'https'];

            return in_array(strtolower($scheme), $allowedSchemes, true);
        }

        return ! str_starts_with($url, '//');
    }
}
