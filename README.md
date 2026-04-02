# Gerenciador de Estoque (Multi-tenant)

Um sistema web completo para gerenciamento de estoque, produtos, compras e relatórios, desenvolvido em PHP e MySQL. O sistema foi projetado para um modelo de negócio **SaaS (Software as a Service)**, incluindo lógica para diferentes planos de assinatura. Agora com suporte a **múltiplas empresas (multi-tenant)**, permitindo que cada empresa gerencie seus dados de forma isolada. O projeto é focado em uma experiência de usuário intuitiva e responsiva, com dashboards informativos e automações para notificações.

## Funcionalidades

### Módulos Principais:

*   **Cadastro de Empresas e Usuários:** Permite que novos usuários registrem suas próprias empresas no sistema, tornando-se administradores da sua conta.
*   **Dashboard Administrativo:** Visão geral e estratégica do negócio com métricas chave e relatórios visuais, **filtrados por empresa**.
*   **Dashboard do Funcionário:** Focado na visualização de produtos e métricas operacionais, **filtrados por empresa**.
*   **Gestão de Produtos:** CRUD completo de produtos, **filtrados por empresa**.
*   **Gestão de Categorias:** CRUD completo para categorização dos produtos, **filtrado por empresa**.
*   **Gestão de Fornecedores:** CRUD completo de fornecedores, **filtrados por empresa**.
*   **Gestão de Usuários:** CRUD completo de usuários (apenas admin), **filtrados por empresa**.
*   **Gestão de Compras:** Registro de compras com múltiplos itens e cálculo de valor total, **filtrados por empresa**.
*   **Processamento de Nota Fiscal com IA:** Extração de dados de notas fiscais (PDF/imagem) anexadas a uma compra usando um script Python.
*   **Movimentações de Estoque:** Registro manual de entradas e saídas de produtos com histórico completo, **filtrados por empresa**.
*   **Relatórios:** Gráficos interativos (Chart.js) com filtros para análise de vendas e estoque, **filtrados por empresa**.
*   **Notificações Automatizadas:** Alertas para administradores sobre estoque baixo e produtos próximos ao vencimento, **filtrados por empresa**.

### Detalhes das Funcionalidades:

*   **Autenticação Segura:** Login **por e-mail** com hash de senha e níveis de acesso (administrador e funcionário).
*   **Recuperação de Senha:** Fluxo completo para redefinição de senha via código de verificação (simulado).
*   **Busca com Autocomplete:** API interna para agilizar a busca de produtos em formulários, **filtrada por empresa**.
*   **Validação de Estoque:** Lógica transacional para garantir consistência ao adicionar ou remover itens do estoque, **filtrada por empresa**.
*   **Visualização de Dados da IA:** Um pop-up na tela de compras permite visualizar os dados extraídos pela IA e a própria imagem da nota fiscal, facilitando a verificação.

## Tecnologias Utilizadas

*   **Backend:**
    *   PHP 7.4+ (PDO para acesso a banco de dados)
    *   Python (para scripts de IA)
*   **Banco de Dados:** MySQL
*   **Frontend:** HTML5, CSS3, JavaScript
*   **Framework CSS:** Bootstrap 5
*   **Ícones:** Font Awesome
*   **Gráficos:** Chart.js (com `chartjs-adapter-date-fns`)

## Configuração do Ambiente

### Pré-requisitos

*   Servidor Web (XAMPP, WAMP, etc.) com Apache e suporte a PHP.
*   PHP 7.4 ou superior.
*   MySQL 5.7 ou superior.
*   **Python** instalado e acessível pelo sistema (necessário para a funcionalidade de IA).
*   **Poppler** instalado e acessível no PATH do sistema (usado pelo script de IA para converter PDFs em imagens).
*   Extensão `PDO` do PHP para MySQL habilitada.

### 1. Configuração do Banco de Dados

1.  Crie um banco de dados MySQL vazio (ex: `gerenciador_estoque`).
2.  Edite o arquivo `includes/db_config.php` com suas credenciais de banco de dados:
    ```php
    <?php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'gerenciador_estoque');
    define('DB_USER', 'seu_usuario'); // ex: 'root'
    define('DB_PASS', 'sua_senha');   // ex: ''
    ?>
    ```
3.  Acesse o script de configuração inicial no seu navegador para criar todas as tabelas:
    *   `http://localhost/gerenciador_de_estoque/configurar_banco_dados.php`

### 2. Acesso ao Sistema

1.  Coloque a pasta do projeto no diretório raiz do seu servidor web (ex: `htdocs` no XAMPP).
2.  Acesse a página inicial: `http://localhost/gerenciador_de_estoque/`
3.  **Cadastro de Nova Empresa:** Clique em "Não tem uma conta? Crie uma agora" na página de login para registrar sua empresa e seu primeiro usuário administrador.
4.  **Login:** Utilize o e-mail e senha cadastrados para acessar o sistema.

### 3. Executando o Sistema para Testes Manuais

Existem duas formas práticas de rodar o sistema localmente em ambiente de testes ou desenvolvimento sem precisar configurar um servidor como o XAMPP manualmente:

