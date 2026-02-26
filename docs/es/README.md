# MRCA — Documentación Completa

## Tabla de Contenidos

1. [Descripción General](#descripción-general)
2. [Arquitectura](#arquitectura)
3. [Instalación](#instalación)
4. [Configuración](#configuración)
5. [Uso](#uso)
6. [Escáneres](#escáneres)
7. [Puntuación de Riesgo](#puntuación-de-riesgo)
8. [Dashboard](#dashboard)
9. [Reportes e Integración](#reportes-e-integración)
10. [Privacidad y RGPD](#privacidad-y-rgpd)
11. [Referencia CLI](#referencia-cli)
12. [Solución de Problemas](#solución-de-problemas)

---

## Descripción General

**MRCA (Moodle Risk & Compliance Analyzer)** es un plugin local de Moodle que realiza auditorías automatizadas de seguridad, privacidad y cumplimiento de tu instalación de Moodle. Escanea los plugins de terceros instalados en múltiples dimensiones de riesgo y produce un **Índice de Riesgo del Sitio (0–100)** unificado.

Por defecto, MRCA solo escanea **plugins de terceros**. Los módulos estándar de Moodle (mantenidos por Moodle HQ) se excluyen para evitar falsos positivos.

---

## ¿Por qué MRCA?

Moodle es el LMS más adoptado del mundo, utilizado por más de 300 millones de usuarios en más de 240 países. En la **Unión Europea**, donde el **RGPD (Reglamento General de Protección de Datos)** es plenamente aplicable desde mayo de 2018, las instituciones educativas enfrentan obligaciones estrictas respecto al tratamiento de datos personales — incluyendo expedientes de estudiantes, calificaciones, asistencia y comunicaciones.

Sin embargo, Moodle **no proporciona ningún mecanismo nativo** para auditar los plugins instalados en cuanto a:

- **Cumplimiento de privacidad** — ¿Declara el plugin qué datos personales almacena?
- **Riesgos de seguridad** — ¿Usa funciones PHP inseguras o APIs deprecadas?
- **Exposición de permisos** — ¿Se asignan capacidades críticas a roles no administrativos?
- **Salud de dependencias** — ¿Están los plugins obsoletos o son incompatibles?

MRCA fue construido para llenar este vacío. En lugar de depender de auditorías manuales costosas o respuesta reactiva a incidentes, los administradores pueden ejecutar **escaneos proactivos y automatizados de cumplimiento** que producen reportes accionables.

## ¿Para quién es?

| Audiencia | Caso de Uso |
|-----------|-------------|
| **Universidades y escuelas europeas** | Cumplimiento RGPD para protección de datos estudiantiles |
| **Instituciones españolas** | Cumplimiento LOPDGDD (Ley Orgánica de Protección de Datos) |
| **Instituciones francesas** | Cumplimiento regulatorio CNIL |
| **Instituciones del Reino Unido** | UK GDPR (protección de datos post-Brexit) |
| **Departamentos de formación corporativa** | Gestión de riesgos para Moodle empresarial |
| **Proveedores de hosting Moodle** | Garantías de seguridad para clientes |
| **Equipos de cumplimiento TI** | Reportes de auditoría automatizados para reguladores |

## ¿Dónde es más relevante?

MRCA es particularmente valioso en jurisdicciones con regulaciones fuertes de protección de datos:

- 🇪🇺 **Unión Europea / EEE** — RGPD (Reglamento 2016/679)
- 🇪🇸 **España** — LOPDGDD + RGPD
- 🇫🇷 **Francia** — Supervisión CNIL + RGPD
- 🇩🇪 **Alemania** — Bundesdatenschutzgesetz (BDSG) + RGPD
- 🇬🇧 **Reino Unido** — UK GDPR + Data Protection Act 2018
- 🇧🇷 **Brasil** — LGPD (Lei Geral de Proteção de Dados)
- 🇦🇷 **Argentina** — Ley de Protección de Datos Personales

Cualquier institución que use Moodle, procese datos personales y esté sujeta a regulaciones de privacidad puede beneficiarse del escaneo automatizado de MRCA.

### Capacidades Principales

- Detectar campos PII sin cumplimiento de Privacy API
- Señalar plugins obsoletos y uso de APIs deprecadas
- Identificar funciones PHP inseguras y estructura de código deficiente
- Analizar capacidades de roles para riesgos de escalamiento de privilegios
- Correlacionar hallazgos entre capas para detección de riesgo sistémico
- Exportar reportes en PDF, CSV y JSON
- Integrar con SIEM/SOC externos vía webhooks o MIH

---

## Arquitectura

```
┌──────────────────────────────────────────────────┐
│                   Motor MRCA                      │
├──────────┬──────────┬──────────┬─────────────────┤
│ Escáner  │ Escáner  │ Escáner  │   Escáner de    │
│Privacidad│Dependenc.│Estructur.│  Capacidades     │
├──────────┴──────────┴──────────┴─────────────────┤
│          Motor de Riesgo + Modelo de Scoring      │
├──────────────────────────────────────────────────┤
│              Motor de Correlación                 │
├──────────────────────────────────────────────────┤
│  Dashboard │ PDF │ CSV │ JSON │ Webhook │ MIH    │
└──────────────────────────────────────────────────┘
```

### Estructura de Directorios

```
local/mrca/
├── classes/
│   ├── engine/          # risk_engine, scoring_model, correlation_engine
│   ├── scanners/        # privacy, dependency, structural, capability
│   ├── models/          # plugin_risk, role_risk, site_risk
│   ├── reporting/       # dashboard, pdf, csv, json
│   ├── heuristics/      # crypto_analyzer
│   ├── manager/         # whitelist_manager
│   ├── privacy/         # Proveedor de Privacy API
│   ├── service/         # webhook_service
│   ├── task/            # run_scan (programada), scan_adhoc
│   └── util/            # core_plugin_helper
├── cli/                 # Script CLI de escaneo
├── db/                  # Esquema, capacidades, eventos, tareas, desinstalación
├── docs/                # Esta documentación
├── lang/                # Paquetes de idioma EN y ES
├── templates/           # Plantillas Mustache
├── tests/               # Tests PHPUnit
└── amd/                 # JavaScript (gráficos del dashboard)
```

---

## Instalación

### Requisitos

- Moodle 4.1 o posterior
- PHP 8.0+
- Acceso de administrador

### Pasos

1. Copiar la carpeta `mrca` a `local/mrca/` en la raíz de Moodle.
2. Ejecutar la actualización:
   ```bash
   php admin/cli/upgrade.php
   ```
   O visitar **Administración del sitio → Notificaciones** en la interfaz web.
3. Navegar a **Administración del sitio → Servidor → MRCA**.

---

## Configuración

Navegar a **Administración del sitio → Servidor → MRCA → Configuración**.

### Ajustes Generales

| Ajuste | Descripción | Predeterminado |
|--------|-------------|----------------|
| **Escanear automáticamente nuevos plugins** | Ejecuta un escaneo cuando se instala o activa un plugin | Desactivado |
| **Escanear plugins del core de Moodle** | Incluir módulos estándar de Moodle en los escaneos. Desactivar para evitar falsos positivos | Desactivado |

### Umbrales de Riesgo

| Ajuste | Descripción | Predeterminado |
|--------|-------------|----------------|
| **Umbral de riesgo alto** | Puntuación a partir de la cual un plugin se marca como alto riesgo | 60 |
| **Umbral de riesgo medio** | Puntuación a partir de la cual un plugin se marca como riesgo medio | 30 |

### Integración Externa

| Ajuste | Descripción |
|--------|-------------|
| **Método de integración** | Elegir: Desactivado, Webhook o MIH |
| **URL del Webhook** | Endpoint para enviar reportes vía POST |
| **Token del Webhook** | Token Bearer para autenticación |
| **Slug del servicio MIH** | Identificador del servicio en Integration Hub for Moodle™ |
| **Trigger de reportes** | Cuándo enviar: siempre, solo_alto_riesgo, o manual |

---

## Uso

### Dashboard Web

1. Ir a **Administración del sitio → Servidor → MRCA → Dashboard**.
2. Hacer clic en **"Escanear Ahora"** para iniciar un escaneo inmediato.
3. Revisar resultados: índice de riesgo, top plugins, alertas, mapa de calor de roles.
4. Exportar reportes usando los botones PDF/CSV/JSON.

### Escaneo CLI

```bash
php local/mrca/cli/run_scan_cli.php
```

### Escaneo Programado

MRCA registra una tarea programada de Moodle que se ejecuta diariamente a las 2:00 AM. Configurar vía **Administración del sitio → Servidor → Tareas programadas**.

---

## Escáneres

### Escáner de Privacidad

Analiza las tablas de base de datos de cada plugin buscando información personal identificable (PII):

- **Detección por palabras clave PII:** Escanea nombres de columnas buscando términos como `email`, `phone`, `password`, `ip`, etc.
- **Verificación de Privacy API:** Verifica que el plugin implemente `\core_privacy\local\metadata\provider`.
- **Detección de encriptación:** Comprueba si los datos almacenados parecen encriptados (patrones base64/hex).
- **Niveles de severidad:** Crítico (password, token), Alto (email, phone), Medio (ip, city).

### Escáner de Dependencias

Verifica la salud y compatibilidad del plugin:

- **Incompatibilidad de versión core:** El plugin requiere una versión de Moodle diferente a la instalada.
- **Dependencias faltantes:** Plugins requeridos no instalados.
- **Detección de obsolescencia:** Timestamp de versión del plugin mayor a 2 años.
- **Uso de APIs deprecadas:** Busca `get_context_instance`, `add_to_log`, `events_trigger_legacy`, `print_error`, etc.

### Escáner Estructural

Evalúa la calidad del código y estructura del plugin:

- **Funciones deprecadas:** `print_header`, `print_footer`, `get_context_instance`, etc.
- **Funciones PHP inseguras:** `eval`, `exec`, `shell_exec`, `passthru`, `popen`, etc.
- **Estructura del plugin:** Verifica `version.php`, `lang/`, `README.md`, `tests/`, `db/access.php`.
- **Madurez:** Señala plugins no declarados como MATURITY_STABLE.

### Escáner de Capacidades

Analiza permisos de roles para riesgos de seguridad:

- **Capacidades críticas en roles no-admin:** Señala `moodle/site:config`, `moodle/user:delete`, etc. asignadas a roles no administrativos.
- **Anulaciones sospechosas:** Detecta nombres de capacidades que contienen `delete`, `config`, `override`, `trust`.
- **Capacidades de alto riesgo:** Identifica capacidades con bitmasks `RISK_XSS`, `RISK_CONFIG`, `RISK_PERSONAL`, `RISK_MANAGETRUST`.

---

## Puntuación de Riesgo

### Puntuación por Plugin

Cada plugin recibe tres sub-puntuaciones (0–65 cada una):

| Puntuación | Origen | Máximo |
|------------|--------|--------|
| Privacidad | Campos PII, Privacy API, encriptación | 65 |
| Dependencias | Versión, APIs, dependencias | 65 |
| Capacidades | Caps críticas, anulaciones | 65 |
| **Total** | Suma de las tres | **195** |

### Constantes de Puntuación

| Hallazgo | Puntos |
|----------|--------|
| Sin Privacy API | 25 |
| Campo PII crítico (sin encriptar) | 35 |
| Campo PII alto | 25 |
| Campo PII medio | 15 |
| Campo encriptado (reducción) | ×0.2 |
| Incompatibilidad de versión core | 25 |
| Dependencia faltante | 20 (cada una) |
| Plugin obsoleto | 15 |
| API deprecada | 10 (máx 3) |
| Cap crítica en no-admin | 25 (máx 3) |

### Índice de Riesgo del Sitio

El **Índice de Riesgo del Sitio (IRS)** es una puntuación normalizada 0-100:

```
IRS = (puntos_riesgo_totales / puntos_máximos_posibles) × 100
```

| Rango | Clasificación |
|-------|---------------|
| 0–20 | 🟢 Saludable |
| 21–40 | 🔵 Riesgo Bajo |
| 41–60 | 🟡 Moderado |
| 61–80 | 🟠 Riesgo Alto |
| 81–100 | 🔴 Crítico |

### Motor de Correlación

El motor de correlación amplifica el riesgo cuando **múltiples capas** señalan el mismo plugin:

- Si tanto la puntuación de privacidad como la de dependencias superan el umbral (40), se aplica un **multiplicador de 1.5x**.
- Genera alertas para patrones de riesgo sistémico (ej: "el plugin tiene alto riesgo de privacidad Y no tiene Privacy API Y define capacidades").

---

## Dashboard

El dashboard proporciona:

- **Índice de Riesgo del Sitio** con clasificación
- **Distribución de Riesgo** gráfico circular
- **Top 5 Plugins más Riesgosos** ordenados por puntuación total
- **Top 5 Roles más Riesgosos** ordenados por cantidad de capacidades críticas
- **Tendencia de Riesgo** gráfico de línea de los últimos 10 escaneos
- **Auditoría de Dependencias** panel con plugins obsoletos/incompatibles
- **Mapa de Calor de Roles** mostrando exposición de permisos
- **Alertas de Correlación** con niveles de severidad
- **Gestor de Lista Blanca** para exclusiones de campos PII

---

## Reportes e Integración

### Formatos de Exportación

| Formato | Caso de Uso |
|---------|-------------|
| **PDF** | Reporte formateado para gestión/auditores |
| **CSV** | Análisis en hoja de cálculo |
| **JSON** | Integración SIEM, procesamiento automatizado |

### Integración Webhook

Configura un endpoint HTTP para recibir solicitudes POST con resultados de escaneo. Soporta autenticación con token Bearer.

### Integración MIH

Si [Integration Hub for Moodle™](https://github.com/x-name15/moodle-local_integrationhub) está instalado, MRCA puede despachar reportes a través del bus de servicios MIH.

---

## Privacidad y RGPD

MRCA implementa la **Privacy API de Moodle** (`\core_privacy\local\metadata\provider`):

- **Datos almacenados:** Solo el `userid` de administradores que añaden campos a la lista blanca (tabla `local_mrca_whitelist`).
- **Exportación:** Las entradas de lista blanca se exportan vía las herramientas de privacidad de Moodle.
- **Eliminación:** Los tres métodos de eliminación están implementados (todos los usuarios, usuario individual, multi-usuario).
- **Sin PII en datos de escaneo:** Los resultados de escaneo, puntuaciones de riesgo y alertas son datos sistémicos no vinculados a usuarios individuales.

---

## Licencia

Licencia MIT. Ver [LICENSE](../../LICENSE).

## Referencia CLI

```
Uso:
    php local/mrca/cli/run_scan_cli.php [--help]

Opciones:
    --help, -h    Mostrar mensaje de ayuda.

Descripción:
    Ejecuta un escaneo completo de riesgo y cumplimiento en todos los
    plugins instalados y roles del sistema. Los resultados se guardan
    en la base de datos y se pueden ver en el dashboard de MRCA.
```

---

## Solución de Problemas

### "Se están señalando plugins del core"

Asegúrate de que **"Escanear plugins del core de Moodle"** esté **desactivado** en configuración. Este es el predeterminado, pero si está activado, los módulos del core se incluirán en los escaneos.

### Alto número de falsos positivos

1. Verifica que el escaneo de plugins del core esté desactivado.
2. Revisa la lista blanca — añade campos legítimos desde el dashboard.
3. Si un plugin de terceros se señala por APIs deprecadas, verifica con la documentación del plugin.

### El escaneo tarda demasiado

Instalaciones grandes con muchos plugins pueden tardar varios minutos. El escaneo ejecuta los 4 escáneres secuencialmente. Usa el CLI para mejor monitoreo:

```bash
php local/mrca/cli/run_scan_cli.php
```

### La integración no envía reportes

1. Verifica que el método de integración esté configurado correctamente.
2. Para webhooks: comprueba la accesibilidad de la URL y la validez del token.
3. Para MIH: asegúrate de que `local_integrationhub` esté instalado y el slug del servicio sea correcto.
