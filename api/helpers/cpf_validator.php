<?php
/**
 * Validador de CPF Brasileiro
 * Valida CPF de acordo com o algoritmo oficial da Receita Federal
 */

/**
 * Valida um CPF brasileiro
 * @param string $cpf CPF com ou sem formatação
 * @return bool true se válido, false se inválido
 */
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) !== 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais (ex: 111.111.111-11)
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    
    // Calcula os dígitos verificadores
    $soma = 0;
    
    // Calcula o primeiro dígito verificador
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }
    $resto = $soma % 11;
    $digitoVerificador1 = ($resto < 2) ? 0 : (11 - $resto);
    
    // Verifica o primeiro dígito
    if (intval($cpf[9]) !== $digitoVerificador1) {
        return false;
    }
    
    // Calcula o segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }
    $resto = $soma % 11;
    $digitoVerificador2 = ($resto < 2) ? 0 : (11 - $resto);
    
    // Verifica o segundo dígito
    if (intval($cpf[10]) !== $digitoVerificador2) {
        return false;
    }
    
    return true;
}

/**
 * Formata um CPF para o padrão XXX.XXX.XXX-XX
 * @param string $cpf CPF sem formatação
 * @return string CPF formatado
 */
function formatarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) !== 11) {
        return $cpf;
    }
    
    return substr($cpf, 0, 3) . '.' . 
           substr($cpf, 3, 3) . '.' . 
           substr($cpf, 6, 3) . '-' . 
           substr($cpf, 9, 2);
}
