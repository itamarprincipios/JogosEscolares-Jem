<?php
/**
 * Categories API - CRUD Operations
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $categories = query("SELECT * FROM categories ORDER BY max_age");
            echo json_encode(['success' => true, 'data' => $categories]);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $sql = "INSERT INTO categories (name, max_age) VALUES (?, ?)";
            
            if (execute($sql, [$data['name'], $data['max_age']])) {
                echo json_encode(['success' => true, 'id' => lastInsertId()]);
            } else {
                throw new Exception('Erro ao criar categoria');
            }
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $sql = "UPDATE categories SET name = ?, max_age = ? WHERE id = ?";
            
            if (execute($sql, [$data['name'], $data['max_age'], $data['id']])) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Erro ao atualizar categoria');
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID não fornecido');
            }
            
            // Check if category has registrations
            $hasRegistrations = queryOne("SELECT COUNT(*) as count FROM registrations WHERE category_id = ?", [$id])['count'];
            
            if ($hasRegistrations > 0) {
                throw new Exception('Não é possível excluir categoria com inscrições vinculadas');
            }
            
            if (execute("DELETE FROM categories WHERE id = ?", [$id])) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Erro ao excluir categoria');
            }
            break;
            
        default:
            throw new Exception('Método não permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
