<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * French language strings for MRCA.
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
$string['dashboard_title'] = 'Tableau de bord MRCA';
$string['dashboard_desc'] = 'Analyse complète des risques pour votre installation Moodle.';
$string['scan_now'] = 'Lancer l’analyse';
$string['scan_completed'] = 'Analyse de risque et de conformité terminée avec succès.';
$string['last_scan'] = 'Dernière analyse';
$string['no_scans_yet'] = 'Aucune analyse n’a encore été effectuée. Cliquez sur "Lancer l’analyse" pour démarrer la première analyse.';

// Site Risk Index.
$string['site_risk_index'] = 'Indice de risque du site';
$string['site_risk_index_desc'] = 'Score normalisé 0-100 combinant toutes les couches de risque.';
$string['total_score'] = 'Score total';
$string['plugins_scanned'] = 'Plugins analysés';
$string['roles_scanned'] = 'Rôles analysés';
$string['scan_date'] = 'Date de l’analyse';

// Risk levels.
$string['risk_healthy'] = 'Sain';
$string['risk_low'] = 'Faible';
$string['risk_medium'] = 'Moyen';
$string['risk_moderate'] = 'Modéré';
$string['risk_high'] = 'Élevé';
$string['risk_critical'] = 'Critique';
$string['risk_score'] = 'Score de risque';
$string['risk_level'] = 'Niveau de risque';

// Scanner sections.
$string['privacy_scanner'] = 'Couche Confidentialité & Données';
$string['privacy_scanner_desc'] = 'Détection des PII, conformité à la Privacy API et analyse du chiffrement.';
$string['dependency_scanner'] = 'Couche Dépendances & Compatibilité';
$string['dependency_scanner_desc'] = 'Exigences des plugins, compatibilité avec le core et détection des API obsolètes.';
$string['capability_scanner'] = 'Couche Capacités & Permissions';
$string['capability_scanner_desc'] = 'Analyse des capacités des rôles et détection des risques d’escalade de privilèges.';
$string['correlation_engine'] = 'Moteur de corrélation';
$string['correlation_engine_desc'] = 'Détection des risques systémiques en corrélant les résultats entre toutes les couches.';

// Plugin risk details.
$string['plugin'] = 'Plugin';
$string['privacy_api'] = 'Privacy API';
$string['privacy_api_yes'] = '✓ Implémentée';
$string['privacy_api_no'] = '✗ Absente';
$string['details'] = 'Détails';

// Findings.
$string['reason_keyword_match'] = 'Le nom du champ correspond à un mot-clé PII';
$string['verified_encrypted'] = 'Contenu vérifié comme chiffré';
$string['verified_plaintext'] = 'Contenu détecté en texte brut';

// Role heatmap.
$string['role_heatmap'] = 'Carte thermique des risques des rôles';
$string['role_heatmap_desc'] = 'Vue d’ensemble visuelle de l’exposition des permissions pour tous les rôles du système.';
$string['role'] = 'Rôle';
$string['critical_caps'] = 'Capacités critiques';
$string['role_risk_score'] = 'Score de risque du rôle';

// Alerts.
$string['alerts'] = 'Alertes de corrélation';
$string['alerts_desc'] = 'Risques systémiques détectés par recoupement des résultats entre les couches.';
$string['no_alerts'] = 'Aucune alerte de corrélation. Le système semble sain.';
$string['alert_privacy_capability_correlation'] = 'CRITIQUE : Le plugin "{$a}" présente un risque élevé en matière de confidentialité, n’implémente pas la Privacy API ET définit des capacités. Exposition potentielle de données via l’héritage des permissions.';
$string['alert_high_risk_unstable_deps'] = 'ÉLEVÉ : Le plugin "{$a}" présente un risque total élevé ET des dépendances instables. Mise à jour ou remplacement recommandé.';
$string['alert_systemic_risk'] = 'CRITIQUE : Le plugin "{$a->plugin}" (risque élevé) combiné avec le rôle ID {$a->roleid} (forte exposition des permissions) crée une probabilité de défaillance systémique.';

// Whitelist.
$string['whitelist'] = 'Liste blanche';
$string['whitelist_desc'] = 'Les champs marqués comme sûrs seront exclus des analyses futures.';
$string['whitelist_add'] = 'Ajouter à la liste blanche';
$string['whitelist_remove'] = 'Retirer de la liste blanche';
$string['whitelist_empty'] = 'Aucun champ dans la liste blanche.';
$string['whitelist_component'] = 'Composant';
$string['whitelist_table'] = 'Table';
$string['whitelist_field'] = 'Champ';

