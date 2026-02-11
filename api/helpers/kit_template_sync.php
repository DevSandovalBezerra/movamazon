<?php
/**
 * Sincroniza kits de evento vinculados a um template:
 * - Atualiza FOTO do kit_evento copiando a foto do template e renomeando via gerarNomeKit('evento', ...)
 * - Substitui PRODUTOS do kit_evento com base em kit_template_produtos
 *
 * Importante:
 * - Operações de arquivo (copy/unlink) não são transacionais.
 * - A função tenta processar todos os kits; falhas por kit são logadas e não interrompem o restante.
 *
 * @param PDO $pdo
 * @param int $template_id
 * @param string $upload_dir Caminho (relativo ou absoluto) para frontend/assets/img/kits/
 * @return array Resumo do processamento (útil para logs)
 */
function syncKitsEventosFromTemplate(PDO $pdo, int $template_id, string $upload_dir): array
{
    $summary = [
        'template_id' => $template_id,
        'kits_total' => 0,
        'kits_ok' => 0,
        'kits_falha' => 0,
        'foto_atualizada' => 0,
        'produtos_atualizados' => 0,
        'errors' => [],
    ];

    // Buscar foto do template (pode ser null/empty)
    $stmt = $pdo->prepare("SELECT foto_kit FROM kit_templates WHERE id = ? LIMIT 1");
    $stmt->execute([$template_id]);
    $template_row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $template_foto = $template_row['foto_kit'] ?? null;

    // Buscar produtos atuais do template
    $stmt = $pdo->prepare("
        SELECT produto_id, quantidade, ordem
        FROM kit_template_produtos
        WHERE kit_template_id = ? AND ativo = 1
        ORDER BY ordem ASC, id ASC
    ");
    $stmt->execute([$template_id]);
    $template_produtos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Buscar kits_eventos vinculados ao template
    $stmt = $pdo->prepare("
        SELECT id, evento_id, foto_kit
        FROM kits_eventos
        WHERE kit_template_id = ? AND ativo = 1
    ");
    $stmt->execute([$template_id]);
    $kits = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $summary['kits_total'] = count($kits);

    if ($summary['kits_total'] === 0) {
        return $summary;
    }

    $source_path = null;
    $template_ext = null;
    if (!empty($template_foto)) {
        $source_path = $upload_dir . $template_foto;
        $template_ext = strtolower(pathinfo($template_foto, PATHINFO_EXTENSION) ?: 'png');
    }

    foreach ($kits as $kit) {
        $kit_id = (int)($kit['id'] ?? 0);
        $evento_id = (int)($kit['evento_id'] ?? 0);
        $old_foto = $kit['foto_kit'] ?? null;

        $new_filename = null;
        $copied = false;

        try {
            // 1) Foto: copiar e preparar novo nome
            if (!empty($source_path) && is_file($source_path)) {
                // gerarNomeKit é definido em file_utils.php (incluído pelo chamador)
                $new_filename = gerarNomeKit('evento', $kit_id, $evento_id, $template_ext ?: 'png');
                $target_path = $upload_dir . $new_filename;

                if (@copy($source_path, $target_path)) {
                    $copied = true;
                } else {
                    $summary['errors'][] = "Kit {$kit_id}: falha ao copiar foto do template (source={$source_path}, target={$target_path})";
                    error_log("🚨 syncKitsEventosFromTemplate - Kit {$kit_id}: falha ao copiar foto (source={$source_path}, target={$target_path})");
                }
            }

            // 2) Produtos + update foto (transação por kit)
            $pdo->beginTransaction();

            // Substituir produtos do kit pelo template (mesmo se o template estiver vazio)
            $pdo->prepare("DELETE FROM kit_produtos WHERE kit_id = ?")->execute([$kit_id]);

            if (!empty($template_produtos)) {
                $stmtIns = $pdo->prepare("
                    INSERT INTO kit_produtos (kit_id, produto_id, quantidade, ordem, ativo)
                    VALUES (?, ?, ?, ?, 1)
                ");

                foreach ($template_produtos as $p) {
                    $produto_id = (int)($p['produto_id'] ?? 0);
                    $quantidade = (int)($p['quantidade'] ?? 1);
                    $ordem = (int)($p['ordem'] ?? 1);

                    if ($produto_id > 0 && $quantidade > 0) {
                        $stmtIns->execute([$kit_id, $produto_id, $quantidade, $ordem]);
                    }
                }
            }

            $summary['produtos_atualizados']++;

            // Atualizar foto no banco apenas se a cópia ocorreu
            if ($copied && !empty($new_filename)) {
                $stmtUp = $pdo->prepare("UPDATE kits_eventos SET foto_kit = ? WHERE id = ?");
                $stmtUp->execute([$new_filename, $kit_id]);
                $summary['foto_atualizada']++;
            }

            $pdo->commit();
            $summary['kits_ok']++;

            // Remover foto antiga (após commit) se for diferente e se o kit tinha foto
            if ($copied && !empty($new_filename) && !empty($old_foto) && $old_foto !== $new_filename) {
                $old_path = $upload_dir . $old_foto;
                if (is_file($old_path)) {
                    @unlink($old_path);
                }
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $summary['kits_falha']++;
            $summary['errors'][] = "Kit {$kit_id}: " . $e->getMessage();
            error_log("🚨 syncKitsEventosFromTemplate - Kit {$kit_id}: erro: " . $e->getMessage());

            // Se copiamos a foto mas falhou no banco, tentar limpar o arquivo novo para evitar órfãos
            if ($copied && !empty($new_filename)) {
                $new_path = $upload_dir . $new_filename;
                if (is_file($new_path)) {
                    @unlink($new_path);
                }
            }
        }
    }

    return $summary;
}

