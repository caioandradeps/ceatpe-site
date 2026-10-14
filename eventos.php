<?php
require __DIR__ . '/inc/layout.php';

$hoje = date('Y-m-d');
$proximos = rows('SELECT * FROM eventos WHERE ativo = 1 AND data >= ? ORDER BY data ASC, id ASC', [$hoje]);
$passados = rows('SELECT * FROM eventos WHERE ativo = 1 AND data < ? ORDER BY data DESC LIMIT 12', [$hoje]);
$insta = setting('instagram');

page_head('Eventos', ['active' => 'eventos', 'description' => 'Agenda de eventos, palestras e oficinas gratuitas do CEATPE na Região Metropolitana do Recife.']);
page_hero('Agenda de eventos', 'Palestras, oficinas e encontros abertos à comunidade. A maioria é gratuita, com vagas limitadas.');
?>
<section class="ce-section">
  <div class="ce-container">
    <?php if ($proximos): ?>
      <div class="ce-section-head"><div class="ce-section-head__text"><h2 class="ce-section-head__title">Próximos eventos</h2></div></div>
      <ol class="agenda">
        <?php foreach ($proximos as $ev) include __DIR__ . '/inc/partials/evento-item.php'; ?>
      </ol>
    <?php else: ?>
      <div class="empty">
        <span class="empty__icon"><?= icon('calendar', 26) ?></span>
        <h2>Nenhum evento com inscrições abertas agora</h2>
        <p>Os próximos eventos aparecem aqui assim que forem confirmados. Enquanto isso, conheça nossos projetos e cursos.</p>
        <div class="ce-hero__actions">
          <a class="ce-btn ce-btn--primary ce-btn--md" href="<?= url('/projetos') ?>">Ver projetos e cursos</a>
          <?php if ($insta): ?><a class="ce-btn ce-btn--secondary ce-btn--md" href="<?= e($insta) ?>" target="_blank" rel="noopener"><?= icon('instagram', 18) ?>Acompanhar no Instagram</a><?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php if ($passados): ?>
<section class="ce-section ce-section--sunken">
  <div class="ce-container">
    <div class="ce-section-head"><div class="ce-section-head__text"><h2 class="ce-section-head__title">Já aconteceu</h2></div></div>
    <ol class="agenda">
      <?php foreach ($passados as $ev) include __DIR__ . '/inc/partials/evento-item.php'; ?>
    </ol>
  </div>
</section>
<?php endif; ?>
<?php page_foot(); ?>
