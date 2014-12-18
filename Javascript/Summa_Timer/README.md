Summa Timer (Javascript)
=================

Pequeño script para generar un temporizador que puede ser estileado a gusto.

Instrucciones:
=================

Copiar el archivo timer.js a un folder del proyecto para librerias JS (en Magento /js/Summa/)
y incluirlo en la carga de la pagina donde se desea tener el temporizador.
Es compatible con Prototype y jQuery, detecta si existe jQuery, si no utiliza Prototype

Como usarlo:
=================

### Las configuraciones posibles son:
* **timeLeft**: Default: 60, Es la cantidad de segundos que se contarán de forma regresiva.
* **keepCounting**: Default: 1, Es el flag que indica si se esta llevando a cabo la cuenta regresiva.
* **noTimeLeftMessage**: Default: 'The time is over', Es el mensaje que se mostrará en lugar del contador cuando el temporizador llege a 0
* **autoStart**: Default: false, Es el flag que indica que cuando se inicialize el temporizador debe iniciar la cuenta regresiva.
* **debug**: Default: false, Es el flag que indica que se esta debuggeando el script, esto habilita los logs de consola. Solo usar para test.
* **outputConnector**: Default: ':', Es el conector entre las horas, minutos y segundos, ej: si es ":" entonces 17horas, 30 min y 20 seg se mostrarian "17:30:20".
* **outputHoursIfZero**: Default: false, Es el flag que indica si debe mostrarse "00" cuando no existen horas en la cuenta regresiva, ej si es true y la cuenta es de 2min y 20 seg se mostraria "00:02:20".
* **outputMinutesIfZero**: Default: true, Es el flag que indica si debe mostrarse "00" cuando no existen minutos en la cuenta regresiva, ej si es true y la cuenta es de 20 seg se mostraria "00:20".
* **onNoTimeLeft**: Default: function(){}, Es la funcion del estilo callback que se ejecuta al terminar la cuenta regresiva.

### Ejemplos de uso:

Por defecto el temporizador buscará mostrarse dentro del primer elemento que matchee con 'countdown_time'
la cuenta regresiva sera de 60 segundos y al finalizar mostrara un mensaje 'The time is over'. Ej:
```js
var timer = new Timer();
```

Configurandolo:

En este ej con esto solo basta para iniciar el temporizador:
```js
	var options = {
	    timeLeft            : 2048,
	    outputElementId     : 'my_div_for_countdown',
	    keepCounting        : 0,
	    noTimeLeftMessage   : '', // this will clean the div after countdown ends.
	    autoStart           : true,
	    outputConnector     : '-',
	    outputHoursIfZero   : true,
	    onNoTimeLeft        : function(){ alert('this is the end of the countdown'); }
	},
	timer = new Timer(options);
```

De esta forma se inicializa pero no comienza la cuenta regresiva hasta que se llame a someFunction().
```js
	var options = {
	    timeLeft            : 2048,
	    outputElementId     : 'my_div_for_countdown',
	    keepCounting        : 0,
	    noTimeLeftMessage   : '', // this will clean the div after countdown ends.
	    autoStart           : false,
	    outputConnector     : '-',
	    outputHoursIfZero   : true,
	    onNoTimeLeft        : function(){ alert('this is the end of the countdown'); }
	},
	timer = new Timer(options);

	******
	
	function someFunction() {
		timer.startTimer()
	}

	******
```

De esta forma se inicializa y comienza la cuenta regresiva, ademas la funcion callback esta definida aparte.
```js
	var options = {
	    timeLeft            : 2048,
	    outputElementId     : 'my_div_for_countdown',
	    noTimeLeftMessage   : '', // this will clean the div after countdown ends.
	    autoStart           : true,
	    outputConnector     : '-',
	    outputHoursIfZero   : true,
	    onNoTimeLeft        : someCallback,
	},
	timer = new Timer(options);

	******
	
	function someCallback() {
		alert('this is the end of the countdown');
		// a lots of code...
	}

	******
```

Implementado en:
=================

El script se desarrollo originalmente para Vapeworld.
Por favor añadir info si se utiliza para otro proyecto y si se le realizan modificaciones, mejoras o fixes.