Summa Auto Suggest Magento Module
=================

Reemplaza el módulo de autosuggest/autocomplete que viene con Magento, por uno más completo. Se integra con Solr y además agrega la funcionalidad de indexar páginas CMS en Solr.

URL: https://github.com/SummaSolutions/utils/tree/development/Magento/Summa_AutoSuggest

Instrucciones:
=================

Se instala igual que cualquier módulo de Magento.

La extensión requiere tener jQuery instalado.
*Nota: Se probó con jQuery 2.1.0*


Configuraciones:
=================
La configuración se encuentra dentro del panel de administración de Magento, en la sección **System > Configuration** y luego en **Catalog > Auto Suggest**.

### Configuración de productos:
* **Show products results**: Muestra/oculta productos en los resultados
* **Limit**: Cantidad de productos a mostrar
* **Display Products Thumbnails**: Habilita/deshabilita el thumbnail de los productos
* **Thumbnail Width**: Ancho del thumbnail
* **Thumbnail Height**: Alto del thumbnail


### Configuración de categorias:
* **Show categories results**: Muestra/oculta categorias en los resultados
* **Limit**: Cantidad de productos a mostrar
* **Display amount of products**: Muestra/oculta la cantidad de productos encontrados dentro de la categoria
* **Minimum Category Level**: Nivel mínimo de categoria a mostrar
* **Category Link**: Determina a donde apunta el link de la categoria
  * *Category Page*: Va a la página de la categoria
  * *Search results Page*: Va a la página de resultados de búsqueda, filtrado por la categoria


### Configuración de Páginas
* **Show categories results**: Muestra/oculta categorias en los resultados
* **Limit**: Cantidad de productos a mostrar

### Configuración avanzada
* **Minimum input to proceed**: Cantidad mínima de caracteres para realizar busquedas
* **Delay after user input (in ms)**: Tiempo de espera antes de realizar la búsqueda cuando el usuario esta escribiendo


El módulo también cuenta con una configuración para habilitar/deshabilitar los resultados CMS en la página de resultados, para ello hay que ir a **System > Configuration** y luego **Catalog > Search**


Todo List:
=================
- [ ] Agregar compatibilidad para cuando está activado flat categories.
- [ ] Permitir que indexado de CMS pages funcione en 'Update on Save'.
- [ ] Invalidar índice de CMS cuando corresponde.
- [ ] Compatibilidad con Solr desactivado (no está probado 100%).
