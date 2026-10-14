<?php
defined('ROOT') or exit;

function adm_link(array $q = []): string { return url('admin/') . ($q ? '?' . http_build_query($q) : ''); }

function adm_head(string $title, string $sub = '', string $actions = '', string $active = ''): void {
    global $user, $naoLidas;
    $mods = modules();
    $flash = flash();
    ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> — Painel CEATPE</title>
<link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
<link rel="stylesheet" href="<?= asset('css/tokens.css') ?>">
<link rel="stylesheet" href="<?= asset('css/components.css') ?>">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="ce-root adm">
<div class="adm-shell">
  <aside class="adm-side" id="adm-side" data-side>
    <a class="adm-side__brand" href="<?= adm_link() ?>"><img src="<?= asset('img/ceatpe-horizontal.png') ?>" alt="CEATPE"><span>Painel</span></a>
    <?= stripe() ?>
    <nav class="adm-nav" aria-label="Painel">
      <a href="<?= adm_link() ?>"<?= $active === 'inicio' ? ' aria-current="page"' : '' ?>><?= icon('layout-dashboard', 20) ?>Início</a>
      <a href="<?= adm_link(['p' => 'mensagens']) ?>"<?= $active === 'mensagens' ? ' aria-current="page"' : '' ?>><?= icon('inbox', 20) ?>Mensagens<?php if ($naoLidas): ?><span class="adm-nav__count" aria-label="<?= $naoLidas ?> não lidas"><?= $naoLidas ?></span><?php endif; ?></a>
      <p class="adm-nav__group">Conteúdo</p>
      <?php foreach ($mods as $k => $m): ?>
        <a href="<?= adm_link(['p' => 'mod', 'm' => $k]) ?>"<?= $active === $k ? ' aria-current="page"' : '' ?>><?= icon($m['icon'], 20) ?><?= e($m['label']) ?></a>
      <?php endforeach; ?>
      <p class="adm-nav__group">Ajustes</p>
      <a href="<?= adm_link(['p' => 'config']) ?>"<?= $active === 'config' ? ' aria-current="page"' : '' ?>><?= icon('settings', 20) ?>Informações do site</a>
      <a href="<?= adm_link(['p' => 'conta']) ?>"<?= $active === 'conta' ? ' aria-current="page"' : '' ?>><?= icon('lock', 20) ?>Acessos e senha</a>
    </nav>
    <div class="adm-side__foot">
      <div class="adm-user">
        <span class="adm-user__avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($user['nome'] ?: $user['email'], 0, 1))) ?></span>
        <span style="min-width:0"><span class="adm-user__name" style="display:block"><?= e($user['nome'] ?: 'Administração') ?></span><span class="adm-user__mail" style="display:block"><?= e($user['email']) ?></span></span>
      </div>
      <form method="post" action="<?= adm_link(['p' => 'sair']) ?>"><?= csrf_field() ?><button class="ce-btn ce-btn--ghost ce-btn--sm" type="submit"><?= icon('log-out', 16) ?>Sair</button></form>
    </div>
  </aside>
  <div class="adm-main">
    <header class="adm-top">
      <div class="adm-top__inner">
        <div style="display:flex;align-items:center;gap:12px;min-width:0">
          <button class="ce-iconbtn ce-iconbtn--outline ce-iconbtn--md adm-menu-btn" type="button" data-side-toggle aria-controls="adm-side" aria-expanded="false"><span class="ce-sr">Abrir menu</span><?= icon('menu', 20) ?></button>
          <div style="min-width:0"><h1><?= e($title) ?></h1><?php if ($sub): ?><p class="adm-top__sub"><?= e($sub) ?></p><?php endif; ?></div>
        </div>
        <div class="adm-top__actions"><?= $actions ?><a class="ce-btn ce-btn--secondary ce-btn--sm" href="<?= url('/') ?>" target="_blank" rel="noopener"><?= icon('external-link', 16) ?><span class="hide-sm">Ver site</span></a></div>
      </div>
    </header>
    <main class="adm-body" id="conteudo">
      <?php if ($flash): ?><div class="adm-alert adm-alert--<?= e($flash[1]) ?>" role="status" data-flash><?= icon($flash[1] === 'success' ? 'circle-check' : 'info', 18) ?><span><?= e($flash[0]) ?></span></div><?php endif; ?>
<?php
}

function adm_foot(string $extra = ''): void { ?>
    </main>
  </div>
</div>
<dialog class="ce-dialog" id="confirmar" aria-labelledby="confirmar-titulo">
  <h2 class="ce-dialog__title" id="confirmar-titulo">Excluir?</h2>
  <div class="ce-dialog__body" data-confirm-text>Essa ação não pode ser desfeita.</div>
  <div class="ce-dialog__actions">
    <button class="ce-btn ce-btn--secondary ce-btn--md" type="button" data-confirm-cancel>Cancelar</button>
    <button class="ce-btn ce-btn--danger ce-btn--md" type="button" data-confirm-ok>Excluir</button>
  </div>
</dialog>
<script src="<?= asset('js/admin.js') ?>" defer></script>
<?= $extra ?>
</body>
</html>
<?php }

