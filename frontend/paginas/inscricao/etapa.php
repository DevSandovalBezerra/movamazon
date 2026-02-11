<?php
session_start();
$etapa = $_GET['etapa'] ?? 'modalidade';
$evento_id = $_GET['evento_id'] ?? 0;
$modalidade_id = $_GET['modalidade_id'] ?? 0;

// Defina as variáveis necessárias para os includes
// Exemplo: $evento_id, $modalidade_id, etc.

switch ($etapa) {
    case 'modalidade':
        include 'modalidade.php';
        break;
    case 'termos':
        include 'termos.php';
        break;
    case 'identificacao':
        include 'identificacao.php';
        break;
    case 'ficha':
        include 'ficha.php';
        break;
    case 'pagamento':
        include 'pagamento.php';
        break;
    default:
        echo '<div class="alert alert-danger">Etapa inválida</div>';
}
