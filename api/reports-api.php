<?php
/**
 * Reports API - System Statistics and Aggregated Data
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? 'stats';
    
    if ($action === 'stats') {
        // 1. General Totals
        $totals = [
            'schools' => queryOne("SELECT COUNT(*) as count FROM schools")['count'],
            'professors' => queryOne("SELECT COUNT(*) as count FROM users WHERE role = 'professor' AND is_active = 1")['count'],
            'students' => queryOne("SELECT COUNT(*) as count FROM students")['count'],
            'teams' => queryOne("SELECT COUNT(*) as count FROM registrations WHERE status = 'approved'")['count']
        ];
        
        // 2. Registrations by Modality
        $byModality = query("
            SELECT 
                m.name,
                COUNT(r.id) as team_count,
                (SELECT COUNT(*) FROM enrollments e JOIN registrations r2 ON e.registration_id = r2.id WHERE r2.modality_id = m.id AND r2.status = 'approved') as student_count
            FROM modalities m
            LEFT JOIN registrations r ON m.id = r.modality_id AND r.status = 'approved'
            GROUP BY m.id, m.name
            ORDER BY team_count DESC
        ");
        
        // 3. Registrations by School
        $bySchool = query("
            SELECT 
                s.name,
                COUNT(r.id) as team_count,
                (SELECT COUNT(*) FROM enrollments e JOIN registrations r2 ON e.registration_id = r2.id WHERE r2.school_id = s.id AND r2.status = 'approved') as student_count
            FROM schools s
            LEFT JOIN registrations r ON s.id = r.school_id AND r.status = 'approved'
            GROUP BY s.id, s.name
            ORDER BY team_count DESC
        ");
        
        // 4. Registrations by Category
        $byCategory = query("
            SELECT 
                c.name,
                COUNT(r.id) as team_count
            FROM categories c
            LEFT JOIN registrations r ON c.id = r.category_id AND r.status = 'approved'
            GROUP BY c.id, c.name
            ORDER BY c.min_birth_year DESC
        ");
        
        echo json_encode([
            'success' => true,
            'data' => [
                'totals' => $totals,
                'byModality' => $byModality,
                'bySchool' => $bySchool,
                'byCategory' => $byCategory
            ]
        ]);
    } else {
        throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
