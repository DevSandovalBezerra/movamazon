# Guia passo a passo (Owner) — Abrir conta e configurar o Asaas para a plataforma de corridas

Público: sócio/owner com pouca familiaridade em TI  
Objetivo: deixar a conta pronta para o time de desenvolvimento integrar Pix/Cartão, webhooks e split (comissão 7% + retenção 30% do organizador).


## 0) Antes de começar (5 minutos)

Você vai precisar de:

- CNPJ e dados da empresa (razão social, nome fantasia, endereço)
- Dados do responsável legal (CPF, data de nascimento, telefone, e-mail)
- Conta bancária da empresa (para transferir/receber valores do Asaas quando necessário)
- Um e-mail que você use com frequência (para confirmações)
- Um lugar seguro para guardar “segredos” (recomendado: gerenciador de senhas)

Dica: faça este processo em um computador (não no celular) e com um navegador atualizado (Chrome/Edge).


## 1) Criar a conta PJ no Asaas

1. Acesse o site do Asaas e clique em Criar conta.
2. Selecione Conta PJ (empresa).
3. Preencha os dados solicitados.
4. Confirme o e-mail (o Asaas envia um link de confirmação).
5. Faça login no painel do Asaas.

Quando concluir, você terá acesso ao Painel e ao menu do usuário.


## 2) Completar/validar o cadastro (KYC)

O Asaas pode pedir validações/documentos para liberar recursos e limites.

1. No painel, procure por:
   - Cadastro, Dados da empresa, Verificação ou mensagens de “pendência”.
2. Envie os documentos solicitados (se houver).
3. Aguarde aprovação (isso pode ser necessário para operar com maior volume).

Dica prática: não avance para produção (vendas reais) sem a conta estar “apta/ativa”.


## 3) Criar e guardar a Chave de API (a “chave do cofre”)

A Chave de API permite que o sistema (seu software) converse com o Asaas.

1. No painel, vá em Menu do usuário → Integrações → Chaves de API.
2. Clique em Gerar/Adicionar nova chave.
3. Dê um nome fácil, por exemplo:
   - `PROD - Plataforma Corridas`
4. Se tiver opção de expiração, defina uma data longa ou deixe sem expirar (conforme política interna).
5. Copie a chave e guarde em local seguro.

Regras de ouro:
- Nunca mande a chave por WhatsApp
- Nunca cole a chave em prints
- Só compartilhe com o líder técnico (ou cofre de segredos do projeto)


## 4) Identificar o WalletId (ID da carteira)

Para split, o sistema precisa do walletId (ID da carteira) dos envolvidos.

O que você precisa fazer agora:
- Confirmar com o time de dev onde o walletId aparece no painel da sua conta
- Ou pedir para o time de dev obter via API (é comum no fluxo de subcontas)

Se no painel existir:
1. Vá em Integrações e procure por WalletId, ID da carteira ou Carteira.
2. Copie e guarde junto com a chave de API.


## 5) Configurar Webhooks (avisos automáticos do Asaas para o sistema)

Sem webhook, o sistema fica “cego” e pode não atualizar inscrições automaticamente.

Você precisa de 1 informação do desenvolvedor:
- A URL do webhook, por exemplo:
  - `https://SEU-DOMINIO.com/webhooks/asaas`

Passo a passo:
1. Vá em Menu do usuário → Integrações → Webhooks.
2. Clique em Criar Webhook.
3. Cole a URL que o dev te passou.
4. Selecione eventos (marque pelo menos):
   - Pagamento confirmado/recebido
   - Pagamento cancelado/vencido
   - Estorno (refund)
   - Chargeback/disputa (cartão), se disponível
5. Salve.

Importante:
- Se houver campo de “token/segredo”, crie um valor forte e guarde (ex.: `ASAAS_WEBHOOK_TOKEN`).
- Envie esse token ao dev para ele validar as chamadas.


## 6) Preparar o split (comissão 7% + retenção 30%)

Para o seu modelo, o split recomendado é em 3 partes por inscrição:

- 7%: Carteira da plataforma (comissão)
- 63%: Carteira do organizador (liberado)
- 30%: Carteira de garantia (retida)

Como isso funciona na prática:
- O split é configurado pelo sistema via API quando ele cria a cobrança.
- Você não precisa “clicar split” no painel; você precisa garantir que:
  1. A plataforma tem chave de API
  2. O sistema tem o walletId da plataforma
  3. O organizador tem walletId (conta Asaas)
  4. Existe walletId para a Garantia

Modelo de Garantia (recomendado no início):
- Criar uma conta/carteira Garantia controlada pela plataforma, separada do caixa principal
- O sistema direciona os 30% para essa carteira e libera depois via transferência no fechamento do evento


## 7) Como cadastrar organizadores (de um jeito simples)

No MVP, a forma mais simples é:

1. O organizador cria uma conta PJ no Asaas.
2. Ele entra no painel e encontra o walletId.
3. Ele envia o walletId para você (pode ser por e-mail corporativo).
4. Você cadastra esse walletId no seu sistema (tela de organizadores).

Boas práticas:
- Peça também razão social e CNPJ do organizador para conferência.
- Guarde comprovantes e aceite de termos (contrato/termos de repasse e chargeback).


## 8) Checklist final (pronto para o dev)

Antes do time começar a integrar, você precisa ter:

- Conta PJ da plataforma ativa (cadastro aprovado)
- Chave de API (PRODUÇÃO) guardada com segurança
- (Se possível) WalletId da plataforma
- Webhook criado com URL do sistema e token de segurança
- Definição de:
  - Quem paga as taxas (recomendação: organizador)
  - Regra de retenção: 30% até fechamento do evento
  - Regra de fechamento: data e condição de liberação


## 9) O que você envia para o desenvolvedor (em um único e-mail)

- `ASAAS_API_KEY` (produção)
- `WALLET_PLATFORM_ID` (se disponível)
- `WALLET_GUARANTEE_ID` (se existir uma carteira garantia)
- `ASAAS_WEBHOOK_URL` (a URL configurada)
- `ASAAS_WEBHOOK_TOKEN` (se configurado)
- Seu ambiente: `PROD` (e depois, também `SANDBOX` para testes)


## 10) Dúvidas comuns (bem rápidas)

1. Posso usar split sem API?
- Não. Split no Asaas é exclusivo via API.

2. Preciso configurar split no painel?
- Normalmente não. O split é feito na criação da cobrança via sistema.

3. O que acontece com a retenção?
- Ela fica na carteira Garantia até o fechamento do evento. No fechamento, o sistema transfere para o organizador.


## Referências (para consulta)
- Split e necessidade de walletId: docs.asaas.com
- Webhooks (habilitação no painel): docs.asaas.com
- Chaves de API (gestão e boas práticas): docs.asaas.com
