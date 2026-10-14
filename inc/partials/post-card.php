<?php /* espera $post */ [$cl, $cc] = cat(CATEGORIAS_BLOG, $post['categoria']); ?>
<a class="ce-card post-card" href="<?= url('/blog/' . $post['slug']) ?>" data-reveal>
  <?php if ($post['capa']): ?>
    <div class="ce-card__media" style="background-image:url('<?= e(upload_url($post['capa'])) ?>')" role="img" aria-label="<?= e($post['titulo']) ?>"></div>
  <?php else: ?>
    <div class="ce-card__media post-card__placeholder post-card__placeholder--<?= e($cc) ?>" aria-hidden="true"><img src="<?= asset('img/ceatpe-simbolo.png') ?>" alt="" width="72" height="72"></div>
  <?php endif; ?>
  <div class="ce-card__body">
    <p class="post-card__meta"><?= tag_html($cl, $cc) ?><time datetime="<?= e($post['data']) ?>"><?= e(data_longa($post['data'])) ?></time></p>
    <h3 class="ce-card__title"><?= e($post['titulo']) ?></h3>
    <p class="ce-card__desc"><?= e($post['resumo']) ?></p>
    <span class="ce-card__footer ce-link">Ler publicação<?= icon('arrow-right', 16) ?></span>
  </div>
</a>