function view_login(string $erro): void { ?><!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title>Entrar — Painel CEATPE</title><link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
<link rel="stylesheet" href="<?= asset('css/tokens.css') ?>"><link rel="stylesheet" href="<?= asset('css/components.css') ?>"><link rel="stylesheet" href="<?= asset('css/admin.css') ?>"></head>
<body class="ce-root adm-auth">
<main class="adm-auth__card">
  <?= stripe() ?>
  <img class="adm-auth__logo" src="<?= asset('img/ceatpe-horizontal.png') ?>" alt="CEATPE">
  <h1>Painel administrativo</h1>
  <p class="adm-muted">Área restrita da equipe do CEATPE.</p>
  <?php if ($erro): ?><div class="adm-alert adm-alert--danger" role="alert"><?= icon('circle-alert', 18) ?><span><?= e($erro) ?></span></div><?php endif; ?>
  <form method="post" class="adm-form">
    <?= csrf_field() ?>
    <label class="ce-field"><span class="ce-field__label">E-mail</span><input class="ce-control" type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" autocomplete="username" required autofocus></label>
    <label class="ce-field"><span class="ce-field__label">Senha</span><input class="ce-control" type="password" name="senha" autocomplete="current-password" required></label>
    <button class="ce-btn ce-btn--primary ce-btn--md ce-btn--block" type="submit">Entrar</button>
  </form>
  <a class="adm-auth__back" href="<?= url('/') ?>"><?= icon('arrow-left', 16) ?>Voltar ao site</a>
</main>
</body></html>
<?php }

/* ---------- Início ---------- */
function view_dashboard(array $c, array $msgs, array $eventos, array $pend): void {
    global $naoLidas;
    adm_head('Olá! O que vamos atualizar hoje?', 'Tudo o que você muda aqui aparece no site na mesma hora.', '', 'inicio');
    $mods = modules();
    $tones = ['banners' => 'blue', 'projetos' => 'blue', 'eventos' => 'gold', 'posts' => 'red', 'campanhas' => 'gold', 'parceiros' => 'green', 'equipe' => 'green'];
    ?>
    <div class="adm-stats">
      <a class="adm-card adm-stat" href="<?= adm_link(['p' => 'mensagens']) ?>">
        <span class="adm-stat__top">Mensagens novas<span class="adm-stat__icon area__icon--red"><?= icon('inbox', 18) ?></span></span>
        <span class="adm-stat__n"><?= $naoLidas ?></span>
      </a>
      <?php foreach (['posts', 'eventos', 'projetos', 'parceiros'] as $k): ?>
        <a class="adm-card adm-stat" href="<?= adm_link(['p' => 'mod', 'm' => $k]) ?>">
          <span class="adm-stat__top"><?= e($mods[$k]['label']) ?><span class="adm-stat__icon area__icon--<?= $tones[$k] ?>"><?= icon($mods[$k]['icon'], 18) ?></span></span>
          <span class="adm-stat__n"><?= $c[$k] ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="adm-grid2">
      <div style="display:flex;flex-direction:column;gap:24px">
        <section class="adm-card">
          <div class="adm-card__head"><h2>Últimas mensagens</h2><a class="ce-link" href="<?= adm_link(['p' => 'mensagens']) ?>">Ver todas<?= icon('arrow-right', 16) ?></a></div>
          <?php if ($msgs): ?>
            <div class="adm-table-wrap"><table class="adm-table"><tbody>
              <?php foreach ($msgs as $m): ?>
                <tr class="<?= (int) $m['lida'] ? '' : 'adm-row--unread' ?>">
                  <td><a class="adm-table__title" href="<?= adm_link(['p' => 'mensagens', 'id' => $m['id']]) ?>"><?= e($m['nome']) ?></a><div class="adm-muted" style="font-size:14px"><?= e($m['assunto']) ?></div></td>
                  <td class="num adm-muted" style="text-align:right"><?= e(date('d/m H:i', strtotime((string) $m['criado_em']))) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody></table></div>
          <?php else: ?>
            <div class="adm-card__body adm-muted">Nenhuma mensagem ainda. As mensagens do formulário de contato chegam aqui.</div>
          <?php endif; ?>
        </section>
        <section class="adm-card">
          <div class="adm-card__head"><h2>Próximos eventos</h2><a class="ce-link" href="<?= adm_link(['p' => 'mod', 'm' => 'eventos', 'a' => 'editar']) ?>"><?= icon('plus', 16) ?>Novo evento</a></div>
          <?php if ($eventos): ?>
            <div class="adm-table-wrap"><table class="adm-table"><tbody>
              <?php foreach ($eventos as $ev): ?>
                <tr><td><a class="adm-table__title" href="<?= adm_link(['p' => 'mod', 'm' => 'eventos', 'a' => 'editar', 'id' => $ev['id']]) ?>"><?= e($ev['titulo']) ?></a></td><td class="num adm-muted" style="text-align:right"><?= e(date('d/m/Y', strtotime((string) $ev['data']))) ?></td></tr>
              <?php endforeach; ?>
            </tbody></table></div>
          <?php else: ?>
            <div class="adm-card__body adm-muted">Nenhum evento futuro cadastrado. A seção de agenda da página inicial fica escondida até haver um.</div>
          <?php endif; ?>
        </section>
      </div>
      <div style="display:flex;flex-direction:column;gap:24px">
        <section class="adm-card">
          <div class="adm-card__head"><h2>Atalhos</h2></div>
          <div class="adm-card__body adm-quick">
            <a class="ce-btn ce-btn--primary ce-btn--md" href="<?= adm_link(['p' => 'mod', 'm' => 'posts', 'a' => 'editar']) ?>"><?= icon('plus', 18) ?>Nova publicação no blog</a>
            <a class="ce-btn ce-btn--secondary ce-btn--md" href="<?= adm_link(['p' => 'mod', 'm' => 'eventos', 'a' => 'editar']) ?>"><?= icon('calendar', 18) ?>Novo evento</a>
            <a class="ce-btn ce-btn--secondary ce-btn--md" href="<?= adm_link(['p' => 'mod', 'm' => 'parceiros', 'a' => 'editar']) ?>"><?= icon('handshake', 18) ?>Novo parceiro</a>
            <a class="ce-btn ce-btn--secondary ce-btn--md" href="<?= adm_link(['p' => 'mod', 'm' => 'banners']) ?>"><?= icon('panels-top-left', 18) ?>Editar banners do topo</a>
          </div>
        </section>
        <?php if ($pend): ?>
        <section class="adm-card">
          <div class="adm-card__head"><h2>Falta preencher</h2></div>
          <div class="adm-card__body">
            <p class="adm-muted" style="margin:0 0 16px;font-size:14px">Essas informações ficam escondidas no site até serem preenchidas.</p>
            <ul class="checklist checklist--warn" style="margin:0 0 20px">
              <?php foreach ($pend as $l): ?><li><?= icon('circle-alert', 18) ?><span><?= e($l) ?></span></li><?php endforeach; ?>
            </ul>
            <a class="ce-btn ce-btn--secondary ce-btn--sm" href="<?= adm_link(['p' => 'config']) ?>">Preencher informações<?= icon('arrow-right', 16) ?></a>
          </div>
        </section>
        <?php endif; ?>
      </div>
    </div>
    <?php adm_foot();
}

