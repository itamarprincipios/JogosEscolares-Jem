<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

$pageTitle = 'Gerenciar Modalidades';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="top-bar-title">Modalidades Esportivas</h1>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars(getCurrentUserName()); ?></div>
                <div class="user-role">Administrador</div>
            </div>
        </div>
    </div>
    
    <div class="content-wrapper">
        <!-- Header com botão -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <p style="color: var(--text-secondary);">
                    Gerencie as modalidades esportivas disponíveis para inscrição
                </p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">
                <span>➕</span>
                <span>Nova Modalidade</span>
            </button>
        </div>
        
        <!-- Tabela -->
        <div class="glass-card">
            <div class="table-container">
                <table class="table" id="modalitiesTable">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Permite Equipes Mistas</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 2rem;">
                                Carregando...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalityModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Nova Modalidade</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="modalityForm">
                <input type="hidden" id="modalityId">
                
                <div class="form-group">
                    <label class="form-label">Nome da Modalidade *</label>
                    <input 
                        type="text" 
                        id="name" 
                        class="form-input" 
                        placeholder="Ex: Futsal, Vôlei, Handebol"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input 
                            type="checkbox" 
                            id="allowsMixed" 
                            style="width: auto; cursor: pointer;"
                        >
                        <span class="form-label" style="margin: 0;">Permite equipes mistas (masculino e feminino juntos)</span>
                    </label>
                    <small style="color: var(--text-secondary); font-size: 0.875rem; display: block; margin-top: 0.5rem;">
                        Marque esta opção se a modalidade permite que meninos e meninas joguem na mesma equipe
                    </small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="saveModality()">Salvar</button>
        </div>
    </div>
</div>

<script>
// Carregar modalidades
async function loadModalities() {
    try {
        const response = await fetch('../api/modalities-api.php');
        const data = await response.json();
        
        if (data.success) {
            renderTable(data.data);
        } else {
            Toast.error('Erro ao carregar modalidades');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao carregar modalidades');
    }
}

// Renderizar tabela
function renderTable(modalities) {
    const tbody = document.querySelector('#modalitiesTable tbody');
    tbody.innerHTML = '';
    
    if (modalities.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-secondary);">Nenhuma modalidade encontrada</td></tr>';
        return;
    }
    
    // Ordenar por nome
    modalities.sort((a, b) => a.name.localeCompare(b.name));
    
    modalities.forEach(modality => {
        const row = document.createElement('tr');
        const allowsMixedBadge = modality.allows_mixed 
            ? '<span class="badge badge-success">Sim</span>' 
            : '<span class="badge" style="background: rgba(100, 100, 100, 0.2); color: var(--text-secondary);">Não</span>';
        
        row.innerHTML = `
            <td><strong>${modality.name}</strong></td>
            <td>${allowsMixedBadge}</td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="editModality(${modality.id})" style="margin-right: 0.5rem;">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="deleteModality(${modality.id})">Excluir</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Abrir modal
function openModal(id = null) {
    const modal = document.getElementById('modalityModal');
    const form = document.getElementById('modalityForm');
    form.reset();
    
    if (id) {
        document.getElementById('modalTitle').textContent = 'Editar Modalidade';
        loadModality(id);
    } else {
        document.getElementById('modalTitle').textContent = 'Nova Modalidade';
        document.getElementById('modalityId').value = '';
        document.getElementById('allowsMixed').checked = false;
    }
    
    modal.classList.add('active');
}

// Fechar modal
function closeModal() {
    document.getElementById('modalityModal').classList.remove('active');
}

// Carregar modalidade para edição
async function loadModality(id) {
    try {
        const response = await fetch('../api/modalities-api.php');
        const data = await response.json();
        
        if (data.success) {
            const modality = data.data.find(m => m.id == id);
            if (modality) {
                document.getElementById('modalityId').value = modality.id;
                document.getElementById('name').value = modality.name;
                document.getElementById('allowsMixed').checked = modality.allows_mixed == 1;
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao carregar modalidade');
    }
}

// Editar modalidade
function editModality(id) {
    openModal(id);
}

// Salvar modalidade
async function saveModality() {
    const id = document.getElementById('modalityId').value;
    const name = document.getElementById('name').value.trim();
    const allowsMixed = document.getElementById('allowsMixed').checked;
    
    if (!name) {
        Toast.error('Por favor, preencha o nome da modalidade');
        return;
    }
    
    const data = {
        id: id || undefined,
        name: name,
        allows_mixed: allowsMixed
    };
    
    try {
        const response = await fetch('../api/modalities-api.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success(id ? 'Modalidade atualizada com sucesso!' : 'Modalidade criada com sucesso!');
            closeModal();
            loadModalities();
        } else {
            Toast.error(result.error || 'Erro ao salvar modalidade');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao salvar modalidade');
    }
}

// Excluir modalidade
async function deleteModality(id) {
    if (!confirm('Tem certeza que deseja excluir esta modalidade? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    try {
        const response = await fetch(`../api/modalities-api.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success('Modalidade excluída com sucesso!');
            loadModalities();
        } else {
            Toast.error(result.error || 'Erro ao excluir modalidade');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao excluir modalidade');
    }
}

// Fechar modal ao clicar fora
document.getElementById('modalityModal').addEventListener('click', (e) => {
    if (e.target.id === 'modalityModal') {
        closeModal();
    }
});

// Carregar ao iniciar
loadModalities();
</script>

<?php include '../includes/footer.php'; ?>
