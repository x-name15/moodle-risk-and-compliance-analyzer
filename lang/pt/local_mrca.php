<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// (at your option) any later version.

/**
 * Portuguese (Portugal) language strings for MRCA.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin identity.
$string['pluginname'] = 'Moodle Risk & Compliance Analyzer';
$string['mrca'] = 'MRCA';

// Dashboard.
$string['dashboard_title'] = 'Painel MRCA';
$string['dashboard_desc'] = 'Análise completa de risco para a sua instalação Moodle.';
$string['scan_now'] = 'Iniciar análise';
$string['scan_completed'] = 'Análise de risco e conformidade concluída com sucesso.';
$string['last_scan'] = 'Última análise';
$string['no_scans_yet'] = 'Ainda não foram realizadas análises. Clique em "Iniciar análise" para começar a primeira análise.';

// Site Risk Index.
$string['site_risk_index'] = 'Índice de risco do site';
$string['site_risk_index_desc'] = 'Pontuação normalizada 0-100 que combina todas as camadas de risco.';
$string['total_score'] = 'Pontuação total';
$string['plugins_scanned'] = 'Plugins analisados';
$string['roles_scanned'] = 'Papéis analisados';
$string['scan_date'] = 'Data da análise';

// Risk levels.
$string['risk_healthy'] = 'Seguro';
$string['risk_low'] = 'Baixo';
$string['risk_medium'] = 'Médio';
$string['risk_moderate'] = 'Moderado';
$string['risk_high'] = 'Alto';
$string['risk_critical'] = 'Crítico';
$string['risk_score'] = 'Pontuação de risco';
$string['risk_level'] = 'Nível de risco';

// Scanner sections.
$string['privacy_scanner'] = 'Camada de Privacidade & Dados';
$string['privacy_scanner_desc'] = 'Deteção de PII, conformidade com a Privacy API e análise de encriptação.';
$string['dependency_scanner'] = 'Camada de Dependências & Compatibilidade';
$string['dependency_scanner_desc'] = 'Requisitos do plugin, compatibilidade com o core e deteção de APIs obsoletas.';
$string['capability_scanner'] = 'Camada de Capacidades & Permissões';
$string['capability_scanner_desc'] = 'Análise das capacidades dos papéis e deteção de risco de escalada de privilégios.';
$string['correlation_engine'] = 'Motor de correlação';
$string['correlation_engine_desc'] = 'Deteção de risco sistémico através da correlação de resultados entre todas as camadas.';

// Plugin risk details.
$string['plugin'] = 'Plugin';
$string['privacy_api'] = 'Privacy API';
$string['privacy_api_yes'] = '✓ Implementada';
$string['privacy_api_no'] = '✗ Em falta';
$string['details'] = 'Detalhes';

// Findings.
$string['reason_keyword_match'] = 'O nome do campo corresponde a uma palavra-chave PII';
$string['verified_encrypted'] = 'Conteúdo verificado como encriptado';
$string['verified_plaintext'] = 'Conteúdo detetado como texto simples';

// Role heatmap.
$string['role_heatmap'] = 'Mapa de calor de risco dos papéis';
$string['role_heatmap_desc'] = 'Visão geral da exposição de permissões em todos os papéis do sistema.';
$string['role'] = 'Papel';
$string['critical_caps'] = 'Capacidades críticas';
$string['role_risk_score'] = 'Pontuação de risco do papel';

// Alerts.
$string['alerts'] = 'Alertas de correlação';
$string['alerts_desc'] = 'Riscos sistémicos detetados através da correlação de resultados entre camadas.';
$string['no_alerts'] = 'Sem alertas de correlação. O sistema parece seguro.';
$string['alert_privacy_capability_correlation'] = 'CRÍTICO: O plugin "{$a}" apresenta elevado risco de privacidade, não implementa a Privacy API E define capacidades. Possível exposição de dados através de herança de permissões.';
$string['alert_high_risk_unstable_deps'] = 'ALTO: O plugin "{$a}" apresenta risco total elevado E dependências instáveis. Recomenda-se atualização ou substituição.';
$string['alert_systemic_risk'] = 'CRÍTICO: O plugin "{$a->plugin}" (alto risco) combinado com o papel ID {$a->roleid} (elevada exposição de permissões) cria probabilidade de falha sistémica.';

// Whitelist.
$string['whitelist'] = 'Lista branca';
$string['whitelist_desc'] = 'Os campos marcados como seguros serão excluídos de futuras análises.';
$string['whitelist_add'] = 'Adicionar à lista branca';
$string['whitelist_remove'] = 'Remover da lista branca';
$string['whitelist_empty'] = 'Sem campos na lista branca.';
$string['whitelist_component'] = 'Componente';
$string['whitelist_table'] = 'Tabela';
$string['whitelist_field'] = 'Campo';

// Settings.
$string['settings'] = 'Configurações MRCA';
$string['autoscan_new_plugins'] = 'Analisar automaticamente novos plugins';
$string['autoscan_new_plugins_desc'] = 'Executa automaticamente uma análise de risco quando um novo plugin é instalado ou ativado.';
$string['scan_core_plugins'] = 'Analisar plugins core do Moodle';
$string['scan_core_plugins_desc'] = 'Inclui os plugins padrão do Moodle (mantidos pelo Moodle HQ) nas análises. Desativado por defeito, pois os plugins core não são de terceiros e são atualizados com o Moodle. Ativar apenas se necessitar de auditoria completa.';

// Risk thresholds.
$string['risk_thresholds_heading'] = 'Limiares de risco';
$string['risk_thresholds_heading_desc'] = 'Configurar os limiares de pontuação para classificação dos níveis de risco.';
$string['threshold_high'] = 'Limiar de risco alto';
$string['threshold_high_desc'] = 'Plugins com pontuação igual ou superior a este valor são classificados como de alto risco.';
$string['threshold_medium'] = 'Limiar de risco médio';
$string['threshold_medium_desc'] = 'Plugins com pontuação igual ou superior a este valor são classificados como de risco médio.';

// Integration settings.
$string['integration_heading'] = 'Integração externa';
$string['integration_heading_desc'] = 'Configurar como o MRCA envia resultados para sistemas externos.';
$string['integration_method'] = 'Método de integração';
$string['integration_method_desc'] = 'Escolha como enviar externamente os resultados da análise.';
$string['integration_method_disabled'] = 'Desativado';
$string['integration_method_webhook'] = 'Webhook genérico';
$string['integration_method_mih'] = 'Moodle Integration Hub';
$string['recommended'] = 'Recomendado';
$string['mih_missing_note'] = 'Moodle Integration Hub (local_integrationhub) não está instalado. <a href="https://github.com/x-name15/moodle-integration-hub/releases/tag/1.0.0" target="_blank">Descarregue-o no GitHub</a> para capacidades avançadas de integração (recomendado).';
$string['mih_service_slug'] = 'MIH Service Slug';
$string['mih_service_slug_desc'] = 'Slug do serviço configurado no Integration Hub para dados do MRCA.';
$string['webhook_url'] = 'URL do webhook';
$string['webhook_url_desc'] = 'URL para onde enviar os resultados da análise (HTTP POST com corpo JSON).';
$string['webhook_token'] = 'Token do webhook';
$string['webhook_token_desc'] = 'Token Bearer para autenticação de pedidos webhook.';

// Reports.
$string['report_title'] = 'MRCA — Relatório de risco e conformidade';
$string['download_pdf'] = 'Descarregar PDF';
$string['download_csv'] = 'Descarregar CSV';
$string['download_json'] = 'Descarregar JSON';
$string['send_report'] = 'Enviar relatório';

// Events.
$string['event_high_risk_detected'] = 'Risco elevado detetado';
$string['event_high_risk_detected_desc'] = 'A análise do utilizador {$a->userid} detetou risco elevado no plugin "{$a->plugin}" com pontuação {$a->score}.';

// Risk distribution chart.
$string['risk_distribution'] = 'Distribuição de risco';

// Capabilities.
$string['mrca:view'] = 'Ver o Painel MRCA';
$string['mrca:configure'] = 'Configurar as definições MRCA';
$string['mrca:manage_scans'] = 'Gerir análises MRCA';

// Score breakdown.
$string['privacy_score'] = 'Pontuação de privacidade';
$string['dependency_score'] = 'Pontuação de dependências';
$string['capability_score'] = 'Pontuação de capacidades';

// JS/AJAX.
$string['whitelist_added'] = 'Campo adicionado à lista branca com sucesso.';
$string['whitelist_removed'] = 'Campo removido da lista branca.';
$string['report_sent'] = 'Relatório enviado com sucesso.';
$string['report_send_failed'] = 'Falha ao enviar o relatório.';
$string['confirm_scan'] = 'Será executada uma análise completa de risco e conformidade. Continuar?';

// Dashboard widgets.
$string['risk_trend'] = 'Tendência de risco';
$string['top_risky_plugins'] = 'Top 5 plugins de risco';
$string['top_risky_roles'] = 'Top 5 papéis de risco';
$string['dependency_audit'] = 'Auditoria de dependências';
$string['dep_issues'] = 'Problemas';
$string['dep_core_mismatch'] = 'Incompatibilidade de versão do core — o plugin requer uma versão mais recente do Moodle.';
$string['dep_missing'] = 'Dependência em falta: {$a}';
$string['dep_outdated'] = 'Versão do plugin desatualizada (sem atualização há mais de 2 anos).';
$string['dep_deprecated_apis'] = 'Detetadas {$a} chamadas de API obsoletas.';
$string['plugin_risk_details'] = 'Detalhes de risco do plugin';

// Structural scanner findings.
$string['structural_no_directory'] = 'Diretório do plugin não encontrado.';
$string['structural_no_version'] = 'version.php em falta — o plugin não pode ser validado.';
$string['structural_no_lang'] = 'Diretório de idioma não encontrado.';
$string['structural_no_readme'] = 'Ficheiro README não encontrado.';
$string['structural_no_tests'] = 'Diretório tests inexistente — o plugin não possui testes unitários.';
$string['structural_no_maturity'] = 'Declaração de maturidade ausente em version.php.';
$string['structural_legacy_cron'] = 'Utiliza $plugin->cron obsoleto. Deve utilizar a Task API.';

// Correlation alerts.
$string['alert_outdated_pii'] = 'ALTO: O plugin "{$a}" está desatualizado E trata dados PII. Vulnerabilidades não corrigidas podem afetar dados pessoais.';
$string['alert_structural_privacy'] = 'ALTO: O plugin "{$a}" apresenta problemas estruturais E lacunas de privacidade sem Privacy API. Provavelmente não conforme.';
$string['alert_multi_role_escalation'] = 'CRÍTICO: {$a} papéis não administradores possuem capacidades críticas excessivas. Configuração incorreta do modelo de permissões.';
$string['alert_deprecated_exposure'] = 'ALTO: O plugin "{$a}" utiliza funções obsoletas E apresenta exposição de PII não protegida. Plugin não mantido que trata dados sensíveis.';

// Report dispatch settings.
$string['report_dispatch_heading'] = 'Opções de envio de relatório';
$string['report_dispatch_heading_desc'] = 'Controlar quando e que dados são enviados para integrações externas após cada análise.';
$string['report_trigger'] = 'Acionador do relatório';
$string['report_trigger_desc'] = 'Quando enviar relatórios para a integração externa.';
$string['report_trigger_always'] = 'Sempre (após cada análise)';
$string['report_trigger_critical'] = 'Apenas quando forem detetados alertas críticos/altos';
$string['report_payload'] = 'Conteúdo do relatório';
$string['report_payload_desc'] = 'Quantidade de dados a incluir nos relatórios enviados.';
$string['report_payload_full'] = 'Relatório completo (todos os plugins + detalhes)';
$string['report_payload_summary'] = 'Apenas resumo (totais + alertas)';

// Whitelist UX.
$string['detected_pii_fields'] = 'Campos PII detetados';
$string['whitelist_this_field'] = 'Adicionar este campo à lista branca';
$string['no_pii_detected'] = 'Nenhum campo PII detetado.';

// Privacy API.
$string['privacy:metadata:whitelist'] = 'Registos de campos colocados na lista branca pelos administradores durante análises de risco.';
$string['privacy:metadata:whitelist:userid'] = 'ID do utilizador que colocou o campo na lista branca.';
$string['privacy:metadata:whitelist:component'] = 'Componente do plugin ao qual o campo pertence.';
$string['privacy:metadata:whitelist:table_name'] = 'Tabela da base de dados que contém o campo.';
$string['privacy:metadata:whitelist:field_name'] = 'Nome do campo da base de dados colocado na lista branca.';
$string['privacy:metadata:whitelist:timecreated'] = 'Data e hora em que o campo foi colocado na lista branca.';