Andreani Summa Module
======

 * Método de envio para **Andreani**.
 * Integrado con MatrixRates.
 * Integrado con todos los servicios de Andreani, Envio Estandar, Retiro en Sucursal y Envio Urgente.
 * Configuraciones para cada servicio, Configuraciones globales para los Web Services.
 * Observers en todos los llamados a los Web Service para customizacion de las operaciones sin necesidad de overridear.
 * Status para Andreani en el shipment.
 * Crones para Actualizar Sucursales de andreani y Status de los Shipments incompletos.
 * Tracking de los envios.
 * Shipping Labels de Magento implementados (permite guardar en magento las constancias de los pedidos de andreani).
 * Botones para generar todos los pasos de andreani en caso de que algun Web Service falle.
 * Listo para integrar en cualquier ambiente, con atributos listos para añadir si no existiesen en el ambiente a integrar.
 * Seguro configurable para que sea añadible directamente al precio del envio o a parte como un subtotal.
 * IVA configurable para MatrixRate (en el caso del Web Service ya viene incluido).
 * Configuraciones para generar automaticamente el pedido a andreani cuando se realiza el pago o cuando se crea el shipment (desde el admin).
 * Configuraciones propias de MatrixRate configurables en Andreani de forma que MatrixRate puede estar deshabilitado o con configuraciones para otro Metodo de Envio.
 * Configuraciones para elegir el Delivery Type para cada servicio de Andreani.
 * Cuando se elimina un tracking code en Magento se Intenta cancelar el Pedido en Andreani.
 * Configuraciones para permitir promociones de Free Shipping en cada Servicio.
 * Traducciones completas en Ingles y Español (ES y AR).

Instalacion:
=================

Se instala igual que cualquier módulo de Magento.

Para integrarlo con Matrixrate Instalar tambien el Modulito que esta dentro de requirements/MatrixRates

Por defecto el modulo no instala atributos a los productos para el calculo de volumen y peso aforado requerido por andreani, para instalarlos:

1. Copiar app/code/community/Summa/Andreani/sql/mysql4-upgrade-PRODUCT_ATTRIBUTES.php a app/code/community/Summa/Andreani/sql/summa_andreani_setup
2. Renombrar de forma que PRODUCT_ATTRIBUTES sea reemplazado por la version del installer que corresponda.
3. Actualizar la version correspondiente en app/code/community/Summa/Andreani/etc/config.xml

Por defecto el modulo no instala el atributo DNI a los customer para los pedidos a andreani, para instalarlo:

1. Copiar app/code/community/Summa/Andreani/sql/mysql4-upgrade-QUOTE_ORDER_ADDRESS_ATTRIBUTES.php a app/code/community/
Summa/Andreani/sql/summa_andreani_setup
2. Renombrar de forma que QUOTE_ORDER_ADDRESS_ATTRIBUTES sea reemplazado por la version del installer que corresponda.
3. Actualizar la version correspondiente en app/code/community/Summa/Andreani/etc/config.xml
4. Descomentar en app/code/community/Summa/Andreani/etc/config.xml las líneas 184, 201, 118 y 128.

Como usar:
=================

Por defecto ya viene con todas las configuraciones precargadas para ser funcional utilizando los datos de Test, 
solo hace falta definir cuales van a ser los atributos de peso, alto, ancho y profundidad de los productos 
(a menos que se utilizen los installers proporcionados por el modulo), habilitar los servicios de andreani que 
se van a ofrecer en el sitio, importar las provincias de Argentina y por ultimo ir al admin en Andreani Branches 
-> Admin Branches y hacer un Fetch de las sucursales.

Datos de Test:
=================
 * Número de Cliente: ANDCORREO
 * Usuario: eCommerce_Integra
 * Contraseña: passw0rd
 * Contrato ESTANDAR: AND00EST
 * Contrato URGENTE: AND00URG
 * Contrato SUCURSAL: AND00SUC

Documentacion de Andreani:
=================

[Documentacion original de Andreani (2013)](https://github.com/summasolutions/utils/raw/development/Magento/Andreani/docs/Documentacion%20De%20Andreani/ImplementacionServiciosAndreani.v1.pdf)

[Documentacion de Andreani 1.8 (02/2015)](https://github.com/summasolutions/utils/raw/development/Magento/Andreani/docs/Documentacion%20De%20Andreani/ImplementacionServiciosAndreani.v1.8.doc)

[Documentacion de Andreani 1.9 (05/2015)](https://github.com/summasolutions/utils/raw/development/Magento/Andreani/docs/Documentacion%20De%20Andreani/ImplementacionServiciosAndreani.v1.9.doc)

[Sitio de Andreani con información de los WS](http://www.andreani.com/Services/Show/142/Env%C3%ADos-de-E-Commerce)

[Link de descarga de la Documentacion más Actualizada de Andreani](http://www.andreani.com/FilesRelated/Download?FileId=17)

[Ejemplos PHP de Andreani](https://github.com/summasolutions/utils/raw/development/Magento/Andreani/docs/Documentacion%20De%20Andreani/EJEMPLOSPHPAndreani.zip)

Archivos Adjuntos al Modulo:
=================

[CSV de Ejemplo de MatrixRate para Test](https://github.com/summasolutions/utils/raw/development/Magento/Andreani/docs/matrixrates%20Para%20Andreani.csv)

[Modulo de MatrixRate para Test](https://github.com/summasolutions/utils/raw/development/Magento/Andreani/docs/Matrxrate-5.0.1.tgz)

[Script SQL para agregar Provincias de Argentina a Magento](https://github.com/summasolutions/utils/raw/development/Magento/Andreani/docs/PROVINCIAS_ARGENTINA.sql)

Listo:
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
- Todos los llamados a andreani deben ser parseados a un Varien_Object.
- Añadir configuracion para decidir que hacer cuando el peso de un producto es menor o igual a 0, setear en 1 (actual), generar error al guardar producto, setear en valor custom, exception.
- Configuraciones de freeshipping para cada carrier de andreani.
- Añadir eventos previos y posteriores a los llamados de los servicios de andreani para permitir customizaciones sin edicion/override de codigo.
- Pasar el seteo de DNI y tipo de DNI a un observer de forma que sirva de referencia.
- Traducciones al español completas, validar que el codigo este todo en ingles.
- Validaciones al alto, ancho y largo para no permitir mayores a 90cm

TODO:
=================
+ Code Review, Test general