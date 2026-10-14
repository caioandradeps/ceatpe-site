<?php
/* Limpa o HTML do editor: só tags de texto, links seguros e imagens. */
function clean_html(?string $html): string {
    $html = trim((string) $html);
    if ($html === '' || $html === '<p><br></p>') return '';
    $allowed = [
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'h2' => [], 'h3' => [], 'h4' => [], 'blockquote' => [], 'ul' => [], 'ol' => [], 'li' => [],
        'a' => ['href', 'target', 'rel'], 'img' => ['src', 'alt'], 'figure' => [], 'figcaption' => [],
        'iframe' => ['src', 'allowfullscreen', 'title'],
    ];
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $doc->getElementById('__root');
    if (!$root) return '';
    _clean_node($root, $allowed);
    $out = '';
    foreach ($root->childNodes as $c) $out .= $doc->saveHTML($c);
    return trim($out);
}

function _clean_node(DOMNode $node, array $allowed): void {
    for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
        $c = $node->childNodes->item($i);
        if ($c instanceof DOMElement) {
            $tag = strtolower($c->tagName);
            if (in_array($tag, ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select'], true)) {
                $node->removeChild($c); continue;
            }
            if ($tag === 'h1') { $c = _rename($c, 'h2'); $tag = 'h2'; }
            if (!isset($allowed[$tag])) {
                _clean_node($c, $allowed);
                while ($c->firstChild) $node->insertBefore($c->firstChild, $c);
                $node->removeChild($c); continue;
            }
            foreach (iterator_to_array($c->attributes) as $attr) {
                if (!in_array($attr->name, $allowed[$tag], true)) $c->removeAttribute($attr->name);
            }
            foreach (['href', 'src'] as $a) {
                if ($c->hasAttribute($a)) {
                    $v = trim($c->getAttribute($a));
                    $safe = preg_match('~^(https?://|/|mailto:|tel:|#)~i', $v);
                    if ($tag === 'iframe') $safe = preg_match('~^https://(www\.)?(youtube\.com|youtube-nocookie\.com|player\.vimeo\.com|www\.google\.com/maps)/~i', $v);
                    if (!$safe) { $c->removeAttribute($a); if ($tag === 'iframe' || $tag === 'img') { $node->removeChild($c); continue 2; } }
                }
            }
            if ($tag === 'a' && preg_match('~^https?://~i', $c->getAttribute('href'))) {
                $c->setAttribute('target', '_blank'); $c->setAttribute('rel', 'noopener');
            }
            if ($tag === 'img') $c->setAttribute('loading', 'lazy');
            _clean_node($c, $allowed);
        } elseif ($c instanceof DOMComment) {
            $node->removeChild($c);
        }
    }
}

function _rename(DOMElement $el, string $tag): DOMElement {
    $new = $el->ownerDocument->createElement($tag);
    while ($el->firstChild) $new->appendChild($el->firstChild);
    $el->parentNode->replaceChild($new, $el);
    return $new;
}
