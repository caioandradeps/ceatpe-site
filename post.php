<?php
require __DIR__ . '/inc/layout.php';

$post = row('SELECT * FROM posts WHERE slug = ? AND publicado = 1 AND data <= ?', [$_GET['slug'] ?? '', date('Y-m-d')]);
if (!$post) not_found();
[$cl, $cc] = cat(CATEGORIAS_BLOG, $post['categoria']);
$relacionados = rows('SELECT * FROM posts WHERE publicado = 1 AND data <= ? AND id <> ? ORDER BY (categoria = ?) DESC, data DESC LIMIT 3', [date('Y-m-d'), $post['id'], $post['categoria']]);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$abs = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '') . url('/blog/' . $post['slug']);
$meta = '<div class="article__meta"><span>' . icon('calendar', 16) . e(data_longa($post['data'])) . '</span><span>' . icon('clock', 16) . read_minutes($post['conteudo']) . ' min de leitura</span>'
      . ($post['autor'] ? '<span>' . icon('user', 16) . e($post['autor']) . '</span>' : '') . '</div>';
$ld = ['@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $post['titulo'], 'datePublished' => $post['data'], 'description' => $post['resumo'],
       'publisher' => ['@type' => 'NGO', 'name' => 'CEATPE'], 'mainEntityOfPage' => $abs];
if ($post['autor']) $ld['author'] = ['@type' => 'Person', 'name' => $post['autor']];

page_head($post['titulo'], ['active' => 'blog', 'description' => $post['resumo'], 'og_type' => 'article', 'image' => $post['capa'] ? upload_url($post['capa']) : null, 'jsonld' => $ld]);
page_hero($post['titulo'], '', ['crumb' => ['Blog', '/blog'], 'before' => tag_html($cl, $cc), 'after' => $meta, 'class' => 'page-hero--article']);
?>
<section class="ce-section">
  <article class="article">
    <?php if ($post['capa']): ?><figure class="article__cover"><img src="<?= e(upload_url($post['capa'])) ?>" alt="<?= e($post['titulo']) ?>"></figure><?php endif; ?>
    <p class="lead article__lead"><?= e($post['resumo']) ?></p>
    <div class="prose"><?= $post['conteudo'] ?></div>
    <div class="share">
      <span class="share__label">Compartilhe</span>
      <a class="ce-btn ce-btn--secondary ce-btn--sm" href="https://wa.me/?text=<?= rawurlencode($post['titulo'] . ' ' . $abs) ?>" target="_blank" rel="noopener"><?= icon('message-circle', 16) ?>WhatsApp</a>
      <a class="ce-btn ce-btn--secondary ce-btn--sm" href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($abs) ?>" target="_blank" rel="noopener"><?= icon('facebook', 16) ?>Facebook</a>
      <a class="ce-btn ce-btn--secondary ce-btn--sm" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($abs) ?>" target="_blank" rel="noopener"><?= icon('linkedin', 16) ?>LinkedIn</a>
      <button class="ce-btn ce-btn--ghost ce-btn--sm" type="button" data-copy data-copy-text="<?= e($abs) ?>"><?= icon('copy', 16) ?><span data-copy-label>Copiar link</span></button>
    </div>
  </article>
</section>

<?php if ($relacionados): ?>
<section class="ce-section ce-section--sunken">
  <div class="ce-container">
    <div class="ce-section-head"><div class="ce-section-head__text"><h2 class="ce-section-head__title">Continue lendo</h2></div>
      <a class="ce-link" href="<?= url('/blog') ?>">Ver todas<?= icon('arrow-right', 16) ?></a></div>
    <div class="ce-grid posts-grid">
      <?php foreach ($relacionados as $post) include __DIR__ . '/inc/partials/post-card.php'; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php page_foot(); ?>
