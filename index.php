<?php
require __DIR__ . '/inc/layout.php';

$slides    = rows('SELECT * FROM banners WHERE ativo = 1 ORDER BY ordem ASC, id ASC');
$projetos  = rows('SELECT * FROM projetos WHERE ativo = 1 ORDER BY ordem ASC, id DESC LIMIT 4');
$eventos   = rows('SELECT * FROM eventos WHERE ativo = 1 AND data >= ? ORDER BY data ASC LIMIT 3', [date('Y-m-d')]);
$posts     = rows('SELECT * FROM posts WHERE publicado = 1 AND data <= ? ORDER BY data DESC, id DESC LIMIT 3', [date('Y-m-d')]);
$campanha  = row('SELECT * FROM campanhas WHERE ativo = 1 ORDER BY destaque DESC, id DESC LIMIT 1');
$parceiros = rows('SELECT * FROM parceiros WHERE ativo = 1 ORDER BY ordem ASC, id ASC');

$ano = setting('ano_fundacao');
$alcance = setting('alcance');
$pix = setting('pix_chave');

$areas = [
    ['Pesquisa', 'red', 'flask-conical', 'Ciência e tecnologia aplicadas aos desafios reais da Região Metropolitana do Recife.'],
    ['Extensão', 'gold', 'leaf', 'Ações na comunidade, da educação ambiental às campanhas de arrecadação.'],
    ['Inovação', 'green', 'lightbulb', 'Colaboração entre diferentes setores para gerar novas ideias e soluções.'],
    ['Formação', 'blue', 'graduation-cap', 'Cursos gratuitos para todas as idades, da robótica à informática.'],
];

page_head('', ['active' => 'inicio']);
?>

<?php if ($slides): ?>
<section class="ce-on-brand hero" aria-roledescription="carrossel" aria-label="Destaques" data-hero data-interval="7000">
  <div class="hero__inner">
    <div class="hero__text">
      <?php foreach ($slides as $i => $s): ?>
        <div class="hero__slide" id="slide-<?= $i ?>" role="group" aria-roledescription="slide" aria-label="<?= $i + 1 ?> de <?= count($slides) ?>" data-slide data-color="<?= e($s['cor']) ?>"<?= $i ? ' hidden' : '' ?>>
          <?php if ($s['eyebrow']): ?><p class="ce-eyebrow hero__eyebrow"><span class="dot dot--<?= e($s['cor']) ?>"></span><?= e($s['eyebrow']) ?></p><?php endif; ?>
          <?php if ($i === 0): ?><h1 class="hero__title" data-balance><?= e($s['titulo']) ?></h1><?php else: ?><p class="hero__title" data-balance><?= e($s['titulo']) ?></p><?php endif; ?>
          <?php if ($s['texto']): ?><p class="hero__sub"><?= e($s['texto']) ?></p><?php endif; ?>
          <div class="ce-hero__actions">
            <?php if ($s['botao_texto'] && $s['botao_link']): ?>
              <a class="ce-btn ce-btn--primary ce-btn--lg" href="<?= e(url($s['botao_link'])) ?>"><?= e($s['botao_texto']) ?><?= icon('arrow-right', 20) ?></a>
            <?php endif; ?>
            <a class="ce-btn ce-btn--secondary ce-btn--lg" href="<?= url('/sobre') ?>">Conheça o CEATPE</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="hero__art" aria-hidden="true">
      <svg class="hero__globe" viewBox="0 0 240 240" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
        <circle cx="120" cy="120" r="104" fill="#11405C"/>
        <ellipse cx="120" cy="120" rx="46" ry="104"/>
        <path d="M120 16v208M22 84h196M22 156h196M16 120h208"/>
      </svg>
      <div class="hero__hands">
        <i class="hand hand--red" data-hand="red"></i>
        <i class="hand hand--gold" data-hand="gold"></i>
        <i class="hand hand--green" data-hand="green"></i>
        <i class="hand hand--blue" data-hand="blue"></i>
      </div>
    </div>

    <?php if (count($slides) > 1): ?>
    <div class="hero__controls">
      <div class="hero__tabs" role="tablist" aria-label="Escolher destaque">
        <?php foreach ($slides as $i => $s): ?>
          <button class="hero__tab" type="button" role="tab" aria-controls="slide-<?= $i ?>" aria-selected="<?= $i ? 'false' : 'true' ?>" data-go="<?= $i ?>" style="--c: var(--accent-<?= e($s['cor']) ?>)">
            <span class="hero__bar"><span class="hero__fill"></span></span>
            <span class="hero__tablabel"><?= e($s['eyebrow'] ?: 'Destaque ' . ($i + 1)) ?></span>
          </button>
        <?php endforeach; ?>
      </div>
      <button class="ce-iconbtn ce-iconbtn--ghost ce-iconbtn--md hero__pause" type="button" data-pause aria-pressed="false">
        <span data-pause-icon><?= icon('pause', 18) ?></span><span data-play-icon hidden><?= icon('play', 18) ?></span>
        <span class="ce-sr" data-pause-label>Pausar destaques</span>
      </button>
    </div>
    <?php endif; ?>
  </div>
