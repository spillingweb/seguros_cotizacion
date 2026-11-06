# Seguro de Viaje — Prueba Técnica

Sistema de cotización y venta de seguro de viaje. Laravel 12 + Vue 3 (TypeScript).

## Requisitos

| Herramienta | Versión mínima |
|-------------|----------------|
| PHP | 8.4 |
| Composer | 2.x |
| Node.js | 20+ |
| npm | 10+ |
| MySQL | 8.0 (o Docker) |

---

## Instalación local (sin Docker)

```bash
# 1. Clonar el repositorio
git clone <url> seguro_viaje && cd seguro_viaje

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
# Editar .env: ajustar DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# y cambiar APP_URL=http://localhost:8000

# 4. Generar clave de la aplicación
php artisan key:generate

# 5. Ejecutar migraciones
php artisan migrate

# 6. (Opcional) Cargar datos de prueba en español
php artisan db:seed

# 7. Compilar assets frontend
npm run build
# O en modo desarrollo con HMR:
# npm run dev

# 8. Levantar el servidor
php artisan serve
```

La aplicación estará disponible en **http://localhost:8000**

---

## Instalación con Docker

```bash
# 1. Clonar e instalar dependencias localmente (necesario para el build)
git clone <url> seguro_viaje && cd seguro_viaje
composer install
npm install && npm run build

# 2. Levantar contenedores
docker compose up -d --build

# 3. Configurar
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

La aplicación estará disponible en **http://localhost:8080**

> El `.env.example` ya está preconfigurado para Docker (host `mysql`, puerto 3306).

---

## Ejecutar pruebas

```bash
# SQLite en memoria (configuración por defecto)
./vendor/bin/pest

# Con reporte de cobertura
./vendor/bin/pest --coverage
```

---

## Arquitectura

### Backend — Actions + Services

La lógica de negocio vive fuera de los controladores:

```
app/
├── Actions/
│   ├── CalculateQuoteAction.php   ← días × $3 + recargo por región
│   └── CreateInsuranceAction.php  ← persiste cotización en base de datos
├── DTOs/
│   └── QuoteResult.php            ← objeto de valor inmutable (readonly)
├── Enums/
│   └── InsuranceStatus.php        ← quoted | contracted + label en español
├── Exceptions/
│   └── CountryServiceException.php
├── Http/
│   ├── Controllers/               ← delegan a Actions/Services, sin lógica propia
│   ├── Requests/
│   │   └── StoreQuoteRequest.php  ← validación con mensajes en español
│   └── Resources/
│       └── InsuranceResource.php  ← contrato de respuesta JSON consistente
├── Models/
│   └── Insurance.php
└── Services/
    └── CountryService.php         ← HTTP externo + caché 24 h + manejo de errores
```

**Actions** (un solo propósito, fáciles de testear unitariamente) vs **Services** (solo para integraciones externas con estado — HTTP, caché).

### Integración con REST Countries

`CountryService` consulta `countries.dev/countries` y cachea la respuesta **24 horas** (`Cache::remember`). Maneja: timeout 10 s, errores 5xx, respuestas no-array, y cualquier excepción de red — todo envuelto en `CountryServiceException`. Además normaliza el contrato para el frontend e incluye el `surcharge_percentage` por región. El frontend cachea también 24 h con Tanstack Query.

### Lógica de precios

```
Total = días × $3 + (días × $3 × recargo_región)
```

| Región | Recargo |
|--------|---------|
| South America | 0% |
| North America | 15% |
| Europe | 20% |
| Asia | 25% |
| Africa | 20% |
| Oceania | 25% |

### Frontend — Vue 3 + TypeScript

```
resources/js/
├── stores/insurance.ts          ← Pinia: cotización activa en memoria
├── composables/
│   ├── useCountries.ts          ← Tanstack Query: GET /api/countries (caché 24h)
│   └── useInsurances.ts         ← Tanstack Query: lista + mutations crear/contratar
├── components/
│   ├── layout/AppHeader.vue     ← nav con aria-current
│   ├── ui/                      ← AppButton, AppInput, AppSelect, StatusBadge
│   ├── quote/
│   │   ├── QuoteForm.vue        ← Laravel Precognition: validación en tiempo real
│   │   └── QuoteSummary.vue     ← resumen + contratar + descargar PDF
│   └── insurance/
│       └── InsuranceTable.vue   ← tabla desktop + tarjetas mobile + búsqueda + paginación
```

**Laravel Precognition** (`laravel-precognition-vue`): el formulario valida contra el servidor mientras el usuario escribe, sin necesidad de submit. El middleware `HandlePrecognitiveRequests` en la ruta `POST /api/quotes` intercepta los HEAD preflight requests.

**Tanstack Query**: gestiona caché, estados de carga, deduplicación e invalidación automática al crear/contratar. No es necesario manejar estado de loading manualmente.

**Pinia**: almacena la cotización activa en memoria para que el resumen persista sin recargar datos innecesariamente.

---

## Decisiones técnicas

| Decisión | Razón |
|----------|-------|
| Actions pattern | Un propósito por clase, testeable unitariamente sin HTTP |
| DTOs `readonly` | Evitan mutación accidental de resultados de negocio |
| Enum `InsuranceStatus` | Type-safety, casting Eloquent automático, labels en español |
| Tanstack Query | Caché automático, deduplicación, estados declarativos — evita boilerplate de Axios |
| Pinia (setup store) | Minimalista, tipo-seguro, sin mutaciones explícitas |
| Tailwind v4 CSS-first | Sin `tailwind.config.js`; configuración en CSS con `@theme` |
| Docker multi-servicio | Entorno reproducible; MySQL 8 + Nginx + PHP-FPM |
| GitHub Actions CI | Valida tests PEST + TypeScript + Vite build en cada push |

---

## Mejoras futuras

- **Pasarela de pagos**: Stripe antes de confirmar la contratación.
- **Autenticación**: Sanctum para separar portal de cliente y panel admin.
- **Email notifications**: Mailables + Queue al cotizar y contratar.
- **Logs estructurados**: canal JSON (Monolog) para Datadog/Elastic.
- **Tests de integración**: mockear HTTP externo en `CountryService`.
- **Caché Redis**: reemplazar `file` en producción para escalado horizontal.
- **Validación de edad mínima**: asegurado ≥ 18 años.
- **Multi-idioma**: Vue I18n para otros mercados.
