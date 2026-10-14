<?php
declare(strict_types=1);
/* Instalador: cria inc/config.php, as tabelas, o conteúdo inicial e o primeiro acesso ao painel.
   Depois de instalado ele se bloqueia sozinho. Apague este arquivo do servidor ao terminar. */

require __DIR__ . '/inc/bootstrap.php';

$installed = is_file(CONFIG_FILE);
$error = '';
$done = false;
$v = fn($k, $d = '') => e($_POST[$k] ?? $d);

if (!$installed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $cfg = [
        'base_url' => $base,
        'debug'    => false,
        'db'       => [
            'driver' => 'mysql',
            'host'   => trim($_POST['db_host'] ?? 'localhost'),
            'name'   => trim($_POST['db_name'] ?? ''),
            'user'   => trim($_POST['db_user'] ?? ''),
            'pass'   => (string) ($_POST['db_pass'] ?? ''),
        ],
    ];
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = (string) ($_POST['senha'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Informe um e-mail válido para o acesso ao painel.';
    elseif (mb_strlen($senha) < 10) $error = 'A senha do painel precisa ter pelo menos 10 caracteres.';
    else {
        try {
            $GLOBALS['CONFIG'] = $cfg;
            db();
            migrate();
            require __DIR__ . '/inc/seed.php';
            seed();
            if (!row('SELECT id FROM usuarios WHERE email = ?', [$email])) {
                q('INSERT INTO usuarios (nome, email, senha, criado_em) VALUES (?,?,?,?)', [$nome ?: 'Administração', $email, password_hash($senha, PASSWORD_DEFAULT), now()]);
            }
            $php = "<?php\n// Gerado pelo instalador em " . date('d/m/Y H:i') . ". Não compartilhe este arquivo.\nreturn " . var_export($cfg, true) . ";\n";
            if (file_put_contents(CONFIG_FILE, $php) === false) throw new RuntimeException('Sem permissão para gravar inc/config.php. Ajuste as permissões da pasta inc/ para 755.');
            @mkdir(ROOT . '/uploads', 0755, true);
            $done = true;
        } catch (PDOException $ex) {
            $error = 'Não consegui conectar ao banco. Confira host, nome, usuário e senha (no Hostinger o host costuma ser "localhost"). Detalhe: ' . $ex->getMessage();
        } catch (Throwable $ex) {
            $error = $ex->getMessage();
        }
    }
}
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Instalação — CEATPE</title>
<link rel="stylesheet" href="assets/css/tokens.css"><link rel="stylesheet" href="assets/css/components.css"><link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="ce-root adm-auth">
<main class="adm-auth__card">
  <img src="assets/img/ceatpe-horizontal.png" alt="CEATPE" class="adm-auth__logo">
  <?php if ($installed && !$done): ?>
    <h1>O site já está instalado</h1>
    <p class="adm-muted">Por segurança, apague o arquivo <code>instalar.php</code> do servidor.</p>
    <a class="ce-btn ce-btn--primary ce-btn--md ce-btn--block" href="admin/">Ir para o painel</a>
  <?php elseif ($done): ?>
    <h1>Tudo pronto</h1>
    <p class="adm-muted">Banco criado, conteúdo inicial carregado e acesso ao painel configurado. Agora apague o arquivo <code>instalar.php</code> do servidor.</p>
    <a class="ce-btn ce-btn--primary ce-btn--md ce-btn--block" href="admin/">Entrar no painel</a>
  <?php else: ?>
    <h1>Instalar o site</h1>
    <p class="adm-muted">Crie o banco MySQL no hPanel (Bancos de dados → MySQL) e preencha os dados abaixo.</p>
    <?php if ($error): ?><div class="adm-alert adm-alert--danger" role="alert"><?= icon('circle-alert', 18) ?><span><?= e($error) ?></span></div><?php endif; ?>
    <form method="post" class="adm-form" novalidate>
      <fieldset><legend>Banco de dados</legend>
        <label class="ce-field"><span class="ce-field__label">Host</span><input class="ce-control" name="db_host" value="<?= $v('db_host', 'localhost') ?>" required></label>
        <label class="ce-field"><span class="ce-field__label">Nome do banco</span><input class="ce-control" name="db_name" value="<?= $v('db_name') ?>" required></label>
        <label class="ce-field"><span class="ce-field__label">Usuário</span><input class="ce-control" name="db_user" value="<?= $v('db_user') ?>" required autocomplete="off"></label>
        <label class="ce-field"><span class="ce-field__label">Senha</span><input class="ce-control" type="password" name="db_pass" autocomplete="off"></label>
      </fieldset>
      <fieldset><legend>Primeiro acesso ao painel</legend>
        <label class="ce-field"><span class="ce-field__label">Seu nome</span><input class="ce-control" name="nome" value="<?= $v('nome') ?>"></label>
        <label class="ce-field"><span class="ce-field__label">E-mail</span><input class="ce-control" type="email" name="email" value="<?= $v('email') ?>" required></label>
        <label class="ce-field"><span class="ce-field__label">Senha</span><input class="ce-control" type="password" name="senha" minlength="10" required autocomplete="new-password"><span class="ce-field__help">Mínimo de 10 caracteres.</span></label>
      </fieldset>
      <button class="ce-btn ce-btn--primary ce-btn--md ce-btn--block" type="submit">Instalar</button>
    </form>
  <?php endif; ?>
</main>
</body>
</html>
