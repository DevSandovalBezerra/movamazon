<div class="progress-container mb-4">
    <div class="progress-info mb-4" style="text-align: center; margin-bottom: 1rem;">
        <?php
        // Definir etapas padrão se não estiverem definidas (4 etapas)
        if (!isset($etapas) || !is_array($etapas)) {
            $etapas = [
                1 => ['nome' => 'Modalidade', 'status' => 'pendente'],
                2 => ['nome' => 'Termos', 'status' => 'pendente'],
                3 => ['nome' => 'Cadastro', 'status' => 'pendente'],
                4 => ['nome' => 'Pagamento', 'status' => 'pendente']
            ];
        }
        
        $totalEtapas = count($etapas);
        $etapaAtual = 1;
        $etapasCompletas = 0;
        
        foreach ($etapas as $num => $etapa) {
            if (($etapa['status'] ?? 'pendente') === 'concluida') {
                $etapasCompletas++;
            } elseif (($etapa['status'] ?? 'pendente') === 'atual') {
                $etapaAtual = $num;
            }
        }
        
        // Calcular porcentagem: (etapas completas / total) * 100
        // Se estamos na etapa 4, temos 3 completas, então: 3/5 = 60%
        // Mas visualmente queremos mostrar até a etapa atual, então: etapaAtual/total
        $porcentagem = ($etapaAtual / $totalEtapas) * 100;
        $porcentagem = min(100, max(0, round($porcentagem)));
        ?>
        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
            <strong style="color: #0b4340;"><?php echo $porcentagem; ?>%</strong> concluído
        </div>
        <div style="font-size: 0.875rem; color: #6b7280;">
            Tempo estimado restante: <strong><?php echo max(2, round(10 - ($etapasCompletas * 2))); ?> minutos</strong>
        </div>
    </div>
    
    <div class="progress-steps">
        <?php 
        $totalEtapas = count($etapas);
        $etapaAtualNum = 1;
        
        // Identificar etapa atual
        foreach ($etapas as $num => $etapa) {
            if (($etapa['status'] ?? 'pendente') === 'atual') {
                $etapaAtualNum = $num;
                break;
            }
        }
        
        for ($i = 1; $i <= $totalEtapas; $i++): 
        ?>
            <div class="step <?php echo $etapas[$i]['status'] ?? 'pendente'; ?>" data-step="<?php echo $i; ?>">
                <div class="step-number"><?php echo $i; ?></div>
                <div class="step-label"><?php echo $etapas[$i]['nome'] ?? 'Etapa ' . $i; ?></div>
            </div>
            <?php 
            if ($i < $totalEtapas): 
                // Linha fica verde se a etapa atual é maior que esta etapa
                // Se estamos na etapa 4, as linhas 1-2, 2-3, 3-4 ficam verdes
                $connectorClass = '';
                if ($i < $etapaAtualNum) {
                    $connectorClass = 'completed';
                }
            ?>
                <div class="step-connector <?php echo $connectorClass; ?>"></div>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
</div>

<style>
.progress-container {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.progress-steps {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
    flex: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step-label {
    font-size: 12px;
    text-align: center;
    font-weight: 500;
    color: #666;
    transition: all 0.3s ease;
}

/* Estados das etapas */
.step.pendente .step-number {
    background: #e9ecef;
    color: #6c757d;
    border: 2px solid #dee2e6;
}

.step.atual .step-number {
    background: #007bff;
    color: white;
    border: 2px solid #007bff;
    box-shadow: 0 0 0 4px rgba(0,123,255,0.2);
}

.step.concluida .step-number {
    background: #28a745;
    color: white;
    border: 2px solid #28a745;
}

.step.atual .step-label {
    color: #007bff;
    font-weight: 600;
}

.step.concluida .step-label {
    color: #28a745;
    font-weight: 600;
}

/* Conectores entre etapas */
.step-connector {
    height: 2px;
    background: #dee2e6;
    flex: 1;
    margin: 0 10px;
    margin-top: -20px;
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
}

.step-connector.completed {
    background: #28a745;
    height: 3px;
}

.progress-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

/* Responsividade */
@media (max-width: 768px) {
    .step-label {
        font-size: 10px;
    }
    
    .step-number {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }
    
    .progress-container {
        padding: 15px;
    }
}
</style>
