<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$pageTitle = 'Contato - MovAmazon';
include '../../includes/header_index.php';
?>

<!-- Main Content -->
<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 md:py-20">
  <!-- Header Section -->
  <div class="text-center mb-12 animate-fade-in-up">
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-4">
      Entre em <span class="text-brand-green">Contato</span>
    </h1>
    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
      Estamos aqui para ajudar! Envie sua mensagem e responderemos o mais breve possível.
    </p>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Formulário de Contato -->
    <div class="lg:col-span-2">
      <div class="card">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Envie sua Mensagem</h2>
        <form id="form-contato" class="space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Nome -->
            <div>
              <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                Nome Completo <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="nome"
                name="nome"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent transition-all"
                placeholder="Seu nome completo"
              />
              <span class="text-red-500 text-sm hidden" id="erro-nome"></span>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                E-mail <span class="text-red-500">*</span>
              </label>
              <input
                type="email"
                id="email"
                name="email"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent transition-all"
                placeholder="seu@email.com"
              />
              <span class="text-red-500 text-sm hidden" id="erro-email"></span>
            </div>
          </div>

          <!-- Telefone -->
          <div>
            <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
              Telefone <span class="text-red-500">*</span>
            </label>
            <input
              type="tel"
              id="telefone"
              name="telefone"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent transition-all"
              placeholder="(92) 99999-9999"
            />
            <span class="text-red-500 text-sm hidden" id="erro-telefone"></span>
          </div>

          <!-- Assunto -->
          <div>
            <label for="assunto" class="block text-sm font-medium text-gray-700 mb-2">
              Assunto <span class="text-red-500">*</span>
            </label>
            <select
              id="assunto"
              name="assunto"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent transition-all bg-white"
            >
              <option value="">Selecione um assunto</option>
              <option value="duvida">Dúvida sobre Eventos</option>
              <option value="inscricao">Problema com Inscrição</option>
              <option value="pagamento">Dúvida sobre Pagamento</option>
              <option value="organizador">Sou Organizador</option>
              <option value="outro">Outro</option>
            </select>
            <span class="text-red-500 text-sm hidden" id="erro-assunto"></span>
          </div>

          <!-- Mensagem -->
          <div>
            <label for="mensagem" class="block text-sm font-medium text-gray-700 mb-2">
              Mensagem <span class="text-red-500">*</span>
            </label>
            <textarea
              id="mensagem"
              name="mensagem"
              rows="6"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent transition-all resize-none"
              placeholder="Descreva sua dúvida ou solicitação..."
            ></textarea>
            <span class="text-red-500 text-sm hidden" id="erro-mensagem"></span>
          </div>

          <!-- Botão Enviar -->
          <div>
            <button
              type="submit"
              id="btn-enviar"
              class="w-full bg-brand-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#0a3a37] transition-colors duration-200 shadow-md hover:shadow-lg flex items-center justify-center space-x-2"
            >
              <span id="btn-text">Enviar Mensagem</span>
              <svg id="btn-loading" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Informações de Contato -->
    <div class="lg:col-span-1">
      <div class="card bg-gradient-to-br from-brand-green to-green-600 text-white p-8" style="min-width: 380px;">
        <h2 class="text-2xl font-bold mb-6">Informações de Contato</h2>
        
        <div class="space-y-6">
          <!-- Email -->
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
            <div class="flex-1" style="min-width: 0; word-break: break-word; overflow-wrap: break-word;">
              <h3 class="font-semibold mb-1">E-mail</h3>
              <a href="mailto:contato@movamazon.com" class="text-white/90 hover:text-white transition-colors inline-block" style="word-break: break-all; max-width: 100%;">
                contato@movamazon.com
              </a>
            </div>
          </div>

          <!-- Telefone -->
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold mb-1">Telefone</h3>
              <a href="tel:+5592982027654" class="text-white/90 hover:text-white transition-colors break-words">
                (92) 98202-7654
              </a>
            </div>
          </div>

          <!-- Horário -->
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold mb-1">Horário de Atendimento</h3>
              <p class="text-white/90 break-words">Segunda a Sexta<br>9h às 18h</p>
            </div>
          </div>
        </div>

        <!-- Redes Sociais -->
        <div class="mt-8 pt-6 border-t border-white/20">
          <h3 class="font-semibold mb-4">Siga-nos</h3>
          <div class="flex space-x-4">
            <a href="https://www.instagram.com/movamazon?igsh=cTFwM3lvcjZsaW5y" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center hover:bg-white/30 transition-colors">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
              </svg>
            </a>
            <a href="#" class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center hover:bg-white/30 transition-colors">
              <span class="sr-only">Facebook</span>
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Script para formulário de contato -->
<script type="module" src="../../js/public/contato.js"></script>

<?php include '../../includes/footer.php'; ?>
