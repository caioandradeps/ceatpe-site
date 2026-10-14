<?php
require __DIR__ . '/inc/layout.php';
start_session();

$assuntos = ['informacoes' => 'Informações gerais', 'voluntariado' => 'Quero ser voluntário', 'doacoes' => 'Doações', 'parcerias' => 'Parcerias institucionais', 'cursos' => 'Cursos e projetos', 'imprensa' => 'Imprensa', 'outro' => 'Outro assunto'];
$pre = (string) ($_GET['assunto'] ?? '');
$livre = '';
if (!isset($assuntos[$pre]) && $pre !== '') { $livre = mb_substr($pre, 0, 120); $pre = 'outro'; }

$erros = []; $enviado = false;
$d = ['nome' => '', 'email' => '', 'telefone' => '', 'assunto' => $pre ?: 'informacoes', 'mensagem' => $livre ? "Sobre: $livre\n\n" : ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($d as $k => $_) $d[$k] = trim((string) ($_POST[$k] ?? ''));
    $tempo = time() - (int) ($_SESSION['form_t'] ?? 0);
    if (!csrf_ok()) $erros['geral'] = 'A página ficou aberta por muito tempo. Recarregue e tente de novo.';
    elseif (($_POST['site'] ?? '') !== '' || $tempo < 3) $enviado = true; // robô: finge que deu certo
    else {
        if (mb_strlen($d['nome']) < 2) $erros['nome'] = 'Informe seu nome.';
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $erros['email'] = 'Informe um e-mail válido, como nome@exemplo.com.';
        if (!isset($assuntos[$d['assunto']])) $d['assunto'] = 'outro';
        if (mb_strlen($d['mensagem']) < 10) $erros['mensagem'] = 'Escreva sua mensagem (pelo menos 10 caracteres).';
        if (empty($_POST['consentimento'])) $erros['consentimento'] = 'Para responder, precisamos da sua autorização para usar estes dados.';
        $recentes = (int) row('SELECT COUNT(*) AS n FROM mensagens WHERE ip = ? AND criado_em > ?', [client_ip(), date('Y-m-d H:i:s', time() - 3600)])['n'];
        if ($recentes >= 5) $erros['geral'] = 'Recebemos várias mensagens deste endereço na última hora. Tente novamente mais tarde.';

        if (!$erros) {
            q('INSERT INTO mensagens (nome, email, telefone, assunto, mensagem, lida, ip, criado_em) VALUES (?,?,?,?,?,0,?,?)',
              [mb_substr($d['nome'], 0, 160), mb_substr($d['email'], 0, 190), mb_substr($d['telefone'], 0, 60), $assuntos[$d['assunto']], mb_substr($d['mensagem'], 0, 5000), client_ip(), now()]);
            if ($para = setting('email_form')) {
                $host = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? 'ceatpe.org.br');
                $corpo = "Nova mensagem pelo site\n\nNome: {$d['nome']}\nE-mail: {$d['email']}\nTelefone: {$d['telefone']}\nAssunto: {$assuntos[$d['assunto']]}\n\n{$d['mensagem']}\n";
                $hdr = "From: Site CEATPE <nao-responda@$host>\r\nReply-To: {$d['email']}\r\nContent-Type: text/plain; charset=UTF-8";
                @mail($para, '=?UTF-8?B?' . base64_encode('[Site] ' . $assuntos[$d['assunto']] . ' — ' . $d['nome']) . '?=', $corpo, $hdr);
            }
            $enviado = true;
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['form_t'])) $_SESSION['form_t'] = time();

$email = setting('email'); $tel = setting('telefone'); $wa = setting('whatsapp'); $end = setting('endereco'); $hor = setting('horario'); $mapa = setting('mapa');
$soc = socials();
$fe = fn($k) => isset($erros[$k]) ? ' aria-invalid="true" aria-describedby="erro-' . $k . '"' : '';
$err = fn($k) => isset($erros[$k]) ? '<span class="ce-field__error" id="erro-' . $k . '">' . icon('circle-alert', 14) . e($erros[$k]) . '</span>' : '';

