/*
* ExtJS Image Carousel with Shadowbox integration.
* This script was based on the carousel example provided
* by ExtJS found here:-
* http://www.extjs.com/playpen/ext-core-latest/examples/carousel/
* Media Manager JS
* This creates the admin GUI Media Manager Pages
*
* JSLint.com Check: 27/01/2010
*/

Ext.ns('Ext.ux');

Ext.ux.Carousel = Ext.extend(Ext.util.Observable, {
    interval: 3,
    transitionDuration: 1,
    transitionEasing: 'easeOut',

    constructor: function(elId, thumbstoShow, slideWidth, containerWidth) {

        config =  {};
        Ext.apply(this, config);
        Ext.ux.Carousel.superclass.constructor.call(this, config);

        this.addEvents(
            'beforeprev',
            'prev',
            'beforenext',
            'next',
            'change'
        );

        this.thumbstoShow = thumbstoShow;

        this.el = Ext.get(elId);
        this.slides = this.els = [];

        // just some giggery-pokery for MSIE7 support
        if (Ext.isIE7){
            this.slideSize = slideWidth;
            this.viewport = containerWidth;
        }

        this.initMarkup();
        this.initEvents();

        if(this.carouselSize > 0) {
            this.refresh();
        }
    },

    initMarkup: function() {
        var dh = Ext.DomHelper;
        
        this.els.navContainer = dh.append(this.el, {cls: 'ux-carousel-nav-container'}, true);
        this.els.container = dh.append(this.el, {cls: 'ux-carousel-container'}, true);
        this.els.slidesWrap = dh.append(this.els.container, {cls: 'ux-carousel-slides-wrap'}, true);
        
        var items = this.el.select("a");
        items.appendTo(this.els.slidesWrap).each(function(item) {
            item = item.wrap({cls: 'ux-carousel-slide'});
            this.slides.push(item);
        }, this);

        this.els.navigation = dh.append(this.els.container, {cls: 'ux-carousel-nav'}, true).hide();
        this.els.navNext = dh.append(this.els.navContainer, {tag: 'a', href: '#', cls: 'ux-carousel-nav-next'}, true);
        this.els.navPrev = dh.append(this.els.navContainer, {tag: 'a', href: '#', cls: 'ux-carousel-nav-prev'}, true);

        this.carouselSize = this.slides.length;

        // just some giggery-pokery for MSIE7 support
        if (!Ext.isIE7){
            var slideSize = Ext.query('.ux-carousel-slide');
            this.slideSize = slideSize[0].clientWidth;
            this.viewport = this.els.container.getWidth();
        }
        
        this.el.clip();
    },

    initEvents: function() {
        this.els.navPrev.on('click', function(ev) {
            ev.preventDefault();
            var target = ev.getTarget();
            target.blur();
            if(Ext.fly(target).hasClass('ux-carousel-nav-disabled')) {return;}
            this.prev();
        }, this);

        this.els.navNext.on('click', function(ev) {
            ev.preventDefault();
            var target = ev.getTarget();
            target.blur();
            if(Ext.fly(target).hasClass('ux-carousel-nav-disabled')) {return;}
            this.next();
        }, this);

    },

    prev: function() {
        if (this.fireEvent('beforeprev') === false) {
            return;
        }

        this.setSlide(this.activeSlide - 1);
        this.fireEvent('prev', this.activeSlide);
        return this;
    },

    next: function() {
        if(this.fireEvent('beforenext') === false) {
            return;
        }

        this.setSlide(this.activeSlide + 1);
        this.fireEvent('next', this.activeSlide);
        return this;
    },

    clear: function() {
        this.els.slidesWrap.update('');
        this.slides = [];
        this.carouselSize = 0;
        return this;
    },

    refresh: function() {
        this.carouselSize = this.slides.length;
        var sliderWidth = (this.carouselSize * this.slideSize);
        this.els.slidesWrap.setWidth(sliderWidth + 'px');
        if(this.carouselSize > 0) {
            this.els.navigation.show();
            this.activeSlide = 0;
            this.setSlide(0, true);
        }
        return this;
    },

    setSlide: function(index, initial) {
        if(!this.slides[index]) {
            return;
        }
        else {
            if(index < 0) {
                index = this.carouselSize-1;
            }
            else if(index > this.carouselSize-1) {
                index = 0;
            }
        }
        if(!this.slides[index]) {
            return;
        }

        var offset = index * this.slideSize;
        if (!initial) {
                var xNew = (-1 * offset) + this.els.container.getX();
                this.els.slidesWrap.stopFx(false);

                // this does not seem to be required.
                //if (Math.abs(xNew) < (this.viewport)){
                    this.els.slidesWrap.shift({
                        duration: this.transitionDuration,
                        x: xNew,
                        easing: this.transitionEasing
                    });
                //}
        }
        else {
            this.els.slidesWrap.setStyle('left', '0');
        }

        this.activeSlide = index;
        this.updateNav();
        this.fireEvent('change', this.slides[index], index);
    },

    updateNav: function() {
        this.els.navPrev.removeClass('ux-carousel-nav-disabled');
        this.els.navNext.removeClass('ux-carousel-nav-disabled');

        if(this.activeSlide === 0) {
            this.els.navPrev.addClass('ux-carousel-nav-disabled');
        }
        if(this.activeSlide === this.carouselSize-this.thumbstoShow) {
            this.els.navNext.addClass('ux-carousel-nav-disabled');
        }
    }
});