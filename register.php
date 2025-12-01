<?php
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php'; // Needed for password hashing

$success = false;
$error = '';

// Fetch schools for dropdown
$schools = query("SELECT id, name FROM schools ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $cpf = sanitize($_POST['cpf'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $schoolId = sanitize($_POST['school_id'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($cpf) || empty($phone) || empty($schoolId) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif (!validateEmail($email)) {
        $error = 'Email inválido.';
    } elseif (!validateCPF($cpf)) {
        $error = 'CPF inválido.';
    } elseif (strlen($password) < 6) {
        $error = 'A senha deve ter no mínimo 6 caracteres.';
    } else {
        // Check if email already exists
        if (emailExists($email)) {
            $error = 'Este email já está cadastrado.';
        } elseif (cpfExists($cpf)) {
            $error = 'Este CPF já está cadastrado.';
        } else {
            // Create user directly
            $hashedPassword = hashPassword($password);
            
            $sql = "INSERT INTO users (name, email, password, cpf, phone, role, school_id, is_active) 
                    VALUES (?, ?, ?, ?, ?, 'professor', ?, 0)";
            
            if (execute($sql, [$name, $email, $hashedPassword, $cpf, $phone, $schoolId])) {
                $success = true;
            } else {
                $error = 'Erro ao realizar cadastro. Tente novamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Professor - Sistema JEM</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .register-box {
            width: 100%;
            max-width: 600px;
            animation: fadeInUp 0.6s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-title {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .register-subtitle {
            color: var(--text-secondary);
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            border: 1px solid;
        }
        
        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border-color: var(--success);
            color: var(--success);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border-color: var(--error);
            color: var(--error);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color var(--transition-fast);
        }
        
        .back-link:hover {
            color: var(--primary);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <div class="register-header">
                <h1 class="register-title">Cadastro de Professor</h1>
                <p class="register-subtitle">Crie sua conta para gerenciar suas equipes</p>
            </div>
            
            <div class="glass-card">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <strong>Cadastro realizado com sucesso!</strong><br>
                        Sua conta foi criada e aguarda aprovação do administrador. Você será notificado quando o acesso for liberado.
                    </div>
                    <div style="text-align: center;">
                        <a href="index.php" class="btn btn-primary">Voltar ao Início</a>
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label" for="name">Nome Completo *</label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="form-input" 
                                placeholder="Seu nome completo"
                                value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                required
                            >
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="email">Email *</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="form-input" 
                                    placeholder="seu@email.com"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                    required
                                >
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="cpf">CPF *</label>
                                <input 
                                    type="text" 
                                    id="cpf" 
                                    name="cpf" 
                                    class="form-input" 
                                    placeholder="000.000.000-00"
                                    data-format="cpf"
                                    value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>"
                                    required
                                >
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="phone">Telefone *</label>
                                <input 
                                    type="text" 
                                    id="phone" 
                                    name="phone" 
                                    class="form-input" 
                                    placeholder="(00) 00000-0000"
                                    data-format="phone"
                                    value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                    required
                                >
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="school_id">Escola *</label>
                                <select id="school_id" name="school_id" class="form-select" required>
                                    <option value="">Selecione sua escola...</option>
                                    <?php foreach ($schools as $school): ?>
                                        <option value="<?php echo $school['id']; ?>" <?php echo (isset($_POST['school_id']) && $_POST['school_id'] == $school['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($school['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="password">Senha *</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Mínimo 6 caracteres"
                                minlength="6"
                                required
                            >
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Criar Conta
                        </button>
                    </form>
                    
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <p style="color: var(--text-secondary);">
                            Já tem conta? 
                            <a href="login.php" style="color: var(--primary); font-weight: 600;">
                                Faça login
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center;">
                <a href="index.php" class="back-link">← Voltar para página inicial</a>
            </div>
        </div>
    </div>
    
    <script src="assets/js/validation.js"></script>
</body>
</html>
