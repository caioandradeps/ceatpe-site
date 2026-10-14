<?php
/* Conteúdo inicial — apenas informações confirmadas pelo CEATPE. */
function seed(): void {
    $t = now();
    $has = fn(string $table) => (int) row("SELECT COUNT(*) AS n FROM $table")['n'] > 0;

    $settings = [
        'quem_somos'   => 'No CEATPE fomentamos e difundimos conhecimentos em ciência e tecnologia, inovação, educação e meio ambiente para transformar a realidade na área metropolitana do Recife, Pernambuco. Junte-se a nós, seja voluntário e fortaleça as ações contínuas com transparência.',
        'ano_fundacao' => '2024',
        'alcance'      => '1000',
        'missao'       => 'Desenvolver e implementar soluções tecnológicas que promovam a sustentabilidade e o bem-estar social. Integramos ciência, inovação e educação para enfrentar os desafios contemporâneos, criando um futuro mais justo, sustentável, plural, de equidade de direitos e conectado para todos.',
        'visao'        => 'Tornar-se um centro líder em tecnologia e inovação, reconhecido por sua capacidade de transformar conhecimento em práticas sustentáveis e inclusivas. Seremos um catalisador para o desenvolvimento tecnológico que valoriza o ser humano e o meio ambiente, contribuindo para o progresso social e econômico.',
        'valores'      => implode("\n", [
            'Sustentabilidade | Promover o equilíbrio entre inovação tecnológica e preservação ambiental, garantindo um futuro cada vez mais sustentável.',
            'Inovação aberta | Incentivar a criatividade e a colaboração entre diferentes setores para a geração de novas ideias e soluções.',
            'Responsabilidade socioambiental | Comprometer-se com o impacto positivo de nossas ações sobre a sociedade e o meio ambiente.',
            'Transparência | Conduzir nossas atividades com clareza, honestidade e respeito às normas éticas.',
            'Educação continuada | Valorizar a educação e o desenvolvimento contínuo como pilares para a inovação e o progresso.',
            'Inclusão | Promover a diversidade e a igualdade de oportunidades em todas as nossas iniciativas e parcerias.',
        ]),
        'doacao_texto' => 'Suas contribuições sustentam os cursos gratuitos, os eventos e os projetos do CEATPE na Região Metropolitana do Recife. Toda arrecadação é aplicada diretamente nas ações, com prestação de contas transparente.',
        'seo_descricao'=> 'Centro de Estudos Avançados em Tecnologia de Pernambuco: ciência, tecnologia, inovação, educação e meio ambiente a serviço das pessoas na Região Metropolitana do Recife.',
    ];
    foreach ($settings as $k => $v) {
        if (!row('SELECT chave FROM settings WHERE chave = ?', [$k])) save_setting($k, $v);
    }

    if (!$has('banners')) {
        $b = [
            ['Tecnologia', 'Tecnologia a serviço das pessoas', 'Formação, pesquisa e inovação para o desenvolvimento da Região Metropolitana do Recife.', 'Seja voluntário', '/contato?assunto=voluntariado', 'blue', 1],
            ['Impacto social', 'Ações contínuas, com transparência', 'Desde 2024, nossos projetos já alcançaram mais de 1.000 pessoas em Pernambuco.', 'Apoie nossos projetos', '/apoie', 'red', 2],
            ['Educação', 'Cursos gratuitos para todas as idades', 'De férias com robótica a informática para idosos: educação tecnológica acessível.', 'Ver cursos e projetos', '/projetos', 'gold', 3],
        ];
        foreach ($b as $r) q('INSERT INTO banners (eyebrow, titulo, texto, botao_texto, botao_link, cor, ordem, ativo, criado_em, atualizado_em) VALUES (?,?,?,?,?,?,?,1,?,?)', [...$r, $t, $t]);
    }

    if (!$has('projetos')) {
        $p = [
            ['Educação Ambiental', 'extensao', 'Comunidade', 'Formação sobre sustentabilidade, meio ambiente e cidadania aplicada à vida na cidade.', 1],
            ['Férias com Robótica', 'formacao', 'Crianças e adolescentes', 'Introdução à robótica e programação para crianças e adolescentes, nas férias escolares.', 2],
            ['Informática para Idosos', 'formacao', 'Terceira idade', 'Alfabetização digital e uso seguro da internet para a terceira idade.', 3],
        ];
        foreach ($p as $r) q('INSERT INTO projetos (titulo, slug, categoria, publico, resumo, ordem, ativo, criado_em, atualizado_em) VALUES (?,?,?,?,?,?,1,?,?)', [$r[0], slugify($r[0]), $r[1], $r[2], $r[3], $r[4], $t, $t]);
    }

    if (!$has('campanhas')) {
        q('INSERT INTO campanhas (titulo, texto, destaque, ativo, criado_em, atualizado_em) VALUES (?,?,1,1,?,?)', [
            'Arrecadação de Produtos de Higiene Feminina',
            'Ajude a garantir dignidade menstrual para mulheres e meninas da comunidade. Doe absorventes e produtos de higiene.',
            $t, $t,
        ]);
    }

    if (!$has('parceiros')) {
        foreach (['Ctrl+C Copiadora', 'Caio Criações', 'Instituto A2S'] as $i => $n) {
            q('INSERT INTO parceiros (nome, ordem, ativo, criado_em, atualizado_em) VALUES (?,?,1,?,?)', [$n, $i + 1, $t, $t]);
        }
    }
}
