# Sistema JEM - Guia de Desenvolvimento

## 🎯 Como Completar o Sistema

Este guia mostra como implementar as páginas restantes seguindo os padrões já estabelecidos.

## 📐 Padrão de Implementação

### Estrutura de uma Página Admin

```php
<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin(); // Ou requireProfessor() para páginas de professor

$pageTitle = 'Título da Página';

// Lógica da página aqui

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="top-bar-title">Título</h1>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars(getCurrentUserName()); ?></div>
                <div class="user-role">Administrador</div>
            </div>
        </div>
    </div>
    
    <div class="content-wrapper">
        <!-- Conteúdo aqui -->
    </div>
</div>

<?php include '../includes/footer.php'; ?>
```

## 📝 Exemplo: Página de Gerenciamento de Escolas

### admin/schools.php

```php
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
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
                        <!-- Preenchido via JavaScript -->
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
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Telefone</label>
                        <input type="text" id="phone" class="form-input" data-format="phone">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" id="email" class="form-input">
                    </div>
                </div>
                
                <div class="form-row">
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
        }
    } catch (error) {
        Toast.error('Erro ao carregar escolas');
    }
}

// Renderizar tabela
function renderTable(schools) {
    const tbody = document.querySelector('#schoolsTable tbody');
    tbody.innerHTML = '';
    
    if (schools.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;">Nenhuma escola encontrada</td></tr>';
        return;
    }
    
    schools.forEach(school => {
        const row = `
            <tr>
                <td>${school.name}</td>
                <td>${school.municipality}</td>
                <td>${school.phone || '-'}</td>
                <td>${school.email || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-secondary" onclick="editSchool(${school.id})">Editar</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteSchool(${school.id})">Excluir</button>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

// Renderizar paginação
function renderPagination(page, totalPages) {
    const pagination = document.getElementById('pagination');
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
    const data = {
        id: id || undefined,
        name: document.getElementById('name').value,
        municipality: document.getElementById('municipality').value,
        address: document.getElementById('address').value,
        phone: document.getElementById('phone').value,
        email: document.getElementById('email').value,
        director: document.getElementById('director').value,
        coordinator: document.getElementById('coordinator').value
    };
    
    try {
        const response = await fetch('../api/schools-api.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success(id ? 'Escola atualizada!' : 'Escola criada!');
            closeModal();
            loadSchools();
        } else {
            Toast.error(result.error || 'Erro ao salvar escola');
        }
    } catch (error) {
        Toast.error('Erro ao salvar escola');
    }
}

// Excluir escola
async function deleteSchool(id) {
    if (!confirm('Tem certeza que deseja excluir esta escola?')) {
        return;
    }
    
    try {
        const response = await fetch(`../api/schools-api.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success('Escola excluída!');
            loadSchools();
        } else {
            Toast.error(result.error || 'Erro ao excluir escola');
        }
    } catch (error) {
        Toast.error('Erro ao excluir escola');
    }
}

// Carregar ao iniciar
loadSchools();
</script>

<?php include '../includes/footer.php'; ?>
```

## 🔄 Padrão para Outras Páginas

### Categories e Modalities

Seguem o mesmo padrão de Schools, apenas ajustando:
- Campos do formulário
- Endpoint da API
- Nomes de variáveis

### Registrations (Aprovação)

```javascript
// Aprovar inscrição
async function approveRegistration(id) {
    const response = await fetch('../api/registrations-api.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status: 'approved' })
    });
    // ...
}

// Rejeitar inscrição
async function rejectRegistration(id) {
    const reason = prompt('Motivo da rejeição:');
    if (!reason) return;
    
    const response = await fetch('../api/registrations-api.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status: 'rejected', rejection_reason: reason })
    });
    // ...
}
```

## 📊 Relatórios

### Estrutura Básica

```php
<div class="glass-card">
    <h2>Filtros</h2>
    <div class="form-row">
        <select id="reportType" class="form-select">
            <option value="by_school">Por Escola</option>
            <option value="by_modality">Por Modalidade</option>
            <option value="by_category">Por Categoria</option>
        </select>
        <button class="btn btn-primary" onclick="generateReport()">Gerar</button>
        <button class="btn btn-secondary" onclick="exportCSV()">Exportar CSV</button>
        <button class="btn btn-secondary" onclick="window.print()">Imprimir</button>
    </div>
</div>

<div id="reportContent" class="glass-card" style="margin-top: 2rem;">
    <!-- Conteúdo do relatório -->
</div>
```

### Exportar CSV

```javascript
function exportCSV() {
    const data = [
        ['Coluna 1', 'Coluna 2', 'Coluna 3'],
        ['Valor 1', 'Valor 2', 'Valor 3']
    ];
    
    const csv = data.map(row => row.join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'relatorio.csv';
    a.click();
}
```

## 👨‍🏫 Módulo Professor

### Upload de Foto de Aluno

```php
// Em api/students-api.php
if ($_FILES['photo']) {
    $file = $_FILES['photo'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $path = STUDENT_PHOTOS_DIR . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $path)) {
        $photoUrl = '/uploads/students/' . $filename;
    }
}
```

### Criar Inscrição com Validações

```javascript
// Validar idade
function validateAge(birthDate, maxAge) {
    const age = calculateAge(birthDate);
    return age <= maxAge;
}

// Validar gênero
function validateGender(studentGender, teamGender, allowsMixed) {
    if (allowsMixed || teamGender === 'mixed') return true;
    return studentGender === teamGender;
}

// Verificar duplicatas
async function checkDuplicate(studentId, modalityId, categoryId) {
    const response = await fetch(
        `../api/registrations-api.php?action=check_duplicate&student=${studentId}&modality=${modalityId}&category=${categoryId}`
    );
    return response.json();
}
```

## 🎨 Componentes Reutilizáveis

### Loading Spinner

```html
<div class="loading" style="display: none;">
    <div style="text-align: center; padding: 2rem;">
        <div style="border: 4px solid var(--border); border-top: 4px solid var(--primary); 
                    border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; 
                    margin: 0 auto;"></div>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
```

### Confirmação de Exclusão

```javascript
async function confirmDelete(message, callback) {
    if (confirm(message)) {
        await callback();
    }
}

// Uso
confirmDelete('Tem certeza?', () => deleteItem(id));
```

## 📱 Responsividade

### Media Queries Importantes

```css
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
```

## 🔍 Debugging

### Console Logs Úteis

```javascript
console.log('Data:', data);
console.table(array);
console.error('Error:', error);
```

### PHP Error Logging

```php
error_log("Debug: " . print_r($data, true));
```

## ✅ Checklist de Implementação

Para cada nova página:

- [ ] Criar arquivo PHP com estrutura padrão
- [ ] Incluir header, sidebar, footer
- [ ] Implementar proteção de rota (requireAdmin/requireProfessor)
- [ ] Criar formulário com validação
- [ ] Implementar funções JavaScript (CRUD)
- [ ] Conectar com API endpoint
- [ ] Adicionar toast notifications
- [ ] Testar responsividade
- [ ] Testar validações
- [ ] Adicionar loading states

## 🚀 Próximos Passos

1. Implemente `admin/schools.php` usando o exemplo acima
2. Replique para `categories.php` e `modalities.php`
3. Implemente `professors.php` com aprovação de solicitações
4. Crie `registrations.php` para aprovação de inscrições
5. Implemente módulo professor completo
6. Adicione relatórios
7. Teste tudo!

---

**Boa sorte com o desenvolvimento! 🎉**
