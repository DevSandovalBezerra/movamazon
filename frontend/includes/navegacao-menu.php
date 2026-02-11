<?php

/**
 * Componente de navegação entre páginas do menu
 * 
 * @param string $paginaAtual - Nome da página atual (ex: 'modalidades')
 * @param string $tituloAtual - Título da página atual (ex: '2- Modalidades')
 */

function renderizarNavegacaoMenu($paginaAtual, $tituloAtual)
{
    // Mapeamento das páginas do menu
    $menuPages = [
        'eventos' => [
            'numero' => 1,
            'titulo' => '1- Meus Eventos',
            'url' => 'eventos/',
            'anterior' => null,
            'proximo' => 'modalidades'
        ],
        'modalidades' => [
            'numero' => 2,
            'titulo' => '2- Modalidades',
            'url' => 'modalidades/',
            'anterior' => 'eventos',
            'proximo' => 'lotes-inscricao'
        ],
        'lotes-inscricao' => [
            'numero' => 3,
            'titulo' => '3- Lotes de Inscrição',
            'url' => 'lotes-inscricao/',
            'anterior' => 'modalidades',
            'proximo' => 'cupons-remessa'
        ],
        'cupons-remessa' => [
            'numero' => 4,
            'titulo' => '4- Cupons de Desconto',
            'url' => 'cupons-remessa/',
            'anterior' => 'lotes-inscricao',
            'proximo' => 'questionario'
        ],
        'questionario' => [
            'numero' => 5,
            'titulo' => '5- Questionário',
            'url' => 'questionario/',
            'anterior' => 'cupons-remessa',
            'proximo' => 'produtos'
        ],
        'produtos' => [
            'numero' => 6,
            'titulo' => '6- Produtos',
            'url' => 'produtos/',
            'anterior' => 'questionario',
            'proximo' => 'kits-templates'
        ],
        'kits-templates' => [
            'numero' => 7,
            'titulo' => '7- Templates de Kit',
            'url' => 'kits-templates/',
            'anterior' => 'produtos',
            'proximo' => 'kits-evento'
        ],
        'kits-evento' => [
            'numero' => 8,
            'titulo' => '8- Kits do Evento',
            'url' => 'kits-evento/',
            'anterior' => 'kits-templates',
            'proximo' => 'retirada-kits'
        ],
        'retirada-kits' => [
            'numero' => 9,
            'titulo' => '9- Retirada de Kits',
            'url' => 'retirada-kits/',
            'anterior' => 'kits-evento',
            'proximo' => 'camisas'
        ],
        'camisas' => [
            'numero' => 10,
            'titulo' => '10- Camisas',
            'url' => 'camisas/',
            'anterior' => 'retirada-kits',
            'proximo' => 'produtos-extras'
        ],
        'produtos-extras' => [
            'numero' => 11,
            'titulo' => '11- Produtos Extras',
            'url' => 'produtos-extras/',
            'anterior' => 'camisas',
            'proximo' => 'programacao'
        ],
        'programacao' => [
            'numero' => 12,
            'titulo' => '12- Programação',
            'url' => 'programacao/',
            'anterior' => 'produtos-extras',
            'proximo' => null
        ]
    ];

    if (!isset($menuPages[$paginaAtual])) {
        return;
    }

    $pagina = $menuPages[$paginaAtual];
    $anterior = $pagina['anterior'] ? $menuPages[$pagina['anterior']] : null;
    $proximo = $pagina['proximo'] ? $menuPages[$pagina['proximo']] : null;
    $baseUrl = '../../../frontend/paginas/organizador/';
?>

    <!-- Navegação do Menu -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex items-center justify-between">
            <!-- Botão Anterior -->
            <div class="flex-1">
                <?php if ($anterior): ?>
                    <a href="<?php echo $baseUrl . $anterior['url']; ?>"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200">
                        <i class="fas fa-chevron-left mr-2"></i>
                        <div class="text-left">
                            <div class="text-xs text-gray-500">Anterior</div>
                            <div class="font-medium"><?php echo $anterior['titulo']; ?></div>
                        </div>
                    </a>
                <?php else: ?>
                    <div class="inline-flex items-center px-4 py-2 bg-gray-50 text-gray-400 rounded-lg">
                        <i class="fas fa-chevron-left mr-2"></i>
                        <div class="text-left">
                            <div class="text-xs">Primeira página</div>
                            <div class="font-medium">Sem anterior</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Página Atual -->
            <div class="flex-1 text-center">
                <div class="inline-flex items-center px-6 py-2 bg-blue-100 text-blue-800 rounded-lg">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    <div>
                        <div class="text-xs text-blue-600">Página Atual</div>
                        <div class="font-semibold"><?php echo $pagina['titulo']; ?></div>
                    </div>
                </div>
            </div>

            <!-- Botão Próximo -->
            <div class="flex-1 text-right">
                <?php if ($proximo): ?>
                    <a href="<?php echo $baseUrl . $proximo['url']; ?>"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200">
                        <div class="text-right">
                            <div class="text-xs text-gray-500">Próximo</div>
                            <div class="font-medium"><?php echo $proximo['titulo']; ?></div>
                        </div>
                        <i class="fas fa-chevron-right ml-2"></i>
                    </a>
                <?php else: ?>
                    <div class="inline-flex items-center px-4 py-2 bg-gray-50 text-gray-400 rounded-lg">
                        <div class="text-right">
                            <div class="text-xs">Última página</div>
                            <div class="font-medium">Sem próximo</div>
                        </div>
                        <i class="fas fa-chevron-right ml-2"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php
}
?>
