<?php
declare(strict_types=1);
require dirname(__DIR__) . '/inc/layout.php';
require __DIR__ . '/views.php';

start_session();
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

try { migrate(); } catch (Throwable $e) { /* sem permissão de ALTER: segue com o que existe */ }

$p = $_GET['p'] ?? 'inicio';
$self = url('admin/');
$link = fn(array $q = []) => $self . ($q ? '?' . http_build_query($q) : '');

/* ---------- Entrar / sair ---------- */
if ($p === 'sair') {
    if (csrf_ok()) { $_SESSION = []; session_destroy(); }
    redirect($link());
}

if (!current_user()) {
    $erro = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_ok()) $erro = 'A sessão expirou. Tente de novo.';
        elseif (too_many_attempts()) $erro = 'Muitas tentativas seguidas. Aguarde 15 minutos e tente novamente.';
        else {
            $u = row('SELECT * FROM usuarios WHERE email = ?', [trim((string) ($_POST['email'] ?? ''))]);
            if ($u && password_verify((string) ($_POST['senha'] ?? ''), $u['senha'])) {
                session_regenerate_id(true);
                $_SESSION['uid'] = (int) $u['id'];
                q('UPDATE usuarios SET ultimo_acesso = ? WHERE id = ?', [now(), $u['id']]);
                q('DELETE FROM tentativas WHERE ip = ?', [client_ip()]);
                if (password_needs_rehash($u['senha'], PASSWORD_DEFAULT)) q('UPDATE usuarios SET senha = ? WHERE id = ?', [password_hash((string) $_POST['senha'], PASSWORD_DEFAULT), $u['id']]);
                redirect($link());
            }
            q('INSERT INTO tentativas (ip, momento) VALUES (?, ?)', [client_ip(), time()]);
            $erro = 'E-mail ou senha incorretos.';
        }
    }
    view_login($erro);
    exit;
}

$user = current_user();
$naoLidas = (int) row('SELECT COUNT(*) AS n FROM mensagens WHERE lida = 0')['n'];

/* ---------- Upload de imagem do editor (JSON) ---------- */
if ($p === 'upload') {
    header('Content-Type: application/json');
    if (!csrf_ok() || empty($_FILES['imagem'])) { http_response_code(400); echo json_encode(['erro' => 'Envio inválido.']); exit; }
    try { echo json_encode(['url' => upload_url(save_image($_FILES['imagem'], 1600))]); }
    catch (Throwable $ex) { http_response_code(422); echo json_encode(['erro' => $ex->getMessage()]); }
    exit;
}

