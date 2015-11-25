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
```
Pero agregado a esto, si el método específico no existe, se puede escuchar un evento solo con el nombre del evento y el callback, por ejemplo:
```
var elem = TinyJ("#id");
elem.on("mouseleave", callback);
```

#### Modificar el value del elemento
Al igual que jQuery, se puede modificar u obtener el atributo value del elemento de la siguiente manera:
```
var elem = TinyJ("#id");
elem.val("hola");
elem.val() --> "hola"
```

#### Recuperar el elemento DOM original
En caso de necesitar alguna funcionalidad no existente en la librería, se puede obtener el elemento del DOM original haciendo getElem()
```
var elem = TinyJ("#id");
elem.getElem() --> DOM element
```
