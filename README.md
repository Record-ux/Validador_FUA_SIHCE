# Sistema de Validación y Gestión de FUA Electrónico (SIS)

Este sistema permite la gestión de formatos únicos de atención (FUA), importación masiva desde Excel, validación de reglas de negocio y gestión de usuarios con roles y permisos. Desarrollado con Laravel 12 y diseñado para entornos de salud.

## 🚀 Tecnologías Utilizadas

* **Framework:** Laravel (PHP)
* **Autenticación:** Laravel Breeze (Blade + Tailwind CSS)
* **Gestión de Roles:** `spatie/laravel-permission`
* **Importación Excel:** `maatwebsite/excel`
* **Base de Datos:** MySQL / MariaDB

## 📋 Requisitos Previos

Asegúrate de tener instalado en tu entorno local:
* PHP >= 8.1
* Composer
* Node.js & NPM
* MySQL

## 🛠️ Manual de Instalación

Sigue estos pasos para levantar el proyecto en tu máquina local:

### 1. Clonar el repositorio
```bash
git clone [https://github.com/TU_USUARIO/TU_PROYECTO.git](https://github.com/TU_USUARIO/TU_PROYECTO.git)
cd TU_PROYECTO
```

### 2. Crear la Base de Datos
nombre de bd: validador_fua_sihce
Cotejamiento: utf8mb4_spanish2_ci

### 3. Crear la nueva key
cp .env.example .env
php artisan key:generate

### 4. Instalar las librerias
composer install
npm install

APP_LOCALE=es

### 5. Publicar las migraciones y seed
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate:fresh --seed

Credenciales Genericas
carlosgutierrezh0@gmail.com
12345678


### 6. Uso
## Controladores
app/http/Controllers/Modulo_Fua     <- Aqui crear los controladores

## Modelos
app/Models     <- Aqui crear los modelos

## Vistas
views/modulos/modulo_fua     <- Aqui crear las vistas

## Rutas
// Rutas para el Módulo FUA
    Route::prefix('fua')->name('fua.')->group(function () {
});

