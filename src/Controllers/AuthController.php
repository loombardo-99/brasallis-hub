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

                    // Regenerate session ID for security
                    session_regenerate_id(true);

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
                    if ($user['user_type'] === 'admin') {
                        $_SESSION['permissions'] = 'all'; 
                    } else {
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
                        $_SESSION['permissions'] = $permMap;
                    }

                    // Redirect logic
                    if ($user['user_type'] === 'super_admin') {
                        header('Location: superadmin/index.php');
                    } elseif ($user['user_type'] === 'admin') {
                        header('Location: admin/painel_admin.php');
                    } else {
                        // Employee logic: Check for setor_id to redirect to proper dashboard
                        if (!empty($_SESSION['setor_id'])) {
                            header('Location: admin/setor_dashboard.php?id=' . $_SESSION['setor_id']);
                        } else {
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
        $planos_validos = ['iniciante', 'growth', 'enterprise'];
        $plano_selecionado = (isset($_GET['plan']) && in_array($_GET['plan'], $planos_validos)) ? $_GET['plan'] : 'iniciante';
        $error_message = $_SESSION['error_message'] ?? '';
        $form_data = $_SESSION['form_data'] ?? [];
        unset($_SESSION['error_message'], $_SESSION['form_data']);
        require __DIR__ . '/../../views/auth/register.php';
    }

    public function store($data) {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $company_name = $this->sanitize_input($data['company_name'] ?? '');
        $username = $this->sanitize_input($data['username'] ?? '');
        $email = $this->sanitize_input($data['email'] ?? '');
        $password = $this->sanitize_input($data['password'] ?? '');
        $confirm_password = $this->sanitize_input($data['confirm_password'] ?? '');
        $plano_selecionado = $data['plan'] ?? 'iniciante';

        $_SESSION['form_data'] = $data;

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

        // Configurações iniciais com base no plano
        $ai_plan_db = $plano_selecionado; 
        $ai_token_limit = ($plano_selecionado === 'enterprise') ? 5000000 : (($plano_selecionado === 'growth') ? 1000000 : 200000);
        $max_users = ($plano_selecionado === 'enterprise') ? 999 : (($plano_selecionado === 'growth') ? 10 : 1);
        $support_level = ($plano_selecionado === 'enterprise') ? 'dedicated' : (($plano_selecionado === 'growth') ? 'priority' : 'community');

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
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (empresa_id, username, email, password, user_type, plan) VALUES (?, ?, ?, ?, 'admin', ?)");
            $stmt->execute([$empresa_id, $username, $email, $hashed_password, $ai_plan_db]);
            $user_id = $this->pdo->lastInsertId();

            // Update Empresa owner
            $stmt = $this->pdo->prepare("UPDATE empresas SET owner_user_id = ? WHERE id = ?");
            $stmt->execute([$user_id, $empresa_id]);

            $this->pdo->commit();
            unset($_SESSION['form_data']);

            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = 'admin';
            $_SESSION['empresa_id'] = $empresa_id;
            $_SESSION['user_plan'] = $ai_plan_db;

            if ($plano_selecionado === 'growth' || $plano_selecionado === 'enterprise') {
                $_SESSION['message'] = "Conta criada! Aproveite seus 15 dias de teste grátis no {$plano_selecionado}.";
                $_SESSION['message_type'] = 'info';
            } else {
                $_SESSION['message'] = 'Sua conta foi criada! Bem-vindo ao Plano MEI.';
                $_SESSION['message_type'] = 'success';
            }
            header('Location: admin/painel_admin.php');
            exit();

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $_SESSION['error_message'] = ($e->getCode() == 23000) ? 'O e-mail informado já está em uso.' : 'Erro ao criar conta.';
            header('Location: register.php?plan=' . $plano_selecionado);
            exit();
        }
    }
}
