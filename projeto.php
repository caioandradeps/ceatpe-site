<?php
require __DIR__ . '/inc/layout.php';

$p = row('SELECT * FROM projetos WHERE slug = ? AND ativo = 1', [$_GET['slug'] ?? '']);
if (!$p) not_found();
[$cl, $cc] = cat(CATEGORIAS_PROGRAMA, $p['categoria']);
$outros = rows('SELECT * FROM projetos WHERE ativo = 1 AND id <> ? ORDER BY ordem ASC, id DESC LIMIT 3', [$p['id']]);

page_head($p['titulo'], ['active' => 'projetos', 'description' => $p['resumo'], 'image' => $p['imagem'] ? upload_url($p['imagem']) : null]);
page_hero($p['titulo'], $p['resumo'], ['crumb' => ['Projetos e cursos', '/projetos'], 'before' => tag_html($cl, $cc)]);
?>
<section class="ce-section">
  <div class="ce-container detail">
    <div>
      <?php if ($p['imagem']): ?><figure class="article__cover"><img src="<?= e(upload_url($p['imagem'])) ?>" alt="<?= e($p['titulo']) ?>"></figure><?php endif; ?>
      <?php if ($p['conteudo']): ?>
        <div class="prose"><?= $p['conteudo'] ?></div>
      <?php else: ?>
        <div class="prose"><p><?= e($p['resumo']) ?></p></div>
      <?php endif; ?>
    </div>
    <aside class="detail__aside" aria-label="Resumo do projeto">
      <dl>
        <div><dt>Categoria</dt><dd><?= e($cl) ?></dd></div>
        <?php if ($p['publico']): ?><div><dt>Público</dt><dd><?= e($p['publico']) ?></dd></div><?php endif; ?>
        <div><dt>Investimento</dt><dd>Gratuito</dd></div>
      </dl>
      <?php if ($p['link_inscricao']): ?>
        <a class="ce-btn ce-btn--primary ce-btn--md ce-btn--block" href="<?= e($p['link_inscricao']) ?>" target="_blank" rel="noopener">Inscreva-se<?= icon('external-link', 18) ?></a>
      <?php else: ?>
        <a class="ce-btn ce-btn--primary ce-btn--md ce-btn--block" href="<?= url('/contato?assunto=' . urlencode('Projeto: ' . $p['titulo'])) ?>">Quero participar<?= icon('arrow-right', 18) ?></a>
      <?php endif; ?>
      <a class="ce-btn ce-btn--secondary ce-btn--md ce-btn--block" href="<?= url('/apoie') ?>"><?= icon('heart', 18) ?>Apoie este projeto</a>
    </aside>
  </div>
</section>

<?php if ($outros): ?>
<section class="ce-section ce-section--sunken">
  <div class="ce-container">
    <div class="ce-section-head"><div class="ce-section-head__text"><h2 class="ce-section-head__title">Outros projetos</h2></div>
      <a class="ce-link" href="<?= url('/projetos') ?>">Ver todos<?= icon('arrow-right', 16) ?></a></div>
    <ol class="rows">
      <?php foreach ($outros as $o): [$ol, $oc] = cat(CATEGORIAS_PROGRAMA, $o['categoria']); ?>
        <li><a class="row-link" href="<?= url('/projetos/' . $o['slug']) ?>">
          <span class="row-link__tag"><?= tag_html($ol, $oc) ?></span>
          <span class="row-link__main"><span class="row-link__title"><?= e($o['titulo']) ?></span><span class="row-link__text"><?= e($o['resumo']) ?></span></span>
          <span class="row-link__meta"><?php if ($o['publico']): ?><?= icon('users', 16) ?><?= e($o['publico']) ?><?php endif; ?></span>
          <span class="row-link__arrow" aria-hidden="true"><?= icon('arrow-up-right', 22) ?></span>
        </a></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>
<?php page_foot(); ?>
