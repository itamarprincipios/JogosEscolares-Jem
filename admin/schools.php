<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

$pageTitle = 'Gerenciar Escolas';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="top-bar-title">Escolas</h1>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars(getCurrentUserName()); ?></div>
                <div class="user-role">Administrador</div>
            </div>
        </div>
    </div>
    
    <div class="content-wrapper">
        <!-- Header com busca e botão -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <input 
                    type="text" 
                    id="searchInput" 
                    class="form-input" 
                    placeholder="Buscar escola..."
                    style="width: 300px;"
                >
            </div>
            <button class="btn btn-primary" onclick="openModal()">
                <span>➕</span>
                <span>Nova Escola</span>
            </button>
        </div>
        
        <!-- Tabela -->
        <div class="glass-card">
            <div class="table-container">
                <table class="table" id="schoolsTable">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Município</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">
                                Carregando...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div id="pagination" style="margin-top: 1rem; text-align: center;"></div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="schoolModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Nova Escola</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="schoolForm">
                <input type="hidden" id="schoolId">
                
                <div class="form-group">
                    <label class="form-label">Nome *</label>
                    <input type="text" id="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Município *</label>
                    <input type="text" id="municipality" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Endereço</label>
                    <input type="text" id="address" class="form-input">
                </div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Telefone</label>
                        <input type="text" id="phone" class="form-input" data-format="phone">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" id="email" class="form-input">
                    </div>
                </div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Diretor</label>
                        <input type="text" id="director" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Coordenador</label>
                        <input type="text" id="coordinator" class="form-input">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="saveSchool()">Salvar</button>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let searchTerm = '';

// Carregar escolas
async function loadSchools() {
    try {
        const response = await fetch(`../api/schools-api.php?action=list&page=${currentPage}&search=${searchTerm}`);
        const data = await response.json();
        
        if (data.success) {
            renderTable(data.data);
            renderPagination(data.page, data.pages);
        } else {
            Toast.error('Erro ao carregar escolas');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao carregar escolas');
    }
}

// Renderizar tabela
function renderTable(schools) {
    const tbody = document.querySelector('#schoolsTable tbody');
    tbody.innerHTML = '';
    
    if (schools.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-secondary);">Nenhuma escola encontrada</td></tr>';
        return;
    }
    
    schools.forEach(school => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${school.name}</td>
            <td>${school.municipality}</td>
            <td>${school.phone || '-'}</td>
            <td>${school.email || '-'}</td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="editSchool(${school.id})" style="margin-right: 0.5rem;">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="deleteSchool(${school.id})">Excluir</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Renderizar paginação
function renderPagination(page, totalPages) {
    const pagination = document.getElementById('pagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '';
    
    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="btn btn-sm ${i === page ? 'btn-primary' : 'btn-secondary'}" 
                        onclick="changePage(${i})" style="margin: 0 0.25rem;">${i}</button>`;
    }
    
    pagination.innerHTML = html;
}

// Mudar página
function changePage(page) {
    currentPage = page;
    loadSchools();
}

// Busca
document.getElementById('searchInput').addEventListener('input', (e) => {
    searchTerm = e.target.value;
    currentPage = 1;
    loadSchools();
});

// Abrir modal
function openModal(id = null) {
    const modal = document.getElementById('schoolModal');
    const form = document.getElementById('schoolForm');
    form.reset();
    
    if (id) {
        document.getElementById('modalTitle').textContent = 'Editar Escola';
        loadSchool(id);
    } else {
        document.getElementById('modalTitle').textContent = 'Nova Escola';
        document.getElementById('schoolId').value = '';
    }
    
    modal.classList.add('active');
}

// Fechar modal
function closeModal() {
    document.getElementById('schoolModal').classList.remove('active');
}

// Carregar escola para edição
async function loadSchool(id) {
    try {
        const response = await fetch(`../api/schools-api.php?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            const school = data.data;
            document.getElementById('schoolId').value = school.id;
            document.getElementById('name').value = school.name;
            document.getElementById('municipality').value = school.municipality;
            document.getElementById('address').value = school.address || '';
            document.getElementById('phone').value = school.phone || '';
            document.getElementById('email').value = school.email || '';
            document.getElementById('director').value = school.director || '';
            document.getElementById('coordinator').value = school.coordinator || '';
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao carregar escola');
    }
}

// Editar escola
function editSchool(id) {
    openModal(id);
}

// Salvar escola
async function saveSchool() {
    const id = document.getElementById('schoolId').value;
    const name = document.getElementById('name').value.trim();
    const municipality = document.getElementById('municipality').value.trim();
    
    if (!name || !municipality) {
        Toast.error('Por favor, preencha os campos obrigatórios');
        return;
    }
    
    const data = {
        id: id || undefined,
        name: name,
        municipality: municipality,
        address: document.getElementById('address').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        email: document.getElementById('email').value.trim(),
        director: document.getElementById('director').value.trim(),
        coordinator: document.getElementById('coordinator').value.trim()
    };
    
    try {
        const response = await fetch('../api/schools-api.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success(id ? 'Escola atualizada com sucesso!' : 'Escola criada com sucesso!');
            closeModal();
            loadSchools();
        } else {
            Toast.error(result.error || 'Erro ao salvar escola');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao salvar escola');
    }
}

// Excluir escola
async function deleteSchool(id) {
    if (!confirm('Tem certeza que deseja excluir esta escola? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    try {
        const response = await fetch(`../api/schools-api.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success('Escola excluída com sucesso!');
            loadSchools();
        } else {
            Toast.error(result.error || 'Erro ao excluir escola');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao excluir escola');
    }
}

// Fechar modal ao clicar fora
document.getElementById('schoolModal').addEventListener('click', (e) => {
    if (e.target.id === 'schoolModal') {
        closeModal();
    }
});

// Carregar ao iniciar
loadSchools();
</script>

<?php include '../includes/footer.php'; ?>
