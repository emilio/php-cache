# Sistema de caché de archivos en PHP

Esta clase provee una forma fácil, orientada a objetos de trabajar con una caché basada en el sistema de archivos.

[Leer más]() | [El autor](http://emiliocobos.net)

## Comenzando
Lo primero que tienes que hacer es incluir el archivo
```php
require 'Cache.php';
```

### Opciones
Hay dos opciones: `cache_dir` y `expires`.

* `cache_dir` es el directorio donde se almacenará la caché. Por defecto es un directorio relativo (`cache`)
* `expires` es el tiempo *en minutos* que debe de pasar para que un item de la caché expire

**Importante**: Verifica que el archivo configurado como `cache_dir` tenga permisos de escritura. Además, **si almacenas datos que no deberían ser accesibles públicamente, tu directorio `cache_dir` debería de tener un archivo `.htaccess` con la siguiente línea:**
```
deny from all
```

Puedes modificar las opciones así:

```php
Cache::configure('nombre_opción', 'valor');
```

O así:

```php
Cache::configure(array(
	'nombre_opcion_1' => 'valor 1',
	// ...
));
```
### Almacenando datos
Para almacenar *cualquier tipo de dato* en la caché usa el método `put()` pasando un identificador (key) y el valor.
```php
Cache::put('identificador', 'valor');
``` 

### Obteniendo datos
Para obtener datos almacenados en la caché usa:

```php
Cache::get('identificador');
```

### Borrando datos
Simplemente invoca al método `delete()` pasando la key:
```php
Cache::delete('identificador');
```

### Borrando toda la caché
Usa el método `flush()` para borrar todos los datos almacenados en la caché:
```php
Cache::flush();
```

## Avanzado
Este sistema permite pasar un segundo argumento a `get()` y `put()`, que dice si los datos se van a guardar en forma de string directamente, o no. Podría ser útil para cachés de páginas enteras:

```php
if( $cached_homepage = Cache::get('index_cached', true) ) {
	echo $cached_homepage;
} else {
	ob_start();
}

// Generar la página principal dinámica

// ...
Cache::put('index_cached', ob_get_contents());

ob_end_flush();
```


## Velocidad
Este sistema de caché está basado en el acceso a archivos, así que es más lento que memcached o apc. No obstante, **cuanto mayores son los datos a almacenar, menor es la diferencia de velocidad**. Puedes comprobarlo ejecutando el archivo `test.php` del repositorio. Los resultados en un servidor local frente a `apc`:

**Volumen de datos pequeño** (nótese que _datos pequeños_ son una pequeña string, así que en estos casos no merece la pena acceder al sistema de ficheros). Aún así la diferencia real de tiempo no es muy grande.
<pre>Diferencia total: 0.11318588256836 segundos
Diferencia porcentual de velocidad: 48.071951581333%</pre>

**Volumen de datos medio**
<pre>Diferencia total: 0.099838733673096 segundos
Diferencia porcentual de velocidad: 7.1366305302393%</pre>

**Volumen de datos grande** (con datos grandes la **diferencia de velocidad es casi nula**)
<pre>Diferencia total: 0.29143214225769 segundos
Diferencia porcentual de velocidad: 0.45639094202677%</pre>
