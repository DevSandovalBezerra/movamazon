<?php
/**
 * Templates de Email para Notificações
 * Centraliza todos os templates de email do sistema
 */

/**
 * Template base para emails
 * 
 * @param string $titulo Título do email
 * @param string $conteudo Conteúdo HTML principal
 * @param string $botao_texto Texto do botão (opcional)
 * @param string $botao_url URL do botão (opcional)
 * @return string HTML completo do email
 */
function getEmailTemplate($titulo, $conteudo, $botao_texto = null, $botao_url = null) {
    $logo_url = 'https://movamazon.com.br/frontend/assets/img/logo.png';
    $site_url = 'https://movamazon.com.br';
    
    $botao_html = '';
    if ($botao_texto && $botao_url) {
        $botao_html = '
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 32px 0;">
                <tr>
                    <td style="text-align: center;">
                        <a href="' . htmlspecialchars($botao_url) . '" 
                           style="background-color: #0b4340; 
                                  color: #ffffff; 
                                  text-decoration: none; 
                                  padding: 12px 24px; 
                                  border-radius: 6px; 
                                  display: inline-block; 
                                  font-weight: 600;">
                            ' . htmlspecialchars($botao_texto) . '
                        </a>
                    </td>
                </tr>
            </table>
        ';
    }
    
    return '
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($titulo) . '</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f7fa;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f5f7fa; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 32px 40px; text-align: center; border-bottom: 1px solid #e9ecef;">
                            <img src="' . $logo_url . '" alt="MovAmazon" style="max-width: 200px; height: auto;">
                        </td>
                    </tr>
                    
                    <!-- Conteúdo -->
                    <tr>
                        <td style="padding: 40px;">
                            <h1 style="margin: 0 0 24px 0; font-size: 24px; font-weight: 600; color: #2c3e50;">
                                ' . htmlspecialchars($titulo) . '
                            </h1>
                            
                            <div style="color: #495057; font-size: 16px; line-height: 1.6;">
                                ' . $conteudo . '
                            </div>
                            
                            ' . $botao_html . '
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 40px; background-color: #f8f9fa; border-top: 1px solid #e9ecef; text-align: center; font-size: 14px; color: #6c757d;">
                            <p style="margin: 0 0 8px 0;">
                                <strong>MovAmazon</strong> - Sistema de Gestão de Eventos Esportivos
                            </p>
                            <p style="margin: 0;">
                                <a href="' . $site_url . '" style="color: #0b4340; text-decoration: none;">Acessar Plataforma</a> | 
                                <a href="' . $site_url . '/contato" style="color: #0b4340; text-decoration: none;">Contato</a>
                            </p>
                            <p style="margin: 16px 0 0 0; font-size: 12px; color: #adb5bd;">
                                Este é um email automático, por favor não responda.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
    ';
}

/**
 * Template: Cancelamento Solicitado (Admin)
 */
function getEmailTemplateCancelamentoSolicitadoAdmin($dados) {
    $titulo = 'Nova Solicitação de Cancelamento';
    $conteudo = '
        <p>Uma nova solicitação de cancelamento foi recebida:</p>
        
        <table style="width: 100%; margin: 24px 0; border-collapse: collapse;">
            <tr>
                <td style="padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 600; width: 40%;">ID da Solicitação:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6;">#' . htmlspecialchars($dados['solicitacao_id']) . '</td>
            </tr>
            <tr>
                <td style="padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 600;">ID da Inscrição:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6;">#' . htmlspecialchars($dados['inscricao_id']) . '</td>
            </tr>
            <tr>
                <td style="padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 600;">Evento:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6;">' . htmlspecialchars($dados['evento_nome']) . '</td>
            </tr>
            <tr>
                <td style="padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 600;">Participante:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6;">
                    ' . htmlspecialchars($dados['usuario_nome']) . '<br>
                    <span style="font-size: 14px; color: #6c757d;">' . htmlspecialchars($dados['usuario_email']) . '</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 600;">Valor da Inscrição:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6; color: #28a745; font-weight: 600;">
                    R$ ' . number_format($dados['valor_total'], 2, ',', '.') . '
                </td>
            </tr>
            <tr>
                <td style="padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 600;">Motivo:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6;">
                    <div style="background-color: #fff3cd; padding: 12px; border-radius: 4px; border-left: 4px solid #ffc107;">
                        ' . nl2br(htmlspecialchars($dados['motivo'])) . '
                    </div>
                </td>
            </tr>
        </table>
        
        <p style="margin-top: 24px; padding: 16px; background-color: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 4px;">
            <strong>⚠️ Ação Necessária:</strong> Acesse o painel administrativo para analisar e processar esta solicitação.
        </p>
    ';
    
    $admin_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/movamazon/frontend/paginas/admin/index.php?page=cancelamentos';
    
    return getEmailTemplate($titulo, $conteudo, 'Acessar Painel Admin', $admin_url);
}

