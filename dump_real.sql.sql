-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 18/03/2026 às 19:42
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gerenciador_estoque`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `ai_agents`
--

CREATE TABLE `ai_agents` (
  `id` int(11) UNSIGNED NOT NULL,
  `empresa_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `model` varchar(50) DEFAULT 'gemini-pro',
  `system_instruction` text DEFAULT NULL,
  `temperature` decimal(3,2) DEFAULT 0.70,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ai_agents`
--

INSERT INTO `ai_agents` (`id`, `empresa_id`, `name`, `role`, `model`, `system_instruction`, `temperature`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Max-financeiro', 'financeiro', 'gemini-2.5-flash', 'vocÊ é o especialista financeiro e precisa realizar a tarefas do financeiro da empresa', 0.40, 'active', '2025-12-29 11:09:03', '2025-12-29 12:07:24'),
(2, 1, 'Maxssuel', 'Gerente estoque', 'gemini-2.5-flash', 'você é o gerente do estoque e vai supervisionar os employee como estrada e saida de produtos e garantir uma boa saude do estoque', 1.00, 'active', '2025-12-29 11:25:01', '2025-12-29 12:07:24'),
(3, 1, 'Growth Manager', 'Estrategista de Negócios', 'gemini-2.5-pro', 'Você é um estrategista de negócios sênior focado em e-commerce e varejo. Seu objetivo é analisar dados financeiros e de vendas para encontrar oportunidades de redução de custos (CAC) e aumento de margem de lucro. Seja direto, use termos técnicos de negócios (LTV, Churn, ROI) mas explique-os. Sempre que possível, cruze dados de vendas com estoque.', 0.50, 'active', '2025-12-29 18:20:01', '2025-12-29 18:20:01'),
(4, 1, 'SEO Specialist', 'Especialista em Marketing', 'gemini-2.5-flash', 'Você é um especialista em SEO para e-commerce. Sua função é criar títulos de produtos atraentes, descrições ricas em palavras-chave e sugerir tags para melhorar o ranqueamento no Google. Ao analisar um produto, foque em: Palavras-chave de cauda longa, Benefícios principais e Gatilhos mentais de compra.', 0.70, 'active', '2025-12-29 18:20:01', '2025-12-29 18:20:01'),
(5, 1, 'Trend Hunter', 'Pesquisador de Produtos', 'gemini-2.5-flash', 'Você é um analista de tendências de mercado. Analise os produtos mais vendidos da loja e identifique padrões de consumo. Sugira promoções para produtos parados (Estoque > Vendas) e estratégias de upsell para os campeões de venda. Use a Curva ABC como base teórica.', 0.40, 'active', '2025-12-29 18:20:01', '2025-12-29 18:20:01'),
(6, 1, 'Sarah (Secretária)', 'Assistente Executiva', 'gemini-2.5-flash', 'Você é Sarah, uma secretária executiva eficiente e educada. Sua função é organizar informações, formatar e-mails corporativos, resumir reuniões e preparar pautas. Mantenha um tom extremamente profissional, polido e prestativo. Nunca invente dados, apenas formate o que for fornecido.', 0.30, 'active', '2025-12-29 18:20:01', '2025-12-29 18:20:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ai_agent_logs`
--

CREATE TABLE `ai_agent_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `agent_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `input_tokens` int(11) DEFAULT 0,
  `output_tokens` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ai_agent_logs`
--

INSERT INTO `ai_agent_logs` (`id`, `agent_id`, `user_id`, `input_tokens`, `output_tokens`, `created_at`) VALUES
(1, 2, 1, 380, 14, '2025-12-29 12:29:17'),
(2, 2, 1, 396, 14, '2025-12-29 12:29:42'),
(3, 1, 1, 354, 71, '2025-12-29 12:31:27'),
(4, 1, 1, 435, 36, '2025-12-29 12:31:45'),
(5, 1, 1, 360, 12, '2025-12-29 18:18:15'),
(6, 5, 1, 423, 231, '2025-12-29 18:23:02'),
(7, 5, 1, 1316, 1466, '2025-12-29 18:24:12'),
(8, 5, 1, 2076, 340, '2025-12-29 18:25:31'),
(9, 5, 1, 401, 204, '2025-12-30 09:46:10'),
(10, 5, 1, 612, 55, '2025-12-30 09:46:30'),
(11, 5, 1, 672, 12, '2025-12-30 09:46:56'),
(12, 3, 1, 418, 271, '2025-12-31 19:32:35'),
(13, 3, 1, 695, 314, '2025-12-31 19:33:07'),
(14, 3, 1, 3, 225, '2026-01-02 11:30:52'),
(15, 4, 1, 410, 218, '2026-01-14 18:26:16'),
(16, 4, 1, 638, 189, '2026-01-14 18:27:12'),
(17, 4, 1, 853, 14, '2026-01-14 18:28:33'),
(18, 2, 1, 374, 89, '2026-01-17 23:42:35'),
(19, 2, 1, 512, 43, '2026-02-19 15:19:59'),
(20, 2, 1, 435, 127, '2026-02-19 15:21:10'),
(21, 3, 1, 43, 223, '2026-02-19 15:36:13'),
(22, 3, 1, 40, 0, '2026-02-19 15:44:14'),
(23, 3, 1, 40, 0, '2026-02-19 15:44:54'),
(24, 3, 1, 24, 1942, '2026-02-19 16:02:06'),
(25, 5, 1, 43, 156, '2026-02-19 16:14:04');

-- --------------------------------------------------------

--
-- Estrutura para tabela `analise_tributaria`
--

CREATE TABLE `analise_tributaria` (
  `id` int(11) UNSIGNED NOT NULL,
  `compra_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED DEFAULT NULL,
  `item_name_xml` varchar(255) DEFAULT NULL,
  `ncm_detectado` varchar(20) DEFAULT NULL,
  `cfop_entrada` varchar(10) DEFAULT NULL,
  `cst_csosn_entrada` varchar(10) DEFAULT NULL,
  `alert_level` enum('info','warning','critical','ok') NOT NULL DEFAULT 'info',
  `ai_suggestion` text DEFAULT NULL,
  `savings_potential` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `descricao` varchar(100) DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) DEFAULT 1,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `api_keys`
--

