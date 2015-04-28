Andreani Summa Module
======

 * Shipping method for Magento using argentinean carrier **Andreani**.
 * 100% Working with Andreani Web Service and, at the same time, Integrated with MatrixRates to get instant rates if it's needed

Settuping:
=================

Se instala igual que cualquier módulo de Magento.

How to:
=================
TODO

Test data:
=================
 * Client Number: ANDCORREO
 * User: eCommerce_Integra
 * Password: passw0rd
 * Contract ESTANDAR: AND00EST
 * Contract URGENTE: AND00URG
 * Contract SUCURSAL: AND00SUC

READY:
=================
- Ordenar las configuraciones User, Pass, Nro Cliente, Account Number => Contrato, etc. Quitar configuraciones de Tab Andreani y pasar todo a Shipping Methods
- Configuracion para limite de peso aforado. 
- Implementar carrier para envio a sucursal 
- Implementar carrier para envio Urgente 
- Implementar carrier para envio Estandar 
- Refactorizar carrier actual para que sea generico y tenga los llamados a los web services de Andreani. 
- Añadir la creacion del atributo DNI en los address y/o customer. 
- Implementar cancelacion/devolucion de envios. 
- Boton en administracion de sucursales para actualizar el listado. 
- Añadir seguro configurable, Opcional: añadir Seguro como añadido aparte. 
- Añadir configuracion para settear el porcentaje que se aplica al subtotal de la orden para calcular el seguro del envio. 
- Añadir configuracion para utilizar un subtotal aparte para el seguro de envio o sumarlo al precio de envio. 
- Mostrar seguro en order view. 
- Implementar funcion que llame al webservice de andreani cuando se crea el shipment. 
- Implementar obtener Rates desde webservice de andreani. 
- Añadir configuraciones para sumar IVA a los rates y que el IVA sea configurable. 
- Añadir configuraciones para habilitar/deshabilitar la creacion automatica del request a andreani cuando se crea el shipment al estilo Magento.
- Añadir configuraciones propias de MatrixRates a la configuracion de andreani de forma que sea configurable a nivel de andreani y no general de MatrixRates.
- Añadir configuracion para marcar cuales son los campos que se van a cargar en el matrixrates de forma que sea seleccionable si se va a filtrar por codigopostal, ciudad, etc.
- Añadir configuracion para cargar el ShippingType que matchea con Matrixrates en cada servicio.
- Añadir la creacion de los atributos de producto necesarios para el calculo del peso aforado dentro del modulo de andreani. 
- Opcional, añadir una configuracion para seleccionar cuales van a ser los atributos que se utilizen como alto, ancho, profundidad, volumen y peso de andreani, de forma que en el calculo se utilizen esas configuraciones.
- Añadir configuraciones para usar un nro de cliente, username y password específico en cada servicio. 
- Añadir el calculo del weight de los productos en el evento before save y dejarlo dentro del modulo de andreani.
- Añadir funcionalidad de que al eliminar un tracking code de andreani se intente cancelar el envio de andreani. 
- Implementar funcion que llame al webservice de andreani cuando se elimina el tracking code.
- Implementar estados de andreani para el shipment y para la orden de forma que sea visible el status de andreani.
- Actualizar el estado de andreani a traves del tracking.
- Añadir cron para actualizar el estado de los shipments de andreani.
- Refactorizar/cambiar desarrollo de la generacion del link de la constancia. Se cambio por la implementacion de Shipping Labels de Magento.
- Añadir configuraciones para habilitar/deshabilitar tracking en cada servicio.
- Todos los llamados a andreani deben ser parseados a un Varien_Object

TODO:
=================
- Añadir eventos previos y posteriores a los llamados de los servicios de andreani para permitir customizaciones sin edicion/override de codigo. -

- Añadir configuracion para decidir que hacer cuando el peso de un producto es menor o igual a 0, setear en 1 (actual), generar error al guardar producto, setear en valor custom, exception. -
- Añadir configuracion para decidir que hacer cuando el volumen de un producto es menor o igual a 0, setear en 1 (actual), generar error al guardar producto, setear en valor custom, exception. -

- Traducciones al español completas, validar que el codigo este todo en ingles. -

- Chequear informacion recibida de web service sucursales cuando hace el fetch. -

- Pasar el seteo de DNI y tipo de DNI a un observer de forma que sirva de referencia. -
