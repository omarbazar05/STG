-- ============================================
-- SCHEMA BASE DE DONNÉES — ABSec Platform
-- ============================================

CREATE DATABASE IF NOT EXISTS absec_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE absec_db;

-- ============================================
-- 1. TABLES UTILISATEURS (sans dépendances)
-- ============================================

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id_hash VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('super', 'admin') NOT NULL DEFAULT 'admin',
    last_login DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id_hash VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NULL,
    department VARCHAR(100) NULL,
    hired_at DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id_hash VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NULL,
    status ENUM('pending', 'active', 'suspended') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 2. SESSIONS & AUTHENTIFICATION
-- ============================================

CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('admin', 'employee', 'client') NOT NULL,
    token_hash VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('admin', 'employee', 'client') NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 3. DEMANDES & ABONNEMENTS (liées clients)
-- ============================================

CREATE TABLE quote_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    contact_email VARCHAR(191) NOT NULL,
    phone VARCHAR(30) NULL,
    needs_description TEXT NULL,
    status ENUM('pending', 'accepted', 'refused') NOT NULL DEFAULT 'pending',
    handled_by_admin INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (handled_by_admin) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    plan VARCHAR(80) NOT NULL,
    status ENUM('active', 'expired', 'suspended') NOT NULL DEFAULT 'active',
    starts_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE soc_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    siem VARCHAR(150) NULL,
    soar VARCHAR(150) NULL,
    ti VARCHAR(150) NULL,
    dashboard_url VARCHAR(500) NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- ============================================
-- 4. INCIDENTS & RÉCLAMATIONS
-- ============================================

CREATE TABLE incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    assigned_employee INT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved') NOT NULL DEFAULT 'open',
    detected_at DATETIME NOT NULL,
    resolved_at DATETIME NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_employee) REFERENCES employees(id) ON DELETE SET NULL
);

CREATE TABLE claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    handled_by INT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved') NOT NULL DEFAULT 'open',
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (handled_by) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE claim_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_id INT NOT NULL,
    author_id INT NOT NULL,
    author_type ENUM('admin', 'client') NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE
);

-- ============================================
-- 5. DOCUMENTS, TÂCHES, ABSENCES, ÉVALUATIONS
-- ============================================

CREATE TABLE document_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    handled_by INT NULL,
    document_type VARCHAR(100) NOT NULL,
    status ENUM('pending', 'ready', 'delivered') NOT NULL DEFAULT 'pending',
    file_path VARCHAR(500) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (handled_by) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assigned_to INT NOT NULL,
    assigned_by INT NULL,
    client_id INT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    status ENUM('todo', 'in_progress', 'done') NOT NULL DEFAULT 'todo',
    due_date DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES admins(id) ON DELETE SET NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
);

CREATE TABLE absences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    approved_by INT NULL,
    type ENUM('conge', 'maladie', 'autre') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'refused') NOT NULL DEFAULT 'pending',
    reason TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE performance_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    reviewer_id INT NULL,
    period VARCHAR(20) NOT NULL,
    score TINYINT NULL,
    comments TEXT NULL,
    kpis JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- ============================================
-- 6. CMS (contenu public)
-- ============================================

CREATE TABLE cms_home (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(100) UNIQUE NOT NULL,
    content_type ENUM('text', 'html', 'image', 'link') NOT NULL,
    value TEXT NULL,
    media_path VARCHAR(500) NULL,
    updated_by INT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE cms_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_key VARCHAR(80) UNIQUE NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    features JSON NULL,
    updated_by INT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE cms_blog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE cms_stages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    requirements TEXT NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    created_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    internship_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL,
    cv_path VARCHAR(500) NOT NULL,
    status ENUM('pending', 'accepted', 'refused') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (internship_id) REFERENCES cms_stages(id) ON DELETE CASCADE
);

-- ============================================
-- 7. NOTIFICATIONS & AUDIT
-- ============================================

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT NOT NULL,
    recipient_type ENUM('admin', 'employee', 'client') NOT NULL,
    type VARCHAR(60) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500) NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor_id INT NOT NULL,
    actor_type ENUM('admin', 'employee', 'client') NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(60) NULL,
    entity_id INT NULL,
    payload JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- INDEX RECOMMANDÉS (performance)
-- ============================================

CREATE INDEX idx_incidents_client ON incidents(client_id);
CREATE INDEX idx_incidents_employee ON incidents(assigned_employee);
CREATE INDEX idx_claims_client ON claims(client_id);
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);
CREATE INDEX idx_sessions_token ON sessions(token_hash);
CREATE INDEX idx_notifications_recipient ON notifications(recipient_id, recipient_type);
CREATE INDEX idx_audit_actor ON audit_logs(actor_id, actor_type);