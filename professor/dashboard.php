<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Ensure user is logged in and is a professor
requireProfessor();

$pageTitle = 'Painel do Professor';

// Get professor's school
$userId = $_SESSION['user_id'];
$user = queryOne("SELECT u.*, s.name as school_name FROM users u LEFT JOIN schools s ON u.school_id = s.id WHERE u.id = ?", [$userId]);

// Get stats
// Get stats
$stats = [
    'teams' => 0,
    'students' => 0,
    'pending' => 0,
    'approved' => 0
];

if ($user && !empty($user['school_id'])) {
    $schoolId = $user['school_id'];
    
    $teamsQuery = queryOne("SELECT COUNT(*) as count FROM registrations WHERE school_id = ?", [$schoolId]);
    $stats['teams'] = $teamsQuery['count'] ?? 0;
    
    $studentsQuery = queryOne("SELECT COUNT(*) as count FROM students WHERE school_id = ?", [$schoolId]);
    $stats['students'] = $studentsQuery['count'] ?? 0;
    
    $pendingQuery = queryOne("SELECT COUNT(*) as count FROM registrations WHERE school_id = ? AND status = 'pending'", [$schoolId]);
    $stats['pending'] = $pendingQuery['count'] ?? 0;
    
    $approvedQuery = queryOne("SELECT COUNT(*) as count FROM registrations WHERE school_id = ? AND status = 'approved'", [$schoolId]);
    $stats['approved'] = $approvedQuery['count'] ?? 0;
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="top-bar-title">Painel do Professor</h1>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                <div class="user-role">Professor - <?php echo htmlspecialchars($user['school_name']); ?></div>
            </div>
        </div>
    </div>
    
    <div class="content-wrapper">
        <!-- Welcome Section -->
        <div class="glass-card" style="margin-bottom: 2rem; background: linear-gradient(135deg, rgba(168, 85, 247, 0.1) 0%, rgba(59, 130, 246, 0.1) 100%);">
            <h2 style="margin-bottom: 0.5rem;">Bem-vindo(a), <?php echo htmlspecialchars($user['name']); ?>!</h2>
            <p style="color: var(--text-secondary);">
                Gerencie suas equipes e atletas para os Jogos Escolares Municipais.
            </p>
        </div>
        
        <!-- Stats Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                <div style="font-size: 2.5rem; background: rgba(59, 130, 246, 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">🏆</div>
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">Total de Equipes</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $stats['teams']; ?></div>
                </div>
            </div>
            
            <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                <div style="font-size: 2.5rem; background: rgba(16, 185, 129, 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">🏃</div>
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">Atletas Cadastrados</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $stats['students']; ?></div>
                </div>
            </div>
            
            <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                <div style="font-size: 2.5rem; background: rgba(245, 158, 11, 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">⏳</div>
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">Inscrições Pendentes</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $stats['pending']; ?></div>
                </div>
            </div>
            
            <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                <div style="font-size: 2.5rem; background: rgba(34, 197, 94, 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">✅</div>
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">Equipes Aprovadas</div>
                    <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $stats['approved']; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <h3 style="margin-bottom: 1.5rem;">Acesso Rápido</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <a href="teams.php" class="glass-card" style="padding: 1.5rem; text-decoration: none; color: inherit; transition: transform 0.2s; display: block;">
                <div style="font-size: 2rem; margin-bottom: 1rem;">📋</div>
                <h4 style="margin-bottom: 0.5rem;">Gerenciar Equipes</h4>
                <p style="color: var(--text-secondary); font-size: 0.875rem;">Visualize e cadastre novas equipes para as competições.</p>
            </a>
            
            <a href="students.php" class="glass-card" style="padding: 1.5rem; text-decoration: none; color: inherit; transition: transform 0.2s; display: block;">
                <div style="font-size: 2rem; margin-bottom: 1rem;">👥</div>
                <h4 style="margin-bottom: 0.5rem;">Gerenciar Atletas</h4>
                <p style="color: var(--text-secondary); font-size: 0.875rem;">Cadastre e edite informações dos seus alunos atletas.</p>
            </a>
        </div>
    </div>
</div>

<style>
.glass-card:hover {
    transform: translateY(-4px);
    border-color: var(--primary);
}
</style>

<?php include '../includes/footer.php'; ?>