/* ---------- Módulos de conteúdo ---------- */
$mods = modules();
if ($p === 'mod') {
    $m = $_GET['m'] ?? '';
    if (!isset($mods[$m])) redirect($link());
    $mod = $mods[$m];
    $a = $_GET['a'] ?? 'lista';
    $id = (int) ($_GET['id'] ?? 0);

    if ($a === 'excluir' && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
        q("DELETE FROM $m WHERE id = ?", [$id]);
        flash(ucfirst($mod['singular']) . (in_array($mod['singular'], ['publicação', 'campanha', 'pessoa'], true) ? ' excluída.' : ' excluído.'));
        redirect($link(['p' => 'mod', 'm' => $m]));
    }
    if ($a === 'visivel' && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
        $campo = isset($mod['fields']['publicado']) ? 'publicado' : 'ativo';
        q("UPDATE $m SET $campo = 1 - $campo, atualizado_em = ? WHERE id = ?", [now(), $id]);
        flash('Visibilidade atualizada.');
        redirect($link(['p' => 'mod', 'm' => $m]));
    }

    if ($a === 'editar') {
        $item = $id ? row("SELECT * FROM $m WHERE id = ?", [$id]) : null;
        if ($id && !$item) redirect($link(['p' => 'mod', 'm' => $m]));
        $erros = [];
        $dados = $item ?? [];
        if (!$item) foreach ($mod['fields'] as $k => $f) {
            $def = $f[2]['default'] ?? '';
            $dados[$k] = $def === 'today' ? date('Y-m-d') : $def;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_ok()) $erros['_'] = 'A sessão expirou. Suas alterações não foram salvas; copie o texto e recarregue a página.';
            $vals = [];
            foreach ($mod['fields'] as $k => [$label, $type]) {
                $o = $mod['fields'][$k][2] ?? [];
                $raw = $_POST[$k] ?? null;
                switch ($type) {
                    case 'bool': $v = !empty($raw) ? 1 : 0; break;
                    case 'int': $v = (int) $raw; break;
                    case 'rich': $v = clean_html((string) $raw); break;
                    case 'image':
                        $v = $item[$k] ?? null;
                        if (!empty($_POST[$k . '__remover'])) $v = null;
                        if (!empty($_FILES[$k]['name'])) {
                            try { $v = save_image($_FILES[$k]); } catch (Throwable $ex) { $erros[$k] = $ex->getMessage(); }
                        }
                        break;
                    case 'select': $v = isset($o['options'][$raw]) ? $raw : ($o['default'] ?? array_key_first($o['options'])); break;
                    case 'date': $v = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $raw) ? $raw : null; break;
                    case 'url':
                        $v = trim((string) $raw);
                        if ($v !== '' && !preg_match('~^https?://~i', $v)) $v = 'https://' . $v;
                        if ($v !== '' && !filter_var($v, FILTER_VALIDATE_URL)) $erros[$k] = 'Endereço inválido. Cole o link completo, começando com https://';
                        break;
                    default: $v = trim((string) $raw);
                }
                $vals[$k] = $v;
                if (!empty($o['required']) && ($v === null || $v === '') && !isset($erros[$k])) $erros[$k] = 'Preencha este campo.';
                if (!empty($o['max']) && is_string($v) && mb_strlen($v) > $o['max'] && $type !== 'rich') $erros[$k] = "Use no máximo {$o['max']} caracteres (agora tem " . mb_strlen($v) . ').';
            }
            $dados = array_merge($dados, $vals);
            if (!$erros) {
                $vals['atualizado_em'] = now();
                if (!empty($mod['slug'])) {
                    $base = slugify((string) ($_POST['slug'] ?? '') ?: (string) $vals[$mod['slug']]);
                    $vals['slug'] = unique_slug($m, $base, $id);
                }
                if ($id) {
                    $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($vals)));
                    q("UPDATE $m SET $set WHERE id = ?", [...array_values($vals), $id]);
                } else {
                    $vals['criado_em'] = now();
                    $cols = implode(', ', array_keys($vals));
                    $qs = implode(', ', array_fill(0, count($vals), '?'));
                    q("INSERT INTO $m ($cols) VALUES ($qs)", array_values($vals));
                    $id = (int) db()->lastInsertId();
                }
                if ($m === 'campanhas' && !empty($vals['destaque'])) q('UPDATE campanhas SET destaque = 0 WHERE id <> ?', [$id]);
                flash(ucfirst($mod['singular']) . (in_array($mod['singular'], ['publicação', 'campanha', 'pessoa'], true) ? ' salva.' : ' salvo.'));
                redirect(!empty($_POST['_novo']) ? $link(['p' => 'mod', 'm' => $m, 'a' => 'editar']) : $link(['p' => 'mod', 'm' => $m, 'a' => 'editar', 'id' => $id]));
            }
        }
        view_edit($m, $mod, $item, $dados, $erros);
        exit;
    }

    $busca = trim((string) ($_GET['q'] ?? ''));
    $first = array_key_first($mod['fields']);
    $sql = "SELECT * FROM $m";
    $params = [];
    if ($busca !== '') { $sql .= " WHERE $first LIKE ?"; $params[] = '%' . $busca . '%'; }
    $sql .= ' ORDER BY ' . $mod['order'];
    view_list($m, $mod, rows($sql, $params), $busca);
    exit;
}

/* ---------- Mensagens ---------- */
if ($p === 'mensagens') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
        $acao = $_POST['acao'] ?? '';
        if ($acao === 'excluir') { q('DELETE FROM mensagens WHERE id = ?', [$id]); flash('Mensagem excluída.'); redirect($link(['p' => 'mensagens'])); }
        if ($acao === 'nao_lida') { q('UPDATE mensagens SET lida = 0 WHERE id = ?', [$id]); flash('Marcada como não lida.'); redirect($link(['p' => 'mensagens'])); }
        if ($acao === 'todas_lidas') { q('UPDATE mensagens SET lida = 1'); flash('Todas marcadas como lidas.'); redirect($link(['p' => 'mensagens'])); }
    }
    if (($_GET['a'] ?? '') === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="mensagens-ceatpe-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Data', 'Nome', 'E-mail', 'Telefone', 'Assunto', 'Mensagem'], ';');
        foreach (rows('SELECT * FROM mensagens ORDER BY criado_em DESC') as $r) fputcsv($out, [$r['criado_em'], $r['nome'], $r['email'], $r['telefone'], $r['assunto'], $r['mensagem']], ';');
        exit;
    }
    if ($id) {
        $msg = row('SELECT * FROM mensagens WHERE id = ?', [$id]);
        if (!$msg) redirect($link(['p' => 'mensagens']));
        if (!(int) $msg['lida']) { q('UPDATE mensagens SET lida = 1 WHERE id = ?', [$id]); $naoLidas = max(0, $naoLidas - 1); }
        view_message($msg);
        exit;
    }
    $filtro = $_GET['f'] ?? '';
    view_messages(rows('SELECT * FROM mensagens' . ($filtro === 'novas' ? ' WHERE lida = 0' : '') . ' ORDER BY criado_em DESC LIMIT 500'), $filtro);
    exit;
}