/* ---------- Lista de um módulo ---------- */
function list_cell(string $k, array $f, array $r, string $m): string {
    [$label, $type] = $f; $o = $f[2] ?? [];
    $v = $r[$k] ?? '';
    return match ($type) {
        'bool' => (int) $v ? '<span class="ce-badge ce-badge--success"><span class="ce-badge__dot"></span>' . ($k === 'destaque' ? 'Destaque' : ($k === 'publicado' ? 'Publicado' : 'Visível')) . '</span>'
                           : '<span class="ce-badge ce-badge--neutral">' . ($k === 'destaque' ? '—' : ($k === 'publicado' ? 'Rascunho' : 'Oculto')) . '</span>',
        'date' => $v ? e(date('d/m/Y', strtotime((string) $v))) : '—',
        'select' => $m === 'banners' && $k === 'cor'
            ? '<span class="ce-tag ce-tag--' . e($v) . '">' . e($o['options'][$v] ?? $v) . '</span>'
            : tag_html($o['options'][$v] ?? (string) $v, (CATEGORIAS_PROGRAMA[$v] ?? CATEGORIAS_BLOG[$v] ?? ['', 'blue'])[1]),
        'image' => $v ? '<span class="adm-table__thumb" style="background-image:url(\'' . e(upload_url($v)) . '\')"></span>' : '<span class="adm-muted">Sem imagem</span>',
        default => e(mb_strimwidth((string) $v, 0, 60, '…')),
    };
}