</section>
<?= stripe() ?>
<?php endif; ?>

<section class="ce-section about">
  <div class="ce-container about__grid">
    <div class="about__head" data-reveal>
      <p class="ce-eyebrow">Quem somos</p>
      <h2 class="display-2" data-balance>Ciência e tecnologia a serviço das pessoas</h2>
    </div>
    <div class="about__body" data-reveal>
      <p class="lead"><?= e(setting('quem_somos')) ?></p>
      <?php if ($ano || $alcance): ?>
      <dl class="facts">
        <?php if ($ano): ?><div class="fact"><dt>Fundação</dt><dd><?= e($ano) ?></dd></div><?php endif; ?>
        <?php if ($alcance): ?><div class="fact"><dt>Pessoas alcançadas</dt><dd>+<span data-count="<?= (int) preg_replace('/\D/', '', $alcance) ?>"><?= e(number_format((int) preg_replace('/\D/', '', $alcance), 0, ',', '.')) ?></span></dd></div><?php endif; ?>
        <div class="fact"><dt>Onde atuamos</dt><dd class="fact__text">Região Metropolitana do Recife</dd></div>
      </dl>
      <?php endif; ?>
      <a class="ce-link" href="<?= url('/sobre') ?>">Conheça nossa missão, visão e valores<?= icon('arrow-right', 16) ?></a>
    </div>
  </div>
</section>

<section class="ce-section ce-section--sunken areas" aria-labelledby="areas-title">
  <div class="ce-container">
    <div class="ce-section-head">
      <div class="ce-section-head__text">
        <p class="ce-eyebrow">Como atuamos</p>
        <h2 class="ce-section-head__title" id="areas-title">Quatro frentes, um mesmo compromisso</h2>
        <p class="ce-section-head__desc">Como as quatro mãos da nossa marca, cada frente sustenta uma parte do trabalho.</p>
      </div>
    </div>
    <ul class="areas__list" data-areas>
      <?php foreach ($areas as $i => [$nome, $cor, $ic, $txt]): ?>
        <li class="area" style="--c: var(--accent-<?= $cor ?>); --i: <?= $i ?>">
          <span class="area__rule" aria-hidden="true"></span>
          <span class="area__icon area__icon--<?= $cor ?>"><?= icon($ic, 24) ?></span>
          <h3 class="area__title"><?= e($nome) ?></h3>
          <p class="area__text"><?= e($txt) ?></p>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php if ($projetos): ?>
<section class="ce-section projects" aria-labelledby="proj-title">
  <div class="ce-container">
    <div class="ce-section-head">
      <div class="ce-section-head__text">
        <p class="ce-eyebrow">Projetos e cursos</p>
        <h2 class="ce-section-head__title" id="proj-title">Gratuitos, com vagas limitadas</h2>
      </div>
      <a class="ce-link" href="<?= url('/projetos') ?>">Ver todos os projetos<?= icon('arrow-right', 16) ?></a>
    </div>
    <ol class="rows">
      <?php foreach ($projetos as $p): [$cl, $cc] = cat(CATEGORIAS_PROGRAMA, $p['categoria']); ?>
        <li data-reveal>
          <a class="row-link" href="<?= url('/projetos/' . $p['slug']) ?>" style="--c: var(--accent-<?= e($cc) ?>)">
            <span class="row-link__tag"><?= tag_html($cl, $cc) ?></span>
            <span class="row-link__main">
              <span class="row-link__title"><?= e($p['titulo']) ?></span>
              <span class="row-link__text"><?= e($p['resumo']) ?></span>
            </span>
            <span class="row-link__meta"><?php if ($p['publico']): ?><?= icon('users', 16) ?><?= e($p['publico']) ?><?php endif; ?></span>
            <span class="row-link__arrow" aria-hidden="true"><?= icon('arrow-up-right', 22) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<?php if ($eventos): ?>
