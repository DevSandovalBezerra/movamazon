  <!-- Footer Padrão MovAmazon -->
  <footer class="bg-gray-800 text-white py-12 mt-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Coluna 1: Branding -->
        <div>
          <div class="flex items-center space-x-3 mb-4">
            <a href="../../paginas/public/index.php" class="flex items-center space-x-2">
              <div class="bg-white/10 backdrop-blur-sm rounded-xl p-2 border border-white/20 shadow-lg">
                <img src="../../assets/img/logo.png" alt="MovAmazon" class="h-10 w-auto transition-transform hover:scale-105">
              </div>
              <span class="text-xl font-bold">
                <span class="text-white">Mov</span><span class="text-brand-yellow">Amazon</span>
              </span>
            </a>
          </div>
          <p class="text-gray-400 text-sm leading-relaxed">
            Encontre sua próxima corrida e participe dos melhores eventos esportivos do país.
          </p>
        </div>

        <!-- Coluna 2: Eventos -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Eventos</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <a href="../../paginas/public/index.php" class="text-gray-400 hover:text-brand-green transition-colors">
                Próximos eventos
              </a>
            </li>
            <li>
              <a href="../../paginas/public/index.php" class="text-gray-400 hover:text-brand-green transition-colors">
                Eventos passados
              </a>
            </li>
            <li>
              <a href="../../paginas/public/index.php" class="text-gray-400 hover:text-brand-green transition-colors">
                Calendário
              </a>
            </li>
          </ul>
        </div>

        <!-- Coluna 3: Participantes -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Participantes</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <a href="../../paginas/public/index.php" class="text-gray-400 hover:text-brand-green transition-colors">
                Como se inscrever
              </a>
            </li>
            <li>
              <a href="../../paginas/participante/index.php" class="text-gray-400 hover:text-brand-green transition-colors">
                Área do participante
              </a>
            </li>
            <li>
              <a href="../../paginas/public/index.php" class="text-gray-400 hover:text-brand-green transition-colors">
                Dúvidas frequentes
              </a>
            </li>
          </ul>
        </div>

        <!-- Coluna 4: Contato -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Contato</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <a href="https://wa.me/5599982027654" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-brand-green transition-colors flex items-center">
                <i class="fab fa-whatsapp mr-2"></i>
                WhatsApp: (99) 98202-7654
              </a>
            </li>
            <li>
              <a href="mailto:suporte@movamazon.com.br" class="text-gray-400 hover:text-brand-green transition-colors flex items-center">
                <i class="fas fa-envelope mr-2"></i>
                Email: suporte@movamazon.com.br
              </a>
            </li>
            <li class="text-gray-400 flex items-center">
              <i class="fas fa-clock mr-2"></i>
              Atendimento: 9h às 18h
            </li>
          </ul>
        </div>
      </div>

      <!-- Linha divisória e Copyright -->
      <div class="border-t border-gray-700 mt-8 pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-sm text-gray-400 text-center md:text-left">
            &copy; <?= date('Y') ?> MovAmazon. Todos os direitos reservados.
          </p>
          <div class="flex space-x-4 text-sm">
            <a href="#" class="text-gray-400 hover:text-brand-green transition-colors">
              Termos de Uso
            </a>
            <span class="text-gray-600">|</span>
            <a href="#" class="text-gray-400 hover:text-brand-green transition-colors">
              Política de Privacidade
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</body>

</html>
<?php
// Finalizar output buffering se estiver ativo
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>
