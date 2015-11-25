function ElemContainer(elem){
    if(elem === null){
        throw "Invalid element";
    }
    this.elem = elem;
    this.displayConfig = {
        hide : 'none',
        show : 'block'
    };

    this.eventNames = {
        click : 'click',
        blur : 'blur',
        change : 'change',
        focus : 'focus'
    };

    this.getElem = function(){
        return elem;
    };

    this.val = function (val){
        if(val){
            this.getElem().value = val;
            return this;
        }
        return this.getElem().value;
    };

    this.hide = function (val){
        this.getElem().style.display = this.displayConfig.hide;
    };

    this.show = function (val){
        this.getElem().style.display = this.displayConfig.show;
    };

    this.click = function(handler){
        this.on(this.eventNames.click, handler);
    };

    this.blur = function(handler){
        this.on(this.eventNames.blur, handler);   
    };

    this.change = function(handler){
        this.on(this.eventNames.change, handler);   
    };

    this.focus = function(handler){
        this.on(this.eventNames.focus, handler);   
    };

    this.on = function(eventName, handler){
        addEvent(this.getElem(), eventName, handler);
    };

    function addEvent(el, eventName, handler){
        if (el.addEventListener) {
            el.addEventListener(eventName, handler);
        } else {
            el.attachEvent('on' + eventName, function(){
                handler.call(el);
            });
        }
    }

}

var TinyJ = function(elemDescriptor){
    if(elemDescriptor){
        return new ElemContainer(document.querySelector(elemDescriptor));
    }
};
