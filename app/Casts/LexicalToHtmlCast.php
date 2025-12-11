<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class LexicalToHtmlCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (empty($value)) {
            return '';
        }

        $data = json_decode($value, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $value;
        }

        if (isset($data['root']['children'])) {
            return $this->convertLexicalToHtml($data['root']['children']);
        }

        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    private function convertLexicalToHtml(array $children): string
    {
        $html = '';
        
        foreach ($children as $node) {
            $html .= $this->renderNode($node);
        }
        
        return $html;
    }

    private function renderNode(array $node): string
    {
        $type = $node['type'] ?? '';
        
        return match ($type) {
            'paragraph' => '<p>' . $this->renderChildren($node) . '</p>',
            'heading' => $this->renderHeading($node),
            'text' => $this->renderText($node),
            'link' => $this->renderLink($node),
            'list' => $this->renderList($node),
            'listitem' => '<li>' . $this->renderChildren($node) . '</li>',
            'quote' => '<blockquote>' . $this->renderChildren($node) . '</blockquote>',
            'code' => '<pre><code>' . htmlspecialchars($node['text'] ?? '') . '</code></pre>',
            'image' => $this->renderImage($node),
            default => $this->renderChildren($node),
        };
    }

    private function renderChildren(array $node): string
    {
        if (!isset($node['children'])) {
            return '';
        }

        $html = '';
        foreach ($node['children'] as $child) {
            $html .= $this->renderNode($child);
        }
        return $html;
    }

    private function renderText(array $node): string
    {
        $text = htmlspecialchars($node['text'] ?? '');
        $format = $node['format'] ?? 0;

        if ($format & 1) $text = '<strong>' . $text . '</strong>';
        if ($format & 2) $text = '<em>' . $text . '</em>';
        if ($format & 4) $text = '<s>' . $text . '</s>';
        if ($format & 8) $text = '<u>' . $text . '</u>';
        if ($format & 16) $text = '<code>' . $text . '</code>';

        return $text;
    }

    private function renderHeading(array $node): string
    {
        $tag = $node['tag'] ?? 'h1';
        return "<{$tag}>" . $this->renderChildren($node) . "</{$tag}>";
    }

    private function renderLink(array $node): string
    {
        $url = htmlspecialchars($node['url'] ?? '#');
        return '<a href="' . $url . '">' . $this->renderChildren($node) . '</a>';
    }

    private function renderList(array $node): string
    {
        $tag = ($node['listType'] ?? 'bullet') === 'number' ? 'ol' : 'ul';
        return "<{$tag}>" . $this->renderChildren($node) . "</{$tag}>";
    }

    private function renderImage(array $node): string
    {
        $src = htmlspecialchars($node['src'] ?? '');
        $alt = htmlspecialchars($node['altText'] ?? '');
        return '<img src="' . $src . '" alt="' . $alt . '" />';
    }
}
