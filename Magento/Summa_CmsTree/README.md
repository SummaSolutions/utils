Summa Cms Pages Tree Magento Module
=================

Permite crear una estructura de arbol de cms pages para ser visualizadas en el menu del sitio.


Instrucciones:
=================

Se instala igual que cualquier módulo de Magento.


Configuraciones:
=================
La configuración se encuentra dentro del panel de administración de Magento, en la sección **System > Configuration > Summa > CMS Tree**.

Por el momento esta version solo permite configurar si el arbol va a ser visible en el menu o no.


Creación del arbol:
=================

La pagina principal de la extensión se encuentra dentro de **CMS > Cms Tree**. 

Dentro de esta se pueden observar 3 secciones. A la izquierda se encuentra el arbol de CMS Pages.

Cuando se instala la extension por defecto agrega un nodo root para el arbol default y uno por cada store existente. Para agregar un nodo al arbol se selecciona el nodo padre y luego se seleccionan todas CMS Pages en el grid inferior.

Esto crea y agrega los nodos del arbol.

Los nodos pueden ser editados y eliminados desde el formulario que se encuentra a la derecha.
El campo Title representa el titulo con el que se identifica el nodo del arbol, el valor que se utilizara en el topmenu del frontend es el Title de la CMS Page correspondiente.

Por defecto cada store utiliza el arbol default, en el caso que se quiera utilizar uno propio de este se debe seleccionar el scope del store en el selector señalado. Una ves hecho esto se debe cambiar el campo "use default" del form y guardar el nodo.

Luego de configurar el arbol es posible ver los nodos en el topmenu de las paginas del sitio.
