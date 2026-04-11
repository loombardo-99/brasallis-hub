# 🍎 Brasallis Hub 360 - Apple Pure Edition

![Brasallis Hub](https://img.shields.io/badge/UI-Apple%20Pure-blue?style=for-the-badge&logo=apple)
![Status](https://img.shields.io/badge/Status-Production%20Ready-success?style=for-the-badge)

Uma plataforma ERP SaaS de próxima geração, focada em simplicidade, fluidez e inteligência. O **Brasallis Hub** redefine a gestão empresarial com uma interface minimalista inspirada no ecossistema Apple, unindo **Glassmorphism**, física tátil e automação por IA.

---

## ✨ A Experiência "Apple Pure"

O Brasallis Hub não é apenas um ERP; é uma ferramenta de produtividade refinada. 

- **Interface Solar**: Navegação translúcida com `backdrop-filter` de alta densidade.
- **Física Hática**: Transições e botões com resposta física (Spring Physics) no hover e clique.
- **Hierarquia Visual**: Foco total no conteúdo, eliminando bordas pesadas e fadiga visual.
- **Smart Hover**: Menus que reagem à intenção do usuário com expansão fluida.

---

## 🚀 Módulos Inteligentes

### 📦 Gestão de Estoque & Catálogo
- **Inventory Library**: Visão tipo "App Store" dos seus ativos.
- **Apple Table Airy**: Tabelas com espaçamento dinâmico e tipografia Inter de alta legibilidade.
- **Controle de Lotes**: Rastreabilidade total com alertas de validade integrados.

### 🤖 Inteligência Artificial (Inovação)
- **Extrator de Notas Fiscais**: Processamento inteligente de PDFs/Imagens para entrada automática de mercadorias.
- **Chat Agent**: Assistente virtual integrado para análise rápida de métricas.

### 📊 Dashboard Executivo
- **Cartões de Performance**: Métricas críticas em painéis Glassmorphism de alto contraste.
- **Gráficos Curvos**: Visualização de tendências com suavização de Bezier (Chart.js).

---

## 🛠️ Stack Tecnológica

- **Core**: PHP 7.4+ / MySQL (MariaDB)
- **Frontend**: Vanilla JS (ES6+), Modern CSS (Variables, Flexbox, Grid)
- **IA/Scripts**: Python 3.10+
- **DevOps**: Docker & Docker Compose Ready

---

## ⚙️ Guia de Instalação Rápida

### 🐳 Via Docker (Recomendado)

O projeto está configurado para subir um ambiente completo (Web + DB + Admin) em segundos.

1. Clone o repositório: `git clone ...`
2. Na raiz do projeto, execute:
   ```bash
   docker-compose up -d
   ```
3. Acesse: **`http://localhost:8001`**
4. Gerenciamento do Banco: `http://localhost:8080`

### 💻 Instalação Manual

1. Configure um servidor Apache/Nginx com PHP 7.4+.
2. Certifique-se de que o **MySQL** está rodando.
3. Copie `includes/config.php` e crie um arquivo local para suas credenciais se necessário.
4. Importe os esquemas localizados no diretório `/sql`.

---

## 🔒 Segurança e Privacidade

Este repositório foi **sanetizado** para subida pública. 
- Credenciais locais e dumps de banco de dados são ignorados via `.gitignore`.
- Chaves de API de terceiros (Gemini/OpenAI) devem ser configuradas via variáveis de ambiente ou no arquivo `includes/config.local.php` (não incluído no repositório).

---

## 📄 Licença

Este projeto é de uso restrito conforme as diretrizes da **Brasallis Solutions**.

---
*Desenvolvido com ❤️ pelo time de Advanced Engineering.*
