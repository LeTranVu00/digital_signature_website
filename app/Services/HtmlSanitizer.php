<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{
    /**
     * @var array<string, list<string>>
     */
    private array $allowedTags = [
        'a' => ['href', 'title', 'target', 'rel'],
        'blockquote' => [],
        'br' => [],
        'code' => [],
        'em' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'img' => ['src', 'alt', 'title'],
        'li' => [],
        'ol' => [],
        'p' => [],
        'pre' => [],
        'strong' => [],
        'table' => [],
        'tbody' => [],
        'td' => [],
        'th' => [],
        'thead' => [],
        'tr' => [],
        'u' => [],
        'ul' => [],
    ];

    public function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument;

        libxml_use_internal_errors(true);
        $document->loadHTML(
            '<!DOCTYPE html><html><body>'.$html.'</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $body = $document->getElementsByTagName('body')->item(0);

        if (! $body) {
            return strip_tags($html);
        }

        $this->sanitizeChildren($body);

        $clean = '';

        foreach ($body->childNodes as $childNode) {
            $clean .= $document->saveHTML($childNode);
        }

        return trim($clean);
    }

    private function sanitizeChildren(DOMNode $node): void
    {
        for ($index = $node->childNodes->length - 1; $index >= 0; $index--) {
            $child = $node->childNodes->item($index);

            if (! $child) {
                continue;
            }

            if ($child instanceof DOMElement) {
                $tagName = strtolower($child->tagName);

                if (! array_key_exists($tagName, $this->allowedTags)) {
                    $node->removeChild($child);

                    continue;
                }

                $this->sanitizeAttributes($child, $this->allowedTags[$tagName]);
            }

            if ($child->hasChildNodes()) {
                $this->sanitizeChildren($child);
            }
        }
    }

    /**
     * @param  list<string>  $allowedAttributes
     */
    private function sanitizeAttributes(DOMElement $element, array $allowedAttributes): void
    {
        for ($index = $element->attributes->length - 1; $index >= 0; $index--) {
            $attribute = $element->attributes->item($index);

            if (! $attribute) {
                continue;
            }

            $name = strtolower($attribute->name);
            $value = trim($attribute->value);

            if (! in_array($name, $allowedAttributes, true)) {
                $element->removeAttributeNode($attribute);

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! $this->isSafeUrl($value)) {
                $element->removeAttributeNode($attribute);
            }
        }

        if (strtolower($element->tagName) === 'a') {
            $element->setAttribute('rel', 'nofollow noopener noreferrer');
        }
    }

    private function isSafeUrl(string $url): bool
    {
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }
}
