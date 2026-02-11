# Plano: Regulamento como Arquivo na Página de Programação

## Objetivo
Transformar o campo de regulamento (textarea) na página de programação em um componente de upload/download de arquivo PDF/DOC/DOCX, similar à página de solicitações do admin.

---

## Análise da Situação Atual

### Banco de Dados
- **Tabela `eventos`**: Possui campo `regulamento_arquivo` (varchar 500) que armazena caminho do arquivo
- **Tabela `eventos`**: Possui campo `regulamento` (text) que armazena texto do regulamento (legado)

### Estrutura de Arquivos
- **Eventos criados via `api/evento/create.php`**: Salvam em `frontend/assets/docs/regulamentos/`
- **Solicitações via `api/organizador/create.php`**: Salvam em `api/uploads/regulamentos/`
- **Download atual**: `api/uploads/regulamentos/download.php` (requer autenticação admin)

### Frontend Atual
- **Página**: `frontend/paginas/organizador/programacao/index.php`
- **Campo atual**: Textarea `#regulamentoEvento` (linha 170)
- **JavaScript**: `frontend/js/programacao.js` - preenche com `evento.regulamento` (linha 161)

### APIs Atuais
- **GET**: `api/organizador/eventos/get.php` - NÃO retorna `regulamento_arquivo`
- **UPDATE**: `api/organizador/eventos/update.php` - NÃO processa upload de arquivo

---

## Impactos e Mudanças Necessárias

### 1. Banco de Dados
**Status**: ✅ Campo `regulamento_arquivo` já existe na tabela `eventos`
**Ação**: Nenhuma mudança necessária

### 2. API GET (`api/organizador/eventos/get.php`)
**Mudança**: Adicionar `regulamento_arquivo` no SELECT
**Impacto**: Baixo - apenas adicionar campo na query

### 3. API UPDATE (`api/organizador/eventos/update.php`)
**Mudança**: 
- Aceitar `multipart/form-data` além de JSON
- Processar `$_FILES['regulamento_arquivo']`
- Validar extensão (PDF, DOC, DOCX)
- Validar tamanho (máx 10MB)
- Salvar arquivo em `frontend/assets/docs/regulamentos/`
- Atualizar campo `regulamento_arquivo` no banco
- Se já existir arquivo, deletar o antigo antes de salvar novo
**Impacto**: Médio - mudança significativa na lógica de upload

### 4. Nova API: Upload de Regulamento (`api/organizador/eventos/upload-regulamento.php`)
**Criar**: Endpoint específico para upload de regulamento
**Funcionalidade**:
- Aceitar apenas `multipart/form-data`
- Validar autenticação de organizador
- Validar que evento pertence ao organizador
- Processar upload do arquivo
- Retornar nome do arquivo salvo
**Impacto**: Médio - novo endpoint

### 5. Nova API: Download de Regulamento (`api/organizador/eventos/download-regulamento.php`)
**Criar**: Endpoint para download de regulamento
**Funcionalidade**:
- Validar autenticação de organizador
- Validar que evento pertence ao organizador
- Servir arquivo com headers corretos
- Suportar PDF, DOC, DOCX
**Impacto**: Médio - novo endpoint

**OU**

**Adaptar**: `api/uploads/regulamentos/download.php` para aceitar organizadores
**Impacto**: Baixo - apenas adicionar verificação de organizador