// Settings.
$string['settings'] = 'Paramètres MRCA';
$string['autoscan_new_plugins'] = 'Analyser automatiquement les nouveaux plugins';
$string['autoscan_new_plugins_desc'] = 'Exécute automatiquement une analyse des risques lorsqu’un nouveau plugin est installé ou activé.';
$string['scan_core_plugins'] = 'Analyser les plugins core de Moodle';
$string['scan_core_plugins_desc'] = 'Inclut les plugins standards de Moodle (maintenus par Moodle HQ) dans les analyses. Désactivé par défaut car les plugins core ne sont pas tiers et sont mis à jour avec Moodle. À activer uniquement pour un audit complet.';

// Risk thresholds.
$string['risk_thresholds_heading'] = 'Seuils de risque';
$string['risk_thresholds_heading_desc'] = 'Configurer les seuils de score pour la classification des niveaux de risque.';
$string['threshold_high'] = 'Seuil de risque élevé';
$string['threshold_high_desc'] = 'Les plugins ayant un score égal ou supérieur à cette valeur sont classés comme à risque élevé.';
$string['threshold_medium'] = 'Seuil de risque moyen';
$string['threshold_medium_desc'] = 'Les plugins ayant un score égal ou supérieur à cette valeur sont classés comme à risque moyen.';

// Integration settings.
$string['integration_heading'] = 'Intégration externe';
$string['integration_heading_desc'] = 'Configurer la manière dont MRCA envoie les résultats aux systèmes externes.';
$string['integration_method'] = 'Méthode d’intégration';
$string['integration_method_desc'] = 'Choisissez comment envoyer les résultats d’analyse vers l’extérieur.';
$string['integration_method_disabled'] = 'Désactivé';
$string['integration_method_webhook'] = 'Webhook générique';
$string['integration_method_mih'] = 'Moodle Integration Hub';
$string['recommended'] = 'Recommandé';
$string['mih_missing_note'] = 'Moodle Integration Hub (local_integrationhub) n’est pas installé. <a href="https://github.com/x-name15/moodle-integration-hub/releases/tag/1.0.0" target="_blank">Téléchargez-le depuis GitHub</a> pour des capacités d’intégration avancées (recommandé).';
$string['mih_service_slug'] = 'MIH Service Slug';
$string['mih_service_slug_desc'] = 'Slug du service configuré dans l’Integration Hub pour les données MRCA.';
$string['webhook_url'] = 'URL du webhook';
$string['webhook_url_desc'] = 'URL vers laquelle envoyer les résultats d’analyse (HTTP POST avec corps JSON).';
$string['webhook_token'] = 'Jeton webhook';
$string['webhook_token_desc'] = 'Jeton Bearer pour authentifier les requêtes webhook.';

// Reports.
$string['report_title'] = 'MRCA — Rapport de risque et conformité';
$string['download_pdf'] = 'Télécharger le PDF';
$string['download_csv'] = 'Télécharger le CSV';
$string['download_json'] = 'Télécharger le JSON';
$string['send_report'] = 'Envoyer le rapport';

// Events.
$string['event_high_risk_detected'] = 'Risque élevé détecté';
$string['event_high_risk_detected_desc'] = 'L’analyse de l’utilisateur {$a->userid} a détecté un risque élevé sur le plugin "{$a->plugin}" avec un score de {$a->score}.';

// Risk distribution chart.
$string['risk_distribution'] = 'Répartition des risques';

// Capabilities.
$string['mrca:view'] = 'Voir le tableau de bord MRCA';
$string['mrca:configure'] = 'Configurer les paramètres MRCA';
$string['mrca:manage_scans'] = 'Gérer les analyses MRCA';

// Score breakdown.
$string['privacy_score'] = 'Score de confidentialité';
$string['dependency_score'] = 'Score de dépendances';
$string['capability_score'] = 'Score de capacités';

// JS/AJAX.
$string['whitelist_added'] = 'Champ ajouté à la liste blanche avec succès.';
$string['whitelist_removed'] = 'Champ retiré de la liste blanche.';
$string['report_sent'] = 'Rapport envoyé avec succès.';
$string['report_send_failed'] = 'Échec de l’envoi du rapport.';
$string['confirm_scan'] = 'Cette action exécutera une analyse complète des risques et de conformité. Continuer ?';

