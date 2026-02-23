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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Italian language strings for MRCA.
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
$string['dashboard_title'] = 'Dashboard MRCA';
$string['dashboard_desc'] = 'Analisi completa del rischio per la tua installazione Moodle.';
$string['scan_now'] = 'Avvia scansione';
$string['scan_completed'] = 'Scansione di rischio e conformità completata con successo.';
$string['last_scan'] = 'Ultima scansione';
$string['no_scans_yet'] = 'Nessuna scansione eseguita. Clicca "Avvia scansione" per iniziare la prima analisi.';

// Site Risk Index.
$string['site_risk_index'] = 'Indice di rischio del sito';
$string['site_risk_index_desc'] = 'Punteggio normalizzato 0-100 che combina tutti i livelli di rischio.';
$string['total_score'] = 'Punteggio totale';
$string['plugins_scanned'] = 'Plugin analizzati';
$string['roles_scanned'] = 'Ruoli analizzati';
$string['scan_date'] = 'Data della scansione';

// Risk levels.
$string['risk_healthy'] = 'Sicuro';
$string['risk_low'] = 'Basso';
$string['risk_medium'] = 'Medio';
$string['risk_moderate'] = 'Moderato';
$string['risk_high'] = 'Alto';
$string['risk_critical'] = 'Critico';
$string['risk_score'] = 'Punteggio di rischio';
$string['risk_level'] = 'Livello di rischio';

// Scanner sections.
$string['privacy_scanner'] = 'Livello Privacy & Dati';
$string['privacy_scanner_desc'] = 'Rilevamento PII, conformità alla Privacy API e analisi della cifratura.';
$string['dependency_scanner'] = 'Livello Dipendenze & Compatibilità';
$string['dependency_scanner_desc'] = 'Requisiti del plugin, compatibilità con il core e rilevamento API deprecate.';
$string['capability_scanner'] = 'Livello Capacità & Permessi';
$string['capability_scanner_desc'] = 'Analisi delle capacità dei ruoli e rilevamento del rischio di escalation dei privilegi.';
$string['correlation_engine'] = 'Motore di correlazione';
$string['correlation_engine_desc'] = 'Rilevamento del rischio sistemico correlando i risultati tra tutti i livelli.';

// Plugin risk details.
$string['plugin'] = 'Plugin';
$string['privacy_api'] = 'Privacy API';
$string['privacy_api_yes'] = '✓ Implementata';
$string['privacy_api_no'] = '✗ Assente';
$string['details'] = 'Dettagli';

// Findings.
$string['reason_keyword_match'] = 'Il nome del campo corrisponde a una parola chiave PII';
$string['verified_encrypted'] = 'Contenuto verificato come cifrato';
$string['verified_plaintext'] = 'Contenuto rilevato come testo in chiaro';

// Role heatmap.
$string['role_heatmap'] = 'Mappa di calore del rischio dei ruoli';
$string['role_heatmap_desc'] = 'Panoramica visiva dell’esposizione dei permessi per tutti i ruoli di sistema.';
$string['role'] = 'Ruolo';
$string['critical_caps'] = 'Capacità critiche';
$string['role_risk_score'] = 'Punteggio di rischio del ruolo';

// Alerts.
$string['alerts'] = 'Avvisi di correlazione';
$string['alerts_desc'] = 'Rischi sistemici rilevati tramite correlazione dei risultati tra i livelli.';
$string['no_alerts'] = 'Nessun avviso di correlazione. Il sistema risulta sicuro.';
$string['alert_privacy_capability_correlation'] = 'CRITICO: Il plugin "{$a}" presenta alto rischio privacy, non implementa la Privacy API E definisce capacità. Possibile esposizione di dati tramite ereditarietà dei permessi.';
$string['alert_high_risk_unstable_deps'] = 'ALTO: Il plugin "{$a}" presenta alto rischio totale E dipendenze instabili. Si consiglia aggiornamento o sostituzione.';
$string['alert_systemic_risk'] = 'CRITICO: Il plugin "{$a->plugin}" (alto rischio) combinato con il ruolo ID {$a->roleid} (alta esposizione dei permessi) crea probabilità di fallimento sistemico.';

