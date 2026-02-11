-- Migration: configurações de IA (OpenAI e outros provedores)
-- Executar após create_config_tables.sql

INSERT INTO `config` (`chave`, `valor`, `tipo`, `categoria`, `descricao`, `editavel`, `visivel`)
VALUES
  -- Provedor ativo
  ('ai.provedor_ativo', 'openai', 'string', 'ai', 'Provedor de IA atualmente em uso (openai, anthropic, google)', 1, 1),
  
  -- OpenAI
  ('ai.openai.api_key', '', 'encrypted', 'ai', 'Chave API OpenAI (sk-...)', 1, 1),
  ('ai.openai.model', 'gpt-4o', 'string', 'ai', 'Modelo OpenAI (gpt-4o, gpt-4-turbo, gpt-3.5-turbo)', 1, 1),
  ('ai.openai.temperature', '0.5', 'number', 'ai', 'Temperature OpenAI (0-2)', 1, 1),
  ('ai.openai.max_tokens', '8000', 'number', 'ai', 'Máximo de tokens por requisição', 1, 1),
  
  -- Anthropic (Claude) - para futuro
  ('ai.anthropic.api_key', '', 'encrypted', 'ai', 'Chave API Anthropic (Claude)', 1, 1),
  ('ai.anthropic.model', 'claude-3-5-sonnet-20241022', 'string', 'ai', 'Modelo Anthropic', 1, 1),
  
  -- Google (Gemini) - para futuro
  ('ai.google.api_key', '', 'encrypted', 'ai', 'Chave API Google Gemini', 1, 1),
  ('ai.google.model', 'gemini-pro', 'string', 'ai', 'Modelo Google Gemini', 1, 1),
  
  -- Configurações gerais
  ('ai.timeout', '120', 'number', 'ai', 'Timeout em segundos para requisições de IA', 1, 1),
  ('ai.prompt_treino_base', 'Você é um Profissional de Educação Física especialista em preparação para corridas de rua, com conhecimento profundo em periodização de treinamento, fisiologia do exercício e prevenção de lesões.', 'string', 'ai', 'Prompt base para geração de treinos', 1, 1);


