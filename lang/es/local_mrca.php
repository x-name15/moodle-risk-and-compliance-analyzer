<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Spanish language strings for MRCA.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['alert_deprecated_exposure'] = 'ALTO: El plugin "{$a}" usa funciones obsoletas Y tiene exposición de PII desprotegida. Plugin sin mantenimiento manejando datos sensibles.';
$string['alert_high_risk_unstable_deps'] = 'ALTO: El plugin "{$a}" tiene alto riesgo total Y dependencias inestables. Se recomienda actualizar o reemplazar.';
$string['alert_multi_role_escalation'] = 'CRÍTICO: {$a} roles no administrativos tienen exceso de capacidades críticas. Configuración errónea del modelo de permisos.';
$string['alert_outdated_pii'] = 'ALTO: El plugin "{$a}" está desactualizado Y maneja datos PII. Las vulnerabilidades sin parche pueden afectar datos personales.';
$string['alert_privacy_capability_correlation'] = 'CRÍTICO: El plugin "{$a}" tiene alto riesgo de privacidad, no tiene API de Privacidad Y define capacidades. Posible exposición de datos a través de herencia de permisos.';
$string['alert_structural_privacy'] = 'ALTO: El plugin "{$a}" tiene problemas estructurales Y brechas de privacidad sin API de Privacidad. Probablemente no cumple las normas.';
$string['alert_systemic_risk'] = 'CRÍTICO: El plugin "{$a->plugin}" (alto riesgo) combinado con el Rol ID {$a->roleid} (alta exposición de permisos) crea probabilidad de fallo sistémico.';
$string['alerts'] = 'Alertas de Correlación';
$string['alerts_desc'] = 'Riesgos sistémicos detectados al cruzar hallazgos entre capas.';
$string['autoscan_new_plugins'] = 'Escanear automáticamente nuevos plugins';
$string['autoscan_new_plugins_desc'] = 'Ejecutar automáticamente un escaneo de riesgos cuando se instala o activa un nuevo plugin.';
$string['capability_scanner'] = 'Capa de Capacidades y Permisos';
$string['capability_scanner_desc'] = 'Análisis de capacidades de roles y detección de riesgo de escalada de privilegios.';
$string['capability_score'] = 'Puntuación de Capacidades';
$string['confirm_scan'] = 'Esto ejecutará un escaneo completo de riesgos y cumplimiento. ¿Continuar?';
$string['correlation_engine'] = 'Motor de Correlación';
$string['correlation_engine_desc'] = 'Detección de riesgos sistémicos correlacionando hallazgos de todas las capas.';
$string['critical_caps'] = 'Capacidades Críticas';
$string['dashboard_desc'] = 'Análisis integral de riesgos para tu instalación Moodle.';
$string['dashboard_title'] = 'Panel de MRCA';
$string['dep_core_mismatch'] = 'Incompatibilidad de versión del core — el plugin requiere una versión más reciente de Moodle.';
$string['dep_deprecated_apis'] = '{$a} llamada(s) a API obsoleta(s) detectada(s).';
$string['dep_issues'] = 'Problemas';
$string['dep_missing'] = 'Dependencia faltante: {$a}';
$string['dep_outdated'] = 'La versión del plugin es antigua (sin actualización en más de 2 años).';
$string['dependency_audit'] = 'Auditoría de Dependencias';
$string['dependency_scanner'] = 'Capa de Dependencias y Compatibilidad';
$string['dependency_scanner_desc'] = 'Requisitos de plugins, compatibilidad con el core y detección de APIs obsoletas.';
$string['dependency_score'] = 'Puntuación de Dependencias';
$string['details'] = 'Detalles';
$string['detected_pii_fields'] = 'Campos PII detectados';
$string['download_csv'] = 'Descargar CSV';
$string['download_json'] = 'Descargar JSON';
$string['download_pdf'] = 'Descargar PDF';
$string['event_high_risk_detected'] = 'Riesgo alto detectado';
$string['event_high_risk_detected_desc'] = 'El escaneo del usuario {$a->userid} detectó riesgo alto en el plugin "{$a->plugin}" con puntuación {$a->score}.';
$string['integration_heading'] = 'Integración Externa';
$string['integration_heading_desc'] = 'Configura cómo MRCA envía resultados a sistemas externos.';
$string['integration_method'] = 'Método de Integración';
$string['integration_method_desc'] = 'Elige cómo enviar los resultados del escaneo externamente.';
$string['integration_method_disabled'] = 'Deshabilitado';
$string['integration_method_mih'] = 'Integration Hub for Moodle™';
$string['integration_method_webhook'] = 'Webhook Genérico';
$string['last_scan'] = 'Último Escaneo';
$string['mih_missing_note'] = 'Integration Hub for Moodle™ (local_integrationhub) no está instalado. <a href="https://github.com/x-name15/moodle-local_integrationhub/releases/tag/1.0.0" target="_blank">Descárgalo desde GitHub</a> para capacidades avanzadas de integración (recomendado).';
$string['mih_service_slug'] = 'Slug del Servicio MIH';
$string['mih_service_slug_desc'] = 'El slug del servicio configurado en el Integration Hub para datos de MRCA.';
$string['mrca'] = 'MRCA';
$string['mrca:configure'] = 'Configurar Ajustes de MRCA';
$string['mrca:manage_scans'] = 'Gestionar Escaneos de MRCA';
$string['mrca:view'] = 'Ver Panel de MRCA';
$string['no_alerts'] = 'Sin alertas de correlación. El sistema se ve saludable.';
$string['no_pii_detected'] = 'No se detectaron campos PII.';
$string['no_scans_yet'] = 'Aún no se han ejecutado escaneos. Haz clic en "Escanear Ahora" para iniciar tu primer análisis.';
$string['plugin'] = 'Plugin';
$string['plugin_risk_details'] = 'Detalle de Riesgo por Plugin';
$string['pluginname'] = 'Analizador de Riesgos y Cumplimiento para Moodle';
$string['plugins_scanned'] = 'Plugins Escaneados';
$string['privacy:metadata:whitelist'] = 'Registros de campos añadidos a la lista blanca por administradores durante escaneos de riesgo.';
$string['privacy:metadata:whitelist:component'] = 'El componente del plugin al que pertenece el campo.';
$string['privacy:metadata:whitelist:field_name'] = 'El nombre del campo de base de datos añadido a la lista blanca.';
$string['privacy:metadata:whitelist:table_name'] = 'La tabla de base de datos que contiene el campo.';
$string['privacy:metadata:whitelist:timecreated'] = 'La fecha en que el campo fue añadido a la lista blanca.';
$string['privacy:metadata:whitelist:userid'] = 'El ID del usuario que añadió el campo a la lista blanca.';
$string['privacy_api'] = 'API de Privacidad';
$string['privacy_api_no'] = '✗ Faltante';
$string['privacy_api_yes'] = '✓ Implementada';
$string['privacy_scanner'] = 'Capa de Privacidad y Datos';
$string['privacy_scanner_desc'] = 'Detección de PII, cumplimiento de Privacy API y análisis de cifrado.';
$string['privacy_score'] = 'Puntuación de Privacidad';
$string['reason_keyword_match'] = 'El nombre del campo coincide con palabra clave de PII';
$string['recommended'] = 'Recomendado';
$string['report_dispatch_heading'] = 'Opciones de Despacho de Reportes';
$string['report_dispatch_heading_desc'] = 'Controlar cuándo y qué se envía a integraciones externas después de cada escaneo.';
$string['report_payload'] = 'Contenido del reporte';
$string['report_payload_desc'] = 'Cuántos datos incluir en los reportes enviados.';
$string['report_payload_full'] = 'Reporte completo (todos los plugins + detalles)';
$string['report_payload_summary'] = 'Solo resumen (totales + alertas)';
$string['report_send_failed'] = 'Error al enviar el informe.';
$string['report_sent'] = 'Informe enviado exitosamente.';
$string['report_title'] = 'MRCA — Informe de Riesgos y Cumplimiento';
$string['report_trigger'] = 'Disparador de reportes';
$string['report_trigger_always'] = 'Siempre (después de cada escaneo)';
$string['report_trigger_critical'] = 'Solo cuando se encuentren alertas críticas/altas';
$string['report_trigger_desc'] = 'Cuándo enviar reportes a la integración externa.';
$string['risk_critical'] = 'Crítico';
$string['risk_distribution'] = 'Distribución de Riesgos';
$string['risk_healthy'] = 'Saludable';
$string['risk_high'] = 'Alto';
$string['risk_level'] = 'Nivel de Riesgo';
$string['risk_low'] = 'Bajo';
$string['risk_medium'] = 'Medio';
$string['risk_moderate'] = 'Moderado';
$string['risk_score'] = 'Puntuación de Riesgo';
$string['risk_thresholds_heading'] = 'Umbrales de Riesgo';
$string['risk_thresholds_heading_desc'] = 'Configura los umbrales de puntuación para la clasificación de niveles de riesgo.';
$string['risk_trend'] = 'Tendencia de Riesgo';
$string['role'] = 'Rol';
$string['role_heatmap'] = 'Mapa de Calor de Riesgo de Roles';
$string['role_heatmap_desc'] = 'Visión general de la exposición de permisos de roles en todo el sistema.';
$string['role_risk_score'] = 'Puntuación de Riesgo del Rol';
$string['roles_scanned'] = 'Roles Escaneados';
$string['scan_completed'] = 'Escaneo de riesgos y cumplimiento completado exitosamente.';
$string['scan_core_plugins'] = 'Escanear plugins del core de Moodle';
$string['scan_core_plugins_desc'] = 'Incluir plugins estándar de Moodle (mantenidos por Moodle HQ) en los escaneos. Desactivado por defecto ya que los plugins del core no son de terceros y se actualizan con Moodle. Activar solo si necesitas una auditoría completa incluyendo módulos del core.';
$string['scan_date'] = 'Fecha del Escaneo';
$string['scan_now'] = 'Escanear Ahora';
$string['send_report'] = 'Enviar Informe';
$string['settings'] = 'Configuración de MRCA';
$string['site_risk_index'] = 'Índice de Riesgo del Sitio';
$string['site_risk_index_desc'] = 'Puntuación normalizada 0-100 combinando todas las capas de riesgo.';
$string['structural_legacy_cron'] = 'Usa $plugin->cron obsoleto. Debería usar Task API.';
$string['structural_no_directory'] = 'Directorio del plugin no encontrado.';
$string['structural_no_lang'] = 'No se encontró directorio de idioma.';
$string['structural_no_maturity'] = 'Sin declaración de madurez en version.php.';
$string['structural_no_readme'] = 'No se encontró archivo README.';
$string['structural_no_tests'] = 'No se encontró directorio de tests — el plugin no tiene pruebas unitarias.';
$string['structural_no_version'] = 'Falta version.php — el plugin no puede ser validado.';
$string['threshold_high'] = 'Umbral de Riesgo Alto';
$string['threshold_high_desc'] = 'Los plugins con puntuaciones iguales o superiores a este valor se clasifican como riesgo Alto.';
$string['threshold_medium'] = 'Umbral de Riesgo Medio';
$string['threshold_medium_desc'] = 'Los plugins con puntuaciones iguales o superiores a este valor se clasifican como riesgo Medio.';
$string['top_risky_plugins'] = 'Top 5 Plugins Más Riesgosos';
$string['top_risky_roles'] = 'Top 5 Roles Más Riesgosos';
$string['total_score'] = 'Puntuación Total';
$string['verified_encrypted'] = 'Contenido verificado como cifrado';
$string['verified_plaintext'] = 'Contenido detectado como texto plano';
$string['webhook_token'] = 'Token del Webhook';
$string['webhook_token_desc'] = 'Token Bearer para autenticar las peticiones del webhook.';
$string['webhook_url'] = 'URL del Webhook';
$string['webhook_url_desc'] = 'URL para enviar los resultados del escaneo (POST HTTP con cuerpo JSON).';
$string['whitelist'] = 'Lista Blanca';
$string['whitelist_add'] = 'Agregar a Lista Blanca';
$string['whitelist_added'] = 'Campo agregado a la lista blanca exitosamente.';
$string['whitelist_component'] = 'Componente';
$string['whitelist_desc'] = 'Los campos marcados como seguros serán excluidos de futuros escaneos.';
$string['whitelist_empty'] = 'No hay campos en la lista blanca.';
$string['whitelist_field'] = 'Campo';
$string['whitelist_remove'] = 'Quitar de Lista Blanca';
$string['whitelist_removed'] = 'Campo eliminado de la lista blanca.';
$string['whitelist_table'] = 'Tabla';
$string['whitelist_this_field'] = 'Añadir este campo a la lista blanca';
