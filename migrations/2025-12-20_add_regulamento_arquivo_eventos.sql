-- Adiciona suporte a upload de regulamento por arquivo na tabela eventos
-- Execute caso seu ambiente esteja com erro "Unknown column 'regulamento_arquivo'"

ALTER TABLE eventos
  ADD COLUMN regulamento_arquivo VARCHAR(500) NULL
  COMMENT 'Nome/caminho do arquivo de regulamento (PDF/DOC/DOCX)';


