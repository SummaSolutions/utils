Summa Js/Css Version Magento Module
=================

Permite agregar el número de versión al final de la inclusión de los css/js.

URL: https://github.com/SummaSolutions/utils/tree/development/Magento/Summa_JsCssVersion

Instrucciones:
=================

Se instala igual que cualquier módulo de Magento.


Configuraciones:
=================
La configuración se encuentra dentro del panel de administración de Magento, en la sección **System > Configuration** y luego en **Advanced > Developer > JS/CSS Version**.

### Configuración:
* **Enable**: habilita/deshabilita esta funcionalidad (Default: deshabilitado)
* **Version**: número de versión (Default: 1.0)

Funcionamiento:
=================
Agregar al final de la ruta de inclusión del javascript/css el número de versión hasheado en md5.

De esta forma, algo que suele ser asi:
```html
<link media="all" href="http://magentostore.com/media/css/7849858c4ab5eb12af89f367b142a67c.css" type="text/css" rel="stylesheet">
<script src="http://magentostore.com/js/prototype/prototype.js" type="text/javascript">
```
Pasa a ser algo asi:
```html
<link media="all" href="http://magentostore.com/media/css/7849858c4ab5eb12af89f367b142a67c.css?v=e4c2e8edac362acab7123654b9e73432" type="text/css" rel="stylesheet">
<script src="http://magentostore.com/js/prototype/prototype.js?v=e4c2e8edac362acab7123654b9e73432" type="text/javascript">
```