<?php
require __DIR__ . '/inc/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$root = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '');
$urls = [['/', null], ['/sobre', null], ['/projetos', null], ['/eventos', null], ['/blog', null], ['/apoie', null], ['/contato', null], ['/privacidade', null]];
foreach (rows('SELECT slug, atualizado_em FROM projetos WHERE ativo = 1') as $r) $urls[] = ['/projetos/' . $r['slug'], $r['atualizado_em']];
foreach (rows('SELECT slug, atualizado_em FROM eventos WHERE ativo = 1') as $r) $urls[] = ['/eventos/' . $r['slug'], $r['atualizado_em']];
foreach (rows('SELECT slug, atualizado_em FROM posts WHERE publicado = 1 AND data <= ?', [date('Y-m-d')]) as $r) $urls[] = ['/blog/' . $r['slug'], $r['atualizado_em']];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as [$u, $m]) {
    echo '  <url><loc>' . e($root . url($u)) . '</loc>' . ($m ? '<lastmod>' . e(substr($m, 0, 10)) . '</lastmod>' : '') . "</url>\n";
}
echo "</urlset>\n";
