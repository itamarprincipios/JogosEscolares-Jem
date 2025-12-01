<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

$pageTitle = 'Gerenciar Categorias';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="top-bar-title">Categorias</h1>
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
                    Gerencie as categorias por ano de nascimento para as competições
                </p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">
                <span>➕</span>
                <span>Nova Categoria</span>
            </button>
        </div>
        
        <!-- Tabela -->
        <div class="glass-card">
            <div class="table-container">
                <table class="table" id="categoriesTable">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Anos de Nascimento</th>
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
<div class="modal-overlay" id="categoryModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Nova Categoria</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="categoryForm">
                <input type="hidden" id="categoryId">
                
                <div class="form-group">
                    <label class="form-label">Nome da Categoria *</label>
                    <input 
                        type="text" 
                        id="name" 
                        class="form-input" 
                        placeholder="Ex: Fraldinha, Pré Mirin, Mirin"
                        required
                    >
                    <small style="color: var(--text-secondary); font-size: 0.875rem;">
                        Nome da categoria conforme regulamento
                    </small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Ano de Nascimento Mínimo *</label>
                    <input 
                        type="number" 
                        id="minBirthYear" 
                        class="form-input" 
                        min="2000" 
                        max="2030"
                        placeholder="Ex: 2010"
                        required
                    >
                    <small style="color: var(--text-secondary); font-size: 0.875rem;">
                        Ano de nascimento mais antigo permitido
                    </small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Ano de Nascimento Máximo *</label>
                    <input 
                        type="number" 
                        id="maxBirthYear" 
                        class="form-input" 
                        min="2000" 
                        max="2030"
                        placeholder="Ex: 2011"
                        required
                    >
                    <small style="color: var(--text-secondary); font-size: 0.875rem;">
                        Ano de nascimento mais recente permitido
                    </small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="saveCategory()">Salvar</button>
        </div>
    </div>
</div>

<script>
// Carregar categorias
async function loadCategories() {
    try {
        const response = await fetch('../api/categories-api.php');
        const data = await response.json();
        
        if (data.success) {
            renderTable(data.data);
        } else {
            Toast.error('Erro ao carregar categorias');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao carregar categorias');
    }
}

// Renderizar tabela
function renderTable(categories) {
    const tbody = document.querySelector('#categoriesTable tbody');
    tbody.innerHTML = '';
    
    if (categories.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 2rem; color: var(--text-secondary);">Nenhuma categoria encontrada</td></tr>';
        return;
    }
    
    // Ordenar por ano de nascimento (mais recente primeiro)
    categories.sort((a, b) => b.min_birth_year - a.min_birth_year);
    
    categories.forEach(category => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${category.name}</strong></td>
            <td>${category.min_birth_year} - ${category.max_birth_year}</td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="editCategory(${category.id})" style="margin-right: 0.5rem;">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="deleteCategory(${category.id})">Excluir</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Abrir modal
function openModal(id = null) {
    const modal = document.getElementById('categoryModal');
    const form = document.getElementById('categoryForm');
    form.reset();
    
    if (id) {
        document.getElementById('modalTitle').textContent = 'Editar Categoria';
        loadCategory(id);
    } else {
        document.getElementById('modalTitle').textContent = 'Nova Categoria';
        document.getElementById('categoryId').value = '';
    }
    
    modal.classList.add('active');
}

// Fechar modal
function closeModal() {
    document.getElementById('categoryModal').classList.remove('active');
}

// Carregar categoria para edição
async function loadCategory(id) {
    try {
        const response = await fetch('../api/categories-api.php');
        const data = await response.json();
        
        if (data.success) {
            const category = data.data.find(c => c.id == id);
            if (category) {
                document.getElementById('categoryId').value = category.id;
                document.getElementById('name').value = category.name;
                document.getElementById('minBirthYear').value = category.min_birth_year;
                document.getElementById('maxBirthYear').value = category.max_birth_year;
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao carregar categoria');
    }
}

// Editar categoria
function editCategory(id) {
    openModal(id);
}

// Salvar categoria
async function saveCategory() {
    const id = document.getElementById('categoryId').value;
    const name = document.getElementById('name').value.trim();
    const minBirthYear = parseInt(document.getElementById('minBirthYear').value);
    const maxBirthYear = parseInt(document.getElementById('maxBirthYear').value);
    
    if (!name || !minBirthYear || !maxBirthYear) {
        Toast.error('Por favor, preencha todos os campos obrigatórios');
        return;
    }
    
    if (minBirthYear < 2000 || maxBirthYear > 2030) {
        Toast.error('Os anos de nascimento devem estar entre 2000 e 2030');
        return;
    }
    
    if (minBirthYear > maxBirthYear) {
        Toast.error('O ano mínimo não pode ser maior que o ano máximo');
        return;
    }
    
    const data = {
        id: id || undefined,
        name: name,
        min_birth_year: minBirthYear,
        max_birth_year: maxBirthYear
    };
    
    try {
        const response = await fetch('../api/categories-api.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success(id ? 'Categoria atualizada com sucesso!' : 'Categoria criada com sucesso!');
            closeModal();
            loadCategories();
        } else {
            Toast.error(result.error || 'Erro ao salvar categoria');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao salvar categoria');
    }
}

// Excluir categoria
async function deleteCategory(id) {
    if (!confirm('Tem certeza que deseja excluir esta categoria? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    try {
        const response = await fetch(`../api/categories-api.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success('Categoria excluída com sucesso!');
            loadCategories();
        } else {
            Toast.error(result.error || 'Erro ao excluir categoria');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao excluir categoria');
    }
}

// Fechar modal ao clicar fora
document.getElementById('categoryModal').addEventListener('click', (e) => {
    if (e.target.id === 'categoryModal') {
        closeModal();
    }
});

// Carregar ao iniciar
loadCategories();
</script>

<?php include '../includes/footer.php'; ?>
