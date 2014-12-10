Summa ViewMails Magento Module
=================

Permite ver los emails desde un browser, desde el lado del usuario..

URL: https://github.com/SummaSolutions/utils/tree/development/Magento/Summa_ViewMails

Instrucciones:
=================

Se instala igual que cualquier módulo de Magento.


Configuraciones:
=================
La configuración se encuentra dentro del panel de administración de Magento, en la sección **System > Configuration > Summa > View E-mails in Browser**.

### Configuración:
* **Enable View in Browser**: habilita/deshabilita esta funcionalidad (Default: deshabilitado)
* **Clear saved emails**: habilita/deshabilita la opción de eliminar los preview guardados (Default: deshabilitado)
* **Days to expire**: días que se mantendra guardados los previews (Default: 0)

Funcionamiento:
=================
El los templates de los email donde se quiere usar esta funcionalidad agregar:

<a href="{{var viewinbrowser}}">Some text here</a>