### 6. Frontend HTML (`frontend/paginas/organizador/programacao/index.php`)
**Mudança**: Substituir textarea por componente de upload/download
**Estrutura**:
```html
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-1">Regulamento</label>
    
    <!-- Se existe arquivo: mostrar link -->
    <div id="regulamento-arquivo-existente" class="hidden mb-3">
        <a id="link-regulamento" href="#" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
            <i class="fas fa-file-pdf mr-2"></i>
            <span id="nome-arquivo-regulamento"></span>
        </a>
        <button id="btn-substituir-regulamento" class="ml-4 text-sm text-gray-600 hover:text-gray-800">
            <i class="fas fa-edit mr-1"></i>Substituir arquivo
        </button>
    </div>
    
    <!-- Input de upload (sempre visível em modo edição) -->
    <div id="regulamento-upload-container" class="hidden">
        <input type="file" id="regulamentoArquivo" name="regulamento_arquivo" 
               accept=".pdf,.doc,.docx" 
               class="w-full border border-gray-300 rounded-lg px-3 py-2">
        <p class="text-xs text-gray-500 mt-1">
            Formatos aceitos: PDF, DOC, DOCX. Tamanho máximo: 10MB
        </p>
    </div>
    
    <!-- Mensagem de confirmação para substituição -->
    <div id="regulamento-confirmacao-substituir" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
        <p class="text-sm text-yellow-800">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Um arquivo já existe. Ao salvar, o arquivo atual será substituído.
        </p>
    </div>
</div>
```
**Impacto**: Médio - mudança na estrutura HTML

### 7. JavaScript (`frontend/js/programacao.js`)
**Mudanças**:
1. **`preencherFormulario()`**: 
   - Verificar se `evento.regulamento_arquivo` existe
   - Se existir: mostrar link e esconder input
   - Se não existir: mostrar input e esconder link

2. **`habilitarCampos()`**: 
   - Habilitar/desabilitar input de arquivo conforme modo edição

3. **`coletarDadosFormulario()`**: 
   - Remover campo `regulamento` (texto)
   - Não incluir arquivo aqui (será enviado separadamente)

4. **Nova função `salvarRegulamento()`**:
   - Criar FormData
   - Adicionar arquivo se houver
   - Adicionar evento_id
   - Enviar para API de upload
   - Atualizar interface após sucesso

5. **Nova função `substituirRegulamento()`**:
   - Mostrar confirmação
   - Habilitar input de arquivo
   - Mostrar aviso de substituição

6. **Event listeners**:
   - `change` no input de arquivo: mostrar preview e aviso de substituição
   - `click` no botão "Substituir": chamar `substituirRegulamento()`
**Impacto**: Alto - várias funções novas e modificações

---

## Plano de Implementação

### Fase 1: APIs Backend
1. ✅ Atualizar `api/organizador/eventos/get.php` para retornar `regulamento_arquivo`
2. ✅ Criar `api/organizador/eventos/upload-regulamento.php`
3. ✅ Criar `api/organizador/eventos/download-regulamento.php` OU adaptar `api/uploads/regulamentos/download.php`

### Fase 2: Frontend HTML
4. ✅ Substituir textarea por componente de upload/download em `programacao/index.php`

### Fase 3: JavaScript
5. ✅ Atualizar `preencherFormulario()` para lidar com arquivo
6. ✅ Atualizar `habilitarCampos()` para incluir input de arquivo
7. ✅ Criar função `salvarRegulamento()`
8. ✅ Criar função `substituirRegulamento()`
9. ✅ Adicionar event listeners
10. ✅ Integrar salvamento de regulamento no fluxo de salvar evento

### Fase 4: Testes
11. ✅ Testar upload de novo arquivo
12. ✅ Testar substituição de arquivo existente
13. ✅ Testar download de arquivo
14. ✅ Testar validações (tipo, tamanho)
15. ✅ Testar permissões (organizador só pode editar seus eventos)

---

## Estrutura de Diretórios de Arquivos

### Opção A: Usar estrutura existente de eventos
- **Diretório**: `frontend/assets/docs/regulamentos/`
- **Padrão de nome**: `regulamento_{evento_id}_{timestamp}.{ext}`
- **Vantagem**: Consistente com `api/evento/create.php`
- **Desvantagem**: Diferente de `api/uploads/regulamentos/`

### Opção B: Padronizar em `api/uploads/regulamentos/`
- **Diretório**: `api/uploads/regulamentos/`
- **Padrão de nome**: `regulamento_{evento_id}_{timestamp}.{ext}`
- **Vantagem**: Centralizado, mesmo padrão de solicitações
- **Desvantagem**: Migrar arquivos existentes

