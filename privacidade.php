<?php
require __DIR__ . '/inc/layout.php';
$email = setting('email');
$canal = $email ? 'pelo e-mail <a href="mailto:' . e($email) . '">' . e($email) . '</a>' : 'pelo formulário da página <a href="' . url('/contato') . '">Contato</a>';

$secoes = [
    ['Coleta de dados', 'Coletamos dados fornecidos voluntariamente por meio de formulários de contato, cadastro de voluntários e inscrições em cursos e eventos, como nome, e-mail, telefone e a mensagem enviada.'],
    ['Uso das informações', 'Os dados são utilizados exclusivamente para responder solicitações, gerenciar inscrições em atividades do CEATPE e manter contato institucional. Não compartilhamos dados com terceiros para fins comerciais.'],
    ['Base legal e conformidade com a LGPD', 'O tratamento de dados pessoais segue os princípios da Lei Geral de Proteção de Dados (Lei nº 13.709/2018), com base no consentimento do titular e no legítimo interesse institucional.'],
    ['Direitos do titular', "Você pode solicitar, a qualquer momento, acesso, correção, portabilidade ou exclusão dos seus dados pessoais, entrando em contato $canal."],
    ['Doações e pagamentos', 'Dados financeiros informados em doações são processados pelas instituições bancárias e plataformas de pagamento parceiras, sob suas próprias políticas de segurança e privacidade. O CEATPE não armazena dados de cartão.'],
    ['Cookies', 'Este site utiliza apenas cookies estritamente necessários ao seu funcionamento, como o que protege o formulário de contato contra envios automáticos.'],
    ['Contato do encarregado de dados', "Dúvidas sobre esta política podem ser enviadas $canal."],
];
page_head('Política de privacidade', ['description' => 'Como o CEATPE coleta, usa e protege dados pessoais, conforme a LGPD.']);
page_hero('Política de uso e privacidade', 'Como coletamos, usamos e protegemos os seus dados, em conformidade com a LGPD.');
?>
<section class="ce-section">
  <article class="article">
    <div class="prose">
      <?php foreach ($secoes as $i => [$t, $txt]): ?>
        <h2><?= $i + 1 ?>. <?= e($t) ?></h2>
        <p><?= $txt ?></p>
      <?php endforeach; ?>
    </div>
  </article>
</section>
<?php page_foot(); ?>
