<?php
/* Só para testar localmente com "php -S localhost:8000 router.php". No Hostinger quem faz isso é o .htaccess. */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) return false;
if (is_dir($file) && is_file(rtrim($file, '/') . '/index.php')) { $_SERVER['SCRIPT_NAME'] = rtrim($path, '/') . '/index.php'; require rtrim($file, '/') . '/index.php'; return true; }
$routes = [
  '#^/$#' => 'index.php', '#^/sobre/?$#' => 'sobre.php', '#^/projetos/?$#' => 'projetos.php', '#^/projetos/([a-z0-9-]+)/?$#' => 'projeto.php',
  '#^/eventos/?$#' => 'eventos.php', '#^/eventos/([a-z0-9-]+)/?$#' => 'evento.php', '#^/blog/?$#' => 'blog.php', '#^/blog/([a-z0-9-]+)/?$#' => 'post.php',
  '#^/apoie/?$#' => 'apoie.php', '#^/contato/?$#' => 'contato.php', '#^/privacidade/?$#' => 'privacidade.php', '#^/sitemap\.xml$#' => 'sitemap.php', '#^/robots\.txt$#' => 'robots.php',
];
foreach ($routes as $re => $target) {
  if (preg_match($re, $path, $m)) { if (isset($m[1])) $_GET['slug'] = $m[1]; $_SERVER['SCRIPT_NAME'] = '/' . $target; require __DIR__ . '/' . $target; return true; }
}
if (preg_match('#\.php$#', $path) && is_file($file)) { require $file; return true; }
require __DIR__ . '/inc/layout.php'; not_found();
