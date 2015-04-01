Andreani Summa Module
=================

 * Shipping method for Magento using carrier Andreani with MatrixRates.

Instrucciones:
=================

Se instala igual que cualquier módulo de Magento.

Funcionamiento:
=================
TODO

Datos de test:
=================
Cliente: ANDCORREO
Usuario: eCommerce_Integra
Contraseña: passw0rd
Contrato ESTANDAR: AND00EST
Contrato URGENTE: AND00URG
Contrato SUCURSAL: AND00SUC

TODO:
- Ordenar las configuraciones User, Pass, Nro Cliente, Account Number => Contrato, etc. Quitar configuraciones de Tab Andreani y pasar todo a Shipping Methods! +
- Configuracion para limite de peso aforado. +
- Configuracion para mostrar en el backend la info de errores de andreani
- Implementar obtener Rates desde webservice de andreani
- Configuracion para usar Matrixrates o Web Service de andreani para los rates
- Implementar carrier para envio a sucursal +
- Implementar carrier para envio Urgente +
- Implementar carrier para envio Estandar +
- Refactorizar carrier actual para que sea generico y tenga los llamados a los web services de Andreani. +
- Añadir la creacion del atributo DNI en los address y/o customer. +
- Implementar cancelacion/devolucion de envios. +
- Boton en administracion de sucursales para actualizar el listado. +
- Añadir seguro configurable, Opcional: añadir Seguro como añadido aparte. +
- Añadir configuracion para settear el porcentaje que se aplica al subtotal de la orden para calcular el seguro del envio. +
- Añadir configuracion para utilizar un subtotal aparte para el seguro de envio o sumarlo al precio de envio. +

- Mostrar seguro en order view. - (GM)

- Añadir eventos previos y posteriores a los llamados de los servicios de andreani para permitir customizaciones sin edicion/override de codigo. -

- Implementar funcion que llame al webservice de andreani cuando se crea el shipment. -

- Añadir configuraciones para habilitar/deshabilitar la creacion automatica del request a andreani cuando se crea el shipment al estilo Magento. -
- Añadir configuraciones para usar un username y password específico en cada servicio. -
- Añadir configuraciones para habilitar/deshabilitar tracking en cada servicio. -
- Añadir configuracion para enviar mail despues de generar el shipment. -
- Añadir configuracion para decidir que hacer cuando el peso de un producto es menor o igual a 0, setear en 1 (actual), generar error al guardar producto, setear en valor custom, exception. -
- Añadir configuracion para cargar el ShippingType que matchea con Matrixrates en cada servicio. -
- Añadir configuraciones propias de MatrixRates a la configuracion de andreani de forma que sea configurable a nivel de andreani y no general de MatrixRates. -
- Añadir configuracion para marcar cuales son los campos que se van a cargar en el matrixrates de forma que sea seleccionable si se va a filtrar por codigopostal, ciudad, etc. (por ahora solo valida por pais y provincia ademas del peso). -

- Añadir el calculo del weight de los productos en el evento before save y dejarlo dentro del modulo de andreani. -

- Añadir la creacion de los atributos de producto necesarios para el calculo del peso aforado dentro del modulo de andreani. -
- Opcional, añadir una configuracion para seleccionar cuales van a ser los atributos que se utilizen como alto, ancho, profundidad, volumen y peso de andreani, de forma que en el calculo se utilizen esas configuraciones.

- Traducciones al español completas, validar que el codigo este todo en ingles. -

- Chequear informacion recibida de web service sucursales cuando hace el fetch. -