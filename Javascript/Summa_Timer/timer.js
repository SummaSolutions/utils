/**
 * Created for  vapeworld.com-1.9.
 * @author:     mhidalgo@summasolutions.net
 * Date:        24/09/14
 * Time:        12:13
 * @copyright   Copyright (c) 2014 Summa Solutions (http://www.summasolutions.net)
 */
var Timer = Class.create();

Timer.defaultOptions = {
    timeLeft            : 60,
    outputElementId     : 'countdown_time',
    keepCounting        : 1,
    noTimeLeftMessage   : 'The time is over',
    autoStart           : false,
    debug               : false,
    outputConnector     : ':',
    outputHoursIfZero   : false,
    outputMinutesIfZero : true,
    onNoTimeLeft        : function(){}
}

Timer.prototype = {
    initialize : function(options,element) {
        this.options = Object.extend({
            timeLeft            : Timer.defaultOptions.timeLeft,
            outputElementId     : Timer.defaultOptions.outputElementId,
            keepCounting        : Timer.defaultOptions.keepCounting,
            noTimeLeftMessage   : Timer.defaultOptions.noTimeLeftMessage,
            autoStart           : Timer.defaultOptions.autoStart,
            debug               : Timer.defaultOptions.debug,
            outputConnector     : Timer.defaultOptions.outputConnector,
            outputHoursIfZero   : Timer.defaultOptions.outputHoursIfZero,
            outputMinutesIfZero : Timer.defaultOptions.outputMinutesIfZero,
            onNoTimeLeft        : Timer.defaultOptions.onNoTimeLeft
        }, options || {});
        this.options.isJqueryReady = typeof jQuery != 'undefined';
        this.outputElement = element || false;
        if (!this.outputElement) {
            if (this.options.isJqueryReady) {
                this.outputElement = jQuery('#' + this.options.outputElementId);
            } else {
                this.outputElement = $(this.options.outputElementId);
            }
        }
        if(this.options.autoStart) {
            this.startTimer();
        }
        this.debugging('initialize');
    },
    addLeadingZero : function(number) {
        if(number.toString().length < 2) {
            return '0' + number;
        } else {
            return number;
        }
    },
    formatOutput : function() {
        var hours, minutes, seconds, output = '';
        seconds = this.options.timeLeft % 60;
        minutes = Math.floor(this.options.timeLeft / 60) % 60;
        hours = Math.floor(this.options.timeLeft / 3600);

        seconds = this.addLeadingZero( seconds );
        minutes = this.addLeadingZero( minutes );
        hours = this.addLeadingZero( hours );

        if((hours == "00" && this.options.outputHoursIfZero) || hours != "00") {
            output += hours + this.options.outputConnector;
        }
        if((minutes == "00" && this.options.outputMinutesIfZero) || minutes != "00") {
            output += minutes + this.options.outputConnector;
        }
        output += seconds;

        this.debugging('formatOutput',output);

        return output;
    },
    showTimeLeft : function() {
        this.fillOutputElement(this.formatOutput());
    },
    noTimeLeft : function() {
        this.fillOutputElement(this.options.noTimeLeftMessage);
        if(typeof this.options.onNoTimeLeft != 'undefined') {
            this.options.onNoTimeLeft();
        }
    },
    fillOutputElement : function(content) {
        if(this.options.isJqueryReady) {
            this.outputElement.html(content);
        } else {
            this.outputElement.update(content);
        }
        this.debugging('fillOutputElement',this.outputElement);
        this.debugging('fillOutputElement',content);
    },
    countdown : function() {
        if(this.options.timeLeft < 2) {
            this.options.keepCounting = 0;
        }
        this.options.timeLeft = this.options.timeLeft - 1;
        this.debugging('countdown');
    },
    count : function() {
        this.countdown();
        this.showTimeLeft();
    },
    timer : function() {
        this.count();

        if(this.options.keepCounting) {
            var $this = this;
            setTimeout(function(){$this.timer();}, 1000);
        } else {
            this.noTimeLeft();
        }
        this.debugging('timer');
    },
    setTimeLeft : function(time) {
        this.options.timeLeft = time;
        if(this.options.keepCounting != 0) {
            this.timer();
        }
        return this;
    },
    startTimer : function(time,elementId) {
        var timeTo = time || false,
            outputElementIdTo = elementId || false;

        if(timeTo) {
            this.options.timeLeft = timeTo;
        }
        if(outputElementIdTo) {
            this.options.outputElementId = outputElementIdTo;
        }
        if(this.options.keepCounting == 0) {
            this.options.keepCounting = 1;
        }
        this.timer();
        this.debugging('startTimer');
        return this;
    },
    debugging : function(from, param) {
        if(this.options.debug){
            console.log(from);
            var paramToDebug = param || false;
            if(paramToDebug){
                console.log(paramToDebug);
            } else {
                console.log(this);
                console.log(this.options);
            }
        }
    }
}