<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('CONFIG_FILE', ROOT . '/inc/config.php');

if (!is_file(CONFIG_FILE)) {
    if (basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'instalar.php') {
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\') . '/instalar.php');
        exit;
    }
    $CONFIG = [];
} else {
    $CONFIG = require CONFIG_FILE;
}

date_default_timezone_set('America/Recife');
mb_internal_encoding('UTF-8');
ini_set('display_errors', !empty($CONFIG['debug']) ? '1' : '0');
error_reporting(E_ALL);

require_once __DIR__ . '/modules.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/sanitize.php';

/* ---------- Base URL ---------- */
function base(): string {
    global $CONFIG;
    return rtrim($CONFIG['base_url'] ?? '', '/');
}
function url(string $path = ''): string {
    if (preg_match('~^(https?:|mailto:|tel:|#)~', $path)) return $path;
    return base() . '/' . ltrim($path, '/');
}
function asset(string $path): string {
    $file = ROOT . '/assets/' . $path;
    $v = is_file($file) ? substr((string) filemtime($file), -6) : '1';
    return url('assets/' . $path) . '?v=' . $v;
}
function upload_url(?string $file): string {
    return $file ? url('uploads/' . $file) : '';
}

/* ---------- Banco ---------- */
function db(): PDO {
    static $pdo = null;
    global $CONFIG;
    if ($pdo) return $pdo;
    $d = $CONFIG['db'] ?? [];
    if (($d['driver'] ?? 'mysql') === 'sqlite') {
        $pdo = new PDO('sqlite:' . $d['path']);
        $pdo->exec('PRAGMA foreign_keys = ON');
    } else {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $d['host'] ?? 'localhost', $d['name'] ?? '');
        $pdo = new PDO($dsn, $d['user'] ?? '', $d['pass'] ?? '', [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4, time_zone = '-03:00'"]);
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}
function is_sqlite(): bool { return db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'; }

function q(string $sql, array $params = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}
function rows(string $sql, array $params = []): array { return q($sql, $params)->fetchAll(); }
function row(string $sql, array $params = []): ?array { $r = q($sql, $params)->fetch(); return $r ?: null; }

/* Cria e atualiza tabelas a partir de modules.php */
function sql_type(string $type): string {
    $my = !is_sqlite();
    return match ($type) {
        'int', 'bool' => $my ? 'INT NOT NULL DEFAULT 0' : 'INTEGER NOT NULL DEFAULT 0',
        'date'        => $my ? 'DATE NULL' : 'TEXT',
        'rich', 'textarea' => $my ? 'MEDIUMTEXT NULL' : 'TEXT',
        default       => $my ? 'VARCHAR(255) NULL' : 'TEXT',
    };
}
function migrate(): void {
    $my = !is_sqlite();
    $pk = $my ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $ts = $my ? 'DATETIME NULL' : 'TEXT';
    $engine = $my ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';

    db()->exec("CREATE TABLE IF NOT EXISTS settings (chave VARCHAR(80) NOT NULL PRIMARY KEY, valor " . ($my ? 'MEDIUMTEXT' : 'TEXT') . " NULL)$engine");
    db()->exec("CREATE TABLE IF NOT EXISTS usuarios (id $pk, nome VARCHAR(120) NULL, email VARCHAR(190) NOT NULL UNIQUE, senha VARCHAR(255) NOT NULL, criado_em $ts, ultimo_acesso $ts)$engine");
    db()->exec("CREATE TABLE IF NOT EXISTS mensagens (id $pk, nome VARCHAR(160) NULL, email VARCHAR(190) NULL, telefone VARCHAR(60) NULL, assunto VARCHAR(160) NULL, mensagem " . ($my ? 'TEXT' : 'TEXT') . " NULL, lida " . sql_type('bool') . ", ip VARCHAR(64) NULL, criado_em $ts)$engine");
    db()->exec("CREATE TABLE IF NOT EXISTS tentativas (ip VARCHAR(64) NOT NULL, momento INT NOT NULL)$engine");

    foreach (modules() as $table => $mod) {
        $cols = ["id $pk"];
        if (!empty($mod['slug'])) $cols[] = 'slug ' . sql_type('text');
        foreach ($mod['fields'] as $name => $f) $cols[] = "$name " . sql_type($f[1]);
        $cols[] = "criado_em $ts";
        $cols[] = "atualizado_em $ts";
        db()->exec("CREATE TABLE IF NOT EXISTS $table (" . implode(', ', $cols) . ")$engine");

        // Colunas novas adicionadas depois da instalação
        $existing = table_columns($table);
        $want = ['slug' => !empty($mod['slug']) ? 'text' : null, 'criado_em' => 'ts', 'atualizado_em' => 'ts'];
        foreach ($mod['fields'] as $name => $f) $want[$name] = $f[1];
        foreach ($want as $name => $type) {
            if ($type === null || in_array($name, $existing, true)) continue;
            db()->exec("ALTER TABLE $table ADD COLUMN $name " . ($type === 'ts' ? $ts : sql_type($type)));
        }
    }
}
function table_columns(string $table): array {
    if (is_sqlite()) return array_column(rows("PRAGMA table_info($table)"), 'name');
    return array_column(rows("SHOW COLUMNS FROM `$table`"), 'Field');
}

/* ---------- Configurações ---------- */
function setting(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try { foreach (rows('SELECT chave, valor FROM settings') as $r) $cache[$r['chave']] = (string) $r['valor']; }
        catch (Throwable $e) { $cache = []; }
    }
    $v = trim($cache[$key] ?? '');
    return $v === '' ? $default : $v;
}
function save_setting(string $key, string $value): void {
    if (is_sqlite()) q('INSERT INTO settings (chave, valor) VALUES (?, ?) ON CONFLICT(chave) DO UPDATE SET valor = excluded.valor', [$key, $value]);
    else q('INSERT INTO settings (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)', [$key, $value]);
}

/* ---------- Saída ---------- */
function e(?string $s): string { return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function nl(?string $s): string { return nl2br(e($s), false); }
function lines(?string $s): array { return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $s)))); }