/**
 * Template: Cancelamento Aprovado (Participante)
 */
function getEmailTemplateCancelamentoAprovado($dados) {
    $titulo = 'Cancelamento Aprovado';
    $conteudo = '
        <p>Olá <strong>' . htmlspecialchars($dados['usuario_nome']) . '</strong>,</p>
        
        <p>Sua solicitação de cancelamento para o evento <strong>' . htmlspecialchars($dados['evento_nome']) . '</strong> foi <span style="color: #28a745; font-weight: 600;">aprovada</span>.</p>
        
        <p>Sua inscrição foi cancelada com sucesso.</p>
    ';
    
    if (isset($dados['reembolso_processado']) && $dados['reembolso_processado'] && isset($dados['valor_reembolso']) && $dados['valor_reembolso'] > 0) {
        $conteudo .= '
            <div style="margin: 24px 0; padding: 20px; background-color: #d4edda; border-left: 4px solid #28a745; border-radius: 4px;">
                <h3 style="margin: 0 0 12px 0; color: #155724; font-size: 18px;">💰 Reembolso Processado</h3>
                <p style="margin: 0; color: #155724;">
                    O valor de <strong>R$ ' . number_format($dados['valor_reembolso'], 2, ',', '.') . '</strong> será creditado em sua conta em até <strong>14 dias úteis</strong>.
                </p>
            </div>
        ';
    } elseif (isset($dados['status_pagamento']) && $dados['status_pagamento'] === 'pago') {
        $conteudo .= '
            <div style="margin: 24px 0; padding: 20px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                <p style="margin: 0; color: #856404;">
                    <strong>ℹ️ Observação:</strong> O reembolso será processado em breve. Em caso de dúvidas, entre em contato conosco.
                </p>
            </div>
        ';
    }
    
    $conteudo .= '
        <p style="margin-top: 24px;">Se tiver alguma dúvida, nossa equipe está à disposição para ajudar.</p>
    ';
    
    return getEmailTemplate($titulo, $conteudo);
}

/**
 * Template: Cancelamento Rejeitado (Participante)
 */
function getEmailTemplateCancelamentoRejeitado($dados) {
    $titulo = 'Solicitação de Cancelamento - Análise';
    $conteudo = '
        <p>Olá <strong>' . htmlspecialchars($dados['usuario_nome']) . '</strong>,</p>
        
        <p>Sua solicitação de cancelamento para o evento <strong>' . htmlspecialchars($dados['evento_nome']) . '</strong> foi analisada.</p>
        
        <div style="margin: 24px 0; padding: 20px; background-color: #f8d7da; border-left: 4px solid #dc3545; border-radius: 4px;">
            <h3 style="margin: 0 0 12px 0; color: #721c24; font-size: 18px;">❌ Resultado: Rejeitada</h3>
            <p style="margin: 0 0 12px 0; color: #721c24; font-weight: 600;">Motivo da Rejeição:</p>
            <p style="margin: 0; color: #721c24;">
                ' . nl2br(htmlspecialchars($dados['motivo_rejeicao'])) . '
            </p>
        </div>
        
        <p>Sua inscrição permanece ativa. Em caso de dúvidas ou para mais informações, entre em contato conosco.</p>
    ';
    
    return getEmailTemplate($titulo, $conteudo);
}

/**
 * Template: Pagamento Confirmado (Participante)
 */
