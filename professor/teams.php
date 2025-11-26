<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireProfessor();

$pageTitle = 'Minhas Equipes';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="top-bar-title">Minhas Equipes</h1>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars(getCurrentUserName()); ?></div>
                <div class="user-role">Professor</div>
            </div>
        </div>
    </div>
    
    <div class="content-wrapper">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div style="display: flex; gap: 1rem;">
                <select id="filterModality" class="form-select" style="width: 200px;">
                    <option value="">Todas as modalidades</option>
                </select>
                <select id="filterStatus" class="form-select" style="width: 200px;">
                    <option value="">Todos os status</option>
                    <option value="pending">Pendente</option>
                    <option value="approved">Aprovado</option>
                    <option value="rejected">Rejeitado</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="openTeamModal()">
                <span>➕</span>
                <span>Nova Equipe</span>
            </button>
        </div>
        
        <div id="teamsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
            <!-- Teams loaded here -->
        </div>
        
        <div id="emptyState" style="display: none; text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>
            <h3 style="color: var(--text-secondary); margin-bottom: 0.5rem;">Nenhuma equipe cadastrada</h3>
            <p style="color: var(--text-muted);">Clique em "Nova Equipe" para começar</p>
        </div>
    </div>
</div>

<!-- Modal: New Team -->
<div class="modal-overlay" id="teamModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Nova Equipe</h3>
            <button class="modal-close" onclick="closeTeamModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="teamForm">
                <div class="form-group">
                    <label class="form-label">Modalidade *</label>
                    <select id="modalityId" class="form-select" required>
                        <option value="">Selecione...</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Categoria *</label>
                    <select id="categoryId" class="form-select" required>
                        <option value="">Selecione...</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Gênero *</label>
                    <select id="gender" class="form-select" required>
                        <option value="">Selecione...</option>
                        <option value="M">Masculino</option>
                        <option value="F">Feminino</option>
                        <option value="mixed">Misto</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeTeamModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="saveTeam()">Criar Equipe</button>
        </div>
    </div>
</div>

<!-- Modal: Manage Athletes -->
<div class="modal-overlay" id="athletesModal">
    <div class="modal" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title" id="athletesModalTitle">Gerenciar Atletas</h3>
            <button class="modal-close" onclick="closeAthletesModal()">×</button>
        </div>
        <div class="modal-body">
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                <select id="studentSelect" class="form-select" style="flex: 1;">
                    <option value="">Selecione um aluno para adicionar...</option>
                </select>
                <button class="btn btn-primary" onclick="addAthlete()">Adicionar</button>
            </div>
            
            <div class="table-container">
                <table class="table" id="athletesTable">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Idade</th>
                            <th>Gênero</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Athletes loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeAthletesModal()">Fechar</button>
        </div>
    </div>
</div>

<style>
.team-card {
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    transition: all var(--transition-base);
}

