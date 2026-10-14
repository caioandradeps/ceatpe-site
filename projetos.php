<?php
require __DIR__ . '/inc/layout.php';

$projetos = rows('SELECT * FROM projetos WHERE ativo = 1 ORDER BY ordem ASC, id DESC');
$contagem = [];
foreach ($projetos as $p) $contagem[$p['categoria']] = ($contagem[$p['categoria']] ?? 0) + 1;

page_head('Projetos e cursos', ['active' => 'projetos', 'description' => 'Cursos gratuitos e projetos do CEATPE em formação, pesquisa, extensão e inovação na Região Metropolitana do Recife.']);
page_hero('Projetos e cursos', 'Educação tecnológica acessível, gratuita e para todas as idades. Conheça as iniciativas do CEATPE e participe.');
?>
<section class="ce-section">
  <div class="ce-container">
    <?php if ($projetos): ?>
      <?php if (count($contagem) > 1): ?>
      <div class="filters" data-filter role="group" aria-label="Filtrar por categoria">
        <button class="chip" type="button" data-filter-btn="todos" aria-pressed="true">Todos <span class="chip__count"><?= count($projetos) ?></span></button>
        <?php foreach (CATEGORIAS_PROGRAMA as $k => [$label, $cor]): if (empty($contagem[$k])) continue; ?>
          <button class="chip" type="button" data-filter-btn="<?= e($k) ?>" aria-pressed="false"><span class="chip__dot" style="background: var(--accent-<?= $cor ?>)"></span><?= e($label) ?> <span class="chip__count"><?= $contagem[$k] ?></span></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <ol class="rows">
        <?php foreach ($projetos as $p): [$cl, $cc] = cat(CATEGORIAS_PROGRAMA, $p['categoria']); ?>
          <li data-cat="<?= e($p['categoria']) ?>" data-reveal>
            <a class="row-link" href="<?= url('/projetos/' . $p['slug']) ?>">
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
      <p class="empty" data-filter-empty hidden>Nenhum projeto nesta categoria no momento.</p>
    <?php else: ?>
      <div class="empty">
        <span class="empty__icon"><?= icon('graduation-cap', 26) ?></span>
        <h2>Novos projetos em breve</h2>
        <p>Estamos preparando as próximas turmas. Fale com a gente para saber quando as inscrições abrem.</p>
        <a class="ce-btn ce-btn--primary ce-btn--md" href="<?= url('/contato') ?>">Fale conosco</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="ce-section ce-section--sunken">
  <div class="ce-container cta-row">
    <div>
      <h2 class="ce-section-head__title">Quer levar um projeto para a sua comunidade?</h2>
      <p class="ce-section-head__desc">Escolas, associações e instituições podem propor parcerias para receber cursos e oficinas do CEATPE.</p>
    </div>
    <a class="ce-btn ce-btn--primary ce-btn--lg" href="<?= url('/contato?assunto=parcerias') ?>">Proponha uma parceria<?= icon('arrow-right', 20) ?></a>
  </div>
</section>
<?php page_foot(); ?>