INSERT INTO `api_keys` (`id`, `empresa_id`, `api_key`, `descricao`, `permissions`, `is_active`, `last_used_at`, `created_at`) VALUES
(1, 1, 'sk_test_1234567890abcdef', 'Chave de Teste - Desenvolvimento', '[\"crm:read\", \"crm:write\", \"fiscal:read\"]', 1, NULL, '2026-01-29 04:05:19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `api_logs`
--

CREATE TABLE `api_logs` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `endpoint` varchar(100) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `request_body` text DEFAULT NULL,
  `response_code` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `avisos_globais`
--

CREATE TABLE `avisos_globais` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `tipo` enum('info','warning','success','danger') DEFAULT 'info',
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `avisos_globais`
--

INSERT INTO `avisos_globais` (`id`, `titulo`, `mensagem`, `tipo`, `active`, `created_at`) VALUES
(1, 'Nova atualização galera!!', 'Vai dar super certo, todos vamos evoluir. Feliz ano novo, Deus abençõe', 'info', 0, '2025-12-29 20:12:35'),
(2, 'BORA GALERA ANO NOVO VIDA NOVA', 'PRA CIMAAAAAAAA 2026 É NOSSO', 'success', 0, '2025-12-31 11:47:41'),
(3, 'Nova integrações com Marketplace: Mercado Livre disponivel.', 'Nova integrações com Marketplace: Mercado Livre disponivel.', 'success', 1, '2026-02-19 23:42:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cargos`
--

CREATE TABLE `cargos` (
  `id` int(11) NOT NULL,
  `setor_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `nivel_hierarquia` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `cargos`
--

INSERT INTO `cargos` (`id`, `setor_id`, `nome`, `nivel_hierarquia`, `created_at`) VALUES
(1, 1, 'motoboy', 1, '2026-01-27 09:21:20'),
(2, 1, 'Desenvolvedor', 1, '2026-01-27 09:24:14'),
(3, 2, 'Desenvolvedor', 1, '2026-01-27 16:54:12'),
(4, 2, 'gerente', 1, '2026-02-16 06:39:13');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) UNSIGNED NOT NULL,
  `empresa_id` int(11) UNSIGNED NOT NULL,
  `nome` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `empresa_id`, `nome`, `created_at`) VALUES
(1, 1, 'Bebidas', '2025-12-01 14:40:30'),
(2, 1, 'Salgados', '2025-12-01 14:40:36'),
(3, 1, 'Doces', '2025-12-01 14:40:42'),
(4, 1, 'Eletrodomésticos', '2026-01-18 02:43:37');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamados_suporte`
--

CREATE TABLE `chamados_suporte` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `resposta` text DEFAULT NULL,
  `status` enum('aberto','respondido','fechado') DEFAULT 'aberto',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chamados_suporte`
--

INSERT INTO `chamados_suporte` (`id`, `empresa_id`, `assunto`, `mensagem`, `resposta`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Dúvida Técnica', 'lindao', 'voce que é lindao', 'fechado', '2025-12-30 13:02:54', '2025-12-31 14:46:59'),
(2, 1, 'Sugestão de Melhoria', 'acho que é legal ser possivel poder mandar imagem do bug por aqui\r\n', NULL, 'fechado', '2025-12-31 15:36:56', '2026-02-20 02:41:09'),
(3, 1, 'Reportar Bug', 'poiuyh', NULL, 'fechado', '2026-02-16 06:27:44', '2026-02-20 02:41:05');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `tipo` enum('PF','PJ') DEFAULT 'PF',
  `cpf_cnpj` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `empresa_id`, `nome`, `tipo`, `cpf_cnpj`, `email`, `telefone`, `endereco`, `cidade`, `estado`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Fernando Lombardo Junior', 'PF', '49966585842', 'jr.lombardo@hotmail.com', '11953228925', 'Rua Joaquim Pires Cerveira, 89', NULL, NULL, 'ativo', '2026-01-29 03:28:22', '2026-01-29 03:28:22'),
(2, 1, 'Fernando Lombardo Junior', 'PF', '49966585842', 'jr.lombardo@hotmail.com', '11953228925', 'Rua Joaquim Pires Cerveira, 89', NULL, NULL, 'ativo', '2026-01-29 03:32:24', '2026-01-29 03:32:24'),
(3, 1, 'Amanda Priscila dos Santos Lombardo', 'PF', '499665858429', 'amandaprisciladossantos@yahoo.com.br', '11937831667', 'Rua Joaquim Pires Cerveira, 89', NULL, NULL, 'ativo', '2026-01-29 03:33:46', '2026-01-29 03:33:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `compras`
--

CREATE TABLE `compras` (
  `id` int(11) UNSIGNED NOT NULL,
  `empresa_id` int(11) UNSIGNED NOT NULL,
  `supplier_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `purchase_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `fiscal_note_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `compras`
--

INSERT INTO `compras` (`id`, `empresa_id`, `supplier_id`, `user_id`, `purchase_date`, `total_amount`, `fiscal_note_path`, `created_at`) VALUES
(1, 1, 1, 1, '2025-12-01', 2698.00, 'uploads/compra_692dab492e52d.pdf', '2025-12-01 14:50:49'),
(2, 1, 3, 1, '2025-12-01', 220.00, 'uploads/compra_692db882b1c0a.pdf', '2025-12-01 15:47:14'),
(3, 1, 2, 1, '2025-12-01', 500.00, 'uploads/compra_692dbad5126f3.pdf', '2025-12-01 15:57:09'),
(4, 1, 1, 1, '2025-12-03', 20000.00, 'uploads/compra_692f76d158a16.pdf', '2025-12-02 23:31:29'),
(5, 1, 1, 1, '2025-12-07', 4000.00, 'uploads/compra_6935813dd461f.pdf', '2025-12-07 13:29:33'),
(6, 1, 1, 1, '2025-12-15', 2.00, 'uploads/compra_694092a5a9b22.pdf', '2025-12-15 22:58:45'),
(7, 1, 2, 1, '2025-12-16', 2.00, 'uploads/compra_69409d3e7aa10.pdf', '2025-12-15 23:43:58'),
(8, 1, 3, 1, '2026-01-18', 1.00, 'uploads/compra_696c4e3328bb6.pdf', '2026-01-18 03:06:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `crm_etapas`
--

CREATE TABLE `crm_etapas` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `ordem` int(11) NOT NULL DEFAULT 1,
  `cor_hex` varchar(7) DEFAULT '#e9ecef',
  `is_final` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `crm_etapas`
--

INSERT INTO `crm_etapas` (`id`, `empresa_id`, `nome`, `ordem`, `cor_hex`, `is_final`, `created_at`) VALUES
(1, 1, 'Prospecção', 1, '#6c757d', 0, '2026-01-29 03:27:46'),
(2, 1, 'Qualificação', 2, '#0dcaf0', 0, '2026-01-29 03:27:46'),
(3, 1, 'Negociação', 3, '#ffc107', 0, '2026-01-29 03:27:46'),
(4, 1, 'Fechamento', 4, '#198754', 0, '2026-01-29 03:27:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `crm_oportunidades`
--

CREATE TABLE `crm_oportunidades` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `titulo` varchar(100) NOT NULL,
  `valor_estimado` decimal(10,2) DEFAULT 0.00,
  `etapa_id` int(11) NOT NULL,
  `responsavel_id` int(11) DEFAULT NULL,
  `origem` varchar(50) DEFAULT NULL,
  `status` enum('aberto','ganho','perdido') DEFAULT 'aberto',
  `data_fechamento` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `crm_oportunidades`
--

INSERT INTO `crm_oportunidades` (`id`, `empresa_id`, `cliente_id`, `titulo`, `valor_estimado`, `etapa_id`, `responsavel_id`, `origem`, `status`, `data_fechamento`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'colheita', 1000.00, 4, 1, NULL, 'aberto', NULL, '2026-01-29 03:58:59', '2026-01-29 04:09:09'),
(2, 1, 3, 'show', 100000.00, 4, 11, NULL, 'aberto', NULL, '2026-01-29 04:09:41', '2026-01-29 04:09:43');

-- --------------------------------------------------------

--
-- Estrutura para tabela `dados_nota_fiscal`
--

CREATE TABLE `dados_nota_fiscal` (
  `compra_id` int(11) UNSIGNED NOT NULL,
  `status` enum('pendente','processado','erro') NOT NULL DEFAULT 'pendente',
  `numero_nota` varchar(255) DEFAULT NULL,
  `data_emissao` date DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `nome_fornecedor` varchar(255) DEFAULT NULL,
  `cnpj_fornecedor` varchar(50) DEFAULT NULL,
  `itens_json` text DEFAULT NULL,
  `texto_completo` text DEFAULT NULL,
  `raw_ai_response` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `dados_nota_fiscal`
--

INSERT INTO `dados_nota_fiscal` (`compra_id`, `status`, `numero_nota`, `data_emissao`, `valor_total`, `nome_fornecedor`, `cnpj_fornecedor`, `itens_json`, `texto_completo`, `raw_ai_response`, `updated_at`) VALUES
(8, 'pendente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-18 03:06:38');

-- --------------------------------------------------------

--
-- Estrutura para tabela `dashboard_layouts`
--

CREATE TABLE `dashboard_layouts` (
  `user_id` int(11) NOT NULL,
  `layout_json` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `dashboard_layouts`
--

INSERT INTO `dashboard_layouts` (`user_id`, `layout_json`, `updated_at`) VALUES
(1, '{\"row1\":[\"financeiro_revenue\",\"financeiro_profit\"],\"row2\":[\"sales_chart\",\"setores_card\",\"estoque_saude\"]}', '2026-02-24 18:59:38');

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `owner_user_id` int(11) UNSIGNED NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `openai_api_key` varchar(255) DEFAULT NULL,
  `gemini_api_key` varchar(255) DEFAULT NULL,
  `ai_plan` enum('free','growth','enterprise') DEFAULT 'free',
  `ai_token_limit` int(11) DEFAULT 100000,
  `ai_tokens_used_month` int(11) DEFAULT 0,
  `max_users` int(11) DEFAULT 1,
  `support_level` enum('community','priority','dedicated') DEFAULT 'community',
  `plan_expires_at` timestamp NULL DEFAULT NULL,
  `branding_primary_color` varchar(7) DEFAULT '#2563eb',
  `branding_secondary_color` varchar(7) DEFAULT '#1e293b',
  `branding_bg_style` varchar(50) DEFAULT 'original'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresas`
--

INSERT INTO `empresas` (`id`, `name`, `owner_user_id`, `address`, `phone`, `email`, `cnpj`, `website`, `logo_path`, `created_at`, `openai_api_key`, `gemini_api_key`, `ai_plan`, `ai_token_limit`, `ai_tokens_used_month`, `max_users`, `support_level`, `plan_expires_at`, `branding_primary_color`, `branding_secondary_color`, `branding_bg_style`) VALUES
(1, 'stockwise', 1, 'Rua Joaquim Pires Cerveira, 89', '11953228925', 'amandaprisciladossantos@yahoo.com.br', '56069898000109', 'https://www.stockwise.com/', 'uploads/logos/cc6be785d31bef2f.png', '2025-12-01 14:39:48', NULL, 'AIzaSyAdTuN4YPRb4rRNSTOJExL6lhyS0pog0fs', 'free', 100000, 16519, 1, 'community', NULL, '#2563eb', '#1e293b', 'original'),
(2, 'ASSAI ATACADISTA', 4, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 18:35:49', NULL, NULL, 'free', 100000, 0, 1, 'community', NULL, '#2563eb', '#1e293b', 'original'),
(4, 'NEWSPACE', 9, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-30 13:32:10', NULL, NULL, 'growth', 2000000, 0, 5, 'priority', NULL, '#2563eb', '#1e293b', 'original'),
(5, 'adega do boy', 12, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-14 17:18:16', NULL, NULL, 'free', 100000, 0, 1, 'community', NULL, '#2563eb', '#1e293b', 'original'),
(6, 'SISTEMA CENTRAL', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-20 02:39:47', NULL, NULL, 'enterprise', 100000, 0, 1, 'community', NULL, '#2563eb', '#1e293b', 'original'),
(7, 'Inteligentegestor', 14, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-26 03:45:10', NULL, NULL, 'free', 100000, 0, 1, 'community', NULL, '#2563eb', '#1e293b', 'original');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fin_categorias`
--

CREATE TABLE `fin_categorias` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `tipo` enum('receita','despesa') NOT NULL,
  `cor_hex` varchar(7) DEFAULT '#6c757d',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `fin_categorias`
--

INSERT INTO `fin_categorias` (`id`, `empresa_id`, `nome`, `tipo`, `cor_hex`, `created_at`) VALUES
(1, 1, 'Vendas de Produtos', 'receita', '#28a745', '2026-01-29 03:43:51'),
(2, 1, 'Serviços Prestados', 'receita', '#20c997', '2026-01-29 03:43:51'),
(3, 1, 'Salários', 'despesa', '#dc3545', '2026-01-29 03:43:51'),
(4, 1, 'Aluguel', 'despesa', '#fd7e14', '2026-01-29 03:43:51'),
(5, 1, 'Fornecedores', 'despesa', '#6f42c1', '2026-01-29 03:43:51'),
(6, 1, 'Impostos', 'despesa', '#343a40', '2026-01-29 03:43:51');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fin_movimentacoes`
--

CREATE TABLE `fin_movimentacoes` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `tipo` enum('receita','despesa') NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `status` enum('pendente','pago','atrasado','cancelado') DEFAULT 'pendente',
  `obs` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `fiscal_impostos`
--

CREATE TABLE `fiscal_impostos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `aliquota_padrao` decimal(5,2) DEFAULT 0.00,
  `descricao` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `fiscal_impostos`
--

INSERT INTO `fiscal_impostos` (`id`, `empresa_id`, `nome`, `aliquota_padrao`, `descricao`, `created_at`) VALUES
(1, 1, 'ICMS', 18.00, 'Imposto sobre Circulação de Mercadorias', '2026-01-29 03:50:45'),
(2, 1, 'ISS', 5.00, 'Imposto Sobre Serviços', '2026-01-29 03:50:45'),
(3, 1, 'IPI', 0.00, 'Imposto sobre Produtos Industrializados', '2026-01-29 03:50:45'),
(4, 1, 'PIS', 1.65, 'Programa de Integração Social', '2026-01-29 03:50:45'),
(5, 1, 'COFINS', 7.60, 'Contribuição para Financiamento da Seguridade Social', '2026-01-29 03:50:45');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fiscal_notas`
--

CREATE TABLE `fiscal_notas` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `serie` varchar(5) DEFAULT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `modelo` enum('nfe','nfse','cte','cupom') DEFAULT 'nfe',
  `chave_acesso` varchar(44) DEFAULT NULL,
  `emitente_destinatario` varchar(150) DEFAULT NULL,
  `cpf_cnpj` varchar(20) DEFAULT NULL,
  `data_emissao` date NOT NULL,
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `valor_impostos` decimal(10,2) DEFAULT 0.00,
  `status` enum('autorizada','cancelada','denegada','rascunho') DEFAULT 'rascunho',
  `xml_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `icms_base` decimal(10,2) DEFAULT 0.00,
  `icms_valor` decimal(10,2) DEFAULT 0.00,
  `ipi_valor` decimal(10,2) DEFAULT 0.00,
  `pis_valor` decimal(10,2) DEFAULT 0.00,
  `cofins_valor` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedores`
--

CREATE TABLE `fornecedores` (
  `id` int(11) UNSIGNED NOT NULL,
  `empresa_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fornecedores`
--

INSERT INTO `fornecedores` (`id`, `empresa_id`, `name`, `contact_person`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 1, 'ATACADÃO', 'Rodolfo', '11937831667', 'amandaprisciladossantos@yahoo.com.br', 'Rua Joaquim Pires Cerveira, 89', '2025-12-01 14:42:08'),
(2, 1, 'ASSAI ATACADISTA', 'Luana', '11937831667', 'amandaprisciladossantos@yahoo.com.br', 'Rua Joaquim Pires Cerveira, 89', '2025-12-01 15:46:09'),
(3, 1, 'PÃO DE AÇUCAR', 'Sara', '11953228925', 'testando@hotmail.com', 'Rua Joaquim Pires Cerveira, 89', '2025-12-01 15:46:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_estoque`
--

CREATE TABLE `historico_estoque` (
  `id` int(11) UNSIGNED NOT NULL,
  `empresa_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `action` enum('entrada','saida','ajuste') NOT NULL,
  `quantity` int(11) NOT NULL,
  `new_quantity` int(11) DEFAULT NULL,
  `venda_id` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `historico_estoque`
--

INSERT INTO `historico_estoque` (`id`, `empresa_id`, `product_id`, `user_id`, `action`, `quantity`, `new_quantity`, `venda_id`, `created_at`) VALUES
(1, 1, 1, 1, 'entrada', 100, 100, NULL, '2025-12-01 14:41:23'),
(2, 1, 2, 1, 'entrada', 100, NULL, NULL, '2025-12-01 14:50:49'),
(3, 1, 3, 1, 'entrada', 100, NULL, NULL, '2025-12-01 14:50:49'),
(4, 1, 4, 1, 'entrada', 100, NULL, NULL, '2025-12-01 14:50:49'),
(5, 1, 5, 1, 'entrada', 100, NULL, NULL, '2025-12-01 14:50:49'),
(6, 1, 6, 1, 'entrada', 100, NULL, NULL, '2025-12-01 14:50:49'),
(7, 1, 7, 1, 'entrada', 100, NULL, NULL, '2025-12-01 14:50:49'),
(8, 1, 8, 1, 'entrada', 100, NULL, NULL, '2025-12-01 14:50:49'),
(9, 1, 9, 1, 'entrada', 100, NULL, NULL, '2025-12-01 14:50:49'),
(10, 1, 10, 1, 'entrada', 100, NULL, NULL, '2025-12-01 14:50:49'),
(11, 1, 3, 2, 'saida', 90, 10, NULL, '2025-12-01 15:06:58'),
(12, 1, 1, 2, 'saida', 20, 80, 1, '2025-12-01 15:41:52'),
(13, 1, 3, 1, 'entrada', 100, NULL, NULL, '2025-12-01 15:47:14'),
(14, 1, 1, 2, 'saida', 50, NULL, 2, '2025-12-01 15:55:40'),
(15, 1, 5, 2, 'saida', 25, NULL, 2, '2025-12-01 15:55:40'),
(16, 1, 6, 2, 'saida', 50, NULL, 2, '2025-12-01 15:55:40'),
(17, 1, 8, 2, 'saida', 90, NULL, 2, '2025-12-01 15:55:40'),
(18, 1, 10, 2, 'saida', 60, NULL, 2, '2025-12-01 15:55:40'),
(19, 1, 8, 1, 'entrada', 100, NULL, NULL, '2025-12-01 15:57:09'),
(20, 1, 2, 3, 'saida', 100, NULL, 3, '2025-12-01 16:10:27'),
(21, 1, 3, 3, 'saida', 100, NULL, 3, '2025-12-01 16:10:27'),
(22, 1, 4, 3, 'saida', 80, NULL, 3, '2025-12-01 16:10:27'),
(23, 1, 5, 3, 'saida', 65, NULL, 3, '2025-12-01 16:10:27'),
(24, 1, 7, 3, 'saida', 88, NULL, 3, '2025-12-01 16:10:27'),
(25, 1, 9, 3, 'saida', 89, NULL, 3, '2025-12-01 16:10:27'),
(27, 1, 1, 2, 'entrada', 200, 230, NULL, '2025-12-02 02:06:52'),
(28, 1, 1, 2, 'saida', 200, NULL, 4, '2025-12-02 02:07:11'),
(29, 1, 1, 1, 'entrada', 10000, NULL, NULL, '2025-12-02 23:31:29'),
(30, 1, 1, 2, 'saida', 1000, NULL, 5, '2025-12-03 13:44:21'),
(31, 1, 5, 3, 'entrada', 1000, 1010, NULL, '2025-12-03 13:50:52'),
(32, 1, 5, 3, 'saida', 1000, NULL, 6, '2025-12-03 13:51:20'),
(33, 1, 1, 3, 'saida', 1000, NULL, 7, '2025-12-03 13:52:16'),
(34, 1, 4, 3, 'saida', 1, NULL, 7, '2025-12-03 13:52:17'),
(35, 1, 8, 3, 'saida', 100, NULL, 7, '2025-12-03 13:52:17'),
(36, 1, 1, 2, 'entrada', 10000, 18030, NULL, '2025-12-05 17:59:22'),
(37, 1, 1, 3, 'saida', 10000, NULL, 8, '2025-12-05 18:16:44'),
(38, 1, 2, 3, 'entrada', 200, 200, NULL, '2025-12-05 18:34:25'),
(39, 1, 1, 2, 'saida', 8000, NULL, 9, '2025-12-07 13:24:58'),
(40, 1, 5, 1, 'entrada', 1000, NULL, NULL, '2025-12-07 13:29:33'),
(41, 1, 5, 3, 'saida', 1000, NULL, 10, '2025-12-07 13:37:42'),
(42, 1, 2, 3, 'saida', 180, NULL, 11, '2025-12-10 14:38:51'),
(43, 1, 5, 3, 'entrada', 3000, 3010, NULL, '2025-12-10 15:02:07'),
(44, 1, 5, 3, 'saida', 2000, NULL, 12, '2025-12-10 15:02:24'),
(45, 1, 1, 3, 'saida', 2, NULL, 13, '2025-12-11 18:55:07'),
(46, 1, 8, 3, 'saida', 1, NULL, 13, '2025-12-11 18:55:07'),
(47, 1, 3, 3, 'entrada', 200, 210, NULL, '2025-12-11 18:59:29'),
(48, 1, 6, 2, 'saida', 1, NULL, 14, '2025-12-11 19:10:37'),
(49, 1, 1, 1, 'entrada', 1, NULL, NULL, '2025-12-15 22:58:45'),
(50, 1, 3, 2, 'saida', 5, NULL, 15, '2025-12-15 23:02:00'),
(51, 1, 8, 2, 'saida', 1, NULL, 15, '2025-12-15 23:02:00'),
(52, 1, 1, 2, 'entrada', 200, 229, NULL, '2025-12-15 23:02:59'),
(53, 1, 1, 3, 'saida', 5, NULL, 16, '2025-12-15 23:12:15'),
(54, 1, 8, 3, 'saida', 2, NULL, 16, '2025-12-15 23:12:15'),
(55, 1, 3, 3, 'entrada', 2000, 2205, NULL, '2025-12-15 23:13:16'),
(56, 1, 3, 3, 'entrada', 5, 2210, NULL, '2025-12-15 23:13:43'),
(57, 1, 12, 1, 'entrada', 100, 100, NULL, '2025-12-15 23:18:03'),
(58, 1, 1, 2, 'saida', 1, NULL, 17, '2025-12-15 23:37:47'),
(59, 1, 8, 2, 'saida', 6, NULL, 17, '2025-12-15 23:37:47'),
(60, 1, 1, 1, 'entrada', 1, NULL, NULL, '2025-12-15 23:43:58'),
(61, 1, 1, 2, 'saida', 1, NULL, 18, '2025-12-15 23:58:25'),
(62, 1, 6, 2, 'saida', 10, NULL, 18, '2025-12-15 23:58:25'),
(63, 1, 1, 2, 'saida', 1, NULL, 19, '2025-12-16 00:09:00'),
(64, 1, 7, 2, 'saida', 2, NULL, 19, '2025-12-16 00:09:00'),
(65, 1, 7, 2, 'entrada', 100, 110, NULL, '2025-12-16 00:10:48'),
(66, 1, 3, 3, 'saida', 1, NULL, 20, '2025-12-16 00:33:32'),
(67, 1, 6, 3, 'saida', 2, NULL, 20, '2025-12-16 00:33:32'),
(68, 1, 1, 3, 'entrada', 2000, 2222, NULL, '2025-12-16 00:35:52'),
(69, 1, 2, 3, 'saida', 2, NULL, 21, '2025-12-16 15:03:11'),
(70, 1, 1, 2, 'saida', 1000, NULL, 22, '2025-12-22 14:11:42'),
(71, 1, 1, 2, 'saida', 1, NULL, 23, '2025-12-31 22:45:01'),
(72, 1, 6, 2, 'saida', 1, NULL, 23, '2025-12-31 22:45:01'),
(73, 1, 12, 3, 'saida', 100, NULL, 24, '2026-01-14 23:33:22'),
(74, 1, 13, 1, 'entrada', 100, 100, NULL, '2026-01-18 02:45:18'),
(75, 1, 12, 3, 'entrada', 500, 500, NULL, '2026-01-18 02:46:28'),
(76, 1, 13, 3, 'saida', 100, NULL, 25, '2026-01-18 02:47:24'),
(77, 1, 8, 3, 'entrada', 50, 50, NULL, '2026-01-18 02:48:56'),
(78, 1, 9, 3, 'entrada', 50, 61, NULL, '2026-01-18 02:49:30'),
(79, 1, 2, 3, 'entrada', 50, 68, NULL, '2026-01-18 02:50:00'),
(80, 1, 4, 3, 'entrada', 500, 519, NULL, '2026-01-18 02:50:17'),
(81, 1, 13, 3, 'entrada', 10, 10, NULL, '2026-01-18 02:50:39'),
(82, 1, 13, 1, 'saida', 1, NULL, 26, '2026-01-18 03:02:20'),
(83, 1, 1, 1, 'entrada', 1, NULL, NULL, '2026-01-18 03:06:27'),
(84, 1, 13, 3, '', 9, NULL, 31, '2026-02-20 01:47:42'),
(85, 1, 3, 3, '', 1, NULL, 32, '2026-02-20 02:03:53'),
(86, 1, 1, 3, '', 1, NULL, 33, '2026-02-20 02:10:27'),
(87, 1, 3, 3, '', 6, NULL, 34, '2026-02-21 13:41:36'),
(88, 1, 9, 3, '', 4, NULL, 35, '2026-02-24 18:43:13'),
(89, 1, 3, 3, '', 6, NULL, 36, '2026-03-01 16:00:43'),
(90, 1, 1, 3, '', 4, NULL, 36, '2026-03-01 16:00:43'),
(91, 1, 3, 1, '', 4, NULL, 37, '2026-03-16 18:23:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_compra`
--

CREATE TABLE `itens_compra` (
  `id` int(11) UNSIGNED NOT NULL,
  `purchase_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `stock_at_purchase` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `itens_compra`
--

INSERT INTO `itens_compra` (`id`, `purchase_id`, `product_id`, `quantity`, `unit_price`, `stock_at_purchase`, `created_at`) VALUES
(1, 1, 2, 100, 4.00, 0, '2025-12-01 14:50:49'),
(2, 1, 3, 100, 0.99, 0, '2025-12-01 14:50:49'),
(3, 1, 4, 100, 2.50, 0, '2025-12-01 14:50:49'),
(4, 1, 5, 100, 5.00, 0, '2025-12-01 14:50:49'),
(5, 1, 6, 100, 1.50, 0, '2025-12-01 14:50:49'),
(6, 1, 7, 100, 0.99, 0, '2025-12-01 14:50:49'),
(7, 1, 8, 100, 4.00, 0, '2025-12-01 14:50:49'),
(8, 1, 9, 100, 5.00, 0, '2025-12-01 14:50:49'),
(9, 1, 10, 100, 3.00, 0, '2025-12-01 14:50:49'),
(10, 2, 3, 100, 2.20, 10, '2025-12-01 15:47:14'),
(11, 3, 8, 100, 5.00, 10, '2025-12-01 15:57:09'),
(12, 4, 1, 10000, 2.00, 30, '2025-12-02 23:31:29'),
(13, 5, 5, 1000, 4.00, 10, '2025-12-07 13:29:33'),
(14, 6, 1, 1, 2.00, 28, '2025-12-15 22:58:45'),
(15, 7, 1, 1, 2.00, 223, '2025-12-15 23:43:58'),
(16, 8, 1, 1, 1.00, 1221, '2026-01-18 03:06:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `leads`
--

CREATE TABLE `leads` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `challenge` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `lotes`
--

CREATE TABLE `lotes` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) UNSIGNED NOT NULL,
  `numero_lote` varchar(50) NOT NULL,
  `data_validade` date DEFAULT NULL,
  `quantidade_inicial` int(11) NOT NULL,
  `quantidade_atual` int(11) NOT NULL,
  `fornecedor` varchar(100) DEFAULT NULL,
  `data_entrada` datetime DEFAULT current_timestamp(),
  `empresa_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `lotes`
--

INSERT INTO `lotes` (`id`, `produto_id`, `numero_lote`, `data_validade`, `quantidade_inicial`, `quantidade_atual`, `fornecedor`, `data_entrada`, `empresa_id`) VALUES
(1, 1, '9999999999999999999', '2025-11-28', 100, 0, 'rodraf', '2025-11-27 13:04:38', 1),
(2, 2, '878565259559', '2025-12-27', 250, 0, NULL, '2025-11-27 13:09:04', 1),
(3, 2, '9999999999999999999', '2025-11-28', 250, 50, 'rodraf', '2025-11-27 13:10:09', 1),
(4, 3, 'LOTE-INICIAL-20251201', NULL, 100, 10, NULL, '2025-12-01 11:10:26', 1),
(5, 4, 'LOTE-INICIAL-20251201', '2026-01-31', 100, 100, NULL, '2025-12-01 11:10:59', 1),
(6, 5, 'LOTE-INICIAL-20251201', NULL, 100, 100, NULL, '2025-12-01 11:11:44', 1),
(7, 6, 'LOTE-INICIAL-20251201', NULL, 100, 100, NULL, '2025-12-01 11:12:15', 1),
(8, 7, 'LOTE-INICIAL-20251201', NULL, 100, 100, NULL, '2025-12-01 11:13:05', 1),
(9, 8, 'LOTE-INICIAL-20251201', NULL, 100, 100, NULL, '2025-12-01 11:13:37', 1),
(10, 9, 'LOTE-INICIAL-20251201', NULL, 100, 100, NULL, '2025-12-01 11:14:20', 1),
(11, 1, 'LOTE-INICIAL-20251201', '2025-12-31', 100, 80, NULL, '2025-12-01 11:41:23', 1),
(13, 1, '878954896528', '2025-12-31', 200, 200, 'rodraf', '2025-12-01 23:06:52', 1),
(14, 5, '141235698745', '2025-12-31', 1000, 1000, 'Pão de açucar', '2025-12-03 10:50:52', 1),
(15, 1, '878989559522', NULL, 10000, 10000, 'Pão de açucar', '2025-12-05 14:59:22', 1),
(16, 2, '878954896528', '2026-01-10', 200, 200, 'Pão de açucar', '2025-12-05 15:34:25', 1),
(17, 5, '741262622', '2026-01-24', 3000, 3000, 'Pão de açucar', '2025-12-10 12:02:07', 1),
(18, 3, '548798524', '2026-04-04', 200, 200, 'Pão de açucar', '2025-12-11 15:59:29', 1),
(19, 1, '8745987456', '2025-12-17', 200, 200, 'Pão de açucar', '2025-12-15 20:02:59', 1),
(20, 3, '8', NULL, 2000, 2000, '', '2025-12-15 20:13:16', 1),
(21, 3, '878955', '2025-12-16', 5, 5, 'Pão de açucar', '2025-12-15 20:13:43', 1),
(22, 12, '874595459', '2026-03-15', 100, 100, NULL, '2025-12-15 20:18:03', 1),
(23, 7, '9999999999999999999', '2025-12-17', 100, 100, 'Pão de açucar', '2025-12-15 21:10:48', 1),
(24, 1, '878989559522', '2025-12-16', 2000, 2000, 'Pão de açucar', '2025-12-15 21:35:52', 1),
(25, 13, '7412368452', NULL, 100, 100, NULL, '2026-01-17 23:45:18', 1),
(26, 12, '2222555544448', '2026-01-31', 500, 500, 'Aguia de ouro', '2026-01-17 23:46:28', 1),
(27, 8, '7841565156', NULL, 50, 50, '', '2026-01-17 23:48:56', 1),
(28, 9, '323223323', NULL, 50, 50, '', '2026-01-17 23:49:30', 1),
(29, 2, '2112edws', NULL, 50, 50, '', '2026-01-17 23:50:00', 1),
(30, 4, '575152565158', NULL, 500, 500, '', '2026-01-17 23:50:17', 1),
(31, 13, '8754786954789', NULL, 10, 10, '', '2026-01-17 23:50:39', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `modulos`
--

CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `icone` varchar(50) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `modulos`
--

INSERT INTO `modulos` (`id`, `nome`, `slug`, `icone`, `descricao`, `created_at`) VALUES
(1, 'Estoque Inteligente', 'estoque', 'fas fa-boxes', 'Gestão de produtos, movimentações e inventário.', '2026-01-27 09:02:38'),
(2, 'Gestão Financeira', 'financeiro', 'fas fa-chart-line', 'Fluxo de caixa, DRE gerencial e contas.', '2026-01-27 09:02:38'),
(3, 'Recursos Humanos', 'rh', 'fas fa-users', 'Gestão de colaboradores, folhas e benefícios.', '2026-01-27 09:02:38'),
(4, 'CRM & Vendas', 'crm', 'fas fa-handshake', 'Gestão de clientes, leads e funil de vendas.', '2026-01-27 09:02:38'),
(5, 'Fiscal & Tributário', 'fiscal', 'fas fa-file-invoice-dollar', 'Emissão de NF-e e inteligência tributária.', '2026-01-27 09:02:38'),
(6, 'Frente de Caixa (PDV)', 'pdv', 'fas fa-cash-register', 'Acesso ao terminal de vendas e processamento de caixa.', '2026-02-19 23:29:12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacao_status_usuario`
--

CREATE TABLE `notificacao_status_usuario` (
  `id` int(11) NOT NULL,
  `notificacao_id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_dismissed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `notificacao_status_usuario`
--

INSERT INTO `notificacao_status_usuario` (`id`, `notificacao_id`, `user_id`, `is_read`, `is_dismissed`, `created_at`, `updated_at`) VALUES
(1, 76, 1, 1, 1, '2025-11-11 17:36:13', '2025-11-11 17:36:18'),
(3, 77, 1, 1, 1, '2025-11-11 17:36:16', '2025-11-11 17:36:18'),
(4, 78, 1, 1, 1, '2025-11-11 17:36:16', '2025-11-11 17:36:18'),
(5, 79, 1, 1, 1, '2025-11-11 17:36:16', '2025-11-11 17:36:18'),
(6, 80, 1, 1, 1, '2025-11-11 17:36:16', '2025-11-11 17:36:18'),
(7, 81, 1, 1, 1, '2025-11-11 17:36:16', '2025-11-11 17:36:18'),
(8, 82, 1, 1, 1, '2025-11-11 17:36:16', '2025-11-11 17:36:18'),
(9, 83, 1, 1, 1, '2025-11-11 17:36:16', '2025-11-11 17:36:18'),
(10, 84, 1, 1, 1, '2025-11-11 17:36:16', '2025-11-11 17:36:18'),
(54, 1, 3, 1, 1, '2025-11-22 06:38:24', '2026-02-16 06:39:58'),
(55, 1, 2, 1, 0, '2025-11-22 06:39:40', '2026-01-21 03:42:18'),
(56, 2, 2, 1, 0, '2025-11-24 23:10:02', '2026-01-21 03:42:18'),
(57, 3, 2, 1, 0, '2025-11-24 23:45:00', '2026-01-21 03:42:18'),
(59, 2, 3, 1, 1, '2025-11-24 23:45:17', '2026-02-16 06:39:58'),
(60, 3, 3, 1, 1, '2025-11-24 23:45:17', '2026-02-16 06:39:58'),
(61, 1, 1, 1, 1, '2025-11-27 16:09:10', '2026-02-20 02:29:06'),
(63, 2, 1, 1, 1, '2025-11-27 16:18:35', '2026-02-20 02:29:06'),
(64, 3, 1, 1, 1, '2025-11-27 16:18:35', '2026-02-20 02:29:06'),
(68, 5, 1, 1, 1, '2025-12-01 19:56:12', '2026-02-20 02:29:06'),
(73, 4, 1, 1, 1, '2025-12-02 02:05:02', '2026-02-20 02:29:06'),
(75, 6, 1, 1, 1, '2025-12-02 02:05:02', '2026-02-20 02:29:06'),
(76, 7, 1, 1, 1, '2025-12-02 02:05:02', '2026-02-20 02:29:06'),
(77, 8, 1, 1, 1, '2025-12-02 02:05:02', '2026-02-20 02:29:06'),
(89, 4, 2, 1, 0, '2025-12-02 02:06:19', '2026-01-21 03:42:18'),
(90, 5, 2, 1, 0, '2025-12-02 02:06:19', '2026-01-21 03:42:18'),
(91, 6, 2, 1, 0, '2025-12-02 02:06:19', '2026-01-21 03:42:18'),
(92, 7, 2, 1, 0, '2025-12-02 02:06:19', '2026-01-21 03:42:18'),
(93, 8, 2, 1, 0, '2025-12-02 02:06:19', '2026-01-21 03:42:18'),
(105, 4, 3, 1, 1, '2025-12-05 18:33:46', '2026-02-16 06:39:58'),
(106, 5, 3, 1, 1, '2025-12-05 18:33:46', '2026-02-16 06:39:58'),
(107, 6, 3, 1, 1, '2025-12-05 18:33:46', '2026-02-16 06:39:58'),
(108, 7, 3, 1, 1, '2025-12-05 18:33:46', '2026-02-16 06:39:58'),
(109, 8, 3, 1, 1, '2025-12-05 18:33:46', '2026-02-16 06:39:58'),
(118, 9, 1, 1, 1, '2026-01-14 23:33:50', '2026-02-20 02:29:06'),
(127, 9, 3, 1, 1, '2026-01-18 02:46:36', '2026-02-16 06:39:58'),
(173, 9, 2, 1, 1, '2026-01-21 03:42:15', '2026-01-21 03:42:22'),
(275, 1, 11, 1, 0, '2026-01-27 17:03:26', '2026-01-27 17:26:54'),
(276, 2, 11, 1, 0, '2026-01-27 17:03:26', '2026-01-27 17:26:54'),
(277, 3, 11, 1, 0, '2026-01-27 17:03:26', '2026-01-27 17:26:54'),
(278, 4, 11, 1, 0, '2026-01-27 17:03:26', '2026-01-27 17:26:54'),
(279, 5, 11, 1, 0, '2026-01-27 17:03:26', '2026-01-27 17:26:54'),
(280, 6, 11, 1, 0, '2026-01-27 17:03:26', '2026-01-27 17:26:54'),
(281, 7, 11, 1, 0, '2026-01-27 17:03:26', '2026-01-27 17:26:54'),
(282, 8, 11, 1, 0, '2026-01-27 17:03:26', '2026-01-27 17:26:54'),
(283, 9, 11, 1, 0, '2026-01-27 17:03:26', '2026-01-27 17:26:54'),
(338, 10, 1, 1, 1, '2026-02-14 19:13:39', '2026-02-20 02:29:06'),
(349, 10, 3, 1, 1, '2026-02-16 06:39:50', '2026-02-16 06:39:58'),
(390, 11, 3, 1, 0, '2026-02-20 01:48:04', '2026-02-20 01:48:04'),
(391, 11, 1, 1, 1, '2026-02-20 02:14:29', '2026-02-20 02:29:06');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `product_id` int(11) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `notificacoes`
--

INSERT INTO `notificacoes` (`id`, `empresa_id`, `type`, `message`, `product_id`, `is_read`, `created_at`) VALUES
(1, 1, 'nearing_expiration', 'Produto próximo ao vencimento: <b>Agua</b>. Vence em: 31/12/2025.', 1, 0, '2025-12-01 14:51:34'),
(2, 1, 'low_stock', 'Estoque baixo para o produto: <b>Agua com gás</b>. Quantidade atual: 10, mínimo: 20.', 3, 0, '2025-12-01 15:07:14'),
(3, 1, 'low_stock', 'Estoque baixo para o produto: <b>Pastel - Carne</b>. Quantidade atual: 10, mínimo: 20.', 8, 0, '2025-12-01 15:56:19'),
(4, 1, 'low_stock', 'Estoque baixo para o produto: <b>Suco de Laranja</b>. Quantidade atual: 0, mínimo: 20.', 2, 0, '2025-12-01 16:10:47'),
(5, 1, 'low_stock', 'Estoque baixo para o produto: <b>Coca-Cola</b>. Quantidade atual: 20, mínimo: 20.', 4, 0, '2025-12-01 16:10:48'),
(6, 1, 'low_stock', 'Estoque baixo para o produto: <b>chocolate</b>. Quantidade atual: 10, mínimo: 20.', 5, 0, '2025-12-01 16:10:48'),
(7, 1, 'low_stock', 'Estoque baixo para o produto: <b>Bala de Goma</b>. Quantidade atual: 12, mínimo: 20.', 7, 0, '2025-12-01 16:10:48'),
(8, 1, 'low_stock', 'Estoque baixo para o produto: <b>Batata Frita</b>. Quantidade atual: 11, mínimo: 20.', 9, 0, '2025-12-01 16:10:48'),
(9, 1, 'low_stock', 'Estoque baixo para o produto: <b>panetone</b>. Quantidade atual: 0, mínimo: 10.', 12, 0, '2026-01-14 23:33:32'),
(10, 1, 'nearing_expiration', 'Produto próximo ao vencimento: <b>panetone</b>. Vence em: 15/03/2026.', 12, 0, '2026-02-14 18:52:25'),
(11, 1, 'low_stock', 'Estoque baixo para o produto: <b>Geladeira LG iceclub</b>. Quantidade atual: 0, mínimo: 5.', 13, 0, '2026-02-20 01:47:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `external_ref` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) NOT NULL DEFAULT 'pix',
  `qr_code` text DEFAULT NULL,
  `qr_code_base64` text DEFAULT NULL,
  `plan_type` enum('growth','enterprise') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pagamentos`
--

INSERT INTO `pagamentos` (`id`, `empresa_id`, `external_ref`, `amount`, `status`, `payment_method`, `qr_code`, `qr_code_base64`, `plan_type`, `created_at`, `updated_at`) VALUES
(1, 1, '1325767768', 99.00, 'pending', 'pix', '00020126580014br.gov.bcb.pix0136b76aa9c2-2ec4-4110-954e-ebfe34f05b61520400005303986540599.005802BR5911WOcKZqIlKEN6012S?fIob xkSlo62230519mpqrinter13257677686304906C', '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', 'growth', '2025-12-30 13:53:06', '2025-12-30 13:53:06'),
(2, 1, '1343495807', 299.00, 'pending', 'pix', '00020126580014br.gov.bcb.pix0136b76aa9c2-2ec4-4110-954e-ebfe34f05b615204000053039865406299.005802BR5911WOcKZqIlKEN6012S?fIob xkSlo62230519mpqrinter13434958076304FC66', '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', 'enterprise', '2025-12-30 13:53:22', '2025-12-30 13:53:22'),
(3, 1, '1343495821', 299.00, 'pending', 'pix', '00020126580014br.gov.bcb.pix0136b76aa9c2-2ec4-4110-954e-ebfe34f05b615204000053039865406299.005802BR5911WOcKZqIlKEN6012S?fIob xkSlo62230519mpqrinter13434958216304BAA3', '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taBFq6BFq6BFq6BFq6B9I+2v/dlIovIdP9fIec4SX5plfShf26bk4XrDgjaDtlZo0apCi1YVWrSq0KJVhRatqg/T5tXWqN2vCljzlUrJKdu88xvZsLQBvrdXAhyOoUW7SrR70FrchlZBi1ZBi1ZBi1ZB+ze1X36hvd3KMz6vxcobbW9TtO4obVW/YC0VtNse2ghatApatApatApatApatMrba5/dE8kn8txozBu+2q75yhQbvv0x4tIK2q3hq+2ar9BG0OoGWrS6gRatbqBFqxto0eoG2rfUWvJFn5l7/9QPyhdrw5LuaLRuvba5PdFIAVo/jhatghatghatghatghatgvajtduQuve137IyUo/EO7mXgCxHd7txLi1o0Spo0Spo0Spo0Spo0Spo0SofrrWmz88nvvxWe9b34qc1mswn5o0sMzkqPhIt2rK3Jq+lVXEhkj20UWbQtsNo0aJFi7Z00aJFux3+e9qcbqkXtvtrrdIPp3GWdUBea0+2VX64762lVWjRqkKLVhVatKrQolWFFq0qtGhVfY7WRtTH2sx2ZPuq8S31idKwNKjlxDuMWku0o2FBW59AixYt2voEWrRo0dYn0KJ9W62lnkjPNml8QezVd5K3TclGdk+fZkWuVrdWX2jRRk4etGjRjilo0cYeWrRo0aL13bfUNpSdnfdrY3txfEvebSjL9pBnK+3U/i1raRVatKrQolWFFq0qtGhVoUWrCi1aVR+mzVv5Yh7wIRsvj1RyTImB9UbujM/YRrUVWrTZXUurImh790GLNoI2D7Qn0KLVEbRodQQtWh35m1q/H6ssPenenvBz89O8uxnr3bhRG20VAzxot6D1/TwRyS7aF0a0aPsqBnjQbkHr+3kikl20L4xo0fZVDPCg3YLW9y21qdIbcX+8sx22pDHPtXJ82qkce7muM72p0hv2RDTQznLs5brO9KZKb9gT0UA7y7GX6zrTmyq9YU9EA+0sx16u60xvqvSGPRENtLMce7muM72p0hv2RDTQznLs5brO9KZKb9gT0UA7y7GX6zrTmyq9YU9EA+0sx16u60xvqvSGPRENtLMce7muM72p0hv2RDTQznLs5brO9KZKb9gT0XgDbYyrpa1ieiqqJw63c5k2ub3hfLtre7na3kCLNoIWbVxbyz4TLVq0v/9BixYt2uyi/SDtdmysoptlMzp0SzZy3ri2DR0rtGjXDQ/aLWh9f55Aizb20PZr29CxQot23fCg3YLW9+cJtD+jzcfaynqjkTc2wOmcr/PZebd+VRuKFi1atGjH4AhaO7KWVsUTaNHqCbRo9QRatHriY7S1ub2Ynsk7DN6efeoXeBnJbu6N0vf26kGL1qoHLVqrHrRorXrQorXqQYvWqgctWqueD9J+HQCxqoDWfcZjvpfzkmyZsnouP3z8+dZSWffiaqzQRtCiXWU9hxYt2t590KKNoI0GWrRof+fPa9uFOjNWwx2T6irSGpl86OSuPy1oI62RyYcGCi1aBS1aBS1aBS1aBS1aBe0babd36mPBawC/kd1TotH+BK1REw/tgrVEu4I24uU4W4Zk/Aba/hraU9BGvBxny5CM30DbX0N7CtqIl+NsGZLxG2j7a2hPQRvxcpwtQzJ+409rT/GzbVLk/GJM972cEmmK7+/WoJ0voj0FLVoFLVoFLVoFLVoFLVrlfbV2omZTrAvbjZyeXxWUdvflqDal3t26aMeLaG2/Zb6Itk9B6zfQotUNtGh1Ay1a3UCLVjf+pjb3oxyTLDlzu5HnfJUvbm/XKdHIL203atDOF+sKbZRo0XbPeAft4W4N2vliXaGNEi3a7hnvoD3crUE7X6yr/2NtndRQNs6G/Nrfzmst2zy/lqhYnQe0uxa0GbQRtGgVtGgVtGgVtGgVtGiVf4O2ZWuk2zM/KI15agdoaDbq+HF4LdH6Hlq02kOLVnto0WoPLVrtoUWrPbT/Gu0s84YnoJZafvd97fBLvAftg9aC9kFrQfugtaB90FrQPmgtaB+0lg/XtnJAbWbG3s6f3PvaebnX3Kdrk+FBi1ZBi1ZBi1ZBi1ZBi1ZBi1b5ZG1LHLOiHbHp+S3ebaUleZGzp324jY8BHrRoFbRoFbRoFbRoFbRoFbRolc/Vvn/Q3gvae0F7L2jvBe29oL0XtPeC9l7Q3gvae0F7L2jvBe29oL0XtPeC9l7Q3gvae0F7L2jvBe29oL0XtPeC9l7Q3gvae0F7L2jvBe29oL0XtPeC9l7Q3gvae0F7L2jvBe29oL0XtPfyYdr/ANXfzm5AIXptAAAAAElFTkSuQmCC', 'enterprise', '2025-12-30 13:54:48', '2025-12-30 13:54:48'),
(4, 1, '1325767814', 299.00, 'pending', 'pix', '00020126580014br.gov.bcb.pix0136b76aa9c2-2ec4-4110-954e-ebfe34f05b615204000053039865406299.005802BR5911WOcKZqIlKEN6012S?fIob xkSlo62230519mpqrinter132576781463040BE3', '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', 'enterprise', '2025-12-30 13:58:27', '2025-12-30 13:58:27'),
(5, 1, '1325767912', 299.00, 'pending', 'pix', '00020126580014br.gov.bcb.pix0136b76aa9c2-2ec4-4110-954e-ebfe34f05b615204000053039865406299.005802BR5911WOcKZqIlKEN6012S?fIob xkSlo62230519mpqrinter132576791263047E07', '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', 'enterprise', '2025-12-30 14:03:05', '2025-12-30 14:03:05'),
(6, 1, '1325768030', 299.00, 'pending', 'pix', '00020126580014br.gov.bcb.pix0136b76aa9c2-2ec4-4110-954e-ebfe34f05b615204000053039865406299.005802BR5911WOcKZqIlKEN6012S?fIob xkSlo62230519mpqrinter1325768030630443CA', 'iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQAAAAB79iscAAAN00lEQVR4Xu3XUZJcOQqF4buD3v8uewc1YQ4IBErHTEzJnVn9n4e0JBD6br35+fqg/P30k3cO2ntBey9o7wXtvaC9F7T3gvZe0N4L2ntBey9o7wXtvaC9F7T3gvZe0N4L2ntBey9o7wXtvaC9F7T3gvZe0N4L2ntBey9o7wXtvaC9F7T3gvZe0N4L2ntBey9o7wXtvaC9F7T3gvZeqvbp+evXmf3E1vu2Qp7llN+vfPu3//jdWGWLJyajrWcvVr5Fi1ZbtGi1RYtWW7RotUWLVlu076vN89zakMcfqzNbNVbj7W1oGm3zX9xAi1ZBi1ZBi1ZBi1ZBi1ZBi1b5Qdq8P25thdr8LMD2Yi3k+FZ98W7betCiVdCiVdCiVdCiVdCiVdCiVX6gthX87Nl5ec0KcS2bvWA3tpbzjwUtWgUtWgUtWgUtWgUtWgUtWuVHa31worYfK/jdF/HZ1jebE+/bF4w1ZS2PbT4Erapo0aqKFq2qaNGqihatqmjRqor2XbVt60NiZq1HS67yiVrNu5us/UXGtQja3zyLtp3H1uagfdDaHLQPWpuD9kFrc9A+aG0O2getzXlTbUuO+7M/k4H2u34mA+13/UwG2u/6mQy03/UzGWi/62cy0H7Xz2Sg/a6fyUD7XT+Tgfa7fibj47Xn2H/97EL7T+Cz/mMY4/w0t3HND+Ks9Xm2vnPQolXQolXQolXQolXQolXQolU+WXvyfK0n8h1LbOu11vLUD2rb+uT8vnFmQbsF7dr12DHa7Um0aI+y05kF7Ra0a9djx2i3J9GiPcpOZxa0W9D6uWWbVH+2liqbqzpgxltadXsj/wR7S669jHaPt6BFqxa0aNWCFq1a0KJVC1q0akH7vlqD5q3NnVd9HdXEZ9rWB+T47Y02LwtthTbPatCiVdCiVdCiVdCiVdCiVdB+kjZz6h2AzOntTH5LfHMm7+ZneE6voW1Bu+VkPN+3oD2+hrYF7ZaT8Xzfgvb4GtoWtFtOxvN9C9rja2hb0G45Gc/3Lf+Q9jSuVXNIbL1vI9eEYj+Nwtf+F7G0T6tPrqXtekYVLVq09i9atP2GP4oWrYIWrYIWrYL2H9bms76ys+bZ3m6FBvW7E58Zn5GPn78v19nmKztDuzJQaNHu1VHwaq6zzVd2hnZloNCi3auj4NVcZ5uv7AztykChRbtXR8Gruc42X9kZ2pWBQvs22uz1bNr69qlly+lLzx8UU/KsjfegjaBdS7RoPauM9jBga0GLFu187NSyBe1aokXrWWW0hwFby/+srYnp/hNnY3qebU/Ux+b2/Bkz4/vQ5juR4UGLVkGLVkGLVkGLVkGLVkH71to2xBOKMbMO2T4oqnU1J7evt+teisltizardTUno0WroEWroEWroEWroEWroH1f7TqKjjbud19gN1vhdJaxqrfYNm9s2xq0aBW0aBW0aBW0aBW0aBW0aJXP1aasrmK7LkR1fsbvv8/PckpsV1MYtyn742uJ1gto0UbQolXQolXQolXQolXQfqrWrmbBtm2IJdznz7AYYFvVu9YXRq+2z0WLFi3aLKBFixbteHINWEu0Xmiretf60KJVH9r30p5e9OR2TqqFdFtLpLUkYGxfXPOgRaugRaugRaugRaugRaugRat8svarUvzHzjZU9mX8NKExYFRPo+Ijc1uNGbS2RYtWW7RotUWLVlu0aLVFi1ZbtB+sbffzHV+9pLwstLs5NPsSn4VMtKxtltCiVdCiVdCiVdCiVdCiVdCiVT5Q6/esI676pEwOyRa7mz/xdp0XfbXw7JOt5UUBLdoIWrQKWrQKWrQKWrQKWrTK52oz7UJ9zBKoPHP3hq83IvVLI60wJmfQnm5E0MbsV2+fZqJVIYP2dCOCNma/evs0E60KGbSnGxG0MfvV26eZaFXIoD3diKCN2a/ePs38R7R5NX/G9JhZB2dLI8c2z1rzaXy75kGLVkGLVkGLVkGLVkGLVkGLVvkJWp8Zqwp41hfYarvRoFWWN1KR0G01qhm0doYWrc7QotUZWrQ6Q4tWZ2jR6gztR2tbx3b1PPNrfUH0NWP7qdXtrq18uzF20FrarrSh3arbXVv5Fm3En4g+tBG0aBW0aBW0aBW0aBW0f0abT9Rs+Dyzf0dhxhqacYza8DXj2lqiRbt60f7K4dkIWgtatApatApatApatMq7aZ86va7+XoUYktu6MkV7O1/MKXGWLVnIu3nDgxatghatghatghatghatghat8rlaL8aLOa4qbNUST1jq3dON7cxvbNXTjwctWgUtWgUtWgUtWgUtWgUtWuWTtZHsaBfOgGd/djbXz41CuztaooDWq5bZPChoY3UCtHjVMpsHBW2sToAWr1pm86CgjdUJ0OJVy2weFLSxOgFavGqZzYOCNlYnQItXLbN5UNDG6gRo8aplNg8K2lidAC1etczmQUEbqxOgxauW2Two/0LtVFhqId6x/rGaA7K6P6tmr57+BPFXQjv6IllFewpatApatApatApatApatMr7ascqtzYuru6jC/QlJWadz2rBsk1ZZ/sO7RoQOZ3VggVtBC3aYswVWjvbd2jXgMjprBYsaCNo0RZjrtDa2b5DuwZETme1YPlp2shJkdt6ltPntdbXCvVGVMfWVvUL1rIkL6JFq6BFq6BFq6BFq6BFq6BFq7y5di+W5BOtahu/drqxXRs3trN6o73m1XaAFq2CFq2CFq2CFq2CFq2CFq3yadqWbaZtqyyrVnjqs+PuVq2fFvPaWX3Nr62lsrrLrVpAi3Zt0R7P0HoVLVpV0aJVFS1aVdGiVfXPa/NC7VDLOIvBtWVLO2t363ZbVXxuvWUtbYe2btEO2Ve/hRbtOkP7K2jRoo0ztL+CFi3aOHsHrRXHE19+y8+2r8qfNsWvxRSfmDdym8lR8cdAi7acedCiVdCiVdCiVdCiVdCiVT5c6z+ReiHvP0trSVn0jW1+X6ZVLW21fThatBG0aONsLW2HFq12aNFqhxatdmjRavc5WhtRH8uZtv1rtVi2r2r4/QklWxvUq5N3GLWWaGshW9H6GLRoFbRoFbRoFbRoFbRolXfVWmrH5vGpUU1APct3orlOOX3QxmuflqtVrbsvtGgjaNEqaNEqaNEqaNEqaNEqH6S1wXHfe7cn6rh8J2+0Lz01Z7aHPNvWuvZvWUvboUWrHVq02qFFqx1atNqhRasdWrTafZg2b+WLraXxsqWSY8qaWG7kyfiMbVRboUWb1bW0XQRtrz5o0UbQtha02/gY1VZo0WZ1LW0XQdurD1q0kf9Xa//WcVuSV8fFs+Ntq27G2pI3stBWMcCDFq2CFq2CFq2CFq2CFq2CFq3yydqvXnzqO5n2TjZvT9RPs2zb/Nt4TttxVndfaNFG0EbQolXQolXQolXQolXQfog2x9XtXLWfWghF5eWAfHt7Y7XHWa62N9CijaBFG9fWss8cj8UKreJnuUKbBXsHbVxbyz5zPBYrtIqf5QptFuwdtHFtLfvM8Vis0Cp+lqufpn2xqo9ZITw+PQERP8sfq+aXbtfy7lihRbtueNBG0K7l0YgWbV+dBp+e9UKeod3OT6vT4NOzXsgztNv5aXUafHrWC3mGdjs/rU6DT896Ic/Qbuen1Wnw6Vkv5BnaSA5p40bBYtsNkNW2qs9atrvt0+pQtGjRokU7BkfqjVih9SfQotUTaNHqCbRo9cT7aw9FJatjUqbdtbRvjq2fRWpLpm39LNfjRbQr2VVbMmgzaHvQZtBGxotoV7KrtmTQZtD2oM2gjYwX0a5kV23J/CytpQ6c+Prips1qnZItsaqjMm1oNsffZvWVS7+y7pWrHrRrW/vQokVbqhG0aBW0aBW0aBW0aJU/r20X6sxUvJhUV5n2uZGsntztw/dqrscTaEsLWm9D+2uNFm1/w4L2QWtB+6C1oH3QWtA+76N9dlk+FrwGGDdO2T7SL+a8U+KhXbCWaFfQRnw7esuQzLhxCtqIb0dvGZIZN05BG/Ht6C1DMuPGKWgjvh29ZUhm3DgFbcS3o7cMyYwbp6CN+Hb0liGZceMUtBHfjt4yJDNunII24tvRW4Zkxo1T/u3aU+yiD47ktr2Y23rXVu37NsXv79agnS+iPaVOiqB9fbcG7XwR7Sl1UgTt67s1aOeLaE+pkyJoX9+tQTtfRHtKnRR5D6111OTMvw9Xv/YXoy+v2Yy827a1sE2pd7cq2vEiWjtvQYtWQYtWQYtWQYtWQYtWeXNtnsd2/GxD9kl9em1u1yzbvPzSdqMG7XxxXLOgRaugRaugRaugRaugRaugfWttnTQ9PsRkMb32RYtnm9eujQEt7a4L1hItWg9atApatApatApatApatMpP0D7riVZIgE8qfaeCb78Of4LYjndH81qirQXffnWAhmYBLVq0aGsBLVq0aGsBLVq07649bfOGJz/oWe9EcwU0d5zV1cR70D5oLWgftBa0D1oL2getBe2D1oL2QWv5cG3bDqjNzJY8S0WsaiHPmvt0bTI8aNEqaNEqaNEqaNEqaNEqaNEqn6xtiTbbtBbfxrectzElb2Tf2CY+fnKABy1aBS1aBS1aBS1aBS1aBS1a5XO17x+094L2XtDeC9p7QXsvaO8F7b2gvRe094L2XtDeC9p7QXsvaO8F7b2gvRe094L2XtDeC9p7QXsvaO8F7b2gvRe094L2XtDeC9p7QXsvaO8F7b2gvRe094L2XtDeC9p7QXsvaO8F7b18mPY/0D/l3ml8w98AAAAASUVORK5CYII=', 'enterprise', '2025-12-30 14:10:26', '2025-12-30 14:10:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissoes_cargo`
--

CREATE TABLE `permissoes_cargo` (
  `id` int(11) NOT NULL,
  `cargo_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `nivel_acesso` enum('leitura','escrita','admin') DEFAULT 'leitura'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `permissoes_cargo`
--

INSERT INTO `permissoes_cargo` (`id`, `cargo_id`, `modulo_id`, `nivel_acesso`) VALUES
(2, 4, 6, 'admin');

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissoes_setor`
--

CREATE TABLE `permissoes_setor` (
  `id` int(11) NOT NULL,
  `setor_id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `nivel_acesso` enum('leitura','escrita','admin') DEFAULT 'leitura',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `permissoes_setor`
--

INSERT INTO `permissoes_setor` (`id`, `setor_id`, `modulo_id`, `nivel_acesso`, `created_at`) VALUES
(1, 1, 4, 'leitura', '2026-01-27 09:24:28'),
(2, 1, 1, 'leitura', '2026-01-27 09:24:28'),
(3, 1, 5, 'leitura', '2026-01-27 09:24:28'),
(4, 1, 2, 'leitura', '2026-01-27 09:24:28'),
(5, 1, 3, 'escrita', '2026-01-27 09:24:28'),
(26, 3, 5, 'leitura', '2026-02-06 05:55:20'),
(27, 3, 2, 'admin', '2026-02-06 05:55:20'),
(28, 2, 4, 'admin', '2026-02-19 23:36:44'),
(29, 2, 1, 'admin', '2026-02-19 23:36:44'),
(30, 2, 5, 'admin', '2026-02-19 23:36:44'),
(31, 2, 6, 'leitura', '2026-02-19 23:36:44'),
(32, 2, 2, 'admin', '2026-02-19 23:36:44'),
(33, 2, 3, 'admin', '2026-02-19 23:36:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) UNSIGNED NOT NULL,
  `empresa_id` int(11) UNSIGNED NOT NULL,
  `categoria_id` int(11) UNSIGNED DEFAULT NULL,
  `fornecedor_id` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` int(11) NOT NULL DEFAULT 0,
  `unidade_medida` varchar(50) NOT NULL DEFAULT 'unidade',
  `lote` varchar(255) DEFAULT NULL,
  `validade` date DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `empresa_id`, `categoria_id`, `fornecedor_id`, `name`, `sku`, `description`, `price`, `cost_price`, `quantity`, `minimum_stock`, `unidade_medida`, `lote`, `validade`, `observacoes`, `created_at`) VALUES
(1, 1, 1, NULL, 'Agua', '', '', 4.50, 1.00, 1217, 20, 'un', '', '2025-12-31', '', '2025-12-01 14:41:23'),
(2, 1, 1, NULL, 'Suco de Laranja', '', NULL, 8.00, 4.00, 68, 20, 'un', NULL, NULL, NULL, '2025-12-01 14:43:01'),
(3, 1, 1, NULL, 'Agua com gás', '', NULL, 3.00, 2.20, 2192, 20, 'un', NULL, NULL, NULL, '2025-12-01 14:43:43'),
(4, 1, 1, NULL, 'Coca-Cola', '', NULL, 5.00, 2.50, 519, 20, 'un', NULL, NULL, NULL, '2025-12-01 14:44:52'),
(5, 1, 3, NULL, 'chocolate', '', NULL, 12.00, 4.00, 1010, 20, 'un', NULL, NULL, NULL, '2025-12-01 14:45:24'),
(6, 1, 3, NULL, 'Bala Halls - menta', '', NULL, 3.00, 1.50, 36, 20, 'un', NULL, NULL, NULL, '2025-12-01 14:46:02'),
(7, 1, 3, NULL, 'Bala de Goma', '', NULL, 2.50, 0.99, 110, 20, 'un', NULL, NULL, NULL, '2025-12-01 14:46:48'),
(8, 1, 2, NULL, 'Pastel - Carne', '', NULL, 12.00, 5.00, 50, 20, 'un', NULL, NULL, NULL, '2025-12-01 14:47:30'),
(9, 1, 2, NULL, 'Batata Frita', '', NULL, 12.00, 5.00, 57, 20, 'un', NULL, NULL, NULL, '2025-12-01 14:47:58'),
(10, 1, 2, NULL, 'Coxinha de Frango', '', NULL, 7.00, 3.00, 40, 20, 'un', NULL, NULL, NULL, '2025-12-01 14:50:42'),
(12, 1, 3, NULL, 'panetone', '545448544745', '', 10.00, 5.00, 500, 10, 'un', '874595459', '2026-03-15', '', '2025-12-15 23:18:03'),
(13, 1, 4, NULL, 'Geladeira LG iceclub', '984512526515', '70 polegadas', 18000.00, 7000.00, 0, 5, 'un', '7412368452', NULL, '', '2026-01-18 02:45:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `redefinicoes_senha`
--

CREATE TABLE `redefinicoes_senha` (
  `email` varchar(100) NOT NULL,
  `code` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `setores`
--

CREATE TABLE `setores` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cor_hex` varchar(7) DEFAULT '#6c757d',
  `responsavel_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `setores`
--

INSERT INTO `setores` (`id`, `empresa_id`, `nome`, `cor_hex`, `responsavel_id`, `created_at`) VALUES
(1, 1, 'Rh', '#2c7865', NULL, '2026-01-27 09:03:52'),
(2, 1, 'Comercial &amp; Vendas', '#FF9800', NULL, '2026-01-27 16:38:11'),
(3, 1, 'Financeiro', '#2C7865', NULL, '2026-01-27 16:55:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tax_rules`
--

CREATE TABLE `tax_rules` (
  `id` int(11) UNSIGNED NOT NULL,
  `ncm` varchar(20) NOT NULL,
  `cest` varchar(20) DEFAULT NULL,
  `type` enum('monofasico','substituicao_tributaria','isento','tributado') NOT NULL DEFAULT 'tributado',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tax_rules`
--

INSERT INTO `tax_rules` (`id`, `ncm`, `cest`, `type`, `description`, `created_at`) VALUES
(1, '22021000', NULL, 'monofasico', 'Águas, incluídas as águas minerais, adicionadas de açúcar - Monofásico PIS/COFINS', '2026-01-18 02:58:56'),
(2, '22030000', NULL, 'substituicao_tributaria', 'Cervejas de malte - Sujeito a ICMS ST', '2026-01-18 02:58:56'),
(3, '40111000', NULL, 'monofasico', 'Pneus novos de borracha - Monofásico PIS/COFINS', '2026-01-18 02:58:56');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) UNSIGNED NOT NULL,
  `empresa_id` int(11) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_type` enum('admin','employee','super_admin') DEFAULT 'employee',
  `plan` varchar(50) NOT NULL DEFAULT 'basico',
  `trial_ends_at` datetime DEFAULT NULL,
  `subscription_status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `empresa_id`, `username`, `password`, `email`, `user_type`, `plan`, `trial_ends_at`, `subscription_status`, `created_at`) VALUES
(1, 1, 'admin', '$2y$10$Ej0nJbVsgWJqAdMrRKPmCOQm78zRTPGW0Puy/h3DMBh6mnJN7THcS', 'admin@teste.com', 'admin', 'enterprise', NULL, 'active', '2025-12-01 14:39:48'),
(2, 1, 'employee', '$2y$10$8q9XW8l/d2nuZQWM7X8.wu6pMOCzypBJI3JTvZV62BbmZDifLFi6W', 'employee@teste.com', 'employee', 'basico', NULL, 'active', '2025-12-01 14:40:13'),
(3, 1, 'Fernando', '$2y$10$Rg8sO/EfolYpLRDBoFqTve8tMdcCYgGQ2/c8Dz.AikupwskIc85O.', 'fernando@teste.com', 'employee', 'basico', NULL, 'active', '2025-12-01 16:08:53'),
(9, 4, 'admin', '$2y$10$ldZ80G2EG2S.ZfK/0.anieT3K42ppe6Qnk4sHOwdfP/LycyAy1XhG', 'admin@gmail.com', 'admin', 'basico', NULL, 'active', '2025-12-30 13:32:10'),
(11, 1, 'juninho', '$2y$10$0Ejc8OfICQZoPL5hFVAGReDYRWdp.fdQgfCkixvueVOiaoV1T3BDq', 'juninho@teste.com', 'employee', 'basico', NULL, 'active', '2026-01-27 17:02:53'),
(12, 5, 'leonardo', '$2y$10$qEZrt9iKhgr7cUgEAmlZ5.9U3szSFqwWlv4qHlmlsRw3/xRMq.A5W', 'leonardo@hotmail.com', 'admin', 'basico', NULL, 'active', '2026-02-14 17:18:16'),
(13, 6, 'superadmin', '$2y$10$e/cMSrqXnN.v4xEwB7vlouAxLtItajK1as3Lx70y4vJYWbSuQVrCy', 'superadmin@sistema.com', 'super_admin', 'basico', NULL, 'active', '2026-02-20 02:39:47'),
(14, 7, 'Fernando', '$2y$10$ZDD3GdADGQldrbtTvvrz1OWyxXJvNP0DaHMwOJxnsZm5YCUT0FOHC', 'jr.lombardo@hotmail.com', 'admin', 'basico', NULL, 'active', '2026-02-26 03:45:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_setor`
--

CREATE TABLE `usuario_setor` (
  `id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `setor_id` int(11) NOT NULL,
  `cargo_id` int(11) DEFAULT NULL,
  `is_chefe` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuario_setor`
--

INSERT INTO `usuario_setor` (`id`, `user_id`, `setor_id`, `cargo_id`, `is_chefe`, `created_at`) VALUES
(6, 3, 2, 4, 0, '2026-02-20 00:28:29'),
(7, 11, 3, NULL, 0, '2026-02-20 02:46:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) UNSIGNED NOT NULL,
  `empresa_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'dinheiro',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `vendas`
--

INSERT INTO `vendas` (`id`, `empresa_id`, `user_id`, `total_amount`, `payment_method`, `created_at`) VALUES
(1, 1, 2, 60.00, 'dinheiro', '2025-12-01 15:41:52'),
(2, 1, 2, 2100.00, 'pix', '2025-12-01 15:55:40'),
(3, 1, 3, 3568.00, 'debito', '2025-12-01 16:10:27'),
(4, 1, 2, 400.00, 'pix', '2025-12-02 02:07:11'),
(5, 1, 2, 2000.00, 'debito', '2025-12-03 13:44:21'),
(6, 1, 3, 12000.00, 'credito', '2025-12-03 13:51:20'),
(7, 1, 3, 3205.00, 'debito', '2025-12-03 13:52:16'),
(8, 1, 3, 20000.00, 'debito', '2025-12-05 18:16:44'),
(9, 1, 2, 16000.00, 'credito', '2025-12-07 13:24:58'),
(10, 1, 3, 12000.00, 'credito', '2025-12-07 13:37:42'),
(11, 1, 3, 1440.00, 'pix', '2025-12-10 14:38:51'),
(12, 1, 3, 24000.00, 'credito', '2025-12-10 15:02:24'),
(13, 1, 3, 20.00, 'credito', '2025-12-11 18:55:07'),
(14, 1, 2, 3.00, 'dinheiro', '2025-12-11 19:10:37'),
(15, 1, 2, 27.00, 'dinheiro', '2025-12-15 23:02:00'),
(16, 1, 3, 44.00, 'dinheiro', '2025-12-15 23:12:15'),
(17, 1, 2, 76.00, 'dinheiro', '2025-12-15 23:37:47'),
(18, 1, 2, 34.00, 'dinheiro', '2025-12-15 23:58:25'),
(19, 1, 2, 9.00, 'dinheiro', '2025-12-16 00:09:00'),
(20, 1, 3, 9.00, 'dinheiro', '2025-12-16 00:33:32'),
(21, 1, 3, 16.00, 'dinheiro', '2025-12-16 15:03:11'),
(22, 1, 2, 4500.00, 'dinheiro', '2025-12-22 14:11:42'),
(23, 1, 2, 7.50, 'dinheiro', '2025-12-31 22:45:01'),
(24, 1, 3, 1000.00, 'credito', '2026-01-14 23:33:22'),
(25, 1, 3, 1800000.00, 'credito', '2026-01-18 02:47:24'),
(26, 1, 1, 18000.00, 'pix', '2026-01-18 03:02:20'),
(31, 1, 3, 162000.00, 'dinheiro', '2026-02-20 01:47:42'),
(32, 1, 3, 3.00, 'dinheiro', '2026-02-20 02:03:53'),
(33, 1, 3, 4.50, 'dinheiro', '2026-02-20 02:10:27'),
(34, 1, 3, 18.00, 'cartao_credito', '2026-02-21 13:41:36'),
(35, 1, 3, 48.00, 'dinheiro', '2026-02-24 18:43:13'),
(36, 1, 3, 36.00, 'dinheiro', '2026-03-01 16:00:43'),
(37, 1, 1, 12.00, 'dinheiro', '2026-03-16 18:23:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `venda_itens`
--

CREATE TABLE `venda_itens` (
  `id` int(11) UNSIGNED NOT NULL,
  `venda_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `venda_itens`
--

INSERT INTO `venda_itens` (`id`, `venda_id`, `product_id`, `quantity`, `unit_price`) VALUES
(1, 1, 1, 20, 3.00),
(2, 2, 1, 50, 3.00),
(3, 2, 5, 25, 12.00),
(4, 2, 6, 50, 3.00),
(5, 2, 8, 90, 12.00),
(6, 2, 10, 60, 7.00),
(7, 3, 2, 100, 8.00),
(8, 3, 3, 100, 3.00),
(9, 3, 4, 80, 5.00),
(10, 3, 5, 65, 12.00),
(11, 3, 7, 88, 2.50),
(12, 3, 9, 89, 12.00),
(13, 4, 1, 200, 2.00),
(14, 5, 1, 1000, 2.00),
(15, 6, 5, 1000, 12.00),
(16, 7, 1, 1000, 2.00),
(17, 7, 4, 1, 5.00),
(18, 7, 8, 100, 12.00),
(19, 8, 1, 10000, 2.00),
(20, 9, 1, 8000, 2.00),
(21, 10, 5, 1000, 12.00),
(22, 11, 2, 180, 8.00),
(23, 12, 5, 2000, 12.00),
(24, 13, 1, 2, 4.00),
(25, 13, 8, 1, 12.00),
(26, 14, 6, 1, 3.00),
(27, 15, 3, 5, 3.00),
(28, 15, 8, 1, 12.00),
(29, 16, 1, 5, 4.00),
(30, 16, 8, 2, 12.00),
(31, 17, 1, 1, 4.00),
(32, 17, 8, 6, 12.00),
(33, 18, 1, 1, 4.00),
(34, 18, 6, 10, 3.00),
(35, 19, 1, 1, 4.00),
(36, 19, 7, 2, 2.50),
(37, 20, 3, 1, 3.00),
(38, 20, 6, 2, 3.00),
(39, 21, 2, 2, 8.00),
(40, 22, 1, 1000, 4.50),
(41, 23, 1, 1, 4.50),
(42, 23, 6, 1, 3.00),
(43, 24, 12, 100, 10.00),
(44, 25, 13, 100, 18000.00),
(45, 26, 13, 1, 18000.00),
(50, 31, 13, 9, 18000.00),
(51, 32, 3, 1, 3.00),
(52, 33, 1, 1, 4.50),
(53, 34, 3, 6, 3.00),
(54, 35, 9, 4, 12.00),
(55, 36, 3, 6, 3.00),
(56, 36, 1, 4, 4.50),
(57, 37, 3, 4, 3.00);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `ai_agents`
--
ALTER TABLE `ai_agents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices de tabela `ai_agent_logs`
--
ALTER TABLE `ai_agent_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `analise_tributaria`
--
ALTER TABLE `analise_tributaria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Índices de tabela `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `api_key_2` (`api_key`);

--
-- Índices de tabela `api_logs`
--
ALTER TABLE `api_logs`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `avisos_globais`
--
ALTER TABLE `avisos_globais`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `setor_id` (`setor_id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices de tabela `chamados_suporte`
--
ALTER TABLE `chamados_suporte`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_clientes_nome` (`nome`),
  ADD KEY `idx_clientes_cpf_cnpj` (`cpf_cnpj`),
  ADD KEY `idx_clientes_email` (`email`);

--
-- Índices de tabela `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `crm_etapas`
--
ALTER TABLE `crm_etapas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `crm_oportunidades`
--
ALTER TABLE `crm_oportunidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `etapa_id` (`etapa_id`),
  ADD KEY `idx_crm_ops_status` (`status`),
  ADD KEY `idx_crm_ops_etapa` (`etapa_id`);

--
-- Índices de tabela `dados_nota_fiscal`
--
ALTER TABLE `dados_nota_fiscal`
  ADD PRIMARY KEY (`compra_id`);

--
-- Índices de tabela `dashboard_layouts`
--
ALTER TABLE `dashboard_layouts`
  ADD PRIMARY KEY (`user_id`);

--
-- Índices de tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `fin_categorias`
--
ALTER TABLE `fin_categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices de tabela `fin_movimentacoes`
--
ALTER TABLE `fin_movimentacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `data_vencimento` (`data_vencimento`),
  ADD KEY `status` (`status`);

--
-- Índices de tabela `fiscal_impostos`
--
ALTER TABLE `fiscal_impostos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices de tabela `fiscal_notas`
--
ALTER TABLE `fiscal_notas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `data_emissao` (`data_emissao`),
  ADD KEY `chave_acesso` (`chave_acesso`),
  ADD KEY `idx_fiscal_notas_periodo` (`data_emissao`);

--
-- Índices de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices de tabela `historico_estoque`
--
ALTER TABLE `historico_estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `venda_id` (`venda_id`);

--
-- Índices de tabela `itens_compra`
--
ALTER TABLE `itens_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Índices de tabela `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `lotes`
--
ALTER TABLE `lotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices de tabela `notificacao_status_usuario`
--
ALTER TABLE `notificacao_status_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_notification` (`notificacao_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Índices de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `permissoes_cargo`
--
ALTER TABLE `permissoes_cargo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_perm` (`cargo_id`,`modulo_id`),
  ADD KEY `modulo_id` (`modulo_id`);

--
-- Índices de tabela `permissoes_setor`
--
ALTER TABLE `permissoes_setor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `setor_id` (`setor_id`),
  ADD KEY `modulo_id` (`modulo_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `fornecedor_id` (`fornecedor_id`);

--
-- Índices de tabela `redefinicoes_senha`
--
ALTER TABLE `redefinicoes_senha`
  ADD PRIMARY KEY (`email`);

--
-- Índices de tabela `setores`
--
ALTER TABLE `setores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices de tabela `tax_rules`
--
ALTER TABLE `tax_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ncm` (`ncm`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Índices de tabela `usuario_setor`
--
ALTER TABLE `usuario_setor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `setor_id` (`setor_id`),
  ADD KEY `cargo_id` (`cargo_id`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `venda_itens`
--
ALTER TABLE `venda_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venda_id` (`venda_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `ai_agents`
--
ALTER TABLE `ai_agents`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `ai_agent_logs`
--
ALTER TABLE `ai_agent_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `analise_tributaria`
--
ALTER TABLE `analise_tributaria`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `api_logs`
--
ALTER TABLE `api_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `avisos_globais`
--
ALTER TABLE `avisos_globais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `chamados_suporte`
--
ALTER TABLE `chamados_suporte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `crm_etapas`
--
ALTER TABLE `crm_etapas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `crm_oportunidades`
--
ALTER TABLE `crm_oportunidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `fin_categorias`
--
ALTER TABLE `fin_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `fin_movimentacoes`
--
ALTER TABLE `fin_movimentacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fiscal_impostos`
--
ALTER TABLE `fiscal_impostos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `fiscal_notas`
--
ALTER TABLE `fiscal_notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fornecedores`
--
ALTER TABLE `fornecedores`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `historico_estoque`
--
ALTER TABLE `historico_estoque`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT de tabela `itens_compra`
--
ALTER TABLE `itens_compra`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `lotes`
--
ALTER TABLE `lotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `notificacao_status_usuario`
--
ALTER TABLE `notificacao_status_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=480;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `permissoes_cargo`
--
ALTER TABLE `permissoes_cargo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `permissoes_setor`
--
ALTER TABLE `permissoes_setor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `setores`
--
ALTER TABLE `setores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `tax_rules`
--
ALTER TABLE `tax_rules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `usuario_setor`
--
ALTER TABLE `usuario_setor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `venda_itens`
--
ALTER TABLE `venda_itens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `ai_agents`
--
ALTER TABLE `ai_agents`
  ADD CONSTRAINT `ai_agents_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ai_agent_logs`
--
ALTER TABLE `ai_agent_logs`
  ADD CONSTRAINT `ai_agent_logs_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `ai_agents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_agent_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `analise_tributaria`
--
ALTER TABLE `analise_tributaria`
  ADD CONSTRAINT `analise_tributaria_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `analise_tributaria_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `cargos`
--
ALTER TABLE `cargos`
  ADD CONSTRAINT `cargos_ibfk_1` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `fornecedores` (`id`),
  ADD CONSTRAINT `compras_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `dados_nota_fiscal`
--
ALTER TABLE `dados_nota_fiscal`
  ADD CONSTRAINT `dados_nota_fiscal_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `fin_movimentacoes`
--
ALTER TABLE `fin_movimentacoes`
  ADD CONSTRAINT `fin_movimentacoes_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `fin_categorias` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `fornecedores`
--
ALTER TABLE `fornecedores`
  ADD CONSTRAINT `fornecedores_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_estoque`
--
ALTER TABLE `historico_estoque`
  ADD CONSTRAINT `historico_estoque_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_estoque_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_estoque_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_estoque_ibfk_4` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `itens_compra`
--
ALTER TABLE `itens_compra`
  ADD CONSTRAINT `itens_compra_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `itens_compra_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `lotes`
--
ALTER TABLE `lotes`
  ADD CONSTRAINT `lotes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `notificacao_status_usuario`
--
ALTER TABLE `notificacao_status_usuario`
  ADD CONSTRAINT `notificacao_status_usuario_ibfk_1` FOREIGN KEY (`notificacao_id`) REFERENCES `notificacoes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificacao_status_usuario_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD CONSTRAINT `notificacoes_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificacoes_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `permissoes_cargo`
--
ALTER TABLE `permissoes_cargo`
  ADD CONSTRAINT `permissoes_cargo_ibfk_1` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissoes_cargo_ibfk_2` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `permissoes_setor`
--
ALTER TABLE `permissoes_setor`
  ADD CONSTRAINT `permissoes_setor_ibfk_1` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissoes_setor_ibfk_2` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produtos_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `produtos_ibfk_3` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `setores`
--
ALTER TABLE `setores`
  ADD CONSTRAINT `setores_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `usuario_setor`
--
ALTER TABLE `usuario_setor`
  ADD CONSTRAINT `usuario_setor_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_setor_ibfk_2` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_setor_ibfk_3` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendas_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `venda_itens`
--
ALTER TABLE `venda_itens`
  ADD CONSTRAINT `venda_itens_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `venda_itens_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produtos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