function view_list(string $m, array $mod, array $rows, string $busca): void {
    $novo = adm_link(['p' => 'mod', 'm' => $m, 'a' => 'editar']);
    adm_head($mod['label'], $mod['help'], '<a class="ce-btn ce-btn--primary ce-btn--sm" href="' . $novo . '">' . icon('plus', 16) . '<span>Novo ' . e($mod['singular']) . '</span></a>', $m);
    $cols = $mod['list'];
    $title = array_shift($cols);
    $img = null;
    foreach ($mod['fields'] as $k => $f) if ($f[1] === 'image') { $img = $k; break; }
    $vis = isset($mod['fields']['publicado']) ? 'publicado' : (isset($mod['fields']['ativo']) ? 'ativo' : null);
    ?>
    <form class="adm-toolbar" method="get" action="<?= url('admin/') ?>" role="search">
      <input type="hidden" name="p" value="mod"><input type="hidden" name="m" value="<?= e($m) ?>">
      <label class="adm-search"><span class="ce-sr">Buscar</span><?= icon('search', 18) ?><input class="ce-control" type="search" name="q" value="<?= e($busca) ?>" placeholder="Buscar por <?= e(mb_strtolower($mod['fields'][$title][0])) ?>"></label>
      <?php if ($busca): ?><a class="ce-btn ce-btn--ghost ce-btn--sm" href="<?= adm_link(['p' => 'mod', 'm' => $m]) ?>">Limpar busca</a><?php endif; ?>
      <span class="adm-muted" style="margin-left:auto;font-size:14px"><?= count($rows) ?> <?= count($rows) === 1 ? 'item' : 'itens' ?></span>
    </form>
    <div class="adm-card">
      <?php if ($rows): ?>
      <div class="adm-table-wrap"><table class="adm-table">
        <thead><tr><th><?= e($mod['fields'][$title][0]) ?></th><?php foreach ($cols as $k): ?><th class="<?= in_array($k, ['logo'], true) ? '' : 'hide-sm' ?>"><?= e($mod['fields'][$k][0]) ?></th><?php endforeach; ?><th><span class="ce-sr">Ações</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $edit = adm_link(['p' => 'mod', 'm' => $m, 'a' => 'editar', 'id' => $r['id']]); ?>
          <tr>
            <td><a class="adm-table__title" href="<?= $edit ?>"><?php if ($img && !in_array($img, $cols, true)): ?><span class="adm-table__thumb" style="<?= $r[$img] ? "background-image:url('" . e(upload_url($r[$img])) . "')" : '' ?>"></span><?php endif; ?><?= e($r[$title]) ?></a></td>
            <?php foreach ($cols as $k): ?><td class="<?= $mod['fields'][$k][1] === 'date' ? 'num ' : '' ?><?= in_array($k, ['logo'], true) ? '' : 'hide-sm' ?>"><?= list_cell($k, $mod['fields'][$k], $r, $m) ?></td><?php endforeach; ?>
            <td><div class="adm-table__actions">
              <?php if (!empty($mod['slug']) && in_array($m, ['projetos', 'eventos', 'posts'], true)): $pub = ['projetos' => '/projetos/', 'eventos' => '/eventos/', 'posts' => '/blog/'][$m]; ?>
                <a class="ce-iconbtn ce-iconbtn--ghost ce-iconbtn--sm" href="<?= url($pub . $r['slug']) ?>" target="_blank" rel="noopener" title="Ver no site"><?= icon('eye', 18) ?><span class="ce-sr">Ver no site</span></a>
              <?php endif; ?>
              <?php if ($vis): ?>
                <form method="post" action="<?= adm_link(['p' => 'mod', 'm' => $m, 'a' => 'visivel', 'id' => $r['id']]) ?>"><?= csrf_field() ?><button class="ce-iconbtn ce-iconbtn--ghost ce-iconbtn--sm" type="submit" title="<?= (int) $r[$vis] ? 'Ocultar do site' : 'Mostrar no site' ?>"><?= icon((int) $r[$vis] ? 'eye-off' : 'eye', 18) ?><span class="ce-sr"><?= (int) $r[$vis] ? 'Ocultar do site' : 'Mostrar no site' ?></span></button></form>
              <?php endif; ?>
              <a class="ce-iconbtn ce-iconbtn--ghost ce-iconbtn--sm" href="<?= $edit ?>" title="Editar"><?= icon('pencil', 18) ?><span class="ce-sr">Editar</span></a>
              <form method="post" action="<?= adm_link(['p' => 'mod', 'm' => $m, 'a' => 'excluir', 'id' => $r['id']]) ?>" data-confirm="Excluir “<?= e($r[$title]) ?>”? Essa ação não pode ser desfeita."><?= csrf_field() ?><button class="ce-iconbtn ce-iconbtn--ghost ce-iconbtn--sm" type="submit" title="Excluir"><?= icon('trash-2', 18) ?><span class="ce-sr">Excluir</span></button></form>
            </div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php else: ?>
        <div class="adm-empty">
          <span class="empty__icon"><?= icon($mod['icon'], 26) ?></span>
          <h2><?= $busca ? 'Nada encontrado' : 'Nenhum ' . e($mod['singular']) . ' ainda' ?></h2>
          <p><?= $busca ? 'Tente outra palavra.' : e($mod['help']) ?></p>
          <?php if (!$busca): ?><a class="ce-btn ce-btn--primary ce-btn--md" href="<?= $novo ?>"><?= icon('plus', 18) ?>Criar o primeiro</a><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php adm_foot();
}

