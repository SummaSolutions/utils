User Agent Module
=================

 * Get information about the device making the request

Instrucciones:
=================

Se instala igual que cualquier módulo de Magento.

Funcionamiento:
=================
Instanciar Helper del modulo.

Obtener el tipo de device haciendo el request:

$foo = userAgentHelper->getDeviceType(); // devuelve 'tablet', 'mobile', 'desktop' o 'unknown'


Obtener el OS del device haciendo el request:

$bar = userAgentHelper->getOsName(); // devuelve 'windowsMobile', 'android', 'iOS' o 'unknown'
```
