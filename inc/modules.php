<?php
/*
 * Definição de todo o conteúdo gerenciável.
 * O painel, o banco (tabelas e colunas) e as validações saem daqui.
 * Para criar um campo novo: adicione aqui e abra o painel — a coluna é criada sozinha.
 */

const CATEGORIAS_PROGRAMA = [
    'formacao'  => ['Formação', 'blue'],
    'pesquisa'  => ['Pesquisa', 'red'],
    'extensao'  => ['Extensão', 'gold'],
    'inovacao'  => ['Inovação', 'green'],
];

const CATEGORIAS_BLOG = [
    'ciencia'    => ['Ciência', 'red'],
    'inovacao'   => ['Inovação', 'green'],
    'educacao'   => ['Educação', 'blue'],
    'comunidade' => ['Comunidade', 'gold'],
];

const CORES_MAOS = [
    'blue'  => 'Azul',
    'red'   => 'Vermelho',
    'gold'  => 'Dourado',
    'green' => 'Verde',
];

function cat_options(array $cats): array {
    $o = [];
    foreach ($cats as $k => $v) $o[$k] = $v[0];
    return $o;
}

function modules(): array {
    static $m = null;
    if ($m !== null) return $m;
    $m = [
        'banners' => [
            'label' => 'Banners do topo', 'singular' => 'banner', 'icon' => 'panels-top-left',
            'help' => 'Os slides que giram no topo da página inicial. Até 5 funcionam melhor.',
            'order' => 'ordem ASC, id ASC', 'list' => ['titulo', 'eyebrow', 'cor', 'ativo'],
            'fields' => [
                'eyebrow'      => ['Rótulo curto', 'text', ['help' => 'Uma ou duas palavras. Ex.: Tecnologia', 'max' => 40]],
                'titulo'       => ['Título', 'text', ['required' => true, 'max' => 90]],
                'texto'        => ['Texto de apoio', 'textarea', ['max' => 220]],
                'botao_texto'  => ['Texto do botão', 'text', ['max' => 40, 'help' => 'Verbo no imperativo. Ex.: Seja voluntário']],
                'botao_link'   => ['Link do botão', 'text', ['help' => 'Ex.: /apoie, /contato ou um endereço completo']],
                'cor'          => ['Cor de destaque', 'select', ['options' => CORES_MAOS, 'default' => 'blue']],
                'ordem'        => ['Ordem', 'int', ['default' => 0]],
                'ativo'        => ['Visível no site', 'bool', ['default' => 1]],
            ],
        ],
        'projetos' => [
            'label' => 'Projetos e cursos', 'singular' => 'projeto', 'icon' => 'graduation-cap',
            'help' => 'Cursos, oficinas e projetos contínuos. Aparecem na página Projetos e na inicial.',
            'order' => 'ordem ASC, id DESC', 'slug' => 'titulo', 'list' => ['titulo', 'categoria', 'ativo'],
            'fields' => [
                'titulo'         => ['Nome do projeto', 'text', ['required' => true, 'max' => 120]],
                'categoria'      => ['Categoria', 'select', ['options' => cat_options(CATEGORIAS_PROGRAMA), 'default' => 'formacao']],
                'publico'        => ['Público', 'text', ['help' => 'Ex.: Crianças e adolescentes', 'max' => 80]],
                'resumo'         => ['Resumo', 'textarea', ['required' => true, 'max' => 260]],
                'conteudo'       => ['Descrição completa', 'rich'],
                'imagem'         => ['Foto', 'image', ['help' => 'Horizontal, de preferência 1600×900.']],
                'link_inscricao' => ['Link de inscrição', 'url', ['help' => 'Formulário externo, se houver.']],
                'ordem'          => ['Ordem', 'int', ['default' => 0]],
                'ativo'          => ['Visível no site', 'bool', ['default' => 1]],
            ],
        ],
        'eventos' => [
            'label' => 'Eventos', 'singular' => 'evento', 'icon' => 'calendar',
            'help' => 'Eventos com data. Os que já passaram saem da agenda e vão para "Já aconteceu".',
            'order' => 'data DESC, id DESC', 'slug' => 'titulo', 'list' => ['titulo', 'data', 'local', 'ativo'],
            'fields' => [
                'titulo'         => ['Nome do evento', 'text', ['required' => true, 'max' => 120]],
                'data'           => ['Data', 'date', ['required' => true]],
                'hora'           => ['Horário', 'text', ['help' => 'Ex.: 14h às 17h', 'max' => 40]],
                'local'          => ['Local', 'text', ['max' => 140]],
                'categoria'      => ['Categoria', 'select', ['options' => cat_options(CATEGORIAS_PROGRAMA), 'default' => 'extensao']],
                'resumo'         => ['Resumo', 'textarea', ['required' => true, 'max' => 260]],
                'conteudo'       => ['Descrição completa', 'rich'],
                'imagem'         => ['Foto ou arte', 'image'],
                'link_inscricao' => ['Link de inscrição', 'url'],
                'gratuito'       => ['Evento gratuito', 'bool', ['default' => 1]],
                'ativo'          => ['Visível no site', 'bool', ['default' => 1]],
            ],
        ],
        'posts' => [
            'label' => 'Blog', 'singular' => 'publicação', 'icon' => 'newspaper',
            'help' => 'Notícias e artigos. Não há limite: publique quantas quiser.',
            'order' => 'data DESC, id DESC', 'slug' => 'titulo', 'list' => ['titulo', 'categoria', 'data', 'publicado'],
            'fields' => [
                'titulo'    => ['Título', 'text', ['required' => true, 'max' => 140]],
                'categoria' => ['Categoria', 'select', ['options' => cat_options(CATEGORIAS_BLOG), 'default' => 'comunidade']],
                'data'      => ['Data de publicação', 'date', ['required' => true, 'default' => 'today']],
                'capa'      => ['Imagem de capa', 'image', ['help' => 'Horizontal, de preferência 1600×900.']],
                'resumo'    => ['Resumo', 'textarea', ['required' => true, 'max' => 280, 'help' => 'Aparece na listagem e no Google.']],
                'conteudo'  => ['Texto', 'rich', ['required' => true]],
                'autor'     => ['Autoria', 'text', ['max' => 80]],
                'publicado' => ['Publicado', 'bool', ['default' => 1]],
            ],
        ],
        'campanhas' => [
            'label' => 'Campanhas de doação', 'singular' => 'campanha', 'icon' => 'heart-handshake',
            'help' => 'Arrecadações em andamento. A marcada como destaque aparece na inicial.',
            'order' => 'destaque DESC, id DESC', 'list' => ['titulo', 'destaque', 'ativo'],
            'fields' => [
                'titulo'    => ['Nome da campanha', 'text', ['required' => true, 'max' => 120]],
                'texto'     => ['Descrição', 'textarea', ['required' => true, 'max' => 400]],
                'como_doar' => ['Como doar', 'textarea', ['help' => 'Itens aceitos, pontos de coleta, horários. Uma informação por linha.', 'max' => 800]],
                'imagem'    => ['Foto', 'image'],
                'destaque'  => ['Destaque na página inicial', 'bool', ['default' => 0]],
                'ativo'     => ['Visível no site', 'bool', ['default' => 1]],
            ],
        ],
        'parceiros' => [
            'label' => 'Parceiros', 'singular' => 'parceiro', 'icon' => 'handshake',
            'help' => 'Aparecem no carrossel da página inicial. Com logo fica melhor; sem logo, mostramos o nome.',
            'order' => 'ordem ASC, id ASC', 'list' => ['nome', 'logo', 'ativo'],
            'fields' => [
                'nome'  => ['Nome', 'text', ['required' => true, 'max' => 80]],
                'logo'  => ['Logo', 'image', ['help' => 'PNG com fundo transparente, se possível.']],
                'site'  => ['Site ou rede social', 'url'],
                'ordem' => ['Ordem', 'int', ['default' => 0]],
                'ativo' => ['Visível no site', 'bool', ['default' => 1]],
            ],
        ],
        'equipe' => [
            'label' => 'Equipe', 'singular' => 'pessoa', 'icon' => 'users',
            'help' => 'Aparece na página Sobre. A seção só aparece quando houver alguém cadastrado.',
            'order' => 'ordem ASC, id ASC', 'list' => ['nome', 'cargo', 'ativo'],
            'fields' => [
                'nome'  => ['Nome', 'text', ['required' => true, 'max' => 80]],
                'cargo' => ['Função', 'text', ['required' => true, 'max' => 80]],
                'foto'  => ['Foto', 'image', ['help' => 'Quadrada, rosto centralizado.']],
                'bio'   => ['Minibiografia', 'textarea', ['max' => 300]],
                'ordem' => ['Ordem', 'int', ['default' => 0]],
                'ativo' => ['Visível no site', 'bool', ['default' => 1]],
            ],
        ],
    ];
    return $m;
}

