<?php
// Arquivo Central de Configuração de Planos e Permissões

function get_planos_config() {
    return [
        // Define as funcionalidades e quais planos podem acessá-las
        'permissoes' => [
            'processamento_ia' => ['essencial', 'enterprise'],
            'relatorios_avancados' => ['essencial', 'enterprise'],
            'analise_lucratividade' => ['enterprise'],
            'previsao_demanda' => ['enterprise']
        ],

        // Define os limites para cada plano
        'limites' => [
            'gratuito' => [
                'produtos' => 50,
                'fornecedores' => 10,
                'usuarios' => 1
            ],
            'essencial' => [
                'produtos' => 500,
                'fornecedores' => 100,
                'usuarios' => 5
            ],
            'enterprise' => [
                'produtos' => PHP_INT_MAX, // Representa infinito
                'fornecedores' => PHP_INT_MAX,
                'usuarios' => PHP_INT_MAX
            ],
            // Plano para quando o trial expira
            'trial_expirado' => [
                'produtos' => 0,
                'fornecedores' => 0,
                'usuarios' => 0
            ]
        ],

        // Detalhes dos planos para exibição
        'detalhes' => [
            'gratuito' => [
                'nome' => 'Plano Gratuito',
                'preco' => ['valor' => 'R$ 0', 'periodicidade' => '/mês'],
                'descricao' => 'Ideal para começar a organizar seu estoque sem custo.',
                'features' => [
                    'Até 50 produtos',
                    'Até 10 fornecedores',
                    '1 usuário',
                    'Relatórios básicos'
                ]
            ],
            'essencial' => [
                'nome' => 'Plano Essencial',
                'preco' => ['valor' => 'R$ 119', 'periodicidade' => '/mês'],
                'descricao' => 'Para negócios em crescimento que precisam de mais controle e automação.',
                'features' => [
                    'Até 500 produtos',
                    'Até 100 fornecedores',
                    '5 usuários',
                    'Processamento de Notas com IA',
                    'Relatórios avançados'
                ]
            ],
            'enterprise' => [
                'nome' => 'Plano Enterprise',
                'preco' => ['valor' => 'Sob Consulta', 'periodicidade' => ''],
                'descricao' => 'Soluções personalizadas para grandes empresas com necessidades complexas.',
                'features' => [
                    'Produtos e Fornecedores ilimitados',
                    'Usuários ilimitados',
                    'Análise de Lucratividade',
                    'Previsão de Demanda com IA',
                    'Suporte prioritário 24/7',
                    'Integrações customizadas'
                ]
            ]
        ]
    ];
}
?>