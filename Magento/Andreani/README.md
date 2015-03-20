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
- Ordenar las configuraciones User, Pass, Nro Cliente, Account Number => Contrato, etc. Quitar configuraciones de Tab Andreani y pasar todo a Shipping Methods!
- Configuracion para limite de peso aforado
- Configuracion para mostrar en el backend la info de errores de andreani
- Implementar obtener Rates desde webservice de andreani
- Configuracion para usar Matrixrates o Web Service de andreani para los rates
- Implementar carrier para envio a sucursal
- Implementar carrier para envio Urgente
- Implementar carrier para envio Estandar
- Refactorizar carrier actual para que sea generico y tenga los llamados a los web services de Andreani
- Añadir el calculo del weight de los productos en el evento before save y dejarlo dentro del modulo de andreani
- Añadir la creacion de los atributos de producto necesarios para el calculo del peso aforado dentro del modulo de andreani
- Añadir la creacion del atributo DNI en los address y/o customer
- Traducciones al español completas, validar que el codigo este todo en ingles.
- Boton en administracion de sucursales para actualizar el listado.
- Añadir eventos previos y posteriores a los llamados de los servicios de andreani para permitir customizaciones sin edicion/override de codigo.
- Añadir configuraciones para usar un username y password específico en cada servicio.
- Añadir configuraciones para habilitar/deshabilitar tracking en cada servicio
- Añadir configuracion para enviar mail despues de generar el shipment
- Añadir configuracion para decidir que hacer cuando el peso de un producto es menor o igual a 0, setear en 1 (actual), generar error al guardar producto, setear en valor custom, exception
- Implementar cancelacion/devolucion de envios
- Añadir seguro configurable, Opcional: añadir Seguro como añadido aparte
- Añadir configuracion para cargar el ShippingType que matchea con Matrixrates en cada servicio