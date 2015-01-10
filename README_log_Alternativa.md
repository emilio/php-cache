# Sistema de caché de archivos en PHP

[Repositorio original] (https://github.com/ecoal95/php-cache)

## Problemas de concurrencia lectura/escritura en un valor de la cache

La librería puede devolver datos corruptos cuando concurren varios procesos leyendo y escribiendo la misma clave de la cache.

Este fork soluciona este problema realizando la escritura en un archivo temporal. Si el resultado es correcto, el fichero temporal se renombrará al definitivo utilizando la función `rename` de PHP. Esta función, es atómica si se realiza en el mismo sistema de ficheros y esto siempre ocurre porque el archivo temporal se genera en el directorio `cache`.

## Test

Con la modificación adjunto una serie de test que simulan la concurrencia y comprueban que no se obtienen datos corruptos. 

Además también se añaden otros que realizan medidas del coste en tiempo de obtener un valor de la cache.

Manteniendo la misma estructura que la del repositorio:

```
+
|
+- cache
+- test
|  |
|  +- benchmark-concurrent.test.php
|  +- benchmark.test.php
|  +- function.common.php
|  +- robustness-rw2.test.php
|  +- robustness-rw.test.php
+- Cache.php

```

los tests se pueden lanzar desde línea de comandos o realizando una llamada a los scripts desde el navegador al servidor web desde donde se copien.

### robustness-rw.test.php

Desde línea de comandos:

```
php -f test/robustness-rw.test.php

```

Lanza 10 procesos concurrentes. Cada uno realiza 50 peticiones que alternativamente, en un clave de la cache, graba un objeto de tres diferentes y obtiene después su valor. 

El valor obtenido de la cache tiene que ser igual a alguno de los 3 objetos. Si es diferente o es nulo el valor se considera como invalido.

Comprueba también si ha habido errores al escribir el valor en la clave.

En total el test realiza 500 peticiones de lectura y escritura sobre una misma clave de la cache.

### robustness-rw2.test.php

Desde línea de comandos:

```
php -f test/robustness-rw2.test.php

```
Es similar al anterior. Lanza 10 procesos concurrentes. Cada proceso hace 50 veces un ciclo de para una misma clave de la cache, guardar un valor de 3 diferentes, obtener el valor, y en este test también en borrar el valor.

Si el resultado de obtener el valor de la cache es diferente a uno de los tres valores o a nulo se considera corrupto.

En total realiza 500 peticiones de lectura, escritura y borrado sobre una misma clave de la cache.

### benchmark.test.php

**Cache.php** está basada en la 'serialización' de los objetos y en el almacenamiento en ficheros, por lo que la velocidad de un valor de la cache depende primero de la rapidez en la 'deserialización' del objeto y segundo de la rapidez del sistema de ficheros.

Este script permite hacer una comparativa entre la velocidad de obtener un valor de la cache (y por tanto leyendo del sistema de ficheros) y el coste en tiempo al 'desserializar' el objeto sin que hubiera acceso a disco. Por tanto comparar el coste en tiempo real y el ideal de obtener un valor de la cache.

El script realiza el test obteniendo un valor de la cache con diferentes tamaños de objeto. Devuelve información tanto del coste real como el ideal, número de peticiones realizadas, tiempo total y las estadísticas con datos sobre la media de milisegundos que ha tardado en hacer una petición y el calculo de las peticiones por segundo.

Extracto de resultado en mi máquina de desarrollo (Debian virtualizada en VirtualBox)

```
…
== Cache::get ====
 Object: List size = 100 (size each object: 20) 
 Requests/thread: 500
 Concurrent thread: 1
 time total: 5395.6 milisec
 statistics: 10.8 milisec/request (93 request/sec)

 >> Value control memory
 Object: List size = 100 (size each object: 20) 
 Requests/thread: 500
 Concurrent thread: 1
 time total: 4819.5 milisec
 statistics: 9.6 milisec/request (104 request/sec)
…
```

El bloque de arriba son los datos del coste real y el bloque de abajo son los datos del coste ideal.

Con este script se puede testear la rapidez del servidor al obtener un valor de la cache y que parte de ese coste es el de acceso a disco.

### benchmark-concurrent.test.php
Similar al anterior script. Calcula los mismos datos con las estadísticas de las peticiones de un hilo leyendo sobre una clave de la cache cuando se ejecutan concurrentemente 5 hilos leyendo sobre la misma clave. 

En este caso el tamaño de objeto que se guarda en la cache es fijo.

Este test sirve para comprobar si se ve penalizado el rendimiento por el acceso de la lectura concurrentemente de un misma clave por varios clientes. Hay que tener en cuenta de que los resultados de este script dependen de la capacidad de  procesamiento en paralelo que tenga tu servidor (cores). También influye como este configurado este aspecto el servidor web.

[M.E.](http://www.logicaalternativa.com)