#### Opção A: Usando Docker (Recomendado)
O projeto já conta com um esquema de containers configurado (`docker-compose.yml`) que cria automaticamente o servidor web com PHP, o banco de dados MariaDB e carrega o database inicial (`dump_real.sql.sql`).

1. Certifique-se de ter o [Docker Desktop](https://www.docker.com/) instalado e rodando em sua máquina.
2. Abra o terminal (Prompt de Comando ou PowerShell) e navegue até a pasta raiz do projeto (`gerenciador_de_estoque`).
3. Suba os containers rodando:
   ```bash
   docker-compose up -d
   ```
4. Acesse o sistema no seu navegador pelo endereço: **`http://localhost:8001`**
5. *(Opcional)* O painel do PHPMyAdmin para visualizar o banco estará em: `http://localhost:8080`
6. Para parar o ambiente após os testes, execute: `docker-compose down`

#### Opção B: Usando o Servidor Embutido do PHP
Se você não usa Docker, mas tem o PHP e o MySQL (via XAMPP ou independente) rodando localmente.

1. Finalize a criação e configuração do banco de dados (conforme o *Passo 1* acima).
2. Abra o terminal na pasta raiz do projeto (`gerenciador_de_estoque`).
3. Inicie o servidor embutido do PHP escolhendo uma porta, por exemplo, a 8000:
   ```bash
   php -S localhost:8000
   ```
4. Acesse o sistema diretamente na raiz: **`http://localhost:8000`**
5. Pressione `Ctrl + C` no terminal para encerrar o teste manual.

## Estrutura do Projeto

```
gerenciador_de_estoque/
├── admin/                  # Módulo administrativo
│   ├── painel_admin.php    # Dashboard principal do admin
│   ├── produtos.php        # CRUD de Produtos
│   ├── fornecedores.php    # CRUD de Fornecedores
│   ├── compras.php         # Gestão de Compras
│   ├── usuarios.php        # CRUD de Usuários
│   ├── relatorios.php      # Relatórios com gráficos
│   ├── notificacoes.php    # Central de notificações
│   └── processar_nota_action.php # Dispara o script de IA
├── api/                    # Endpoints de API (AJAX)
├── assets/                 # Arquivos estáticos (CSS, JS, Imagens)
├── employee/               # Módulo do funcionário
│   ├── painel_funcionario.php # Dashboard do funcionário
│   ├── atualizar_estoque.php  # Formulário para entrada/saída manual
│   └── movimentacoes.php      # Histórico de movimentações
├── includes/               # Arquivos PHP reutilizáveis
├── scripts/                # Scripts de automação (Python)
├── uploads/                # Diretório para uploads (notas fiscais)
├── index.php               # Página de login
├── home.php                # Landing page de apresentação
├── login.php               # Lógica de autenticação
├── registrar.php           # Formulário de cadastro de nova empresa/usuário
├── registrar_action.php    # Lógica de processamento do cadastro
├── configurar_banco_dados.php # Script de setup do banco de dados
└── README.md
```

## Esquema do Banco de Dados

O sistema utiliza um banco de dados relacional MySQL com a seguinte estrutura, adaptada para multi-tenancy:

*   **`empresas`**: `id`, `name`, `owner_user_id`, `created_at`.
*   **`usuarios`**: `id`, `empresa_id` (FK), `username`, `password`, `email` (UNIQUE), `user_type`, `plan`, `trial_ends_at`, `subscription_status`, `created_at`.
*   **`fornecedores`**: `id`, `empresa_id` (FK), `name`, `contact_person`, `phone`, `email`, `address`, `created_at`.
*   **`categorias`**: `id`, `empresa_id` (FK), `nome`, `created_at`.
*   **`produtos`**: `id`, `empresa_id` (FK), `name`, `description`, `price`, `cost_price`, `quantity`, `minimum_stock`, `categoria_id` (FK), `lote`, `validade`, `observacoes`, `created_at`.
*   **`compras`**: `id`, `empresa_id` (FK), `supplier_id` (FK), `user_id` (FK), `purchase_date`, `total_amount`, `fiscal_note_path`, `created_at`.
*   **`itens_compra`**: `id`, `purchase_id` (FK), `product_id` (FK), `quantity`, `unit_price`, `created_at`.
*   **`dados_nota_fiscal`**: `compra_id` (FK), `status`, `numero_nota`, `data_emissao`, `valor_total`, `chave_acesso_nfde`, `cnpj_tomador_servico`, `codigo_tributacao_nacional`, e outros campos para depuração e dados brutos.
*   **`historico_estoque`**: `id`, `empresa_id` (FK), `product_id` (FK), `user_id` (FK), `action`, `quantity`, `created_at`.
*   **`redefinicoes_senha`**: `email` (PK), `code`, `created_at`.
*   **`notificacoes`**: `id`, `empresa_id` (FK), `type`, `message`, `product_id` (FK), `is_read`, `created_at`.
*   **`leads`**: `id`, `name`, `email` (UNIQUE), `created_at`.
