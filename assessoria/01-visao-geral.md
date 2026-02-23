# 01 – Visão geral

## Objetivo
Implementar no Movamazon um módulo de **Assessoria de Corrida** (PF ou PJ) com **painel separado** em **/assessoria**, permitindo:

- Cadastro de assessorias e equipe (admin/assessores)
- Importação/vinculação de atletas já existentes (do cadastro e/ou inscrições em eventos)
- Criação, geração, revisão e publicação de treinos (por evento ou contínuo)
- Monitoramento de execução e evolução (progresso, adesão, alertas)
- Histórico e versionamento de planos

## Entidades-chave (conceitos)
- Assessoria: PF/PJ, dados institucionais e responsável
- Equipe da Assessoria: usuários que atuam como admin/assessor/suporte
- Atleta da Assessoria: usuário atleta já existente vinculado à assessoria
- Programa: “macro plano” (por evento ou contínuo) que agrupa atletas e treinos
- Plano gerado: saída (AI ou manual) que vira treinos publicados
- Progresso: registros do atleta (e/ou do assessor) sobre a execução real

## Integração com o que já existe
O módulo não recria treinos do zero. Ele **reutiliza** as tabelas já existentes de treinos e adiciona:
- Identidade da assessoria (quem é dono do plano/treino)
- Programa (contexto macro)
- Status/versionamento e publicação
- Capacidade do assessor registrar feedback/ocorrências no progresso

## Fluxos principais
1. Admin cria assessoria e adiciona equipe
2. Importa atletas por evento (inscrições) ou vincula manualmente
3. Cria programa (evento/contínuo) e adiciona atletas
4. Assessor cria anamnese (ou reutiliza), gera plano, revisa e publica
5. Atleta registra execução (progresso) e acompanha evolução
6. Assessor monitora adesão/alertas e ajusta plano (versões)
