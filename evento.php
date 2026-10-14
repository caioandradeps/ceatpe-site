<?php
require __DIR__ . '/inc/layout.php';

$ev = row('SELECT * FROM eventos WHERE slug = ? AND ativo = 1', [$_GET['slug'] ?? '']);
if (!$ev) not_found();
[$cl, $cc] = cat(CATEGORIAS_PROGRAMA, $ev['categoria']);
$dp = data_partes($ev['data']);
$passado = $ev['data'] < date('Y-m-d');

$meta = '<div class="page-hero__meta"><span>' . icon('calendar', 18) . e(ucfirst($dp['semana']) . ', ' . data_longa($ev['data'])) . '</span>'
      . ($ev['hora'] ? '<span>' . icon('clock', 18) . e($ev['hora']) . '</span>' : '')
      . ($ev['local'] ? '<span>' . icon('map-pin', 18) . e($ev['local']) . '</span>' : '') . '</div>';

$ld = ['@context' => 'https://schema.org', '@type' => 'Event', 'name' => $ev['titulo'], 'startDate' => $ev['data'], 'description' => $ev['resumo'],
       'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode', 'organizer' => ['@type' => 'NGO', 'name' => 'CEATPE']];
if ($ev['local']) $ld['location'] = ['@type' => 'Place', 'name' => $ev['local'], 'address' => $ev['local']];
if ((int) $ev['gratuito']) $ld['isAccessibleForFree'] = true;

page_head($ev['titulo'], ['active' => 'eventos', 'description' => $ev['resumo'], 'image' => $ev['imagem'] ? upload_url($ev['imagem']) : null, 'jsonld' => $ld]);
page_hero($ev['titulo'], $ev['resumo'], ['crumb' => ['Agenda de eventos', '/eventos'], 'before' => tag_html($cl, $cc), 'after' => $meta]);
?>
<section class="ce-section">
  <div class="ce-container detail">
    <div>
      <?php if ($ev['imagem']): ?><figure class="article__cover"><img src="<?= e(upload_url($ev['imagem'])) ?>" alt="<?= e($ev['titulo']) ?>"></figure><?php endif; ?>
      <div class="prose"><?= $ev['conteudo'] ?: '<p>' . e($ev['resumo']) . '</p>' ?></div>
    </div>
    <aside class="detail__aside" aria-label="Informações do evento">
      <time class="datebox" datetime="<?= e($ev['data']) ?>" style="--c: var(--accent-<?= e($cc) ?>)">
        <span class="datebox__day"><?= e($dp['dia']) ?></span><span class="datebox__month"><?= e($dp['mes'] . ' ' . $dp['ano']) ?></span>
      </time>
      <dl>
        <?php if ($ev['hora']): ?><div><dt>Horário</dt><dd><?= e($ev['hora']) ?></dd></div><?php endif; ?>
        <?php if ($ev['local']): ?><div><dt>Local</dt><dd><?= e($ev['local']) ?></dd></div><?php endif; ?>
        <div><dt>Entrada</dt><dd><?= (int) $ev['gratuito'] ? 'Gratuita' : 'Consulte' ?></dd></div>
      </dl>
      <?php if ($passado): ?>
        <span class="ce-badge ce-badge--neutral">Este evento já aconteceu</span>
      <?php elseif ($ev['link_inscricao']): ?>
        <a class="ce-btn ce-btn--primary ce-btn--md ce-btn--block" href="<?= e($ev['link_inscricao']) ?>" target="_blank" rel="noopener">Inscreva-se<?= icon('external-link', 18) ?></a>
      <?php else: ?>
        <a class="ce-btn ce-btn--primary ce-btn--md ce-btn--block" href="<?= url('/contato?assunto=' . urlencode('Evento: ' . $ev['titulo'])) ?>">Quero participar<?= icon('arrow-right', 18) ?></a>
      <?php endif; ?>
    </aside>
  </div>
</section>
<?php page_foot(); ?>
