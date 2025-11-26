<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

$pageTitle = 'Relatórios e Estatísticas';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="top-bar-title">Relatórios</h1>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars(getCurrentUserName()); ?></div>
                <div class="user-role">Administrador</div>
            </div>
        </div>
    </div>
    
    <div class="content-wrapper">
        <!-- Header Actions -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
            <button class="btn btn-primary" onclick="window.print()">
                <span>🖨️</span>
                <span>Imprimir Relatório</span>
            </button>
        </div>

        <!-- Summary Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                <div style="font-size: 2.5rem; background: rgba(59, 130, 246, 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">🏫</div>
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">Escolas</div>
                    <div style="font-size: 1.5rem; font-weight: 700;" id="totalSchools">-</div>
                </div>
            </div>
            
            <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                <div style="font-size: 2.5rem; background: rgba(16, 185, 129, 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">👨‍🏫</div>
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">Professores</div>
                    <div style="font-size: 1.5rem; font-weight: 700;" id="totalProfessors">-</div>
                </div>
            </div>
            
            <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                <div style="font-size: 2.5rem; background: rgba(245, 158, 11, 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">🏆</div>
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">Equipes</div>
                    <div style="font-size: 1.5rem; font-weight: 700;" id="totalTeams">-</div>
                </div>
            </div>
            
            <div class="glass-card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                <div style="font-size: 2.5rem; background: rgba(239, 68, 68, 0.1); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">🏃</div>
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">Atletas</div>
                    <div style="font-size: 1.5rem; font-weight: 700;" id="totalStudents">-</div>
                </div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
            <!-- By Modality -->
            <div class="glass-card">
                <h3 style="margin-bottom: 1.5rem;">Equipes por Modalidade</h3>
                <div class="table-container">
                    <table class="table" id="modalityTable">
                        <thead>
                            <tr>
                                <th>Modalidade</th>
                                <th style="text-align: right;">Equipes</th>
                                <th style="text-align: right;">Atletas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="3" style="text-align: center;">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- By Category -->
            <div class="glass-card">
                <h3 style="margin-bottom: 1.5rem;">Equipes por Categoria</h3>
                <div class="table-container">
                    <table class="table" id="categoryTable">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th style="text-align: right;">Equipes</th>
                                <th>Distribuição</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="3" style="text-align: center;">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- By School -->
        <div class="glass-card" style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Participação por Escola</h3>
            <div class="table-container">
                <table class="table" id="schoolTable">
                    <thead>
                        <tr>
                            <th>Escola</th>
                            <th style="text-align: right;">Equipes Inscritas</th>
                            <th style="text-align: right;">Total de Atletas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="4" style="text-align: center;">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .top-bar, .btn {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .content-wrapper {
        padding: 0 !important;
    }
    .glass-card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        break-inside: avoid;
    }
    body {
        background: white !important;
        color: black !important;
    }
}

.progress-bar-bg {
    width: 100%;
    height: 8px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: var(--primary);
    border-radius: 4px;
}
</style>

<script>
async function loadStats() {
    try {
        const response = await fetch('../api/reports-api.php');
        const data = await response.json();
        
        if (data.success) {
            updateDashboard(data.data);
        } else {
            Toast.error('Erro ao carregar estatísticas');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao carregar estatísticas');
    }
}

function updateDashboard(data) {
    // 1. Totals
    document.getElementById('totalSchools').textContent = data.totals.schools;
    document.getElementById('totalProfessors').textContent = data.totals.professors;
    document.getElementById('totalTeams').textContent = data.totals.teams;
    document.getElementById('totalStudents').textContent = data.totals.students;
    
    // 2. Modality Table
    const modTbody = document.querySelector('#modalityTable tbody');
    modTbody.innerHTML = '';
    
    data.byModality.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${item.name}</strong></td>
            <td style="text-align: right;">${item.team_count}</td>
            <td style="text-align: right;">${item.student_count}</td>
        `;
        modTbody.appendChild(row);
    });
    
    // 3. Category Table
    const catTbody = document.querySelector('#categoryTable tbody');
    catTbody.innerHTML = '';
    
    const maxTeams = Math.max(...data.byCategory.map(c => c.team_count), 1);
    
    data.byCategory.forEach(item => {
        const percentage = (item.team_count / maxTeams) * 100;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td style="text-align: right;">${item.team_count}</td>
            <td style="width: 50%;">
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: ${percentage}%"></div>
                </div>
            </td>
        `;
        catTbody.appendChild(row);
    });
    
    // 4. School Table
    const schoolTbody = document.querySelector('#schoolTable tbody');
    schoolTbody.innerHTML = '';
    
    data.bySchool.forEach(item => {
        const row = document.createElement('tr');
        const status = item.team_count > 0 
            ? '<span class="badge badge-success">Participando</span>' 
            : '<span class="badge" style="background: rgba(100, 100, 100, 0.2);">Sem inscrições</span>';
            
        row.innerHTML = `
            <td>${item.name}</td>
            <td style="text-align: right;">${item.team_count}</td>
            <td style="text-align: right;">${item.student_count}</td>
            <td>${status}</td>
        `;
        schoolTbody.appendChild(row);
    });
}

// Initialize
loadStats();
</script>

<?php include '../includes/footer.php'; ?>
