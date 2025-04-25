# Sistema de Gestión de Comercios Municipal

## Descripción
Este proyecto implementa un sistema completo para la gestión y registro de comercios locales para el Ayuntamiento de Villanueva de la Cañada. La plataforma permite el registro, categorización, y administración de los negocios del municipio, con un sistema robusto de auditoría para garantizar la transparencia y seguridad de los datos.

## Estructura del Sistema

El sistema está compuesto por las siguientes entidades principales:

### Gestión de Usuarios y Acceso
- **Login**: Sistema de autenticación para usuarios del ayuntamiento
- **Usuario**: Información de los funcionarios con acceso al sistema
- **Ayuntamiento**: Datos de la entidad municipal

### Gestión de Comercios
- **Comercios**: Información completa de los negocios locales
- **Dirección**: Localización física de cada establecimiento
- **Horario**: Registro de horarios de apertura y cierre
- **Contactos**: Datos de las personas de contacto de cada negocio

### Categorización
- **Categoría**: Clasificación principal de comercios
- **Sub-Categoría**: Clasificación secundaria más específica

### Presencia Web
- **Página Web**: Información sobre la presencia digital de cada comercio

### Sistema de Auditoría
- **Auditoría**: Registro completo de todas las operaciones realizadas en el sistema, incluyendo:
  - Usuario que realizó la acción
  - Tipo de acción (inserción, modificación, eliminación)
  - Fecha y hora de la acción
  - Motivo de la modificación
  - Valores modificados

## Características Principales

- **Interfaz Intuitiva**: Acceso sencillo a toda la información comercial del municipio
- **Categorización Jerárquica**: Sistema de categorías y subcategorías para facilitar búsquedas
- **Gestión de Horarios**: Registro detallado de horarios comerciales
- **Sistema de Auditoría**: Seguimiento completo de cambios para garantizar la integridad de los datos
- **Gestión Documental**: Almacenamiento de información relevante de cada comercio

## Requisitos Técnicos

- Base de datos relacional
- Servidor web con soporte para PHP
- Capacidad para implementar triggers de base de datos
- Sistema de copias de seguridad automatizado

## Seguridad

El sistema implementa múltiples capas de seguridad:

- Autenticación segura de usuarios
- Registro detallado de acciones (auditoría)
- Permisos basados en roles
- Protección contra inyección SQL y otros ataques comunes
- Cifrado de datos sensibles

## Implementación

La implementación del sistema sigue un enfoque modular, permitiendo la expansión futura y la adición de nuevas funcionalidades según las necesidades del ayuntamiento.

## Contacto

Para más información sobre este proyecto, contacte con el Departamento de Informática del Ayuntamiento de Villanueva de la Cañada.
