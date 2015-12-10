# Summa Tiny J (Javascript)

Esta librería javascript incluye funcionalidad simple de jQuery como puede ser referenciar a un elemento del dom. Evita tener que incluir una librería tan pesada como jQuery y tener funcionalidad básica.

## Uso
Incluir la librería en su código:
```
<script type="text/javascript" src="summaTinyJ.js"></script>
```

#### Recuperar elementos
Al igual que jQuery se pueden recuperar elementos haciendo referencia a su id, clase o selectores:
```
TinyJ("input[type=text]");
TinyJ("#id");
TinyJ(".class");
```
#### Mostrar / ocultar elementos
Los elementos retornados responden a ciertos métodos, entre ellos los que permiten ocultar y mostrar el elemento en la página:
```
var elem = TinyJ("#id");
elem.show();
elem.hide();
```

#### Agregar eventos
Los elementos permiten escuchar ciertos eventos de manera directa y agregarles una función de callback. Los que existen actualmente son:
```
var elem = TinyJ("#id");
elem.click(callback);
elem.blur(callback);
elem.change(callback);
elem.focus(callback);
elem.focusout(callback);
elem.focus(callback);

```
Pero agregado a esto, si el método específico no existe, se puede escuchar un evento solo con el nombre del evento y el callback, por ejemplo:
```
var elem = TinyJ("#id");
elem.on("mouseleave", callback);
```

#### Modificar attributos del elemento
Al igual que jQuery, se puede modificar u obtener el atributo 'value' del elemento de la siguiente manera:
```
var elem = TinyJ("#id");
elem.val("hola");
elem.val() --> "hola"
```
También se puede modificar el valor del atributo 'id':
```
var elem = TinyJ("#id");
elem.id("nuevoId");
```
O el atributo class. Este permite agregar o quitar clases del mismo:
```
var elem = TinyJ("#id");
elem.addClass("nuevoId");
elem.attribute('class'); --> 'nuevoId'
elem.removeClass("nuevoId");
elem.attribute('class'); --> ''
```
O modificar los atributos de edicion (específicos para inputs):
```
var elem = TinyJ("#id");
elem.disable();
elem.enable();
```
En caso de faltar el método directo se puede modificar y recuperar cualquier atributo con el nombre del mismo:
```
var elem = TinyJ("#id");
elem.attribute('hola'); --> undefined
elem.attribute('hola', 'value');
elem.attribute('hola'); --> 'value'
```
O borrar el attributo por completo:
```
var elem = TinyJ("#id");
elem.removeAttribute('hola');
```

#### Modificar el html dentro del elemento
Se puede modificar el html del element (innerHTML) así:
```
var elem = TinyJ("#id");
elem.html('<div id="uno"></div>');
```
O sinó vaciar el elemento, equivalente a hacer .html(""):
```
var elem = TinyJ("#id");
elem.empty();
```
O agregar un elemento html como hijo:
```
var elem = TinyJ("#id");
elem.appendChild(htmlElement);
```

#### Recuperar el elemento DOM original
En caso de necesitar alguna funcionalidad no existente en la librería, se puede obtener el elemento del DOM original haciendo getElem()
```
var elem = TinyJ("#id");
elem.getElem() --> DOM element
```
##### Evaluar selector
Es posible también evaluar si un selector está seleccionado o pedir el seleccionado de un combo:
```
var elem = TinyJ("#id");
elem.isChecked();
elem.getSelectedOption();
```