// Whitelist.
$string['whitelist'] = 'Lista di esclusione';
$string['whitelist_desc'] = 'I campi contrassegnati come sicuri saranno esclusi dalle scansioni future.';
$string['whitelist_add'] = 'Aggiungi alla lista di esclusione';
$string['whitelist_remove'] = 'Rimuovi dalla lista di esclusione';
$string['whitelist_empty'] = 'Nessun campo nella lista di esclusione.';
$string['whitelist_component'] = 'Componente';
$string['whitelist_table'] = 'Tabella';
$string['whitelist_field'] = 'Campo';

// Settings.
$string['settings'] = 'Impostazioni MRCA';
$string['autoscan_new_plugins'] = 'Scansione automatica dei nuovi plugin';
$string['autoscan_new_plugins_desc'] = 'Esegue automaticamente una scansione di rischio quando un nuovo plugin viene installato o attivato.';
$string['scan_core_plugins'] = 'Scansiona plugin core di Moodle';
$string['scan_core_plugins_desc'] = 'Include i plugin standard di Moodle (mantenuti da Moodle HQ) nelle scansioni. Disabilitato per impostazione predefinita poiché i plugin core non sono di terze parti e vengono aggiornati con Moodle. Abilitare solo per audit completo.';

// Risk thresholds.
$string['risk_thresholds_heading'] = 'Soglie di rischio';
$string['risk_thresholds_heading_desc'] = 'Configura le soglie di punteggio per la classificazione del livello di rischio.';
$string['threshold_high'] = 'Soglia rischio alto';
$string['threshold_high_desc'] = 'I plugin con punteggio uguale o superiore a questo valore sono classificati come ad alto rischio.';
$string['threshold_medium'] = 'Soglia rischio medio';
$string['threshold_medium_desc'] = 'I plugin con punteggio uguale o superiore a questo valore sono classificati come a rischio medio.';

// Integration settings.
$string['integration_heading'] = 'Integrazione esterna';
$string['integration_heading_desc'] = 'Configura come MRCA invia i risultati ai sistemi esterni.';
$string['integration_method'] = 'Metodo di integrazione';
$string['integration_method_desc'] = 'Scegli come inviare esternamente i risultati della scansione.';
$string['integration_method_disabled'] = 'Disabilitato';
$string['integration_method_webhook'] = 'Webhook generico';
$string['integration_method_mih'] = 'Moodle Integration Hub';
$string['recommended'] = 'Raccomandato';
$string['mih_missing_note'] = 'Moodle Integration Hub (local_integrationhub) non è installato. <a href="https://github.com/x-name15/moodle-integration-hub/releases/tag/1.0.0" target="_blank">Scaricalo da GitHub</a> per funzionalità di integrazione avanzate (raccomandato).';
$string['mih_service_slug'] = 'MIH Service Slug';
$string['mih_service_slug_desc'] = 'Slug del servizio configurato nell’Integration Hub per i dati MRCA.';
$string['webhook_url'] = 'URL Webhook';
$string['webhook_url_desc'] = 'URL a cui inviare i risultati della scansione (HTTP POST con corpo JSON).';
$string['webhook_token'] = 'Token Webhook';
$string['webhook_token_desc'] = 'Token Bearer per autenticare le richieste webhook.';

// Reports.
$string['report_title'] = 'MRCA — Report di rischio e conformità';
$string['download_pdf'] = 'Scarica PDF';
$string['download_csv'] = 'Scarica CSV';
$string['download_json'] = 'Scarica JSON';
$string['send_report'] = 'Invia report';

// Events.
$string['event_high_risk_detected'] = 'Alto rischio rilevato';
$string['event_high_risk_detected_desc'] = 'La scansione dell’utente {$a->userid} ha rilevato alto rischio nel plugin "{$a->plugin}" con punteggio {$a->score}.';

// Risk distribution chart.
$string['risk_distribution'] = 'Distribuzione del rischio';

// Capabilities.
$string['mrca:view'] = 'Visualizzare la Dashboard MRCA';
$string['mrca:configure'] = 'Configurare le impostazioni MRCA';
$string['mrca:manage_scans'] = 'Gestire le scansioni MRCA';

// Score breakdown.
$string['privacy_score'] = 'Punteggio privacy';
$string['dependency_score'] = 'Punteggio dipendenze';
$string['capability_score'] = 'Punteggio capacità';

