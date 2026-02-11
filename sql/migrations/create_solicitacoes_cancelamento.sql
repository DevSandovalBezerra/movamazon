-- Tabela para gerenciar solicitações de cancelamento de inscrições
CREATE TABLE IF NOT EXISTS solicitacoes_cancelamento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inscricao_id INT NOT NULL,
    usuario_id INT NOT NULL,
    motivo TEXT,
    status ENUM('pendente', 'aprovada', 'rejeitada', 'processada') DEFAULT 'pendente',
    motivo_rejeicao TEXT NULL,
    admin_id INT NULL COMMENT 'Admin que processou a solicitação',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_processamento TIMESTAMP NULL,
    valor_reembolso DECIMAL(10,2) NULL,
    observacoes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (inscricao_id) REFERENCES inscricoes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_inscricao (inscricao_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_status (status),
    INDEX idx_data_solicitacao (data_solicitacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
