<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema JEM - Jogos Escolares Municipais</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 800px;
            padding: 2rem;
        }
        
        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.8s ease;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease 0.2s both;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.4s both;
        }
        
        .features {
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .feature-card {
            text-align: center;
            animation: fadeInUp 0.8s ease;
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .feature-description {
            color: var(--text-secondary);
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
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="hero-content">
            <h1 class="hero-title">Sistema JEM</h1>
            <p class="hero-subtitle">
                Gerenciamento completo dos Jogos Escolares Municipais
            </p>
            <div class="hero-buttons">
                <a href="login.php" class="btn btn-primary btn-lg">Acessar Sistema</a>
                <a href="register.php" class="btn btn-secondary btn-lg">Solicitar Acesso</a>
            </div>
        </div>
    </div>
    
    <div class="features">
        <h2 class="text-center" style="font-size: 2.5rem; margin-bottom: 1rem;">Funcionalidades</h2>
        <p class="text-center" style="color: var(--text-secondary); font-size: 1.25rem;">
            Tudo que você precisa para gerenciar os jogos escolares
        </p>
        
        <div class="features-grid">
            <div class="feature-card glass-card">
                <div class="feature-icon">🏫</div>
                <h3 class="feature-title">Gestão de Escolas</h3>
                <p class="feature-description">
                    Cadastre e gerencie todas as escolas participantes com facilidade
                </p>
            </div>
            
            <div class="feature-card glass-card">
                <div class="feature-icon">👨‍🏫</div>
                <h3 class="feature-title">Portal do Professor</h3>
                <p class="feature-description">
                    Professores podem cadastrar alunos e criar inscrições de equipes
                </p>
            </div>
            
            <div class="feature-card glass-card">
                <div class="feature-icon">⚽</div>
                <h3 class="feature-title">Modalidades Esportivas</h3>
                <p class="feature-description">
                    Gerencie múltiplas modalidades e categorias de idade
                </p>
            </div>
            
            <div class="feature-card glass-card">
                <div class="feature-icon">👥</div>
                <h3 class="feature-title">Cadastro de Atletas</h3>
                <p class="feature-description">
                    Sistema completo para registro de alunos atletas
                </p>
            </div>
            
            <div class="feature-card glass-card">
                <div class="feature-icon">✅</div>
                <h3 class="feature-title">Aprovação de Inscrições</h3>
                <p class="feature-description">
                    Processo organizado de revisão e aprovação de equipes
                </p>
            </div>
            
            <div class="feature-card glass-card">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title">Relatórios Completos</h3>
                <p class="feature-description">
                    Gere relatórios detalhados e exporte dados em CSV
                </p>
            </div>
        </div>
    </div>
    
    <footer style="text-align: center; padding: 2rem; color: var(--text-muted);">
        <p>&copy; 2024 Sistema JEM - Jogos Escolares Municipais</p>
    </footer>
</body>
</html>
