<?php
require __DIR__ . '/inc/layout.php';

$campanhas = rows('SELECT * FROM campanhas WHERE ativo = 1 ORDER BY destaque DESC, id DESC');
$pix = setting('pix_chave'); $titular = setting('pix_titular');
$banco = lines(setting('banco'));
$online = setting('doacao_link');
$temCanal = $pix || $banco || $online;

page_head('Apoie', ['active' => 'apoie', 'description' => 'Doe para o CEATPE ou seja voluntário. Suas contribuições sustentam cursos gratuitos, eventos e projetos na Região Metropolitana do Recife.']);
page_hero('Apoie o CEATPE', setting('doacao_texto'));
?>
<section class="ce-section" aria-labelledby="doar-title">
  <div class="ce-container">
    <div class="ce-section-head"><div class="ce-section-head__text">
      <p class="ce-eyebrow">Doações</p>
      <h2 class="ce-section-head__title" id="doar-title">Como doar</h2>
      <p class="ce-section-head__desc">Toda arrecadação é aplicada diretamente nas ações, com prestação de contas transparente.</p>
    </div></div>
    <div class="give">
      <?php if ($pix): ?>
        <div class="give__card" data-reveal>
          <h3><?= icon('key-round', 22) ?>PIX</h3>
          <p>Copie a chave e faça a transferência pelo aplicativo do seu banco.</p>
          <div class="give__key"><code data-copy-src><?= e($pix) ?></code><button class="ce-btn ce-btn--secondary ce-btn--sm" type="button" data-copy><?= icon('copy', 16) ?><span data-copy-label>Copiar chave</span></button></div>
          <?php if ($titular): ?><p>Titular: <strong><?= e($titular) ?></strong></p><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ($banco): ?>
        <div class="give__card" data-reveal>
          <h3><?= icon('landmark', 22) ?>Transferência bancária</h3>
          <ul class="give__lines"><?php foreach ($banco as $l): ?><li><?= e($l) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>
      <?php if ($online): ?>
        <div class="give__card" data-reveal>
          <h3><?= icon('heart-handshake', 22) ?>Doação online</h3>
          <p>Doe com cartão ou boleto pela plataforma parceira, em poucos passos.</p>
          <a class="ce-btn ce-btn--primary ce-btn--md" href="<?= e($online) ?>" target="_blank" rel="noopener">Doar agora<?= icon('external-link', 18) ?></a>
        </div>
      <?php endif; ?>
      <?php if (!$temCanal): ?>
        <div class="give__card" data-reveal>
          <h3><?= icon('heart-handshake', 22) ?>Fale com a gente</h3>
          <p>Envie uma mensagem e nossa equipe responde com os dados para a sua doação.</p>
          <a class="ce-btn ce-btn--primary ce-btn--md" href="<?= url('/contato?assunto=doacoes') ?>">Quero doar<?= icon('arrow-right', 18) ?></a>
        </div>
      <?php endif; ?>
      <div class="give__card" data-reveal>
        <h3><?= icon('users', 22) ?>Seja voluntário</h3>
        <p>Tempo e conhecimento também transformam. Conte o que você sabe fazer e como quer ajudar.</p>
        <a class="ce-btn ce-btn--secondary ce-btn--md" href="<?= url('/contato?assunto=voluntariado') ?>">Quero ser voluntário<?= icon('arrow-right', 18) ?></a>
      </div>
    </div>
  </div>
</section>

<?php if ($campanhas): ?>
<section class="ce-section ce-section--sunken" id="campanhas" aria-labelledby="camp-title">
  <div class="ce-container">
    <div class="ce-section-head"><div class="ce-section-head__text">
      <p class="ce-eyebrow">Campanhas</p>
      <h2 class="ce-section-head__title" id="camp-title">Arrecadações em andamento</h2>
    </div></div>
    <div class="campaigns">
      <?php foreach ($campanhas as $c): $itens = lines($c['como_doar']); ?>
        <article class="campaign-row" data-reveal>
          <div>
            <?php if ((int) $c['destaque']): ?><span class="ce-badge ce-badge--warning" style="margin-bottom:12px"><span class="ce-badge__dot"></span>Em destaque</span><?php endif; ?>
            <h3><?= e($c['titulo']) ?></h3>
            <p><?= e($c['texto']) ?></p>
            <?php if ($itens): ?>
              <ul class="checklist"><?php foreach ($itens as $l): ?><li><?= icon('circle-check', 18) ?><span><?= e($l) ?></span></li><?php endforeach; ?></ul>
            <?php else: ?>
              <a class="ce-btn ce-btn--primary ce-btn--md" style="margin-top:20px" href="<?= url('/contato?assunto=' . urlencode('Campanha: ' . $c['titulo'])) ?>">Quero contribuir<?= icon('arrow-right', 18) ?></a>
            <?php endif; ?>
          </div>
          <?php if ($c['imagem']): ?>
            <div class="campaign-row__img" style="background-image:url('<?= e(upload_url($c['imagem'])) ?>')" role="img" aria-label="<?= e($c['titulo']) ?>"></div>
          <?php else: ?>
            <div class="campaign-row__art" aria-hidden="true"><div class="page-hero__hands"><i></i><i></i><i></i><i></i></div></div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php page_foot(); ?>
