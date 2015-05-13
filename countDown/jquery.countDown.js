(function ($){
$.fn.countDown = function(args){
    // arguments
    // seconds : quantity of seconds to countdown
    // restart: default is false, when the count down is 0, restart the counter.
    // minTimer : element (class name or id name) to toggle the minute value. Default is class="minutes".
    // secTimer : element (class name or id name) to toggle the seconds value. Default is class="seconds".
    this.initTime = 600;
    this.seconds = 600;
    this.secondsElement = false;
    this.minutesElement = false;
    this.intervalFn = false;
    this.actualMins = 0;
    this.actualSecs = 0;
    this.error = false;
    this.restart = false;
    this.interval = function(){
        var _this = this;
        this.intervalFn = setInterval(function () {
            _this.seconds -= 1;
            _this.setTime();
        }, 1000);
    };
    this.setTime = function(){
        this.actualMins = parseInt(this.seconds / 60);
        this.actualSecs = this.seconds % 60;
        if (this.actualSecs < 10){
            this.secondsElement.text('0'+this.actualSecs);
        }
        else{
            this.secondsElement.text(this.actualSecs);
        }
        if (this.actualMins < 10){
            this.minutesElement.text('0'+this.actualMins);
        }
        else{
            this.minutesElement.text(this.actualMins);
        }
        if (!this.seconds){
            if (this.restart){
                this.resetInterval();
            }
            else{
                this.removeInterval();
            }
            this.trigger('countDown', []);
        }
    };
    this.removeInterval = function(){
        clearInterval(this.intervalFn);
    };
    this.resetInterval = function(){
        this.removeInterval();
        this.seconds = this.initTime;
        this.interval();
    };
    this.init = function(options){
        if (options.restart){
            this.restart = true;
        }
        if (options.seconds && !isNaN(options.seconds)){
            this.seconds = parseInt(options.seconds);
        }
        if (options.minTimer && $(options.minTimer).length){
            this.minutesElement = $(options.minTimer);
        }
        else{
            if (!this.find('.minutes').length && !this.find('.seconds').length){
                this.append('<span class="minutes"></span>');
                this.append('<span>:</span>');
                this.append('<span class="seconds"></span>');
                console.log($(this).find('.minutes'));
                this.minutesElement = $(this).find('.minutes');
                this.secondsElement = $(this).find('.seconds');
            }
            else{
                if (this.find('.minutes').length && this.find('.seconds').length){
                    this.minutesElement = this.find('.minutes');
                    this.secondsElement = this.find('.seconds')
                }
                else{
                    console.log('Mintes or Seconds element not found');
                }
            }
        }
        if (options.secTimer && $(options.secTimer).length){
            this.secondsElement = $(options.secTimer);
        }
        if (!this.minutesElement.length){
            console.log('Minutes counter not found');
            this.error = true;
            return false;
        }
        if (!this.secondsElement.length){
            console.log('Seconds counter not found');
            this.error = true;
            return false;
        }
        if (!this.error){
            this.setTime();
            this.interval();
        }
    };
    this.init(args);
};
})($);