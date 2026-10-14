<?php
require __DIR__ . '/inc/layout.php';

$hoje = date('Y-m-d');
$cat = $_GET['categoria'] ?? '';
if (!isset(CATEGORIAS_BLOG[$cat])) $cat = '';
$porPagina = 9;
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));

$where = 'publicado = 1 AND data <= ?'; $params = [$hoje];
if ($cat) { $where .= ' AND categoria = ?'; $params[] = $cat; }
$total = (int) row("SELECT COUNT(*) AS n FROM posts WHERE $where", $params)['n'];
$paginas = max(1, (int) ceil($total / $porPagina));
$pagina = min($pagina, $paginas);
$offset = ($pagina - 1) * $porPagina;
$posts = rows("SELECT * FROM posts WHERE $where ORDER BY data DESC, id DESC LIMIT $porPagina OFFSET $offset", $params);

$contagem = [];
foreach (rows('SELECT categoria, COUNT(*) AS n FROM posts WHERE publicado = 1 AND data <= ? GROUP BY categoria', [$hoje]) as $r) $contagem[$r['categoria']] = (int) $r['n'];
$totalGeral = array_sum($contagem);

$destaque = (!$cat && $pagina === 1 && $posts) ? array_shift($posts) : null;
$link = fn(string $c = '', int $p = 1) => url('/blog') . (($q = http_build_query(array_filter(['categoria' => $c, 'pagina' => $p > 1 ? $p : null]))) ? "?$q" : '');

page_head($cat ? CATEGORIAS_BLOG[$cat][0] . ' — Blog' : 'Blog', ['active' => 'blog', 'description' => 'Notícias, artigos e novidades do CEATPE sobre ciência, inovação, educação e comunidade.']);
page_hero('Blog do CEATPE', 'Notícias, bastidores dos projetos e conteúdos sobre ciência, inovação, educação e comunidade.');
?>
<section class="ce-section">
  <div class="ce-container">
    <?php if ($totalGeral): ?>
    <nav class="filters" aria-label="Categorias do blog">
      <a class="chip" href="<?= $link() ?>"<?= !$cat ? ' aria-current="true"' : '' ?>>Todas <span class="chip__count"><?= $totalGeral ?></span></a>
      <?php foreach (CATEGORIAS_BLOG as $k => [$label, $cor]): ?>
        <a class="chip" href="<?= $link($k) ?>"<?= $cat === $k ? ' aria-current="true"' : '' ?>><span class="chip__dot" style="background: var(--accent-<?= $cor ?>)"></span><?= e($label) ?> <span class="chip__count"><?= $contagem[$k] ?? 0 ?></span></a>
      <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <?php if ($destaque): $post = $destaque; [$cl, $cc] = cat(CATEGORIAS_BLOG, $post['categoria']); ?>
      <a class="featured" href="<?= url('/blog/' . $post['slug']) ?>">
        <?php if ($post['capa']): ?><div class="featured__media" style="background-image:url('<?= e(upload_url($post['capa'])) ?>')" role="img" aria-label="<?= e($post['titulo']) ?>"></div>
        <?php else: ?><div class="featured__media post-card__placeholder post-card__placeholder--<?= e($cc) ?>" aria-hidden="true"><img src="<?= asset('img/ceatpe-simbolo.png') ?>" alt="" width="120" height="120"></div><?php endif; ?>
        <div class="featured__body">
          <p class="post-card__meta"><?= tag_html($cl, $cc) ?><time datetime="<?= e($post['data']) ?>"><?= e(data_longa($post['data'])) ?></time></p>
          <h2 class="featured__title"><?= e($post['titulo']) ?></h2>
          <p class="featured__text"><?= e($post['resumo']) ?></p>
          <span class="ce-link">Ler publicação<?= icon('arrow-right', 16) ?></span>
        </div>
      </a>
    <?php endif; ?>

    <?php if ($posts): ?>
      <div class="ce-grid posts-grid">
        <?php foreach ($posts as $post) include __DIR__ . '/inc/partials/post-card.php'; ?>
      </div>
    <?php elseif (!$destaque): ?>
      <div class="empty">
        <span class="empty__icon"><?= icon('newspaper', 26) ?></span>
        <h2><?= $cat ? 'Ainda não há publicações nesta categoria' : 'As primeiras publicações chegam em breve' ?></h2>
        <p>Aqui vamos contar o que acontece nos projetos, eventos e campanhas do CEATPE.</p>
        <?php if ($cat): ?><a class="ce-btn ce-btn--secondary ce-btn--md" href="<?= $link() ?>">Ver todas as publicações</a><?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($paginas > 1): ?>
      <nav class="pager" aria-label="Páginas">
        <?php if ($pagina > 1): ?><a href="<?= $link($cat, $pagina - 1) ?>" aria-label="Página anterior"><?= icon('chevron-left', 18) ?></a><?php endif; ?>
        <?php for ($i = 1; $i <= $paginas; $i++): ?>
          <?php if ($i === $pagina): ?><span aria-current="page"><?= $i ?></span><?php else: ?><a href="<?= $link($cat, $i) ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $paginas): ?><a href="<?= $link($cat, $pagina + 1) ?>" aria-label="Próxima página"><?= icon('chevron-right', 18) ?></a><?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
<?php page_foot(); ?>
