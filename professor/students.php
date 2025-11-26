<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireProfessor();

$pageTitle = 'Meus Atletas';

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-bar">
        <h1 class="top-bar-title">Meus Atletas</h1>
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
                <input 
                    type="text" 
                    id="searchStudent" 
                    class="form-input" 
                    placeholder="Buscar atleta..."
                    style="width: 300px;"
                >
            </div>
            <button class="btn btn-primary" onclick="openStudentModal()">
                <span>➕</span>
                <span>Novo Atleta</span>
            </button>
        </div>
        
        <div class="glass-card">
            <div class="table-container">
                <table class="table" id="studentsTable">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nome</th>
                            <th>RG / Certidão</th>
                            <th>Idade</th>
                            <th>Gênero</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="6" style="text-align: center; padding: 2rem;">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Student -->
<div class="modal-overlay" id="studentModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Novo Atleta</h3>
            <button class="modal-close" onclick="closeStudentModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="studentForm" enctype="multipart/form-data">
                <input type="hidden" id="studentId" name="id">
                
                <div class="form-group">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" id="name" name="name" class="form-input" required>
                </div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">RG ou Certidão *</label>
                        <input type="text" id="document_number" name="document_number" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Data de Nascimento *</label>
                        <input type="date" id="birthDate" name="birth_date" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Gênero *</label>
                    <select id="gender" name="gender" class="form-select" required>
                        <option value="">Selecione...</option>
                        <option value="M">Masculino</option>
                        <option value="F">Feminino</option>
                    </select>
                </div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Foto do Aluno *</label>
                        <input type="file" id="photo" name="photo" class="form-input" accept="image/*">
                        <small style="color: var(--text-secondary);">JPG ou PNG</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Foto do Documento *</label>
                        <input type="file" id="document_photo" name="document_photo" class="form-input" accept="image/*,application/pdf">
                        <small style="color: var(--text-secondary);">JPG, PNG ou PDF</small>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeStudentModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="saveStudent()">Salvar</button>
        </div>
    </div>
</div>

<style>
.student-thumb {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--bg-tertiary);
}
</style>

<script>
// Load students
async function loadStudents() {
    try {
        const search = document.getElementById('searchStudent').value;
        const response = await fetch('../api/students-api.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            let students = data.data;
            
            if (search) {
                students = students.filter(s => 
                    s.name.toLowerCase().includes(search.toLowerCase()) ||
                    s.document_number.includes(search)
                );
            }
            
            renderStudentsTable(students);
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao carregar alunos');
    }
}

// Render table
function renderStudentsTable(students) {
    const tbody = document.querySelector('#studentsTable tbody');
    tbody.innerHTML = '';
    
    if (students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-secondary);">Nenhum atleta encontrado</td></tr>';
        return;
    }
    
    students.forEach(student => {
        const photoUrl = student.photo_path ? '../' + student.photo_path : '../assets/img/default-avatar.png';
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><img src="${photoUrl}" class="student-thumb" onerror="this.src='https://ui-avatars.com/api/?name=${student.name}'"></td>
            <td>${student.name}</td>
            <td>${student.document_number || '-'}</td>
            <td>${student.age} anos</td>
            <td>${student.gender === 'M' ? 'Masculino' : 'Feminino'}</td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="editStudent(${student.id})" style="margin-right: 0.5rem;">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="deleteStudent(${student.id})">Excluir</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Open modal
function openStudentModal(id = null) {
    const modal = document.getElementById('studentModal');
    const form = document.getElementById('studentForm');
    form.reset();
    
    if (id) {
        document.getElementById('modalTitle').textContent = 'Editar Atleta';
        document.getElementById('studentId').value = id;
        loadStudentDetails(id);
    } else {
        document.getElementById('modalTitle').textContent = 'Novo Atleta';
        document.getElementById('studentId').value = '';
    }
    
    modal.classList.add('active');
}

function closeStudentModal() {
    document.getElementById('studentModal').classList.remove('active');
}

// Load details
async function loadStudentDetails(id) {
    try {
        const response = await fetch(`../api/students-api.php?action=details&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const s = data.data;
            document.getElementById('name').value = s.name;
            document.getElementById('document_number').value = s.document_number;
            document.getElementById('birthDate').value = s.birth_date;
            document.getElementById('gender').value = s.gender;
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Save student
async function saveStudent() {
    const form = document.getElementById('studentForm');
    const formData = new FormData(form);
    
    if (!formData.get('name') || !formData.get('document_number') || !formData.get('birth_date') || !formData.get('gender')) {
        Toast.error('Preencha todos os campos obrigatórios');
        return;
    }
    
    // Check files for new student
    if (!formData.get('id')) {
        if (document.getElementById('photo').files.length === 0 || document.getElementById('document_photo').files.length === 0) {
            Toast.error('As fotos são obrigatórias para novos cadastros');
            return;
        }
    }
    
    try {
        const response = await fetch('../api/students-api.php', {
            method: 'POST',
            body: formData // Fetch automatically sets Content-Type to multipart/form-data
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success('Atleta salvo com sucesso!');
            closeStudentModal();
            loadStudents();
        } else {
            Toast.error(result.error || 'Erro ao salvar atleta');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao salvar atleta');
    }
}

// Delete student
async function deleteStudent(id) {
    if (!confirm('Tem certeza que deseja excluir este atleta?')) return;
    
    try {
        const response = await fetch(`../api/students-api.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            Toast.success('Atleta excluído!');
            loadStudents();
        } else {
            Toast.error(result.error || 'Erro ao excluir atleta');
        }
    } catch (error) {
        console.error('Error:', error);
        Toast.error('Erro ao excluir atleta');
    }
}

// Search listener
document.getElementById('searchStudent').addEventListener('input', loadStudents);

// Close modal on outside click
document.getElementById('studentModal').addEventListener('click', (e) => {
    if (e.target.id === 'studentModal') closeStudentModal();
});

// Initialize
loadStudents();
</script>

<?php include '../includes/footer.php'; ?>
