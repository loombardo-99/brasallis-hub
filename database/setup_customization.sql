-- Adiciona colunas de personalização na tabela empresas
-- Use procedure para verificar se coluna existe antes de adicionar (MySQL 5.7+ não tem IF NOT EXISTS para colunas nativo simples em um comando só, então vamos usar um bloco seguro ou apenas tentar adicionar e suprimir erro no PHP)

-- Tentativa direta (O PHP vai tratar se já existir)
ALTER TABLE empresas ADD COLUMN branding_primary_color VARCHAR(7) DEFAULT '#2563eb';
ALTER TABLE empresas ADD COLUMN branding_secondary_color VARCHAR(7) DEFAULT '#1e293b';
ALTER TABLE empresas ADD COLUMN branding_bg_style VARCHAR(50) DEFAULT 'original';

-- Tabela de Layouts do Dashboard
CREATE TABLE IF NOT EXISTS dashboard_layouts (
    user_id INT NOT NULL,
    layout_json TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_layout_user FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
