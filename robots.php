<?php
header('Content-Type: text/plain; charset=utf-8');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
echo "User-agent: *\nDisallow: /admin/\nDisallow: /instalar.php\n\nSitemap: $scheme://" . ($_SERVER['HTTP_HOST'] ?? '') . "/sitemap.xml\n";
