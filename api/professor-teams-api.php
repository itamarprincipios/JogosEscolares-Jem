<?php
/**
 * Professor Teams API - CRUD Operations for Teams
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireProfessor();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$schoolId = getCurrentSchoolId();

try {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                // List teams for the professor's school
                $teams = query("
                    SELECT 
                        r.*,
                        m.name as modality_name,
                        c.name as category_name,
                        (SELECT COUNT(*) FROM enrollments e WHERE e.registration_id = r.id) as athlete_count
                    FROM registrations r
                    JOIN modalities m ON r.modality_id = m.id
                    JOIN categories c ON r.category_id = c.id
                    WHERE r.school_id = ?
                    ORDER BY r.created_at DESC
                ", [$schoolId]);
                
                echo json_encode(['success' => true, 'data' => $teams]);
                
            } elseif ($action === 'details') {
                $id = $_GET['id'] ?? null;
                if (!$id) throw new Exception('ID não fornecido');
                
                // Get team details
                $team = queryOne("
                    SELECT 
                        r.*,
                        m.name as modality_name,
                        c.name as category_name,
                        s.name as school_name
                    FROM registrations r
                    JOIN modalities m ON r.modality_id = m.id
                    JOIN categories c ON r.category_id = c.id
                    JOIN schools s ON r.school_id = s.id
                    WHERE r.id = ? AND r.school_id = ?
                ", [$id, $schoolId]);
                
                if (!$team) throw new Exception('Equipe não encontrada');
                
                // Get enrolled athletes
                $athletes = query("
                    SELECT s.*, e.id as enrollment_id
                    FROM students s
                    JOIN enrollments e ON s.id = e.student_id
                    WHERE e.registration_id = ?
                    ORDER BY s.name
                ", [$id]);
                
                $team['athletes'] = $athletes;
                
                echo json_encode(['success' => true, 'data' => $team]);
                
            } else {
                throw new Exception('Ação não especificada');
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'create') {
                // Create new team (registration)
                if (empty($data['modality_id']) || empty($data['category_id']) || empty($data['gender'])) {
                    throw new Exception('Preencha todos os campos obrigatórios');
                }
                
                // Check if team already exists
                $exists = queryOne("
                    SELECT id FROM registrations 
                    WHERE school_id = ? AND modality_id = ? AND category_id = ? AND gender = ?
                ", [$schoolId, $data['modality_id'], $data['category_id'], $data['gender']]);
                
                if ($exists) {
                    throw new Exception('Esta equipe já está cadastrada');
                }
                
                $sql = "INSERT INTO registrations (school_id, modality_id, category_id, gender, status) 
                        VALUES (?, ?, ?, ?, 'pending')";
                
                if (execute($sql, [$schoolId, $data['modality_id'], $data['category_id'], $data['gender']])) {
                    echo json_encode(['success' => true, 'id' => lastInsertId()]);
                } else {
                    throw new Exception('Erro ao criar equipe');
                }
                
            } elseif ($action === 'add_athlete') {
                // Add athlete to team
                $teamId = $data['team_id'];
                $studentId = $data['student_id'];
                
                // Verify team ownership
                $team = queryOne("SELECT * FROM registrations WHERE id = ? AND school_id = ?", [$teamId, $schoolId]);
                if (!$team) throw new Exception('Equipe não encontrada');
                
                // Verify student ownership
                $student = queryOne("SELECT * FROM students WHERE id = ? AND school_id = ?", [$studentId, $schoolId]);
                if (!$student) throw new Exception('Aluno não encontrado');
                
                // Check if already enrolled
                $exists = queryOne("SELECT id FROM enrollments WHERE registration_id = ? AND student_id = ?", [$teamId, $studentId]);
                if ($exists) throw new Exception('Aluno já inscrito nesta equipe');
                
                // Check gender compatibility
                if ($team['gender'] !== 'mixed' && $team['gender'] !== $student['gender']) {
                    throw new Exception('Gênero do aluno incompatível com a equipe');
                }
                
                // Check age compatibility (simplified logic, can be expanded)
                // Get category max age
                $category = queryOne("SELECT max_age FROM categories WHERE id = ?", [$team['category_id']]);
                if ($student['age'] > $category['max_age']) {
                    throw new Exception("Aluno excede a idade máxima da categoria ({$category['max_age']} anos)");
                }
                
                if (execute("INSERT INTO enrollments (registration_id, student_id) VALUES (?, ?)", [$teamId, $studentId])) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Erro ao adicionar aluno');
                }
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            $type = $_GET['type'] ?? 'team';
            
            if ($type === 'team') {
                // Delete team (only if pending)
                $team = queryOne("SELECT status FROM registrations WHERE id = ? AND school_id = ?", [$id, $schoolId]);
                
                if (!$team) throw new Exception('Equipe não encontrada');
                if ($team['status'] !== 'pending') throw new Exception('Apenas equipes pendentes podem ser excluídas');
                
                // Delete enrollments first
                execute("DELETE FROM enrollments WHERE registration_id = ?", [$id]);
                
                if (execute("DELETE FROM registrations WHERE id = ?", [$id])) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Erro ao excluir equipe');
                }
                
            } elseif ($type === 'athlete') {
                // Remove athlete from team
                $enrollmentId = $id; // Here ID is enrollment_id
                
                // Verify ownership via join
                $enrollment = queryOne("
                    SELECT e.id 
                    FROM enrollments e 
                    JOIN registrations r ON e.registration_id = r.id 
                    WHERE e.id = ? AND r.school_id = ?
                ", [$enrollmentId, $schoolId]);
                
                if (!$enrollment) throw new Exception('Inscrição não encontrada');
                
                if (execute("DELETE FROM enrollments WHERE id = ?", [$enrollmentId])) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Erro ao remover aluno');
                }
            }
            break;
            
        default:
            throw new Exception('Método não permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
