Summa Geolocation Magento Module
=================

Determina el pais del usuario en base a su IP y lo envia al store cuyo pais por defecto sea el del usuario, sino permite configurar una página de selección de pais.

URL: https://github.com/SummaSolutions/utils/Magento/Summa_Geolocation

Instrucciones:
=================

Una vez instalado el módulo en Magento, la mayor parte es configuración.

La configuración esta dividida en 2 partes (global y por store), todas las configuraciones se encuentran dentro del panel de administración de Magento, en la sección **System > Configuration** y luego en **General > Country Options**.

### Configuración Global:
* **Use geo-location service**: Habilita/deshabilita la funcionalidad del módulo. (default: No)
* **Method**: Método utilizado para determinar el pais en base a la IP (default: Geoplugin)
* **Use splash screen**: Habilita/deshabilita la pantalla de selección de store, en caso de que no se pueda determinar a que pais tiene que ir el usuario
* **Store use for splash screen**: Selección del store que va a mostrar el splash
* **Url for splash screen**: Url utilizada para el splash screen.


### Configuración por Store:
* **Include store in geolocation**: Determina si el store es incluido en la lógica de redirección por IP
* **Include store in splash screen**: Determina si el store es mostrado en la pantalla de selección de país.
* **Label for splash screen**: Etiqueta a mostrar en la pantalla de selección de pais


### Métodos
Hay varias opciones para detectar el pais de geolocation en base a la ip del usuario. 
En la extensión viene 2 formas, pero está preparada para agregar métodos en caso de asi quererlo:

* **PHP**: Este método utiliza las libreria de GeoIP provista por PHP, para más detalles se puede ver este [link] (http://php.net/manual/es/book.geoip.php)
* **Geoplugin**: Este método utiliza un web service, para más detalles se peude ver este [link] (http://www.geoplugin.com/)

##### Agregando métodos
Si se desean agregar nuevos métodos hay que realizar los siguientes pasos:

* Modificar la clase `Summa_Geolocation_Helper_Data` para agregar la opción del nuevo método
* Generar la clase correspondiente al método, extendiendo de `Summa_Geolocation_Model_Method_Abstract` e implementando el método `getCountry`


### Splash screen
La splash screen puede ser una CMS page. La extensión provee un bloque para poder ser incluido en cualquier página.
El bloque `Summa_Geolocation_Block_Splash` se encarga de listar todos los stores que asi hayan sido configurados, tiene asociado un template también.

En caso de querer agregarlo como parte de una CMS, este sería el código:
```
{{block type="summa_geolocation/splash"}}
```

Para agregarlo desde un layout el código ser´â:
```xml
<block type="summa_geolocation/splash" name="splash" />
```