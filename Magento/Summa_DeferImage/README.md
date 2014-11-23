Summa Defer Image Magento Module
=================

Agrega la capacidad de hacer la carga de imágenes luego de que el dom se cargo, para evitar frenar la carga del html.

Ya incluye esta funcionalidad en el listado de productos.

URL: https://github.com/SummaSolutions/utils/tree/development/Magento/Summa_DeferImage

Instrucciones:
=================

Se instala igual que cualquier módulo de Magento.

La extensión requiere tener jQuery instalado.
*Nota: Se probó con jQuery 1.10.2*


Configuraciones:
=================
La configuración se encuentra dentro del panel de administración de Magento, en la sección **System > Configuration** y luego en **Catalog > Defer Image Loading**.

### Configuración:
* **Enable**: habilita/deshabilita esta funcionalidad

Funcionamiento:
=================
Si bien el módulo ya incluye la funcionalidad para los listados de productos, esto se puede utulizar con cualquier tag html **<img />**

La forma en la que funciona es la siguiente, hay que reemplazar el tag img que se quiere cargar en diferido.
Asumiendo que se tiene algo asi:
```html
<img src="path/to/my_image.jpg" />
```
Esto se tiene que reemplazar por lo siguiente:
```html
<img class="defer-image" data-src="path/to/my_image.jpg" />
```

Y asegurarse de que la libreria javascript esté correctamente cargada, para la inclusión de la misma en páginas que no son listados de productos, este sería el código para incluirla:

```xml
<reference name="head">
    <action method="addItem" ifconfig="catalog/defer_images/enable">
        <type>skin_js</type>
        <name>js/deferImages.js</name>
    </action>
</reference>
```