/* ---------- Campo de formulário ---------- */
function field_html(string $k, array $f, $v, ?string $err): string {
    [$label, $type] = $f; $o = $f[2] ?? [];
    $req = !empty($o['required']) ? '<span class="ce-field__req" aria-hidden="true">*</span>' : '';
    $inv = $err ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
    $help = !empty($o['help']) ? '<span class="ce-field__help">' . e($o['help']) . '</span>' : '';
    $error = $err ? '<span class="ce-field__error" id="err-' . $k . '">' . icon('circle-alert', 14) . e($err) . '</span>' : '';
    $max = !empty($o['max']) ? ' data-max="' . (int) $o['max'] . '"' : '';
    $counter = !empty($o['max']) ? '<span class="adm-counter" data-counter-for="f-' . $k . '"></span>' : '';
    $lab = '<span class="adm-field__top"><span class="ce-field__label">' . e($label) . $req . '</span>' . $counter . '</span>';
    $val = e((string) ($v ?? ''));
    switch ($type) {
        case 'textarea':
            return '<label class="ce-field">' . $lab . '<textarea class="ce-control" id="f-' . $k . '" name="' . $k . '" rows="4"' . $max . $inv . '>' . $val . '</textarea>' . $help . $error . '</label>';
        case 'rich':
            return '<div class="ce-field"><span class="ce-field__label" id="lbl-' . $k . '">' . e($label) . $req . '</span><div class="adm-rich"><div data-rich="' . $k . '" aria-labelledby="lbl-' . $k . '">' . (string) $v . '</div></div><input type="hidden" name="' . $k . '" value="' . $val . '">' . $help . $error . '</div>';
        case 'select':
            $opts = '';
            foreach ($o['options'] as $ok => $ol) $opts .= '<option value="' . e((string) $ok) . '"' . ((string) $v === (string) $ok ? ' selected' : '') . '>' . e($ol) . '</option>';
            return '<label class="ce-field">' . $lab . '<span class="ce-select"><select class="ce-control" name="' . $k . '"' . $inv . '>' . $opts . '</select><span class="ce-select__chev">' . icon('chevron-down', 18) . '</span></span>' . $help . $error . '</label>';
        case 'bool':
            return '<div class="adm-switchrow"><span class="adm-switchrow__text" id="lbl-' . $k . '">' . e($label) . '</span><label class="ce-check"><input type="checkbox" name="' . $k . '" value="1"' . ((int) $v ? ' checked' : '') . ' aria-labelledby="lbl-' . $k . '"><span class="ce-switch__track"><span class="ce-switch__thumb"></span></span></label></div>';
        case 'image':
            $bg = $v ? ' style="background-image:url(\'' . e(upload_url((string) $v)) . '\')"' : '';
            return '<div class="ce-field" data-image><span class="ce-field__label">' . e($label) . $req . '</span><div class="adm-image"><div class="adm-image__preview" data-preview' . $bg . '>' . ($v ? '' : icon('image', 28)) . '</div><div class="adm-image__controls">'
                . '<span class="ce-btn ce-btn--secondary ce-btn--sm adm-file">' . icon('upload', 16) . ($v ? 'Trocar imagem' : 'Enviar imagem') . '<input type="file" name="' . $k . '" accept="image/jpeg,image/png,image/webp" data-file></span>'
                . '<span class="adm-image__name" data-filename></span>'
                . ($v ? '<label class="ce-check" style="font-size:14px"><input type="checkbox" name="' . $k . '__remover" value="1"><span class="ce-check__box">' . icon('check', 14) . '</span>Remover imagem</label>' : '')
                . '</div></div>' . $help . $error . '</div>';
        case 'date':
            return '<label class="ce-field">' . $lab . '<input class="ce-control" type="date" name="' . $k . '" value="' . $val . '"' . $inv . '>' . $help . $error . '</label>';
        case 'int':
            return '<label class="ce-field">' . $lab . '<input class="ce-control" type="number" name="' . $k . '" value="' . $val . '" inputmode="numeric" style="max-width:140px">' . $help . $error . '</label>';
        case 'url':
            return '<label class="ce-field">' . $lab . '<input class="ce-control" type="url" name="' . $k . '" value="' . $val . '" placeholder="https://"' . $inv . '>' . $help . $error . '</label>';
        default:
            return '<label class="ce-field">' . $lab . '<input class="ce-control" id="f-' . $k . '" name="' . $k . '" value="' . $val . '"' . $max . $inv . '>' . $help . $error . '</label>';
    }
}