// JS/AJAX.
$string['whitelist_added'] = 'Campo aggiunto alla lista di esclusione con successo.';
$string['whitelist_removed'] = 'Campo rimosso dalla lista di esclusione.';
$string['report_sent'] = 'Report inviato con successo.';
$string['report_send_failed'] = 'Invio del report non riuscito.';
$string['confirm_scan'] = 'Verrà eseguita una scansione completa di rischio e conformità. Continuare?';

// Phase 5: Dashboard widgets.
$string['risk_trend'] = 'Trend del rischio';
$string['top_risky_plugins'] = 'Top 5 plugin a rischio';
$string['top_risky_roles'] = 'Top 5 ruoli a rischio';
$string['dependency_audit'] = 'Audit delle dipendenze';
$string['dep_issues'] = 'Problemi';
$string['dep_core_mismatch'] = 'Incompatibilità versione core — il plugin richiede una versione Moodle più recente.';
$string['dep_missing'] = 'Dipendenza mancante: {$a}';
$string['dep_outdated'] = 'Versione del plugin obsoleta (non aggiornata da oltre 2 anni).';
$string['dep_deprecated_apis'] = 'Rilevate {$a} chiamate API deprecate.';
$string['plugin_risk_details'] = 'Dettagli rischio plugin';

// Structural scanner findings.
$string['structural_no_directory'] = 'Directory del plugin non trovata.';
$string['structural_no_version'] = 'version.php mancante — il plugin non può essere validato.';
$string['structural_no_lang'] = 'Directory lingua non trovata.';
$string['structural_no_readme'] = 'File README non trovato.';
$string['structural_no_tests'] = 'Directory tests assente — il plugin non dispone di test unitari.';
$string['structural_no_maturity'] = 'Dichiarazione di maturità mancante in version.php.';
$string['structural_legacy_cron'] = 'Utilizza $plugin->cron deprecato. È necessario usare la Task API.';

// New correlation alerts.
$string['alert_outdated_pii'] = 'ALTO: Il plugin "{$a}" è obsoleto E gestisce dati PII. Vulnerabilità non corrette potrebbero compromettere dati personali.';
$string['alert_structural_privacy'] = 'ALTO: Il plugin "{$a}" presenta problemi strutturali E lacune privacy senza Privacy API. Probabile non conformità.';
$string['alert_multi_role_escalation'] = 'CRITICO: {$a} ruoli non amministratori hanno capacità critiche eccessive. Configurazione errata del modello di permessi.';
$string['alert_deprecated_exposure'] = 'ALTO: Il plugin "{$a}" utilizza funzioni deprecate E presenta esposizione PII non protetta. Plugin non mantenuto che gestisce dati sensibili.';

// Report dispatch settings.
$string['report_dispatch_heading'] = 'Opzioni di invio report';
$string['report_dispatch_heading_desc'] = 'Controlla quando e quali dati vengono inviati alle integrazioni esterne dopo ogni scansione.';
$string['report_trigger'] = 'Attivazione report';
$string['report_trigger_desc'] = 'Quando inviare i report all’integrazione esterna.';
$string['report_trigger_always'] = 'Sempre (dopo ogni scansione)';
$string['report_trigger_critical'] = 'Solo in presenza di avvisi critici/alto rischio';
$string['report_payload'] = 'Contenuto report';
$string['report_payload_desc'] = 'Quantità di dati da includere nei report inviati.';
$string['report_payload_full'] = 'Report completo (tutti i plugin + dettagli)';
$string['report_payload_summary'] = 'Solo riepilogo (totali + avvisi)';

// Whitelist UX.
$string['detected_pii_fields'] = 'Campi PII rilevati';
$string['whitelist_this_field'] = 'Aggiungi questo campo alla lista di esclusione';
$string['no_pii_detected'] = 'Nessun campo PII rilevato.';

// Privacy API.
$string['privacy:metadata:whitelist'] = 'Registrazioni dei campi inseriti nella lista di esclusione dagli amministratori durante le scansioni.';
$string['privacy:metadata:whitelist:userid'] = 'ID dell’utente che ha inserito il campo nella lista di esclusione.';
$string['privacy:metadata:whitelist:component'] = 'Componente del plugin a cui appartiene il campo.';
$string['privacy:metadata:whitelist:table_name'] = 'Tabella del database contenente il campo.';
$string['privacy:metadata:whitelist:field_name'] = 'Nome del campo del database inserito nella lista di esclusione.';
$string['privacy:metadata:whitelist:timecreated'] = 'Data e ora in cui il campo è stato inserito nella lista di esclusione.';
