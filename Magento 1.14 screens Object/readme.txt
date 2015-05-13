Es un objeto de javascript que tiene que ir en el inicio del app.js o en algun script que se cargue luego de modernizer y enquire.js.
Utiliza jQuery y mordernizer.
valida el screen actual seteando distintos valores de variables dependiendo los valores de los medias quieries, retornando valores booleanos.

Setea isTouch si el el dispositivo es touch.

Existen 2 tipos de validaciones:
1)
isDesktop: Width > bp.large
isTablet Width <= bp.large && Width > bp.medium
isMobile width <= bp.medium

2)
xsmall: W <= bp.xsmall
small : W <= bp.small && W > bp.xsmall
medium : W <= bp.medium && W > bp.small
large : W <= bp.large && W > bp.medium
xlarge : W > bp.xlarge