page_head('Contato', ['active' => 'contato', 'description' => 'Fale com o CEATPE: informações, voluntariado, doações e parcerias.']);
page_hero('Fale conosco', 'Dúvidas, propostas de parceria, voluntariado ou imprensa: escreva para a gente e responderemos o quanto antes.');
?>
<section class="ce-section">
  <div class="ce-container contact">
    <div class="form-card">
      <?php if ($enviado): ?>
        <div class="sent" role="status">
          <span class="sent__icon"><?= icon('check', 28) ?></span>
          <h2>Mensagem enviada</h2>
          <p>Obrigado por escrever<?= $d['nome'] ? ', ' . e(explode(' ', $d['nome'])[0]) : '' ?>. Nossa equipe vai responder pelo e-mail informado.</p>
          <a class="ce-btn ce-btn--secondary ce-btn--md" href="<?= url('/') ?>">Voltar para o início</a>
        </div>
      <?php else: ?>
        <h2>Envie uma mensagem</h2>
        <p>Campos com <span class="ce-field__req" aria-hidden="true">*</span> são obrigatórios.</p>
        <?php if ($erros): ?><div class="alert alert--danger" role="alert"><?= icon('circle-alert', 18) ?><span><?= e($erros['geral'] ?? 'Revise os campos destacados abaixo.') ?></span></div><?php endif; ?>
        <form method="post" action="<?= url('/contato') ?>" novalidate>
          <?= csrf_field() ?>
          <div class="hp" aria-hidden="true"><label>Não preencha<input type="text" name="site" tabindex="-1" autocomplete="off"></label></div>
          <div class="form-grid">
            <label class="ce-field"><span class="ce-field__label">Nome<span class="ce-field__req" aria-hidden="true">*</span></span>
              <input class="ce-control" name="nome" value="<?= e($d['nome']) ?>" autocomplete="name" required<?= $fe('nome') ?>><?= $err('nome') ?></label>
            <label class="ce-field"><span class="ce-field__label">E-mail<span class="ce-field__req" aria-hidden="true">*</span></span>
              <input class="ce-control" type="email" name="email" value="<?= e($d['email']) ?>" autocomplete="email" required<?= $fe('email') ?>><?= $err('email') ?></label>
            <label class="ce-field"><span class="ce-field__label">Telefone ou WhatsApp</span>
              <input class="ce-control" type="tel" name="telefone" value="<?= e($d['telefone']) ?>" autocomplete="tel"></label>
            <label class="ce-field"><span class="ce-field__label">Assunto</span>
              <span class="ce-select"><select class="ce-control" name="assunto">
                <?php foreach ($assuntos as $k => $l): ?><option value="<?= $k ?>"<?= $d['assunto'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
              </select><span class="ce-select__chev"><?= icon('chevron-down', 18) ?></span></span></label>
            <label class="ce-field full"><span class="ce-field__label">Mensagem<span class="ce-field__req" aria-hidden="true">*</span></span>
              <textarea class="ce-control" name="mensagem" rows="6" required<?= $fe('mensagem') ?>><?= e($d['mensagem']) ?></textarea><?= $err('mensagem') ?></label>
            <div class="full ce-field">
              <label class="ce-check"><input type="checkbox" name="consentimento" value="1"<?= !empty($_POST['consentimento']) ? ' checked' : '' ?><?= $fe('consentimento') ?>><span class="ce-check__box"><?= icon('check', 14) ?></span>
                <span>Autorizo o CEATPE a usar estes dados para responder ao meu contato, conforme a <a href="<?= url('/privacidade') ?>">política de privacidade</a>.</span></label>
              <?= $err('consentimento') ?>
            </div>
            <div class="full"><button class="ce-btn ce-btn--primary ce-btn--lg" type="submit"><?= icon('send', 18) ?>Enviar mensagem</button></div>
          </div>
        </form>
      <?php endif; ?>
    </div>

    <aside aria-label="Informações de contato">
      <ul class="info-list">
        <?php if ($email): ?><li><?= icon('mail', 22) ?><div><strong>E-mail</strong><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></div></li><?php endif; ?>
        <?php if ($tel): ?><li><?= icon('phone', 22) ?><div><strong>Telefone</strong><a href="tel:<?= e(preg_replace('/\D/', '', $tel)) ?>"><?= e($tel) ?></a></div></li><?php endif; ?>
        <?php if ($wa): ?><li><?= icon('message-circle', 22) ?><div><strong>WhatsApp</strong><a href="https://wa.me/55<?= e(preg_replace('/\D/', '', $wa)) ?>" target="_blank" rel="noopener">Conversar no WhatsApp</a></div></li><?php endif; ?>
        <?php if ($end): ?><li><?= icon('map-pin', 22) ?><div><strong>Endereço</strong><span><?= nl($end) ?></span></div></li><?php endif; ?>
        <?php if ($hor): ?><li><?= icon('clock', 22) ?><div><strong>Atendimento</strong><span><?= e($hor) ?></span></div></li><?php endif; ?>
        <li><?= icon('globe', 22) ?><div><strong>Atuação</strong><span>Região Metropolitana do Recife, Pernambuco</span></div></li>
      </ul>
      <?php if ($soc): ?>
        <div class="social-row">
          <?php foreach ($soc as $k => [$label, $u]): ?><a class="ce-btn ce-btn--secondary ce-btn--sm" href="<?= e($u) ?>" target="_blank" rel="noopener"><?= icon(isset(ICONS[$k]) ? $k : 'music-2', 16) ?><?= e($label) ?></a><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="give__card" style="margin-top:32px">
        <h3><?= icon('heart-handshake', 22) ?>Quer ajudar?</h3>
        <p>Doações, campanhas e voluntariado: veja todas as formas de apoiar o CEATPE.</p>
        <a class="ce-btn ce-btn--secondary ce-btn--md" href="<?= url('/apoie') ?>">Formas de apoiar<?= icon('arrow-right', 18) ?></a>
      </div>
      <?php if ($mapa && preg_match('~^https://(www\.)?google\.com/maps/embed~', $mapa)): ?>
        <div class="map"><iframe src="<?= e($mapa) ?>" title="Mapa de localização do CEATPE" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>
      <?php endif; ?>
    </aside>
  </div>
</section>
<?php page_foot(); ?>