/* Configurações gerais: chave => [rótulo, tipo, opções]. Agrupadas por aba. */
function settings_schema(): array {
    return [
        'Institucional' => [
            'quem_somos'   => ['Texto "Quem somos"', 'textarea', ['help' => 'Aparece na inicial e na página Sobre.']],
            'ano_fundacao' => ['Ano de fundação', 'text'],
            'alcance'      => ['Pessoas alcançadas', 'text', ['help' => 'Só o número. Ex.: 1000']],
            'missao'       => ['Missão', 'textarea'],
            'visao'        => ['Visão', 'textarea'],
            'valores'      => ['Valores', 'textarea', ['help' => 'Um valor por linha, no formato: Nome | descrição']],
            'cnpj'         => ['CNPJ', 'text', ['help' => 'Aparece no rodapé quando preenchido.']],
        ],
        'Contato' => [
            'email'      => ['E-mail', 'text'],
            'telefone'   => ['Telefone', 'text'],
            'whatsapp'   => ['WhatsApp', 'text', ['help' => 'Com DDD, só números. Ex.: 81999999999']],
            'endereco'   => ['Endereço', 'textarea', ['help' => 'Rua, número, bairro, cidade/UF, CEP']],
            'horario'    => ['Horário de atendimento', 'text'],
            'mapa'       => ['Mapa (link de incorporação do Google Maps)', 'text', ['help' => 'No Google Maps: Compartilhar → Incorporar um mapa → copie só o endereço que está dentro de src="…"']],
            'email_form' => ['E-mail que recebe o formulário', 'text', ['help' => 'Se vazio, as mensagens ficam só no painel.']],
        ],
        'Redes sociais' => [
            'instagram' => ['Instagram', 'text', ['help' => 'Endereço completo do perfil']],
            'facebook'  => ['Facebook', 'text'],
            'youtube'   => ['YouTube', 'text'],
            'linkedin'  => ['LinkedIn', 'text'],
            'tiktok'    => ['TikTok', 'text'],
        ],
        'Doações' => [
            'pix_chave'    => ['Chave PIX', 'text'],
            'pix_titular'  => ['Titular da conta', 'text', ['help' => 'Nome que aparece no PIX, para o doador conferir.']],
            'banco'        => ['Dados bancários', 'textarea', ['help' => 'Banco, agência e conta. Uma informação por linha.']],
            'doacao_link'  => ['Link de doação online', 'text', ['help' => 'Plataforma de doação (cartão/boleto), se houver.']],
            'doacao_texto' => ['Texto sobre doações', 'textarea'],
        ],
        'Busca e compartilhamento' => [
            'seo_descricao' => ['Descrição do site no Google', 'textarea', ['help' => 'Até 160 caracteres.']],
        ],
    ];
}
