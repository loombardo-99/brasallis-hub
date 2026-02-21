<?php

namespace App\Controllers;

use PDO;
use PDOException;

class AuthController {
    
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function login() {
        $error_message = '';
        require __DIR__ . '/../../views/login.php';
    }

    public function authenticate($data) {
        $email = $this->sanitize_input($data['email']);
        $password = $this->sanitize_input($data['password']);
        $error_message = '';

        try {
            $stmt = $this->pdo->prepare("SELECT id, username, password, user_type, empresa_id, plan FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $user['password'])) {
                    // Start Session if not started
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['empresa_id'] = $user['empresa_id'];
                    $_SESSION['user_plan'] = $user['plan'];

                    // --- CARREGAR BRANDING DA EMPRESA ---
                    $stmtBranding = $this->pdo->prepare("SELECT branding_primary_color, branding_secondary_color, branding_bg_style FROM empresas WHERE id = ?");
                    $stmtBranding->execute([$user['empresa_id']]);
                    if ($branding = $stmtBranding->fetch(PDO::FETCH_ASSOC)) {
                        $_SESSION['branding'] = $branding;
                    }

                    // --- CARREGAR DADOS ORGANIZACIONAIS ---
                    $stmtOrg = $this->pdo->prepare("SELECT setor_id, cargo_id FROM usuario_setor WHERE user_id = ?");
                    $stmtOrg->execute([$user['id']]);
                    $orgData = $stmtOrg->fetch(PDO::FETCH_ASSOC);

                    $_SESSION['setor_id'] = $orgData['setor_id'] ?? null;
                    $_SESSION['cargo_id'] = $orgData['cargo_id'] ?? null;

                    // Carregar Permissões
                    $permMap = [];
                    // Se for admin, acesso total (hack temporário até tudo ser migrado)
                    if ($user['user_type'] === 'admin') {
                        $_SESSION['permissions'] = 'all'; 
                    } else {
                        // Permissions: Load from ROLE (Cargo) not Sector
                        if ($_SESSION['cargo_id']) {
                            $stmtPerms = $this->pdo->prepare("
                                SELECT m.slug, pc.nivel_acesso
                                FROM permissoes_cargo pc
                                JOIN modulos m ON pc.modulo_id = m.id
                                WHERE pc.cargo_id = ?
                            ");
                            $stmtPerms->execute([$_SESSION['cargo_id']]);
                            while ($row = $stmtPerms->fetch(PDO::FETCH_ASSOC)) {
                                $permMap[$row['slug']] = $row['nivel_acesso'];
                            }
                        }
                        
                        // Fallback: If no role or no permissions found, try sector legacy permissions (Migration Phase)
                        // Or just leave empty if strictly RBAC. Let's start strictly RBAC for consistency.
                        // If they have no role, they have no permissions unless Admin.
                        
                        $_SESSION['permissions'] = $permMap;
                    }

                    // Redirect logic
                    if ($user['user_type'] === 'super_admin') {
                        header('Location: superadmin/index.php');
                    } elseif ($user['user_type'] === 'admin') {
                        header('Location: admin/painel_admin.php');
                    } else {
                        // Funcionário: Verifica Smart Redirect (Acesso Direto) ou Setor
                        $perms = $_SESSION['permissions'] ?? [];
                        if (is_array($perms) && count($perms) === 1) {
                             $slug = array_key_first($perms);
                             $redirect = '';
                             
                             switch($slug) {
                                 case 'pdv': $redirect = 'modules/pdv/views/index.php'; break;
                                 case 'rh': $redirect = 'modules/rh/views/index.php'; break;
                                 case 'fiscal': $redirect = 'modules/fiscal/views/index.php'; break;
                                 case 'financeiro': $redirect = 'modules/financeiro/views/index.php'; break;
                                 // Estoque usually has multiple sub-pages, so maybe keep dashboard? 
                                 // But if user wants direct, maybe products? Let's stick to Dashboard for complex modules 
                                 // unless explicitly requested.
                             }

                             if ($redirect) {
                                 header("Location: $redirect");
                                 exit();
                             }
                        }

                        // Fallback padrão
                        if (!empty($_SESSION['setor_id'])) {
                            header('Location: admin/setor_dashboard.php?id=' . $_SESSION['setor_id']);
                        } else {
                            // Fallback caso não tenha setor definido
                            header('Location: admin/dashboard_funcionario.php'); 
                        }
                    }
                    exit();
                } else {
                    $error_message = "E-mail ou senha incorretos.";
                }
            } else {
                $error_message = "E-mail ou senha incorretos.";
            }

        } catch (PDOException $e) {
            $error_message = "Erro no servidor. Tente novamente mais tarde.";
            error_log("Erro de login: " . $e->getMessage());
        }

        // Se houver erro, renderiza a view com a mensagem
        require __DIR__ . '/../../views/login.php';
    }

