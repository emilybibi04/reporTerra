# ReporTerra

Plataforma web para **reportar incidentes ambientales** (contaminación,
incendios, etc.) con un frontend simple (HTML/CSS/JS con Vite para desarrollo) y **backend PHP**.

------------------------------------------------------------------------

## Herramientas necesarias

-   **PHP 8.1+** (probado con 8.2)
-   **Composer** (para instalar Kreait)
-   **Node 18+ y npm**
-   **Firebase Realtime Database**

------------------------------------------------------------------------

## Estructura del proyecto

    reporTerra/
    ├─ api.php
    ├─ metodos.php
    ├─ vendor/        
    ├─ secrets/
    │  └─ firebase-key.json
    ├─ index.html
    ├─ formulario.html
    ├─ tablaincidentes.html
    ├─ src/
    │  ├─ main.js
    │  └─ style.css
    ├─ public/
    ├─ vite.config.js
    └─ README.md

------------------------------------------------------------------------

## Instalación

1)  **Dependencias PHP**

``` bash
composer require kreait/firebase-php
```

2)  **Dependencias frontend**

``` bash
npm install
```

------------------------------------------------------------------------

## Ejecutar

### A) Backend (PHP) -- puerto 9000

En la carpeta raíz:

``` bash
php -S localhost:9000 -t .
```

### B) Frontend

``` bash
npm run dev
```

------------------------------------------------------------------------

## Probar la demo (paso a paso)

### 1) Desde la UI

-   Abre `http://localhost:5173` (o tu server estático).
-   **Reportar incidente** → llena **tipo, región, ubicación** y
    detalles → **Enviar**.
-   Ve a **"Incidentes Reportados"**:
    -   Botón **Ver más** abre la ficha en modo lectura.
    -   Si el estado es **Pendiente**, podrás **Editar** desde la ficha.
    -   Botón de estado:
        -   **Pendiente → Atender** (cambia a **En proceso**)
        -   **En proceso → Finalizar** (cambia a **Resuelta**)
        -   **Resuelta**: solo queda **Ver más**.

------------------------------------------------------------------------

## Flujo general

1.  La UI (HTML/JS) envía `fetch` a **`api.php`**.
2.  `api.php` enruta y llama métodos de **`metodos.php`**.
3.  `metodos.php` guarda y lee en **Firebase**.
4.  `api.php` responde **JSON**.

------------------------------------------------------------------------

## Créditos

Este proyecto se usa con fines educativos.\
Autores: Emily Valarezo Plaza, Joshua Zaruma Game y Raúl Laurido Aguirre.