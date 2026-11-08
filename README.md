# Seguro de Viaje

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

1. Clonar el repositorio
```bash
git clone https://github.com/spillingweb/seguros_cotizacion.git
```

2. Entrar al directorio del proyecto
```bash
cd seguros_cotizacion
```

3. Instalar dependencias de PHP
```bash
composer install
```

4. Instalar dependencias de Node.js
```bash
npm install
```

5. Configurar el archivo de entorno
```bash
cp .env.example .env
```

> **Nota:** Editar el archivo `.env` para ajustar `DB_HOST=127.0.0.1`, tus credenciales de base de datos y `APP_URL=http://localhost:8000`.

6. Generar clave de la aplicación
```bash
php artisan key:generate
```

5. Ejecutar migraciones y seeders
```bash
php artisan migrate --seed
```

6. Compilar assets frontend
```bash
npm run build
```

(O en modo desarrollo con HMR:)
```bash
npm run dev
```

7. Levantar el servidor
```bash
php artisan serve
```
*(Si tu entorno local de Windows no te permite ejecutar `php artisan serve`, prueba con `php -S 127.0.0.1:8000 -t public`).*

La aplicación estará disponible en **http://localhost:8000**

---

## Instalación con Docker

> **Requisito previo:** Asegúrate de tener **Docker Desktop** (o el servicio/daemon de Docker) instalado y **en ejecución** en tu sistema antes de continuar.

1. Clonar el proyecto de GitHub
```bash
git clone https://github.com/spillingweb/ seguros_cotizacion
```

2. Navegar al directorio del proyecto
```bash
cd seguros_cotizacion
```

3. Instalar dependencias de PHP
```bash
composer install
```

4. Instalar dependencias de Node.js
```bash
npm install
```

5. Compilar assets
```bash
npm run build
```

6. Levantar contenedores
```bash
docker compose up -d --build
```

7. Configurar el archivo .env dentro del contenedor (ya está configurado para Docker)
```bash
docker compose exec app cp .env.example .env
```

8. Generar clave de la aplicación en Docker
```bash
docker compose exec app php artisan key:generate
```

9. Ejecutar migraciones y seeders en Docker
```bash
docker compose exec app php artisan migrate --seed
```

10. (Opcional) Correr el servidor Vite localmente para ver actualizaciones en vivo
```bash
npm run dev
```

La aplicación estará disponible en **http://localhost:8080**

> El `.env.example` ya está preconfigurado para Docker (host `mysql`, puerto 3306).

---

## Pruebas Automatizadas (PEST)

El proyecto incluye pruebas automatizadas para validar la lógica de negocio y endpoints utilizando **PEST**:

```bash
# Ejecutar la suite de pruebas
php artisan test
```

---

## Arquitectura

### Backend — Actions + Services

La lógica de negocio vive fuera de los controladores:

```
app/
├── Actions/
│   ├── CalculateQuoteAction.php        ← días × $3 + recargo por región
│   └── ContractInsuranceAction.php     ← marca el seguro como contratado y registra fecha
│   └── CreateQuoteAction.php           ← persiste cotización en base de datos
│   └── ExportInsurancePdfAction.php    ← genera y descarga el PDF de la cotización
├── DTOs/
│   └── QuoteResult.php                 ← objeto de valor inmutable (readonly)
├── Enums/
│   └── InsuranceStatus.php             ← quoted | contracted + label en español
│   └── RegionSurcharge.php             ← tabla de porcentajes de recargo por región
├── Http/
│   ├── Controllers/                    ← delegan a Actions/Services, sin lógica propia
│   ├── Requests/
│   │   └── StoreQuoteRequest.php       ← validación con mensajes en español
│   └── Resources/
│       └── InsuranceResource.php       ← contrato de respuesta JSON consistente
├── Models/
│   └── Insurance.php
└── Services/
    └── CountryService.php              ← HTTP externo + caché 24 h + manejo de errores
    └── InsuranceService.php            ← consultas, filtros, paginación y estadísticas
```