.team-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.45);
    border-color: var(--primary);
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
.status-approved { background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
.status-rejected { background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
</style>

<script>
let currentTeamId = null;

// Load initial data
async function loadData() {
    try {
        const [modalitiesRes, categoriesRes, studentsRes] = await Promise.all([
            fetch('../api/modalities-api.php'),
            fetch('../api/categories-api.php'),
            fetch('../api/students-api.php?action=list_available') // We need to create this endpoint later
        ]);
        
        const modalities = await modalitiesRes.json();
        const categories = await categoriesRes.json();
        
        if (modalities.success) {
            const select = document.getElementById('modalityId');
            const filter = document.getElementById('filterModality');
            modalities.data.forEach(m => {
                select.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                filter.innerHTML += `<option value="${m.id}">${m.name}</option>`;
            });
        }
        
        if (categories.success) {
            const select = document.getElementById('categoryId');
            categories.data.forEach(c => {
                select.innerHTML += `<option value="${c.id}">${c.name} (até ${c.max_age} anos)</option>`;
            });
        }
        
        loadTeams();
    } catch (error) {
        console.error('Error:', error);
    }
}

// Load teams
async function loadTeams() {
    try {
        const response = await fetch('../api/professor-teams-api.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            renderTeams(data.data);
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao carregar equipes');
    }
}

// Render teams
function renderTeams(teams) {
    const grid = document.getElementById('teamsGrid');
    const emptyState = document.getElementById('emptyState');
    const filterModality = document.getElementById('filterModality').value;
    const filterStatus = document.getElementById('filterStatus').value;
    
    // Apply filters
    const filtered = teams.filter(t => {
        if (filterModality && t.modality_id != filterModality) return false;
        if (filterStatus && t.status != filterStatus) return false;
        return true;
    });
    
    grid.innerHTML = '';
    
    if (filtered.length === 0) {
        grid.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }
    
    grid.style.display = 'grid';
    emptyState.style.display = 'none';
    
    const genderLabels = { 'M': 'Masculino', 'F': 'Feminino', 'mixed': 'Misto' };
    const statusLabels = { 'pending': 'Pendente', 'approved': 'Aprovada', 'rejected': 'Rejeitada' };
    
    filtered.forEach(team => {
        const card = document.createElement('div');
        card.className = 'team-card';
        
        card.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                <div>
                    <h3 style="margin: 0 0 0.25rem 0;">${team.modality_name}</h3>
                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem;">${team.category_name}</p>
                </div>
                <span class="status-badge status-${team.status}">${statusLabels[team.status]}</span>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <div>
                    <span style="color: var(--text-secondary);">Gênero:</span>
                    <div>${genderLabels[team.gender]}</div>
                </div>
                <div>
                    <span style="color: var(--text-secondary);">Atletas:</span>
                    <div>${team.athlete_count} inscritos</div>
                </div>
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-sm btn-primary" onclick="manageAthletes(${team.id})" style="flex: 1;">
                    👥 Atletas
                </button>
                ${team.status === 'pending' ? `
                    <button class="btn btn-sm btn-danger" onclick="deleteTeam(${team.id})">
                        🗑️
                    </button>
                ` : ''}
            </div>
        `;
        
        grid.appendChild(card);
    });
}

// Create team
async function saveTeam() {
    const modalityId = document.getElementById('modalityId').value;
    const categoryId = document.getElementById('categoryId').value;
    const gender = document.getElementById('gender').value;
    
    if (!modalityId || !categoryId || !gender) {
        Toast.error('Preencha todos os campos');
        return;
    }
    
    try {
        const response = await fetch('../api/professor-teams-api.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ modality_id: modalityId, category_id: categoryId, gender })
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success('Equipe criada com sucesso!');
            closeTeamModal();
            loadTeams();
        } else {
            Toast.error(result.error || 'Erro ao criar equipe');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao criar equipe');
    }
}

// Manage athletes
async function manageAthletes(teamId) {
    currentTeamId = teamId;
    
    try {
        // Load team details and athletes
        const response = await fetch(`../api/professor-teams-api.php?action=details&id=${teamId}`);
        const data = await response.json();
        
        if (data.success) {
            const team = data.data;
            document.getElementById('athletesModalTitle').textContent = `${team.modality_name} - ${team.category_name}`;
            
            renderAthletesTable(team.athletes);
            loadAvailableStudents(); // Load students for dropdown
            
            document.getElementById('athletesModal').classList.add('active');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao carregar detalhes');
    }
}

// Render athletes table
function renderAthletesTable(athletes) {
    const tbody = document.querySelector('#athletesTable tbody');
    tbody.innerHTML = '';
    
    if (!athletes || athletes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 1rem;">Nenhum atleta inscrito</td></tr>';
        return;
    }
    
    athletes.forEach(athlete => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${athlete.name}</td>
            <td>${athlete.age} anos</td>
            <td>${athlete.gender === 'M' ? 'Masc' : 'Fem'}</td>
            <td>
                <button class="btn btn-sm btn-danger" onclick="removeAthlete(${athlete.enrollment_id})">Remover</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Load available students for dropdown
async function loadAvailableStudents() {
    try {
        // We need an endpoint that lists students NOT in this team
        // For now, let's assume we implement a generic list endpoint and filter client-side or improve API later
        // Using a placeholder endpoint for now
        const response = await fetch('../api/students-api.php?action=list'); 
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('studentSelect');
            select.innerHTML = '<option value="">Selecione um aluno para adicionar...</option>';
            
            data.data.forEach(student => {
                select.innerHTML += `<option value="${student.id}">${student.name} (${student.age} anos)</option>`;
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Add athlete
async function addAthlete() {
    const studentId = document.getElementById('studentSelect').value;
    if (!studentId) return;
    
    try {
        const response = await fetch('../api/professor-teams-api.php?action=add_athlete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ team_id: currentTeamId, student_id: studentId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success('Atleta adicionado!');
            manageAthletes(currentTeamId); // Reload list
        } else {
            Toast.error(result.error || 'Erro ao adicionar atleta');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao adicionar atleta');
    }
}

// Remove athlete
async function removeAthlete(enrollmentId) {
    if (!confirm('Remover atleta da equipe?')) return;
    
    try {
        const response = await fetch(`../api/professor-teams-api.php?action=delete&id=${enrollmentId}&type=athlete`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success('Atleta removido!');
            manageAthletes(currentTeamId); // Reload list
        } else {
            Toast.error(result.error || 'Erro ao remover atleta');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao remover atleta');
    }
}

// Delete team
async function deleteTeam(id) {
    if (!confirm('Tem certeza que deseja excluir esta equipe?')) return;
    
    try {
        const response = await fetch(`../api/professor-teams-api.php?action=delete&id=${id}&type=team`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success('Equipe excluída!');
            loadTeams();
        } else {
            Toast.error(result.error || 'Erro ao excluir equipe');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao excluir equipe');
    }
}

// Modals
function openTeamModal() {
    document.getElementById('teamModal').classList.add('active');
}
function closeTeamModal() {
    document.getElementById('teamModal').classList.remove('active');
}
function closeAthletesModal() {
    document.getElementById('athletesModal').classList.remove('active');
    loadTeams(); // Refresh main list to update counts
}

// Filter listeners
document.getElementById('filterModality').addEventListener('change', loadTeams);
document.getElementById('filterStatus').addEventListener('change', loadTeams);

// Initialize
loadData();
</script>

<?php include '../includes/footer.php'; ?>
