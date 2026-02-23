DOCUMENTO TÉCNICO – MVP Módulo de Assessoria MovAmazon
Objetivo do MVP
Permitir que uma assessoria esportiva opere dentro da MovAmazon com o mínimo necessário para:
	Gerenciar assessorias, treinadores e atletas
	Criar e entregar treinos
	Registrar presença em treinos presenciais
	Comunicar-se com atletas
	Gerenciar planos e assinaturas básicas
1. Arquitetura Geral
1.1. Estilo Arquitetural
	Backend: Microsserviços ou monólito modular (dependendo da maturidade da equipe)
	Frontend: Web + Mobile (React Native recomendado)
	API: REST + WebSockets (para chat e notificações em tempo real)
	Banco de Dados: PostgreSQL (relacional)
	Cache: Redis (sessões, tokens, filas)
	Mensageria: RabbitMQ ou Kafka (opcional no MVP)
	Autenticação: JWT + OAuth2 (para integrações futuras)
2. Modelagem de Dados (MVP 1.0)
2.1. Entidades Principais
Assessoria
Campo	Tipo	Descrição
Id	UUID	Identificador
Nome	string	Nome da assessoria
logo_url	string	Logo
Descrição	text	Descrição
local_treino	string	Local principal
created_at	datetime	Registro

Treinador
Campo	Tipo
Id	UUID
assessoria_id	FK
Nome	string
Email	string
certificacoes	text
created_at	datetime

Atleta
Campo	Tipo
id	UUID
assessoria_id	FK
nome	string
email	string
data_nascimento	date
nivel	enum(iniciante, intermediario, avançado)
created_at	datetime

Plano de Treino (conforme Prompot)
Campo	Tipo
id	UUID
atleta_id	FK
treinador_id	FK
semana	int
descricao	text
status	enum(ativo, concluído)
created_at	Datetime

Treino (Prompt)
Campo	Tipo
id	UUID
plano_id	FK
data	Date
tipo	enum(corrida, força, mobilidade)
distancia	Float
duracao	Int
observacoes	Text
status	enum(pendente, concluído)

Presença
Campo	Tipo
id	UUID
treino_id	FK
atleta_id	FK
checkin_hora	datetime
metodo	enum(qr_code, manual)

Chat
Campo	Tipo
id	UUID
remetente_id	FK
destinatario_id	FK
mensagem	text
created_at	datetime

Plano / Assinatura
Campo	Tipo
id	UUID
assessoria_id	FK
nome	string
valor	decimal
periodo	enum(mensal, trimestral, anual)
created_at	datetime

3. Endpoints do MVP 1.0
3.1. Assessoria
POST /assessorias
GET /assessorias/{id}
PUT /assessorias/{id}
3.2. Treinadores
POST /assessorias/{id}/treinadores
GET /assessorias/{id}/treinadores
3.3. Atletas
POST /assessorias/{id}/atletas
GET /assessorias/{id}/atletas
3.4. Planos de Treino
POST /atletas/{id}/planos
GET /atletas/{id}/planos
3.5. Treinos
POST /planos/{id}/treinos
GET /planos/{id}/treinos
PATCH /treinos/{id}/status
3.6. Presença
POST /treinos/{id}/checkin
GET /treinos/{id}/presencas
3.7. Chat
WebSocket:
ws://movamazon.com/chat
REST:
GET /chat/conversas/{id}
POST /chat/mensagem
3.8. Planos e Assinaturas
POST /assessorias/{id}/planos
GET /assessorias/{id}/planos
POST /atletas/{id}/assinaturas

4. Regras de Negócio (MVP 1.0)
4.1. Assessoria
	Uma assessoria só pode ser criada por um usuário autenticado com permissão de "admin".
4.2. Treinadores
	Cada treinador pertence a uma única assessoria, mas caso seja necessário cadastrar outros treinadores, essa função deverá ser disponibilizada.
	Treinadores podem criar, alterar e importar/exportar treinos apenas para atletas da sua assessoria.
	Os treinadores deverão ter acesso aos treinos e relatórios dos atletas da sua assessoria.
4.3. Atletas
	Atletas só podem visualizar treinos da sua assessoria.
	Atletas podem marcar treinos como concluídos.
	Manter as funções já desenvolvidas na Plataforma.
	Atletas deverão ter acesso aos gráficos, financeiros (cash back)  e de treinamentos.
4.4. Planos e Treinos
	Cada atleta pode ter múltiplos planos ativos, desde que esteja inscrito e como foco principal a prova mais próxima que o atleta estiver inscrito.
	Treinos devem ser gerados visando a prova foco, independente do período disponível.
	Treinos concluídos não podem ser editados, somente pelo treinador da assessoria ou pelo Admin da Plataforma.
4.5. Check-in
	QR Code deve conter:
treino_id + hash temporário
	Check-in deve ser  válido dentro da janela, independente do dia e horário do treino.
4.6. Chat
	Apenas comunicação 1:1 no MVP.
	Histórico deve ser paginado.

5. Integrações (MVP 1.0)
5.1. Notificações Push
	Firebase Cloud Messaging (FCM)
	Eventos disparados: 
o	Novo treino disponível
o	Mensagem recebida
o	Lembrete de treino
5.2. QR Code
	Biblioteca recomendada: 
o	Backend: qrcode (Node) ou qrcodegen (Go)
o	Mobile: react-native-qrcode-svg

6. Testes
6.1. Testes Unitários
	Cobertura mínima: 70%
	Principais módulos: 
o	Autenticação
o	Criação de treinos
o	Check-in
o	Chat
6.2. Testes de Integração
	Fluxo completo: 
1.	Criar assessoria
2.	Cadastrar treinador
3.	Cadastrar atleta
4.	Criar plano
5.	Criar treinos
6.	Atleta faz check-in
7.	Atleta conclui treino

7. Roadmap Técnico 
	Gestão de assessoria
	Gestão de atletas e treinadores
	Planilhas e treinos
	Check-in com QR Code
	Chat básico
	Assinaturas manuais
	Integração com Strava
	Feed social
	Gamificação inicial
	Cobrança recorrente
	Ranking interno
	Conteúdos recomendados



8. Considerações de Escalabilidade
	Separar serviços de: 
o	Treinos
o	Chat
o	Notificações
o	Financeiro
	Implementar filas para: 
o	Processamento de notificações
o	Geração de QR Codes
	Criar camada de API Gateway para: 
o	Rate limiting
o	Autorização centralizada

