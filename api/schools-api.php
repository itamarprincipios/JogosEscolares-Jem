<?php
/**
 * Schools API - CRUD Operations
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
                $search = $_GET['search'] ?? '';
                $page = max(1, intval($_GET['page'] ?? 1));
                $limit = ITEMS_PER_PAGE;
                $offset = ($page - 1) * $limit;
                
                $where = '';
                $params = [];
                
                if ($search) {
                    $where = "WHERE name LIKE ? OR municipality LIKE ?";
                    $params = ["%$search%", "%$search%"];
                }
                
                $total = queryOne("SELECT COUNT(*) as count FROM schools $where", $params)['count'];
                $schools = query("SELECT * FROM schools $where ORDER BY name LIMIT $limit OFFSET $offset", $params);
                
                echo json_encode([
                    'success' => true,
                    'data' => $schools,
                    'total' => $total,
                    'page' => $page,
                    'pages' => ceil($total / $limit)
                ]);
            } elseif ($action === 'get' && isset($_GET['id'])) {
                $school = queryOne("SELECT * FROM schools WHERE id = ?", [$_GET['id']]);
                echo json_encode(['success' => true, 'data' => $school]);
            } else {
                $schools = query("SELECT * FROM schools ORDER BY name");
                echo json_encode(['success' => true, 'data' => $schools]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $sql = "INSERT INTO schools (name, municipality, address, phone, email, director, coordinator) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            if (execute($sql, [
                $data['name'],
                $data['municipality'],
                $data['address'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['director'] ?? null,
                $data['coordinator'] ?? null
            ])) {
                echo json_encode(['success' => true, 'id' => lastInsertId()]);
            } else {
                throw new Exception('Erro ao criar escola');
            }
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $sql = "UPDATE schools SET 
                    name = ?, municipality = ?, address = ?, phone = ?, 
                    email = ?, director = ?, coordinator = ?
                    WHERE id = ?";
            
            if (execute($sql, [
                $data['name'],
                $data['municipality'],
                $data['address'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['director'] ?? null,
                $data['coordinator'] ?? null,
                $data['id']
            ])) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Erro ao atualizar escola');
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID não fornecido');
            }
            
            $force = isset($_GET['force']) && $_GET['force'] === 'true';
            
            if (!$force) {
                // Check if school has students or users
                $hasStudents = queryOne("SELECT COUNT(*) as count FROM students WHERE school_id = ?", [$id])['count'];
                $hasUsers = queryOne("SELECT COUNT(*) as count FROM users WHERE school_id = ?", [$id])['count'];
                
                if ($hasStudents > 0 || $hasUsers > 0) {
                    throw new Exception('DEPENDENCY_ERROR: Esta escola possui alunos ou professores vinculados');
                }
            }
            
            if (execute("DELETE FROM schools WHERE id = ?", [$id])) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Erro ao excluir escola');
            }
            break;
            
        default:
            throw new Exception('Método não permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
