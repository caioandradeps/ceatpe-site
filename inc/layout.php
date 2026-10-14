<?php
require_once __DIR__ . '/bootstrap.php';

const NAV = [
    'inicio'   => ['Início', '/'],
    'sobre'    => ['Sobre', '/sobre'],
    'projetos' => ['Projetos', '/projetos'],
    'eventos'  => ['Eventos', '/eventos'],
    'blog'     => ['Blog', '/blog'],
    'contato'  => ['Contato', '/contato'],
];

function socials(): array {
    $out = [];
    foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'youtube' => 'YouTube', 'linkedin' => 'LinkedIn', 'tiktok' => 'TikTok'] as $k => $label) {
        $u = setting($k);
        if ($u && preg_match('~^https?://~', $u)) $out[$k] = [$label, $u];
    }
    return $out;
}

function tag_html(string $label, string $color): string {
    return '<span class="ce-tag ce-tag--' . e($color) . '">' . e($label) . '</span>';
}

function stripe(string $class = ''): string {
    return '<div class="hands-stripe ' . $class . '" aria-hidden="true"><i></i><i></i><i></i><i></i></div>';
}

function page_head(string $title, array $o = []): void {
    $site = 'CEATPE';
    $full = $title ? "$title — $site" : "$site — Centro de Estudos Avançados em Tecnologia de Pernambuco";
    $desc = $o['description'] ?? setting('seo_descricao');
    $img  = $o['image'] ?? asset('img/og-ceatpe.png');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $abs = fn($u) => preg_match('~^https?://~', $u) ? $u : "$scheme://$host" . $u;
    $canonical = $abs(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
    $active = $o['active'] ?? '';
    ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<script>document.documentElement.classList.add('js')</script>
<title><?= e($full) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:type" content="<?= e($o['og_type'] ?? 'website') ?>">
<meta property="og:site_name" content="CEATPE">
<meta property="og:title" content="<?= e($title ?: $full) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($abs($img)) ?>">
<meta property="og:locale" content="pt_BR">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#11405C">
<link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
<link rel="apple-touch-icon" href="<?= asset('img/apple-touch-icon.png') ?>">
<link rel="preload" href="<?= asset('fonts/inter-latin-800-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= asset('fonts/inter-latin-400-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= asset('css/tokens.css') ?>">
<link rel="stylesheet" href="<?= asset('css/components.css') ?>">
<link rel="stylesheet" href="<?= asset('css/site.css') ?>">
<?php if (!empty($o['jsonld'])): ?><script type="application/ld+json"><?= json_encode($o['jsonld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script><?php endif; ?>
<script type="application/ld+json"><?= json_encode(org_jsonld($abs), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body class="ce-root page-<?= e($active ?: 'interna') ?>">
<a class="skip" href="#conteudo">Pular para o conteúdo</a>
<header class="ce-header site-header" data-header>
  <div class="ce-header__inner">
    <a class="ce-header__logo ce-focusable" href="<?= url('/') ?>" aria-label="CEATPE — página inicial">
      <img src="<?= asset('img/ceatpe-horizontal.png') ?>" alt="CEATPE" width="106" height="36">
    </a>
    <nav class="ce-header__nav" aria-label="Principal">
      <?php foreach (NAV as $k => [$label, $href]): ?>
        <a class="ce-navlink" href="<?= url($href) ?>"<?= $k === $active ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <a class="ce-btn ce-btn--primary ce-btn--sm ce-header__cta" href="<?= url('/apoie') ?>"<?= $active === 'apoie' ? ' aria-current="page"' : '' ?>><?= icon('heart', 16) ?>Apoie</a>
    <button class="ce-iconbtn ce-iconbtn--ghost ce-iconbtn--md ce-header__menu" type="button" aria-expanded="false" aria-controls="menu-movel" data-menu>
      <span class="ce-sr">Abrir menu</span><?= icon('menu', 22) ?>
    </button>
  </div>
  <nav class="ce-header__drawer" id="menu-movel" aria-label="Menu" data-drawer>
    <?php foreach (NAV as $k => [$label, $href]): ?>
      <a class="ce-navlink" href="<?= url($href) ?>"<?= $k === $active ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
    <a class="ce-btn ce-btn--primary ce-btn--md ce-btn--block" href="<?= url('/apoie') ?>"><?= icon('heart', 18) ?>Apoie o CEATPE</a>
  </nav>
</header>
<main id="conteudo" tabindex="-1">
<?php
}

function page_hero(string $title, string $lead = '', array $o = []): void { ?>
<section class="ce-on-brand page-hero <?= e($o['class'] ?? '') ?>">
  <div class="page-hero__inner">
    <?php if (!empty($o['crumb'])): ?>
      <a class="page-hero__back ce-focusable" href="<?= url($o['crumb'][1]) ?>"><?= icon('arrow-left', 16) ?><?= e($o['crumb'][0]) ?></a>
    <?php endif; ?>
    <?php if (!empty($o['before'])) echo $o['before']; ?>
    <h1 class="page-hero__title" data-balance><?= e($title) ?></h1>
    <?php if ($lead): ?><p class="page-hero__lead"><?= e($lead) ?></p><?php endif; ?>
    <?php if (!empty($o['after'])) echo $o['after']; ?>
  </div>
  <div class="page-hero__hands" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
</section>
<?= stripe() ?>
<?php }

function page_foot(array $o = []): void {
    $soc = socials();
    $email = setting('email'); $tel = setting('telefone'); $wa = setting('whatsapp'); $end = setting('endereco');
    $cnpj = setting('cnpj');
    ?>
</main>
<?= stripe('hands-stripe--footer') ?>
<footer class="ce-footer ce-on-brand site-footer">
  <div class="ce-footer__inner">
    <div class="ce-footer__top">
      <div class="ce-footer__brand">
        <img src="<?= asset('img/ceatpe-horizontal-negativo.png') ?>" alt="CEATPE" width="118" height="40">
        <p>Centro de Estudos Avançados em Tecnologia de Pernambuco. Ciência e tecnologia a serviço das pessoas.</p>
        <?php if ($soc): ?>
          <ul class="footer-social" aria-label="Redes sociais">
            <?php foreach ($soc as $k => [$label, $u]): ?>
              <li><a class="ce-iconbtn ce-iconbtn--ghost ce-iconbtn--md" href="<?= e($u) ?>" target="_blank" rel="noopener"><?= icon(isset(ICONS[$k]) ? $k : 'music-2', 20) ?><span class="ce-sr"><?= e($label) ?></span></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
      <div class="ce-footer__cols">
        <div class="ce-footer__col">
          <h2>Institucional</h2>
          <ul>
            <?php foreach (NAV as [$label, $href]): ?><li><a href="<?= url($href) ?>"><?= e($label) ?></a></li><?php endforeach; ?>
            <li><a href="<?= url('/apoie') ?>">Apoie</a></li>
          </ul>
        </div>
        <div class="ce-footer__col">
          <h2>Contato</h2>
          <ul>
            <?php if ($email): ?><li><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li><?php endif; ?>
            <?php if ($tel): ?><li><a href="tel:<?= e(preg_replace('/\D/', '', $tel)) ?>"><?= e($tel) ?></a></li><?php endif; ?>
            <?php if ($wa): ?><li><a href="https://wa.me/55<?= e(preg_replace('/\D/', '', $wa)) ?>" target="_blank" rel="noopener">WhatsApp</a></li><?php endif; ?>
            <?php if ($end): ?><li><span><?= nl($end) ?></span></li><?php endif; ?>
            <li><a href="<?= url('/contato') ?>">Fale conosco</a></li>
            <li><a href="<?= url('/contato?assunto=voluntariado') ?>">Seja voluntário</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="ce-footer__legal footer-legal">
      <span>© <?= date('Y') ?> CEATPE<?= $cnpj ? ' · CNPJ ' . e($cnpj) : '' ?></span>
      <span class="footer-legal__links"><a href="<?= url('/privacidade') ?>">Política de privacidade</a><a href="<?= url('/admin/') ?>" rel="nofollow">Painel</a></span>
    </div>
  </div>
</footer>
<script src="<?= asset('js/site.js') ?>" defer></script>
<?php if (!empty($o['scripts'])) echo $o['scripts']; ?>
</body>
</html>
<?php
}

function org_jsonld(callable $abs): array {
    $d = [
        '@context' => 'https://schema.org',
        '@type' => 'NGO',
        'name' => 'Centro de Estudos Avançados em Tecnologia de Pernambuco',
        'alternateName' => 'CEATPE',
        'url' => $abs(url('/')),
        'logo' => $abs(url('assets/img/ceatpe-vertical.png')),
        'description' => setting('seo_descricao'),
        'areaServed' => 'Região Metropolitana do Recife, Pernambuco, Brasil',
    ];
    if ($y = setting('ano_fundacao')) $d['foundingDate'] = $y;
    if ($e = setting('email')) $d['email'] = $e;
    if ($t = setting('telefone')) $d['telephone'] = $t;
    if ($s = array_column(socials(), 1)) $d['sameAs'] = $s;
    return $d;
}

function not_found(): never {
    http_response_code(404);
    page_head('Página não encontrada');
    page_hero('Página não encontrada', 'O endereço pode ter mudado ou o conteúdo saiu do ar.');
    ?>
    <section class="ce-section"><div class="ce-container empty">
      <p>Volte para a página inicial ou use o menu para encontrar o que procura.</p>
      <a class="ce-btn ce-btn--primary ce-btn--md" href="<?= url('/') ?>"><?= icon('arrow-left', 18) ?>Ir para o início</a>
    </div></section>
    <?php
    page_foot();
    exit;
}
