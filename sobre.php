<?php
require __DIR__ . '/inc/layout.php';

$equipe = rows('SELECT * FROM equipe WHERE ativo = 1 ORDER BY ordem ASC, id ASC');
$valores = [];
foreach (lines(setting('valores')) as $l) {
    $p = array_map('trim', explode('|', $l, 2));
    $valores[] = [$p[0], $p[1] ?? ''];
}
$cores = ['red', 'gold', 'green', 'blue'];
$ano = setting('ano_fundacao');
$alcance = (int) preg_replace('/\D/', '', setting('alcance'));

page_head('Sobre nós', ['active' => 'sobre', 'description' => 'Missão, visão e valores do CEATPE, o Centro de Estudos Avançados em Tecnologia de Pernambuco.']);
page_hero($ano ? "Fundado em $ano para transformar Pernambuco" : 'Sobre o CEATPE', 'O CEATPE nasceu para fomentar e difundir conhecimento em ciência, tecnologia, inovação, educação e meio ambiente na área metropolitana do Recife.');
?>

<section class="ce-section">
  <div class="ce-container about-intro">
    <h2 class="display-2" data-balance data-reveal>Ciência e tecnologia a serviço das pessoas</h2>
    <div class="about__body" data-reveal>
      <p class="lead"><?= e(setting('quem_somos')) ?></p>
      <?php if ($ano || $alcance): ?>
      <dl class="facts">
        <?php if ($ano): ?><div class="fact"><dt>Fundação</dt><dd><?= e($ano) ?></dd></div><?php endif; ?>
        <?php if ($alcance): ?><div class="fact"><dt>Pessoas alcançadas</dt><dd>+<?= e(number_format($alcance, 0, ',', '.')) ?></dd></div><?php endif; ?>
        <div class="fact"><dt>Onde atuamos</dt><dd class="fact__text">Região Metropolitana do Recife</dd></div>
      </dl>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="ce-section ce-section--sunken" aria-labelledby="mv-title">
  <div class="ce-container">
    <div class="ce-section-head">
      <div class="ce-section-head__text">
        <p class="ce-eyebrow">Propósito</p>
        <h2 class="ce-section-head__title" id="mv-title">Missão e visão</h2>
      </div>
    </div>
    <div class="mv">
      <?php if ($m = setting('missao')): ?>
        <article class="mv__item" data-reveal>
          <p class="mv__label"><?= icon('flask-conical', 20) ?>Missão</p>
          <p class="mv__text"><?= e($m) ?></p>
        </article>
      <?php endif; ?>
      <?php if ($v = setting('visao')): ?>
        <article class="mv__item" data-reveal>
          <p class="mv__label"><?= icon('globe', 20) ?>Visão</p>
          <p class="mv__text"><?= e($v) ?></p>
        </article>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ($valores): ?>
<section class="ce-section" aria-labelledby="val-title">
  <div class="ce-container">
    <div class="ce-section-head">
      <div class="ce-section-head__text">
        <p class="ce-eyebrow">Valores</p>
        <h2 class="ce-section-head__title" id="val-title">O que orienta cada decisão</h2>
      </div>
    </div>
    <ul class="values">
      <?php foreach ($valores as $i => [$nome, $desc]): ?>
        <li class="value" style="--c: var(--accent-<?= $cores[$i % 4] ?>)" data-reveal>
          <h3 class="value__name"><?= e($nome) ?></h3>
          <?php if ($desc): ?><p class="value__text"><?= e($desc) ?></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($equipe): ?>
<section class="ce-section ce-section--sunken" aria-labelledby="eq-title">
  <div class="ce-container">
    <div class="ce-section-head">
      <div class="ce-section-head__text">
        <p class="ce-eyebrow">Equipe</p>
        <h2 class="ce-section-head__title" id="eq-title">Quem faz o CEATPE</h2>
      </div>
    </div>
    <ul class="team">
      <?php foreach ($equipe as $p): ?>
        <li class="person" data-reveal>
          <?php if ($p['foto']): ?>
            <div class="person__photo" style="background-image:url('<?= e(upload_url($p['foto'])) ?>')" role="img" aria-label="Foto de <?= e($p['nome']) ?>"></div>
          <?php else: ?>
            <div class="person__photo" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($p['nome'], 0, 1))) ?></div>
          <?php endif; ?>
          <h3 class="person__name"><?= e($p['nome']) ?></h3>
          <p class="person__role"><?= e($p['cargo']) ?></p>
          <?php if ($p['bio']): ?><p class="person__bio"><?= e($p['bio']) ?></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<section class="ce-section ce-on-brand support">
  <div class="ce-container support__grid">
    <div class="support__text" data-reveal>
      <h2 class="ce-section-head__title" data-balance>Faça parte dessa transformação</h2>
      <p class="support__lead">Seja como voluntário, doador ou parceiro institucional, há um lugar para você nas ações do CEATPE.</p>
      <div class="ce-hero__actions">
        <a class="ce-btn ce-btn--primary ce-btn--lg" href="<?= url('/contato?assunto=voluntariado') ?>">Seja voluntário<?= icon('arrow-right', 20) ?></a>
        <a class="ce-btn ce-btn--secondary ce-btn--lg" href="<?= url('/contato?assunto=parcerias') ?>">Proponha uma parceria</a>
      </div>
    </div>
  </div>
</section>

<?php page_foot(); ?>
