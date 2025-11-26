<?php
/**
 * Professors API - CRUD Operations and Request Approval
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                // List all active professors
                $professors = query("
                    SELECT u.*, s.name as school_name 
                    FROM users u
                    LEFT JOIN schools s ON u.school_id = s.id
                    WHERE u.role = 'professor' AND u.is_active = 1
                    ORDER BY u.name
                ");
                echo json_encode(['success' => true, 'data' => $professors]);
                
            } elseif ($action === 'requests') {
                // List pending inactive professors (waiting for approval)
                $requests = query("
                    SELECT u.*, s.name as school_name 
                    FROM users u
                    LEFT JOIN schools s ON u.school_id = s.id
                    WHERE u.role = 'professor' AND u.is_active = 0
                    ORDER BY u.created_at DESC
                ");
                echo json_encode(['success' => true, 'data' => $requests]);
                
            } else {
                throw new Exception('Ação não especificada');
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['action'])) {
                // Handle special actions
                if ($data['action'] === 'approve_request') {
                    // Approve registration (activate user)
                    $userId = $data['request_id'];
                    
                    if (execute("UPDATE users SET is_active = 1 WHERE id = ?", [$userId])) {
                        echo json_encode(['success' => true, 'message' => 'Professor aprovado com sucesso']);
                    } else {
                        throw new Exception('Erro ao aprovar professor');
                    }
                    
                } elseif ($data['action'] === 'reject_request') {
                    // Reject registration (delete user)
                    $userId = $data['request_id'];
                    
                    if (execute("DELETE FROM users WHERE id = ?", [$userId])) {
                        echo json_encode(['success' => true]);
                    } else {
                        throw new Exception('Erro ao rejeitar solicitação');
                    }
                    
                } else {
                    throw new Exception('Ação inválida');
                }
            } else {
                // Create new professor (manually by admin - active by default)
                if (emailExists($data['email'])) {
                    throw new Exception('Este email já está cadastrado');
                }
                
                if (!validateCPF($data['cpf'])) {
                    throw new Exception('CPF inválido');
                }
                
                $hashedPassword = hashPassword($data['password']);
                
                $sql = "INSERT INTO users (name, email, password, cpf, phone, role, school_id, is_active) 
                        VALUES (?, ?, ?, ?, ?, 'professor', ?, 1)";
                
                if (execute($sql, [
                    $data['name'],
                    $data['email'],
                    $hashedPassword,
                    $data['cpf'],
                    $data['phone'] ?? null,
                    $data['school_id']
                ])) {
                    echo json_encode(['success' => true, 'id' => lastInsertId()]);
                } else {
                    throw new Exception('Erro ao criar professor');
                }
            }
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Update professor (mainly for activating/deactivating)
            $sql = "UPDATE users SET is_active = ? WHERE id = ? AND role = 'professor'";
            
            if (execute($sql, [$data['is_active'] ? 1 : 0, $data['id']])) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Erro ao atualizar professor');
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID não fornecido');
            }
            
            // Check if professor has students
            $hasStudents = queryOne("SELECT COUNT(*) as count FROM students WHERE school_id IN (SELECT school_id FROM users WHERE id = ?)", [$id])['count'];
            
            if ($hasStudents > 0) {
                throw new Exception('Não é possível excluir professor com alunos cadastrados');
            }
            
            if (execute("DELETE FROM users WHERE id = ? AND role = 'professor'", [$id])) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Erro ao excluir professor');
            }
            break;
            
        default:
            throw new Exception('Método não permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