<section class="ce-section ce-section--sunken" aria-labelledby="ev-title">
  <div class="ce-container">
    <div class="ce-section-head">
      <div class="ce-section-head__text">
        <p class="ce-eyebrow">Agenda</p>
        <h2 class="ce-section-head__title" id="ev-title">Próximos eventos</h2>
      </div>
      <a class="ce-link" href="<?= url('/eventos') ?>">Ver agenda completa<?= icon('arrow-right', 16) ?></a>
    </div>
    <ol class="agenda">
      <?php foreach ($eventos as $ev) include __DIR__ . '/inc/partials/evento-item.php'; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<section class="ce-section ce-on-brand support" aria-labelledby="apoio-title">
  <div class="ce-container support__grid">
    <div class="support__text" data-reveal>
      <p class="ce-eyebrow">Apoie nossas ações</p>
      <h2 class="ce-section-head__title" id="apoio-title" data-balance>Fortaleça ações contínuas, com transparência</h2>
      <p class="support__lead"><?= e(setting('doacao_texto')) ?></p>
      <div class="ce-hero__actions">
        <a class="ce-btn ce-btn--primary ce-btn--lg" href="<?= url('/apoie') ?>"><?= icon('heart', 20) ?>Fazer uma doação</a>
        <a class="ce-btn ce-btn--secondary ce-btn--lg" href="<?= url('/contato?assunto=voluntariado') ?>">Seja voluntário</a>
      </div>
      <?php if ($pix): ?>
        <div class="pix pix--inline">
          <span class="pix__label">PIX</span>
          <code class="pix__key" data-copy-src><?= e($pix) ?></code>
          <button class="ce-btn ce-btn--ghost ce-btn--sm" type="button" data-copy><?= icon('copy', 16) ?><span data-copy-label>Copiar</span></button>
        </div>
      <?php endif; ?>
    </div>
    <?php if ($campanha): ?>
    <article class="campaign" data-reveal>
      <?= stripe() ?>
      <p class="campaign__kicker"><span class="campaign__pulse" aria-hidden="true"></span>Campanha em andamento</p>
      <h3 class="campaign__title"><?= e($campanha['titulo']) ?></h3>
      <p class="campaign__text"><?= e($campanha['texto']) ?></p>
      <a class="ce-btn ce-btn--secondary ce-btn--md campaign__cta" href="<?= url('/apoie#campanhas') ?>">Saiba como doar<?= icon('arrow-right', 18) ?></a>
    </article>
    <?php endif; ?>
  </div>
</section>

<?php if ($posts): ?>
<section class="ce-section" aria-labelledby="blog-title">
  <div class="ce-container">
    <div class="ce-section-head">
      <div class="ce-section-head__text">
        <p class="ce-eyebrow">Blog</p>
        <h2 class="ce-section-head__title" id="blog-title">Novidades do CEATPE</h2>
      </div>
      <a class="ce-link" href="<?= url('/blog') ?>">Ver todas as publicações<?= icon('arrow-right', 16) ?></a>
    </div>
    <div class="ce-grid posts-grid">
      <?php foreach ($posts as $post) include __DIR__ . '/inc/partials/post-card.php'; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($parceiros): ?>
<section class="ce-section ce-section--sunken partners" aria-labelledby="parc-title">
  <div class="ce-container partners__head">
    <h2 class="partners__title" id="parc-title">Parceiros que apoiam o CEATPE</h2>
    <a class="ce-link" href="<?= url('/contato?assunto=parcerias') ?>">Quero ser parceiro<?= icon('arrow-right', 16) ?></a>
  </div>
  <div class="marquee" data-marquee>
    <ul class="marquee__track" data-track>
      <?php foreach ($parceiros as $pa): ?>
        <li class="partner">
          <?php $inner = $pa['logo']
              ? '<img src="' . e(upload_url($pa['logo'])) . '" alt="' . e($pa['nome']) . '" loading="lazy">'
              : '<span class="partner__name">' . e($pa['nome']) . '</span>'; ?>
          <?php if ($pa['site']): ?><a href="<?= e($pa['site']) ?>" target="_blank" rel="noopener"><?= $inner ?></a><?php else: ?><?= $inner ?><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php page_foot(); ?>