function getEmailTemplatePagamentoConfirmado($dados) {
    $titulo = 'Pagamento Confirmado';
    $conteudo = '
        <p>Olá <strong>' . htmlspecialchars($dados['usuario_nome']) . '</strong>,</p>
        
        <p>Seu pagamento para o evento <strong>' . htmlspecialchars($dados['evento_nome']) . '</strong> foi <span style="color: #28a745; font-weight: 600;">confirmado com sucesso</span>!</p>
        
        <div style="margin: 24px 0; padding: 20px; background-color: #d4edda; border-left: 4px solid #28a745; border-radius: 4px;">
            <p style="margin: 0; color: #155724;">
                <strong>✅ Sua inscrição está ativa!</strong> Você receberá mais informações sobre o evento em breve.
            </p>
        </div>
        
        <p>Obrigado por participar! Estamos ansiosos para vê-lo no evento.</p>
    ';
    
    return getEmailTemplate($titulo, $conteudo);
}

/**
 * Template: Reembolso Processado (Participante)
 */
function getEmailTemplateReembolsoProcessado($dados) {
    $titulo = 'Reembolso Processado';
    $conteudo = '
        <p>Olá <strong>' . htmlspecialchars($dados['usuario_nome']) . '</strong>,</p>
        
        <p>Informamos que o reembolso referente ao cancelamento da sua inscrição no evento <strong>' . htmlspecialchars($dados['evento_nome']) . '</strong> foi processado.</p>
        
        <div style="margin: 24px 0; padding: 20px; background-color: #d4edda; border-left: 4px solid #28a745; border-radius: 4px;">
            <h3 style="margin: 0 0 12px 0; color: #155724; font-size: 18px;">💰 Detalhes do Reembolso</h3>
            <table style="width: 100%; margin: 12px 0;">
                <tr>
                    <td style="padding: 8px 0; color: #155724;"><strong>Valor:</strong></td>
                    <td style="padding: 8px 0; color: #155724; font-size: 20px; font-weight: 600;">
                        R$ ' . number_format($dados['valor_reembolso'], 2, ',', '.') . '
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #155724;"><strong>Prazo de Crédito:</strong></td>
                    <td style="padding: 8px 0; color: #155724;">Até 14 dias úteis</td>
                </tr>
            </table>
        </div>
        
        <p>O valor será creditado na mesma forma de pagamento utilizada na compra original.</p>
    ';
    
    return getEmailTemplate($titulo, $conteudo);
}

/**
 * Template: Alerta de Sincronização (Admin)
 */
function getEmailTemplateAlertaSincronizacao($dados) {
    $titulo = 'Alerta: Alta Taxa de Falha na Sincronização';
    $conteudo = '
        <p>A taxa de falha na sincronização automática de pagamentos está acima do esperado.</p>
        
        <table style="width: 100%; margin: 24px 0; border-collapse: collapse;">
            <tr>
                <td style="padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 600;">Total Processado:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6;">' . number_format($dados['total'], 0, ',', '.') . '</td>
            </tr>
            <tr>
                <td style="padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 600;">Sucessos:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6; color: #28a745;">' . number_format($dados['sucessos'], 0, ',', '.') . '</td>
            </tr>
            <tr>
                <td style="padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 600;">Falhas:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6; color: #dc3545;">' . number_format($dados['falhas'], 0, ',', '.') . '</td>
            </tr>
            <tr>
                <td style="padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 600;">Taxa de Falha:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6; color: #dc3545; font-weight: 600;">
                    ' . number_format($dados['taxa_falha'], 2, ',', '.') . '%
                </td>
            </tr>
            <tr>
                <td style="padding: 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 600;">Status Atualizados:</td>
                <td style="padding: 12px; border: 1px solid #dee2e6;">' . number_format($dados['atualizados'], 0, ',', '.') . '</td>
            </tr>
        </table>
        
        <p style="margin-top: 24px; padding: 16px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
            <strong>⚠️ Ação Recomendada:</strong> Verifique os logs do sistema para identificar a causa das falhas e tomar as medidas necessárias.
        </p>
    ';
    
    $admin_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/movamazon/frontend/paginas/admin/index.php?page=pagamentos-pendentes';
    
    return getEmailTemplate($titulo, $conteudo, 'Ver Pagamentos Pendentes', $admin_url);
}