**Recomendação**: Opção B (padronizar em `api/uploads/regulamentos/`)

---

## Validações Necessárias

### Backend
- ✅ Autenticação: usuário deve ser organizador
- ✅ Autorização: evento deve pertencer ao organizador
- ✅ Tipo de arquivo: apenas PDF, DOC, DOCX
- ✅ Tamanho: máximo 10MB
- ✅ Extensão: validar pelo nome e pelo MIME type

### Frontend
- ✅ Tipo de arquivo: `accept=".pdf,.doc,.docx"`
- ✅ Tamanho: validar antes de enviar (10MB)
- ✅ Confirmação: avisar se já existe arquivo e será substituído
- ✅ Feedback visual: mostrar loading durante upload

---

## Fluxo de Uso

### Cenário 1: Novo Evento (sem regulamento)
1. Organizador seleciona evento
2. Campo de upload aparece
3. Organizador seleciona arquivo
4. Ao salvar, arquivo é enviado e salvo
5. Link para download aparece

### Cenário 2: Evento com Regulamento Existente
1. Organizador seleciona evento
2. Link para download aparece
3. Botão "Substituir arquivo" aparece
4. Ao clicar, input de upload aparece
5. Organizador seleciona novo arquivo
6. Aviso de substituição aparece
7. Ao salvar, arquivo antigo é deletado e novo é salvo
8. Link atualizado aparece

### Cenário 3: Download do Regulamento
1. Organizador clica no link
2. Arquivo abre em nova aba (PDF) ou faz download (DOC/DOCX)

---

## Considerações de Segurança

1. ✅ Validar autenticação em todas as APIs
2. ✅ Validar que evento pertence ao organizador
3. ✅ Validar tipo de arquivo (extensão + MIME)
4. ✅ Validar tamanho do arquivo
5. ✅ Sanitizar nome do arquivo antes de salvar
6. ✅ Usar `basename()` para prevenir path traversal
7. ✅ Deletar arquivo antigo antes de salvar novo (evitar acúmulo)

---

## Compatibilidade com Legado

- ✅ Manter campo `regulamento` (texto) no banco para compatibilidade
- ✅ Se `regulamento_arquivo` existir, priorizar arquivo
- ✅ Se apenas `regulamento` (texto) existir, continuar mostrando texto (legado)
- ✅ Permitir migração gradual de texto para arquivo

---

## Checklist de Implementação

### Backend
- [ ] Atualizar `api/organizador/eventos/get.php` - adicionar `regulamento_arquivo`
- [ ] Criar `api/organizador/eventos/upload-regulamento.php`
- [ ] Criar `api/organizador/eventos/download-regulamento.php`
- [ ] Testar upload de arquivo
- [ ] Testar substituição de arquivo
- [ ] Testar download de arquivo
- [ ] Testar validações (tipo, tamanho, permissões)

### Frontend
- [ ] Atualizar HTML em `programacao/index.php`
- [ ] Atualizar `preencherFormulario()` em `programacao.js`
- [ ] Atualizar `habilitarCampos()` em `programacao.js`
- [ ] Criar `salvarRegulamento()` em `programacao.js`
- [ ] Criar `substituirRegulamento()` em `programacao.js`
- [ ] Adicionar event listeners
- [ ] Integrar com fluxo de salvamento
- [ ] Testar interface em modo visualização
- [ ] Testar interface em modo edição
- [ ] Testar upload de novo arquivo
- [ ] Testar substituição de arquivo existente
- [ ] Testar download de arquivo

---

## Notas Finais

- Este plano mantém compatibilidade com o sistema legado
- A implementação pode ser feita incrementalmente
- Testes devem ser realizados em ambiente de desenvolvimento antes de produção
- Considerar migração de arquivos existentes de `frontend/assets/docs/regulamentos/` para `api/uploads/regulamentos/` se optar por padronização

