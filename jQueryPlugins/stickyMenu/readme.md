StickyMenu Plugin
=================

Crea un collapsible sticky Menu que acompaña mientras se scrollea la página.

URL: https://github.com/SummaSolutions/utils/jQueryPlugins/stickyMenu/

Instrucciones:
=================

Ejemplo de Html:
<div class="quick-links">
	<div class="sidebar-block-title">
		<h3 class="title">Quick Links <span class="toggle-links">+</span></h3>
	</div>
	<div>
		<ul>
			<li>
				<a href="#pp-payments-pro">Paypal Payments Pro</a>
			</li>
			<li>
				<a href="#pp-checkout-express">PayPal Express Checkout</a>
			</li>
			<li>
				<a href="#relevant-work">Relevant Work</a>
			</li>
		</ul>
	</div>
</div>

Llamado del plugin:


```javascript
jQuery('.quick-links').stickyMenu( {
	title: '.quick-links .title',
	list: '.quick-links ul'
});
```

Estos son los Argumentos necesarios para la ejecución del plugin, donde:
"title" es el bloque que contendrá el titulo, el cual será donde se clickeará para mostrar o esconder el menu.
"list" es el bloque que contiene el menu, es el que se esconde/muestra.

Options/Arguments:
"toggle" : elemento dentro del título al cual contiene un "+" y es cambiado por un '-' cuando se colapsa el menú.

"smoothScrolling" : habilita una transición de scroll a links internos (anchor).
"scrollingSpeed" : determina la velocidad de la transición del scroll (requiere smoothScrolling en true).
"links" : elementos html "a" con que se lanzará el smoothScrolling.
"extraTopMargin" : espacio superior al elemento que se realizó el smoothScrooling.

top: Posición en la que se fijara el menú cuando este acompañe el scroll.