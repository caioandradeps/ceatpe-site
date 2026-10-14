<?php /* espera $ev */ $dp = data_partes($ev['data']); [$cl, $cc] = cat(CATEGORIAS_PROGRAMA, $ev['categoria']); $passado = $ev['data'] < date('Y-m-d'); ?>
<li class="agenda__item<?= $passado ? ' agenda__item--past' : '' ?>" data-reveal>
  <a class="agenda__link" href="<?= url('/eventos/' . $ev['slug']) ?>" style="--c: var(--accent-<?= e($cc) ?>)">
    <time class="datebox" datetime="<?= e($ev['data']) ?>">
      <span class="datebox__day"><?= e($dp['dia']) ?></span>
      <span class="datebox__month"><?= e($dp['mes']) ?><?= $dp['ano'] !== date('Y') ? ' ' . e($dp['ano']) : '' ?></span>
    </time>
    <span class="agenda__main">
      <span class="agenda__tags"><?= tag_html($cl, $cc) ?><?php if ((int) $ev['gratuito']): ?><span class="ce-badge ce-badge--success">Gratuito</span><?php endif; ?></span>
      <span class="agenda__title"><?= e($ev['titulo']) ?></span>
      <span class="agenda__text"><?= e($ev['resumo']) ?></span>
      <span class="agenda__meta">
        <span><?= icon('calendar', 16) ?><?= e(ucfirst($dp['semana'])) ?></span>
        <?php if ($ev['hora']): ?><span><?= icon('clock', 16) ?><?= e($ev['hora']) ?></span><?php endif; ?>
        <?php if ($ev['local']): ?><span><?= icon('map-pin', 16) ?><?= e($ev['local']) ?></span><?php endif; ?>
      </span>
    </span>
    <span class="row-link__arrow" aria-hidden="true"><?= icon('arrow-up-right', 22) ?></span>
  </a>
</li>