    private function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public function register() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $planos_validos = ['free', 'growth', 'enterprise'];
        $plano_selecionado = (isset($_GET['plan']) && in_array($_GET['plan'], $planos_validos)) ? $_GET['plan'] : 'free';
        
        $error_message = $_SESSION['error_message'] ?? '';
        $form_data = $_SESSION['form_data'] ?? [];
        
        // Limpa mensagens após leitura
        unset($_SESSION['error_message'], $_SESSION['form_data']);

        require __DIR__ . '/../../views/auth/register.php';
    }

    public function store($data) {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Sanitizar
        $company_name = $this->sanitize_input($data['company_name'] ?? '');
        $username = $this->sanitize_input($data['username'] ?? '');
        $email = $this->sanitize_input($data['email'] ?? '');
        $password = $this->sanitize_input($data['password'] ?? '');
        $confirm_password = $this->sanitize_input($data['confirm_password'] ?? '');
        $plano_selecionado = $data['plan'] ?? 'free';

        // Armazena para repopular
        $_SESSION['form_data'] = $data;

        // Validar
        if (empty($company_name) || empty($username) || empty($email) || empty($password)) {
            $_SESSION['error_message'] = 'Todos os campos são obrigatórios.';
            header('Location: register.php?plan=' . $plano_selecionado);
            exit();
        }

        if ($password !== $confirm_password) {
            $_SESSION['error_message'] = 'As senhas não coincidem.';
            header('Location: register.php?plan=' . $plano_selecionado);
            exit();
        }

        // Configurações
        $ai_plan_db = 'free'; 
        $ai_token_limit = 100000;
        $max_users = 1; /* Free limit */
        $support_level = 'community';

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new PDOException("O e-mail informado já está em uso.", 23000);
            }

            // Insert Empresa
            $stmt = $this->pdo->prepare("INSERT INTO empresas (name, owner_user_id, ai_plan, ai_token_limit, max_users, support_level, ai_tokens_used_month) VALUES (?, 0, ?, ?, ?, ?, 0)");
            $stmt->execute([$company_name, $ai_plan_db, $ai_token_limit, $max_users, $support_level]);
            $empresa_id = $this->pdo->lastInsertId();

            // Insert User
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (empresa_id, username, email, password, user_type) VALUES (?, ?, ?, ?, 'admin')");
            $stmt->execute([$empresa_id, $username, $email, $hashed_password]);
            $user_id = $this->pdo->lastInsertId();

            // Update Empresa owner
            $stmt = $this->pdo->prepare("UPDATE empresas SET owner_user_id = ? WHERE id = ?");
            $stmt->execute([$user_id, $empresa_id]);

            $this->pdo->commit();
            unset($_SESSION['form_data']);

            // Login
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = 'admin';
            $_SESSION['empresa_id'] = $empresa_id;
            $_SESSION['ai_plan'] = $ai_plan_db;

            // Redirect
            if ($plano_selecionado === 'growth' || $plano_selecionado === 'enterprise') {
                $_SESSION['message'] = 'Conta criada! Complete sua assinatura.';
                $_SESSION['message_type'] = 'info';
                header('Location: admin/checkout.php?plan=' . $plano_selecionado);
            } else {
                $_SESSION['message'] = 'Sua conta foi criada com sucesso!';
                $_SESSION['message_type'] = 'success';
                header('Location: admin/painel_admin.php');
            }
            exit();

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            
            $_SESSION['error_message'] = ($e->getCode() == 23000) ? 'O e-mail informado já está em uso.' : 'Erro ao criar conta.';
            header('Location: register.php?plan=' . $plano_selecionado);
            exit();
        }
    }
}