/* ---------- Configurações ---------- */
if ($p === 'config') {
    $schema = settings_schema();
    $aba = $_GET['aba'] ?? array_key_first($schema);
    if (!isset($schema[$aba])) $aba = array_key_first($schema);
    $erros = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_ok()) $erros['_'] = 'A sessão expirou. Recarregue a página.';
        else {
            foreach ($schema[$aba] as $k => $f) {
                $v = trim((string) ($_POST[$k] ?? ''));
                if (in_array($k, ['email', 'email_form'], true) && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) { $erros[$k] = 'E-mail inválido.'; continue; }
                if (in_array($k, ['instagram', 'facebook', 'youtube', 'linkedin', 'tiktok', 'doacao_link'], true) && $v !== '') {
                    if (!preg_match('~^https?://~i', $v)) $v = 'https://' . ltrim($v, '@/');
                    if (!filter_var($v, FILTER_VALIDATE_URL)) { $erros[$k] = 'Cole o endereço completo, começando com https://'; continue; }
                }
                if ($k === 'mapa' && $v !== '') {
                    if (preg_match('~src="([^"]+)"~', $v, $mm)) $v = $mm[1];
                    if (!preg_match('~^https://(www\.)?google\.com/maps/embed~', $v)) { $erros[$k] = 'Use o link de "Incorporar um mapa" do Google Maps (começa com https://www.google.com/maps/embed).'; continue; }
                }
                save_setting($k, $v);
            }
            if (!$erros) { flash('Configurações salvas.'); redirect($link(['p' => 'config', 'aba' => $aba])); }
        }
    }
    view_settings($schema, $aba, $erros);
    exit;
}

/* ---------- Conta e acessos ---------- */
if ($p === 'conta') {
    $erros = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
        $acao = $_POST['acao'] ?? '';
        if ($acao === 'senha') {
            $u = row('SELECT * FROM usuarios WHERE id = ?', [$user['id']]);
            $nova = (string) ($_POST['nova'] ?? '');
            if (!password_verify((string) ($_POST['atual'] ?? ''), $u['senha'])) $erros['atual'] = 'Senha atual incorreta.';
            elseif (mb_strlen($nova) < 10) $erros['nova'] = 'A nova senha precisa ter pelo menos 10 caracteres.';
            elseif ($nova !== ($_POST['confirma'] ?? '')) $erros['confirma'] = 'As senhas não conferem.';
            else { q('UPDATE usuarios SET senha = ? WHERE id = ?', [password_hash($nova, PASSWORD_DEFAULT), $user['id']]); flash('Senha alterada.'); redirect($link(['p' => 'conta'])); }
        }
        if ($acao === 'novo') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $senha = (string) ($_POST['senha'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros['email'] = 'E-mail inválido.';
            elseif (row('SELECT id FROM usuarios WHERE email = ?', [$email])) $erros['email'] = 'Já existe um acesso com este e-mail.';
            elseif (mb_strlen($senha) < 10) $erros['senha'] = 'A senha precisa ter pelo menos 10 caracteres.';
            else { q('INSERT INTO usuarios (nome, email, senha, criado_em) VALUES (?,?,?,?)', [trim((string) ($_POST['nome'] ?? '')) ?: $email, $email, password_hash($senha, PASSWORD_DEFAULT), now()]); flash('Acesso criado. Envie a senha para a pessoa por um canal seguro.'); redirect($link(['p' => 'conta'])); }
        }
        if ($acao === 'remover') {
            $rid = (int) ($_POST['id'] ?? 0);
            if ($rid !== (int) $user['id']) { q('DELETE FROM usuarios WHERE id = ?', [$rid]); flash('Acesso removido.'); }
            redirect($link(['p' => 'conta']));
        }
    }
    view_account(rows('SELECT id, nome, email, ultimo_acesso FROM usuarios ORDER BY id'), $erros);
    exit;
}

/* ---------- Início ---------- */
$contagens = [];
foreach ($mods as $k => $mod) $contagens[$k] = (int) row("SELECT COUNT(*) AS n FROM $k")['n'];
$pendencias = [];
foreach (['email' => 'E-mail de contato', 'telefone' => 'Telefone', 'endereco' => 'Endereço', 'instagram' => 'Instagram', 'pix_chave' => 'Chave PIX', 'cnpj' => 'CNPJ'] as $k => $l) if (!setting($k)) $pendencias[$k] = $l;
view_dashboard($contagens, rows('SELECT * FROM mensagens ORDER BY criado_em DESC LIMIT 5'), rows('SELECT * FROM eventos WHERE data >= ? ORDER BY data ASC LIMIT 3', [date('Y-m-d')]), $pendencias);
