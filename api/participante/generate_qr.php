<?php
session_start();
// Apenas usuários logados podem gerar QR codes.
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    // Encerra o script com uma mensagem clara para evitar a geração de uma imagem quebrada.
    exit('Acesso negado.');
}

// Garante que o autoloader do Composer foi carregado.
// Se este arquivo falhar, significa que a dependência não está instalada corretamente.
require_once __DIR__ . '/../../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;
// Pega o dado a ser codificado (o número da inscrição) da URL.
$data = $_GET['data'] ?? null;

// Validação: Garante que os dados não estão vazios.
if (empty($data)) {
    http_response_code(400);
    exit('Dados para o QR Code não foram fornecidos.');
}

try {
    // 1. O Builder é iniciado. Ele vai construir nosso objeto QR Code.
    $builder = Builder::create();

    // 2. Definimos o que será escrito no QR Code.
    $builder->data($data);

    // 3. Definimos o "Writer", que é o formato da imagem de saída (PNG neste caso).
    $builder->writer(new PngWriter());

    // 4. Configurações adicionais para garantir boa leitura e compatibilidade.
    $builder->encoding(new Encoding('UTF-8'));
    $builder->errorCorrectionLevel(new ErrorCorrectionLevelHigh()); // Maior correção de erros
    $builder->size(250);
    $builder->margin(10);

    // 5. O método build() executa a construção e retorna um objeto 'Result'.
    $result = $builder->build();

    // 6. ANTES de qualquer 'echo', definimos o cabeçalho HTTP.
    // O método getMimeType() do objeto 'Result' retorna 'image/png',
    // informando ao navegador para tratar a resposta como uma imagem.
    header('Content-Type: ' . $result->getMimeType());

    // 7. Finalmente, usamos o método getString() do objeto 'Result' para
    // enviar os dados brutos da imagem para o navegador.
    echo $result->getString();

} catch (Exception $e) {
    // Se qualquer parte do processo de construção falhar, capturamos o erro.
    http_response_code(500);
    // Logamos o erro para depuração (em um cenário de produção).
    error_log('Erro ao gerar QR Code: ' . $e->getMessage());
    // E encerramos o script com uma mensagem de erro.
    exit('Não foi possível gerar o QR Code.');
}

// Nenhum outro código ou espaço em branco deve existir após o 'echo' da imagem.
