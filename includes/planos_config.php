<?php
// Arquivo Central de Configuração de Planos e Permissões

function get_planos_config() {
    return [
        // Define as funcionalidades e quais planos podem acessá-las
        'permissoes' => [
            'processamento_ia' => ['growth', 'enterprise'],
            'relatorios_avancados' => ['growth', 'enterprise'],
            'analise_lucratividade' => ['enterprise'],
            'previsao_demanda' => ['enterprise']
        ],

        // Define os limites para cada plano
        'limites' => [
            'iniciante' => [ // Plano MEI
                'produtos' => 100,
                'fornecedores' => 20,
                'usuarios' => 1
            ],
            'growth' => [ // Plano PME
                'produtos' => 1000,
                'fornecedores' => 200,
                'usuarios' => 10
            ],
            'enterprise' => [ // Plano Corporativo
                'produtos' => PHP_INT_MAX,
                'fornecedores' => PHP_INT_MAX,
                'usuarios' => PHP_INT_MAX
            ]
        ],

        // Detalhes dos planos para exibição
        'detalhes' => [
            'iniciante' => [
                'nome' => 'Plano MEI',
                'preco' => ['valor' => 'R$ 49,90', 'periodicidade' => '/mês'],
                'descricao' => 'Essencial para o microempreendedor organizar sua operação.',
                'features' => [
                    'Até 100 produtos',
                    'Até 20 fornecedores',
                    '1 usuário administrativo',
                    'Relatórios básicos de estoque'
                ]
            ],
            'growth' => [
                'nome' => 'Plano PME',
                'preco' => ['valor' => 'R$ 149,90', 'periodicidade' => '/mês'],
                'descricao' => 'Inteligência e automação para empresas em plena expansão.',
                'features' => [
                    'Até 1000 produtos',
                    'Até 200 fornecedores',
                    '10 usuários simultâneos',
                    'Entrada de Notas por IA',
                    'Relatórios gerenciais avançados'
                ]
            ],
            'enterprise' => [
                'nome' => 'Plano Corporativo',
                'preco' => ['valor' => 'R$ 499,90', 'periodicidade' => '/mês'],
                'descricao' => 'Potência total e análise preditiva para grandes volumes.',
                'features' => [
                    'Produtos e Fornecedores ilimitados',
                    'Usuários ilimitados',
                    'Análise de Lucratividade em tempo real',
                    'Previsão de Demanda com IA',
                    'Suporte prioritário 24/7'
                ]
            ]
        ]
    ];
}