**Actions** (un solo propósito, fáciles de testear unitariamente) vs **Services** (lectura de datos, filtros, paginación e integraciones externas con estado — HTTP/caché).

### Integración con REST Countries

`CountryService` consulta `countries.dev/countries` y cachea la respuesta **24 horas** (`Cache::remember`). Maneja: timeout 10 s, errores 5xx, respuestas no-array, y cualquier excepción de red con `RuntimeException`, manteniendo la capa de negocio simple. Además normaliza el contrato para el frontend e incluye el `surcharge_percentage` por región. El frontend cachea también 24 h con Tanstack Query.

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
├── App.vue                    ← raíz de la aplicación Vue
├── app.ts                    ← entrada principal (Vue + plugins)
├── env.d.ts                  ← tipos globales para Vite/Vue
├── router/
│   └── index.ts              ← Vue Router: rutas SPA
├── stores/
│   └── insurance.ts          ← Pinia: cotización activa en memoria
├── composables/
│   ├── useCountries.ts       ← Tanstack Query: GET /api/countries
│   ├── useInsurances.ts      ← Tanstack Query + infinite scroll + mutations
│   └── useQuoteForm.ts       ← Tanstack Form + validación y submit
├── schemas/
│   └── quoteFormSchema.ts    ← Zod: reglas del formulario de cotización
├── lib/
│   ├── api.ts                ← instancia Axios configurada
│   └── utils.ts              ← helpers de fecha y moneda
├── types/
│   └── index.ts              ← tipos compartidos del dominio
├── views/
│   ├── QuoteView.vue         ← página de cotización
│   └── InsurancesView.vue    ← página de gestión de seguros
└── components/
    ├── header/               ← navegación principal
    ├── footer/               ← footer global
    ├── quotes-view/          ← formulario, resumen y cards de cotización
    ├── insurance-view/       ← listado, estadísticas y tabla de seguros
    └── ui/                   ← componentes reutilizables (botones, inputs, selects, dialogs, etc.)
```

**Tanstack Query**: gestiona caché, estados de carga, deduplicación e invalidación automática al crear/contratar. No es necesario manejar estado de loading manualmente.

**Infinite scroll (mobile)**: `useInsurancesInfinite` usa `useInfiniteQuery` para acumular páginas conforme el usuario desplaza. Un elemento sentinel de 1 px al pie de las tarjetas es observado con `useIntersectionObserver` de VueUse; cuando entra al viewport se llama `fetchNextPage()`. En pantallas `sm:` y mayores se mantiene la paginación clásica con botones.

**Tanstack Form + Zod**: `useQuoteForm` centraliza el estado y envío del formulario. `quoteFormSchema` define las reglas de validación (incluyendo validaciones entre campos como fechas). En respuestas `422`, los errores de Laravel se mapean al `form.state.meta` de Tanstack para mostrarse debajo del input correspondiente.

**Pinia**: almacena la cotización activa en memoria para que el resumen persista sin recargar datos innecesariamente.

---

## Decisiones técnicas

| Decisión | Razón |
|----------|-------|
| Actions pattern | Un propósito por clase, testeable unitariamente sin HTTP |
| DTO `QuoteResult` | Evita pasar 4 valores escalares sueltos entre acciones |
| Enum `InsuranceStatus` | Type-safety, casting Eloquent automático, labels en español |
| Tanstack Query | Caché automático, deduplicación, estados declarativos — evita boilerplate de Axios |
| `useInfiniteQuery` (infinite scroll) | Acumula páginas en mobile sin recargar; sentinel + IntersectionObserver evita polling |
| Tanstack Form | Estado y validación del formulario con API declarativa y tipada |
| Zod schema (`quoteFormSchema`) | Reglas de validación centralizadas y reutilizables en frontend |
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
