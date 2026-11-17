# Sistema web de citas médicas portable

Prototipo académico para el TPE 4 de Interacción Humano Computador, UNEMI. Usa PHP, SQLite y CSS. Los datos se guardan en un archivo local `citas_medicas.db` que se crea al iniciar el sistema.

## Iniciar en Ubuntu o WSL

Se requiere PHP con las extensiones `PDO` y `pdo_sqlite` habilitadas. Desde esta carpeta, ejecutar:

```bash
bash EJECUTAR_EN_UBUNTU.sh
```

Abrir `http://127.0.0.1:8080` en el navegador.

## Iniciar en Windows

Copiar la carpeta `php` del material proporcionado por el docente junto a `index.php`, `db_config.php` y `EJECUTAR_SISTEMA.bat`. Debe contener `php.exe` y `php.ini`, con `pdo_sqlite` activada. Ejecutar `EJECUTAR_SISTEMA.bat` y abrir `http://127.0.0.1:8080`.

La carpeta `php` no se incluye en este repositorio. No es necesario importar un archivo SQL externo.

## Uso de demostración

- Usuario: `admin`
- Contraseña inicial: `1234`
- Registrar una cita con nombre ficticio, especialidad, fecha y hora.
- Consultar la cita en el listado y cerrar sesión.

La cuenta inicial es solo para pruebas locales. Cambiar la contraseña antes de utilizar el sistema con otras personas. No ingresar datos médicos reales en este prototipo.

## Mejoras del código

Se añadieron protección del registro por sesión, contraseñas con hash, token CSRF, validación en el servidor, escape de contenido mostrado, etiquetas y mensajes visibles, interfaz adaptable y redirección tras guardar.

## Pruebas realizadas

Se probó en Ubuntu con PHP 8.5.4, PDO SQLite y Chrome: acceso correcto e incorrecto, registro de una cita ficticia, persistencia después de reiniciar el servidor y cierre de sesión. Las capturas se incorporaron al manual del TPE 4.

