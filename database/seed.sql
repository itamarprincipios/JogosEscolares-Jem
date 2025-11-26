-- Sistema JEM - Seed Data
-- Initial data for testing and development

-- Insert default admin user
-- Password: Admin@123 (hashed with bcrypt)
-- Note: This hash is generated with password_hash('Admin@123', PASSWORD_BCRYPT)
INSERT INTO users (name, email, password, role, is_active) VALUES
('Administrador', 'admin@jem.com', '$2y$10$DMDYkx7FRu8Co.iImtjwi.LGGjkH/3iuIkEMvNVHxsL6xljp3Qg9a', 'admin', TRUE);

-- Insert sample categories
INSERT INTO categories (name, max_age) VALUES
('Sub-12', 12),
('Sub-14', 14),
('Sub-16', 16),
('Sub-18', 18);

-- Insert sample modalities
INSERT INTO modalities (name, allows_mixed) VALUES
('Futsal', FALSE),
('Vôlei', FALSE),
('Handebol', FALSE),
('Basquete', FALSE),
('Atletismo', TRUE),
('Xadrez', TRUE),
('Tênis de Mesa', FALSE),
('Judô', FALSE);

-- Insert a sample school for testing
INSERT INTO schools (name, municipality, address, phone, email, director, coordinator) VALUES
('Escola Municipal Exemplo', 'São Paulo', 'Rua Exemplo, 123', '(11) 1234-5678', 'contato@escolaexemplo.com', 'João Silva', 'Maria Santos');

-- Note: The admin password is 'Admin@123'
-- This should be changed immediately after first login