// Dashboard widgets.
$string['risk_trend'] = 'Tendance du risque';
$string['top_risky_plugins'] = 'Top 5 des plugins à risque';
$string['top_risky_roles'] = 'Top 5 des rôles à risque';
$string['dependency_audit'] = 'Audit des dépendances';
$string['dep_issues'] = 'Problèmes';
$string['dep_core_mismatch'] = 'Incompatibilité de version du core — le plugin nécessite une version plus récente de Moodle.';
$string['dep_missing'] = 'Dépendance manquante : {$a}';
$string['dep_outdated'] = 'Version du plugin obsolète (non mise à jour depuis plus de 2 ans).';
$string['dep_deprecated_apis'] = '{$a} appel(s) API obsolète(s) détecté(s).';
$string['plugin_risk_details'] = 'Détails du risque du plugin';

// Structural scanner findings.
$string['structural_no_directory'] = 'Répertoire du plugin introuvable.';
$string['structural_no_version'] = 'version.php manquant — le plugin ne peut pas être validé.';
$string['structural_no_lang'] = 'Aucun répertoire de langue trouvé.';
$string['structural_no_readme'] = 'Aucun fichier README trouvé.';
$string['structural_no_tests'] = 'Aucun répertoire tests — le plugin ne dispose pas de tests unitaires.';
$string['structural_no_maturity'] = 'Déclaration de maturité absente dans version.php.';
$string['structural_legacy_cron'] = 'Utilise $plugin->cron obsolète. Doit utiliser la Task API.';

// Correlation alerts.
$string['alert_outdated_pii'] = 'ÉLEVÉ : Le plugin "{$a}" est obsolète ET traite des données PII. Des vulnérabilités non corrigées peuvent affecter des données personnelles.';
$string['alert_structural_privacy'] = 'ÉLEVÉ : Le plugin "{$a}" présente des problèmes structurels ET des lacunes de confidentialité sans Privacy API. Probablement non conforme.';
$string['alert_multi_role_escalation'] = 'CRITIQUE : {$a} rôles non administrateurs disposent de capacités critiques excessives. Mauvaise configuration du modèle de permissions.';
$string['alert_deprecated_exposure'] = 'ÉLEVÉ : Le plugin "{$a}" utilise des fonctions obsolètes ET expose des données PII non protégées. Plugin non maintenu traitant des données sensibles.';

// Report dispatch settings.
$string['report_dispatch_heading'] = 'Options d’envoi des rapports';
$string['report_dispatch_heading_desc'] = 'Contrôler quand et quelles données sont envoyées aux intégrations externes après chaque analyse.';
$string['report_trigger'] = 'Déclencheur du rapport';
$string['report_trigger_desc'] = 'Moment d’envoi des rapports vers l’intégration externe.';
$string['report_trigger_always'] = 'Toujours (après chaque analyse)';
$string['report_trigger_critical'] = 'Uniquement en cas d’alertes critiques/élevées';
$string['report_payload'] = 'Contenu du rapport';
$string['report_payload_desc'] = 'Quantité de données à inclure dans les rapports envoyés.';
$string['report_payload_full'] = 'Rapport complet (tous les plugins + détails)';
$string['report_payload_summary'] = 'Résumé uniquement (totaux + alertes)';

// Whitelist UX.
$string['detected_pii_fields'] = 'Champs PII détectés';
$string['whitelist_this_field'] = 'Ajouter ce champ à la liste blanche';
$string['no_pii_detected'] = 'Aucun champ PII détecté.';

// Privacy API.
$string['privacy:metadata:whitelist'] = 'Enregistrements des champs placés en liste blanche par les administrateurs lors des analyses de risque.';
$string['privacy:metadata:whitelist:userid'] = 'ID de l’utilisateur ayant placé le champ en liste blanche.';
$string['privacy:metadata:whitelist:component'] = 'Composant du plugin auquel appartient le champ.';
$string['privacy:metadata:whitelist:table_name'] = 'Table de base de données contenant le champ.';
$string['privacy:metadata:whitelist:field_name'] = 'Nom du champ de base de données placé en liste blanche.';
$string['privacy:metadata:whitelist:timecreated'] = 'Date et heure auxquelles le champ a été placé en liste blanche.';