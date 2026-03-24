# Copilot Instructions for Amiri App

## Overview
The Amiri App is a PHP-based web application designed for managing various services. It utilizes a MySQL database and follows a structured architecture that separates concerns across different components and services.

## Architecture
- **Core Components**: The application is divided into several key components, including:
  - **API**: Located in the `api/` directory, handling all backend logic and data processing.
  - **Frontend**: Managed through PHP files in the root directory, serving as the user interface.
  - **Services**: Business logic is encapsulated in the `src/services/` directory, with classes like `ClienteService.php` and `FlujoCajaService.php`.

- **Data Flow**: Data flows from the frontend through the API to the database, with services acting as intermediaries for business logic.

## Developer Workflows
- **Setup**: Follow the instructions in `README.md` for installation and configuration. Key commands include:
  ```bash
  # Health check
  php scripts/health-check.php
  ```

- **Testing**: Ensure to run health checks and monitor logs for any issues. Use the `monitor.php` for real-time monitoring of application health.

- **Debugging**: Utilize the logs located in the `logs/` directory. The `logs/error.log` file is crucial for identifying critical issues.

## Project Conventions
- **File Naming**: Use snake_case for file names and class names in PHP should follow PascalCase.
- **Configuration**: Environment variables are managed in the `.env` file. Ensure to set the required variables as specified in the `README.md`.

## Integration Points
- **Database**: The application connects to a MySQL database. Ensure the database is set up as per the instructions in `README.md`.
- **External Services**: The application may interact with external APIs. Check the `api/` directory for integration points.

## Database Schema (v2.0)
Main tables and their purpose:
- **movimientos**: Registro principal de operaciones financieras (antes: flujo_movimientos)
- **historial_saldos**: Historial de cambios en saldos de cuentas (antes: movimientos_cuenta)
- **inventario_monedas**: Inventario FIFO de divisas (antes: inventario_divisas)
- **deudas_clientes**: Lo que clientes deben por ventas de efectivo (antes: ventas_efectivo_clientes)
- **deudas_empresa**: Lo que la empresa debe a prestamistas (antes: prestamos_prestamistas)
- **pagos_promotores**: Pagos de comisiones a promotores (antes: pagos_comisiones_promotores)
- **categorias_gasto**: Categorías de gastos operativos (antes: tipos_gasto)
- **tipos_cuenta**: Catálogo de tipos de cuenta (antes: cuentas_tipo)

## Communication Patterns
- **Service Communication**: Services communicate through method calls. For example, `ClienteService` may call methods from `FlujoCajaService` to process transactions.

## Key Files and Directories
- **Main Application**: `index.php` serves as the entry point.
- **API Endpoints**: Located in the `api/` directory, each file corresponds to a specific resource.
- **Configuration**: All configuration files are in the `src/config/` directory.