function view_edit(string $m, array $mod, ?array $item, array $d, array $erros): void {
    $titulo = $item ? 'Editar ' . $mod['singular'] : 'Novo ' . $mod['singular'];
    if (in_array($mod['singular'], ['publicação', 'campanha', 'pessoa'], true)) $titulo = $item ? 'Editar ' . $mod['singular'] : 'Nova ' . $mod['singular'];
    adm_head($titulo, $item ? (string) ($item[array_key_first($mod['fields'])] ?? '') : $mod['help'], '<a class="ce-btn ce-btn--ghost ce-btn--sm" href="' . adm_link(['p' => 'mod', 'm' => $m]) . '">' . icon('arrow-left', 16) . '<span class="hide-sm">Voltar</span></a>', $m);
    $side = ['select', 'bool', 'int', 'date'];
    $main = $aside = [];
    foreach ($mod['fields'] as $k => $f) {
        if (in_array($f[1], $side, true) && !($m === 'eventos' && $k === 'data')) $aside[$k] = $f; else $main[$k] = $f;
    }
    if ($m === 'eventos') { $aside = ['data' => $mod['fields']['data']] + $aside; unset($main['data']); }
    $pubBase = ['projetos' => '/projetos/', 'eventos' => '/eventos/', 'posts' => '/blog/'][$m] ?? null;
    ?>
    <?php if ($erros): ?><div class="adm-alert adm-alert--danger" role="alert"><?= icon('circle-alert', 18) ?><span><?= e($erros['_'] ?? 'Não foi possível salvar. Revise os campos destacados.') ?></span></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="adm-edit" data-editform>
      <?= csrf_field() ?>
      <div class="adm-card"><div class="adm-card__body adm-fields">
        <?php foreach ($main as $k => $f) echo field_html($k, $f, $d[$k] ?? '', $erros[$k] ?? null); ?>
        <?php if (!empty($mod['slug'])): ?>
          <label class="ce-field"><span class="ce-field__label">Endereço da página</span>
            <input class="ce-control" name="slug" value="<?= e($d['slug'] ?? '') ?>" placeholder="Criado automaticamente a partir do título">
            <span class="ce-field__help"><?= e(url($pubBase ?? '/')) ?><strong data-slug-preview><?= e($d['slug'] ?? '') ?></strong></span></label>
        <?php endif; ?>
      </div></div>
      <div class="adm-edit__side">
        <div class="adm-card"><div class="adm-card__body adm-fields">
          <?php foreach ($aside as $k => $f) echo field_html($k, $f, $d[$k] ?? '', $erros[$k] ?? null); ?>
          <div class="adm-actions" style="flex-direction:column">
            <button class="ce-btn ce-btn--primary ce-btn--md ce-btn--block" type="submit"><?= icon('check', 18) ?>Salvar</button>
            <button class="ce-btn ce-btn--secondary ce-btn--md ce-btn--block" type="submit" name="_novo" value="1">Salvar e criar outro</button>
            <?php if ($item && $pubBase && !empty($item['slug'])): ?><a class="ce-btn ce-btn--ghost ce-btn--md ce-btn--block" href="<?= url($pubBase . $item['slug']) ?>" target="_blank" rel="noopener"><?= icon('external-link', 18) ?>Ver no site</a><?php endif; ?>
          </div>
        </div></div>
        <?php if ($item): ?>
          <p class="adm-muted" style="font-size:13px;margin:0">Criado em <?= e(date('d/m/Y H:i', strtotime((string) $item['criado_em']))) ?><?= $item['atualizado_em'] ? ' · atualizado em ' . e(date('d/m/Y H:i', strtotime((string) $item['atualizado_em']))) : '' ?></p>
        <?php endif; ?>
      </div>
    </form>
    <?php if ($item): ?>
      <form method="post" action="<?= adm_link(['p' => 'mod', 'm' => $m, 'a' => 'excluir', 'id' => $item['id']]) ?>" data-confirm="Excluir este <?= e($mod['singular']) ?>? Essa ação não pode ser desfeita." class="adm-danger-zone" style="margin-top:24px">
        <?= csrf_field() ?><button class="ce-btn ce-btn--ghost ce-btn--sm" type="submit" style="color:var(--status-danger-text)"><?= icon('trash-2', 16) ?>Excluir <?= e($mod['singular']) ?></button>
      </form>
    <?php endif; ?>
    <?php
    $rich = false; foreach ($mod['fields'] as $f) if ($f[1] === 'rich') $rich = true;
    adm_foot($rich ? '<link rel="stylesheet" href="' . asset('vendor/quill/quill.snow.css') . '"><script src="' . asset('vendor/quill/quill.js') . '"></script><script>window.CE_UPLOAD = ' . json_encode(['url' => adm_link(['p' => 'upload']), 'csrf' => csrf()]) . ';</script>' : '');
}