function now(): string { return date('Y-m-d H:i:s'); }

function slugify(string $s): string {
    $s = mb_strtolower(trim($s));
    $s = strtr($s, ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ó'=>'o','ò'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n']);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string) $s, '-') ?: 'item';
}
function unique_slug(string $table, string $base, int $ignoreId = 0): string {
    $slug = $base; $i = 2;
    while (row("SELECT id FROM $table WHERE slug = ? AND id <> ?", [$slug, $ignoreId])) $slug = $base . '-' . $i++;
    return $slug;
}

const MESES = ['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
const MESES_LONGOS = ['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
const DIAS = ['domingo','segunda-feira','terça-feira','quarta-feira','quinta-feira','sexta-feira','sábado'];

function data_longa(?string $d): string {
    if (!$d) return '';
    $t = strtotime($d);
    return date('j', $t) . ' de ' . MESES_LONGOS[(int) date('n', $t) - 1] . ' de ' . date('Y', $t);
}
function data_partes(?string $d): array {
    $t = strtotime((string) $d) ?: time();
    return ['dia' => date('d', $t), 'mes' => MESES[(int) date('n', $t) - 1], 'ano' => date('Y', $t), 'semana' => DIAS[(int) date('w', $t)]];
}

function cat(array $cats, ?string $key): array {
    return $cats[$key ?? ''] ?? [ucfirst((string) $key), 'blue'];
}

function read_minutes(?string $html): int {
    $words = str_word_count(strip_tags((string) $html));
    return max(1, (int) round($words / 200));
}

/* ---------- Sessão, CSRF, flash ---------- */
function start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('ceatpe_sess');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}
function csrf(): string {
    start_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf()) . '">'; }
function csrf_ok(): bool {
    start_session();
    return !empty($_POST['_csrf']) && hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['_csrf']);
}
function flash(?string $msg = null, string $tone = 'success'): ?array {
    start_session();
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $tone]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function redirect(string $to): never { header('Location: ' . $to); exit; }
function client_ip(): string { return substr($_SERVER['REMOTE_ADDR'] ?? '0', 0, 64); }

/* ---------- Uploads de imagem ---------- */
function save_image(array $file, int $maxW = 2000): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
            ? 'A imagem é maior que o limite do servidor. Envie um arquivo com até 8 MB.'
            : 'Não foi possível receber a imagem. Tente de novo.');
    }
    $info = @getimagesize($file['tmp_name']);
    $ok = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$info || !isset($ok[$info[2]])) throw new RuntimeException('Formato não aceito. Envie JPG, PNG ou WEBP.');
    $ext = $ok[$info[2]];
    $dir = date('Y/m');
    @mkdir(ROOT . "/uploads/$dir", 0755, true);
    $name = $dir . '/' . date('His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = ROOT . '/uploads/' . $name;

    [$w, $h] = $info;
    if (function_exists('imagecreatefromstring') && $w > $maxW && $ext !== 'gif') {
        $src = imagecreatefromstring((string) file_get_contents($file['tmp_name']));
        if ($src) {
            $nh = (int) round($h * $maxW / $w);
            $dst = imagecreatetruecolor($maxW, $nh);
            imagealphablending($dst, false); imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxW, $nh, $w, $h);
            match ($ext) { 'jpg' => imagejpeg($dst, $dest, 84), 'png' => imagepng($dst, $dest, 8), 'webp' => imagewebp($dst, $dest, 82) };
            return $name;
        }
    }
    if (!move_uploaded_file($file['tmp_name'], $dest) && !copy($file['tmp_name'], $dest)) throw new RuntimeException('Não foi possível salvar a imagem no servidor.');
    return $name;
}

/* ---------- Autenticação ---------- */
function current_user(): ?array {
    start_session();
    if (empty($_SESSION['uid'])) return null;
    static $u = null;
    return $u ??= row('SELECT id, nome, email FROM usuarios WHERE id = ?', [$_SESSION['uid']]);
}
function too_many_attempts(): bool {
    q('DELETE FROM tentativas WHERE momento < ?', [time() - 900]);
    return (int) row('SELECT COUNT(*) AS n FROM tentativas WHERE ip = ?', [client_ip()])['n'] >= 8;
}