/* ---------- Mensagens ---------- */
function view_messages(array $rows, string $filtro): void {
    global $naoLidas;
    $acoes = '<a class="ce-btn ce-btn--secondary ce-btn--sm" href="' . adm_link(['p' => 'mensagens', 'a' => 'csv']) . '">' . icon('file-text', 16) . '<span class="hide-sm">Exportar planilha</span></a>';
    adm_head('Mensagens', 'Tudo o que chega pelo formulário de contato do site.', $acoes, 'mensagens');
    ?>
    <div class="adm-toolbar">
      <div class="filters" style="margin:0">
        <a class="chip" href="<?= adm_link(['p' => 'mensagens']) ?>"<?= $filtro !== 'novas' ? ' aria-current="true"' : '' ?>>Todas</a>
        <a class="chip" href="<?= adm_link(['p' => 'mensagens', 'f' => 'novas']) ?>"<?= $filtro === 'novas' ? ' aria-current="true"' : '' ?>>Não lidas <span class="chip__count"><?= $naoLidas ?></span></a>
      </div>
      <?php if ($naoLidas): ?><form method="post" style="margin-left:auto"><?= csrf_field() ?><input type="hidden" name="acao" value="todas_lidas"><button class="ce-btn ce-btn--ghost ce-btn--sm" type="submit"><?= icon('check', 16) ?>Marcar todas como lidas</button></form><?php endif; ?>
    </div>
    <div class="adm-card">
      <?php if ($rows): ?>
      <div class="adm-table-wrap"><table class="adm-table">
        <thead><tr><th>Nome</th><th class="hide-sm">Assunto</th><th class="hide-sm">E-mail</th><th>Recebida</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr class="<?= (int) $r['lida'] ? '' : 'adm-row--unread' ?>">
              <td><a class="adm-table__title" href="<?= adm_link(['p' => 'mensagens', 'id' => $r['id']]) ?>"><?= e($r['nome']) ?></a></td>
              <td class="hide-sm"><?= e($r['assunto']) ?></td>
              <td class="hide-sm"><?= e($r['email']) ?></td>
              <td class="num adm-muted"><?= e(date('d/m/Y H:i', strtotime((string) $r['criado_em']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php else: ?>
        <div class="adm-empty"><span class="empty__icon"><?= icon('inbox', 26) ?></span><h2><?= $filtro === 'novas' ? 'Nenhuma mensagem nova' : 'Nenhuma mensagem ainda' ?></h2><p>Quando alguém preencher o formulário de contato do site, a mensagem aparece aqui.</p></div>
      <?php endif; ?>
    </div>
    <?php adm_foot();
}

function view_message(array $m): void {
    adm_head($m['assunto'] ?: 'Mensagem', 'Recebida em ' . date('d/m/Y \à\s H:i', strtotime((string) $m['criado_em'])), '<a class="ce-btn ce-btn--ghost ce-btn--sm" href="' . adm_link(['p' => 'mensagens']) . '">' . icon('arrow-left', 16) . '<span class="hide-sm">Voltar</span></a>', 'mensagens');
    $tel = preg_replace('/\D/', '', (string) $m['telefone']);
    ?>
    <div class="adm-card" style="max-width:820px"><div class="adm-card__body">
      <dl class="adm-msg__meta">
        <dt>Nome</dt><dd><?= e($m['nome']) ?></dd>
        <dt>E-mail</dt><dd><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></dd>
        <?php if ($m['telefone']): ?><dt>Telefone</dt><dd><?= e($m['telefone']) ?></dd><?php endif; ?>
        <dt>Assunto</dt><dd><?= e($m['assunto']) ?></dd>
      </dl>
      <p class="adm-msg__text"><?= e($m['mensagem']) ?></p>
      <div class="adm-actions">
        <a class="ce-btn ce-btn--primary ce-btn--md" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . $m['assunto'] . ' — CEATPE') ?>"><?= icon('mail', 18) ?>Responder por e-mail</a>
        <?php if (strlen($tel) >= 10): ?><a class="ce-btn ce-btn--secondary ce-btn--md" href="https://wa.me/55<?= e(ltrim($tel, '0')) ?>" target="_blank" rel="noopener"><?= icon('message-circle', 18) ?>WhatsApp</a><?php endif; ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="nao_lida"><button class="ce-btn ce-btn--ghost ce-btn--md" type="submit">Marcar como não lida</button></form>
        <form method="post" data-confirm="Excluir esta mensagem? Essa ação não pode ser desfeita."><?= csrf_field() ?><input type="hidden" name="acao" value="excluir"><button class="ce-btn ce-btn--ghost ce-btn--md" type="submit" style="color:var(--status-danger-text)"><?= icon('trash-2', 18) ?>Excluir</button></form>
      </div>
    </div></div>
    <?php adm_foot();
}

/* ---------- Configurações ---------- */
function view_settings(array $schema, string $aba, array $erros): void {
    adm_head('Informações do site', 'Contato, redes sociais, doações e textos institucionais. Campos vazios ficam escondidos no site.', '', 'config');
    ?>
    <div class="ce-tabs" style="margin-bottom:24px">
      <div class="ce-tabs__list" role="tablist">
        <?php foreach (array_keys($schema) as $t): ?>
          <a class="ce-tab" role="tab" href="<?= adm_link(['p' => 'config', 'aba' => $t]) ?>" aria-selected="<?= $t === $aba ? 'true' : 'false' ?>"><?= e($t) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if ($erros): ?><div class="adm-alert adm-alert--danger" role="alert"><?= icon('circle-alert', 18) ?><span><?= e($erros['_'] ?? 'Alguns campos não foram salvos. Revise os destacados.') ?></span></div><?php endif; ?>
    <form method="post" class="adm-card" style="max-width:820px">
      <?= csrf_field() ?>
      <div class="adm-card__body adm-fields">
        <?php foreach ($schema[$aba] as $k => $f) echo field_html($k, $f, $_POST[$k] ?? setting($k), $erros[$k] ?? null); ?>
        <div><button class="ce-btn ce-btn--primary ce-btn--md" type="submit"><?= icon('check', 18) ?>Salvar <?= e(mb_strtolower($aba)) ?></button></div>
      </div>
    </form>
    <?php adm_foot();
}

/* ---------- Acessos ---------- */
function view_account(array $users, array $erros): void {
    global $user;
    adm_head('Acessos e senha', 'Quem pode entrar no painel.', '', 'conta');
    $err = fn($k) => isset($erros[$k]) ? $erros[$k] : null;
    ?>
    <div class="adm-grid2">
      <section class="adm-card">
        <div class="adm-card__head"><h2>Pessoas com acesso</h2></div>
        <div class="adm-table-wrap"><table class="adm-table"><tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><span class="adm-table__title" style="text-decoration:none"><?= e($u['nome'] ?: $u['email']) ?></span><div class="adm-muted" style="font-size:14px"><?= e($u['email']) ?></div></td>
              <td class="adm-muted hide-sm" style="font-size:14px"><?= $u['ultimo_acesso'] ? 'Último acesso ' . e(date('d/m/Y', strtotime((string) $u['ultimo_acesso']))) : 'Nunca entrou' ?></td>
              <td><?php if ((int) $u['id'] === (int) $user['id']): ?><span class="ce-badge ce-badge--info">Você</span><?php else: ?>
                <form method="post" data-confirm="Remover o acesso de <?= e($u['email']) ?>?"><?= csrf_field() ?><input type="hidden" name="acao" value="remover"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>"><button class="ce-iconbtn ce-iconbtn--ghost ce-iconbtn--sm" type="submit" title="Remover acesso"><?= icon('trash-2', 18) ?><span class="ce-sr">Remover acesso</span></button></form>
              <?php endif; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody></table></div>
        <form method="post" class="adm-card__body adm-fields" style="border-top:1px solid var(--border-subtle)">
          <?= csrf_field() ?><input type="hidden" name="acao" value="novo">
          <h3 style="margin:0;font-size:16px;color:var(--text-heading)">Dar acesso a outra pessoa</h3>
          <?= field_html('nome', ['Nome', 'text'], $_POST['nome'] ?? '', null) ?>
          <?= field_html('email', ['E-mail', 'text', ['required' => true]], $_POST['email'] ?? '', $err('email')) ?>
          <label class="ce-field"><span class="ce-field__label">Senha provisória<span class="ce-field__req">*</span></span><input class="ce-control" type="password" name="senha" autocomplete="new-password"<?= $err('senha') ? ' aria-invalid="true"' : '' ?>><?= $err('senha') ? '<span class="ce-field__error">' . icon('circle-alert', 14) . e($err('senha')) . '</span>' : '<span class="ce-field__help">Mínimo de 10 caracteres. Peça para a pessoa trocar depois.</span>' ?></label>
          <div><button class="ce-btn ce-btn--secondary ce-btn--md" type="submit"><?= icon('plus', 18) ?>Criar acesso</button></div>
        </form>
      </section>
      <section class="adm-card">
        <div class="adm-card__head"><h2>Trocar minha senha</h2></div>
        <form method="post" class="adm-card__body adm-fields">
          <?= csrf_field() ?><input type="hidden" name="acao" value="senha">
          <?php foreach (['atual' => 'Senha atual', 'nova' => 'Nova senha', 'confirma' => 'Repita a nova senha'] as $k => $l): ?>
            <label class="ce-field"><span class="ce-field__label"><?= $l ?></span><input class="ce-control" type="password" name="<?= $k ?>" autocomplete="<?= $k === 'atual' ? 'current-password' : 'new-password' ?>"<?= $err($k) ? ' aria-invalid="true"' : '' ?>><?= $err($k) ? '<span class="ce-field__error">' . icon('circle-alert', 14) . e($err($k)) . '</span>' : '' ?></label>
          <?php endforeach; ?>
          <div><button class="ce-btn ce-btn--primary ce-btn--md" type="submit">Trocar senha</button></div>
        </form>
      </section>
    </div>
    <?php adm_foot();
}
