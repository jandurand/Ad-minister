(function($){
	Array.prototype.rotate = (function() {
		var unshift = Array.prototype.unshift,
			splice = Array.prototype.splice;

		return function(count) {
			var len = this.length >>> 0,
				count = count >> 0;

			unshift.apply(this, splice.call(this, count % len, len));
			return this;
		};
	})();	
	
	$(document).ready(function() {
		var pageLoad = parseInt(localStorage.getItem('pageLoad'), 10) + 1;
			pageLoad = isNaN(pageLoad) ? 1 : pageLoad;
		localStorage.setItem('pageLoad', pageLoad);
		
		$('.administer-ad-container').each(function() {
			$children = $(this).children().toArray();
			if ($children.length > 1) {
				$children = $children.rotate(pageLoad % $children.length);
				$(this).append($children);
			}
		});
		
		$('.administer-ad-container .administer-ad:first-child').css('display', 'block');
	});
})(jQuery);

		

// Lazy Load Functionality
(function($){

    /**
     * Copyright 2012, Digital Fusion
     * Licensed under the MIT license.
     * http://teamdf.com/jquery-plugins/license/
     *
     * @author Sam Sehnert
     * @desc A small plugin that checks whether elements are within
     *       the user visible viewport of a web browser.
     *       only accounts for vertical position, not horizontal.
     */
    var $w = $(window);
    $.fn.visible = function(partial,hidden,direction){

        if (this.length < 1)
            return;

        var $t        = this.length > 1 ? this.eq(0) : this,
            t         = $t.get(0),
            vpWidth   = $w.width(),
            vpHeight  = $w.height(),
            direction = (direction) ? direction : 'both',
            clientSize = hidden === true ? t.offsetWidth * t.offsetHeight : true;

        if (typeof t.getBoundingClientRect === 'function'){
			// Use this native browser method, if available.
            var rec = t.getBoundingClientRect(),
                tViz = rec.top    >= 0 && rec.top    <  vpHeight,
                bViz = rec.bottom >  0 && rec.bottom <= vpHeight,
                lViz = rec.left   >= 0 && rec.left   <  vpWidth,
                rViz = rec.right  >  0 && rec.right  <= vpWidth,
                vVisible   = partial ? tViz || bViz : tViz && bViz,
                hVisible   = partial ? lViz || rViz : lViz && rViz;

			if(direction === 'both')
                return clientSize && vVisible && hVisible;
            else if(direction === 'vertical')
                return clientSize && vVisible;
            else if(direction === 'horizontal')
                return clientSize && hVisible;
        } else {
            var viewTop         = $w.scrollTop(),
                viewBottom      = viewTop + vpHeight,
                viewLeft        = $w.scrollLeft(),
                viewRight       = viewLeft + vpWidth,
                offset          = $t.offset(),
                _top            = offset.top,
                _bottom         = _top + $t.height(),
                _left           = offset.left,
                _right          = _left + $t.width(),
                compareTop      = partial === true ? _bottom : _top,
                compareBottom   = partial === true ? _top : _bottom,
                compareLeft     = partial === true ? _right : _left,
                compareRight    = partial === true ? _left : _right;

            if(direction === 'both')
                return !!clientSize && ((compareBottom <= viewBottom) && (compareTop >= viewTop)) && ((compareRight <= viewRight) && (compareLeft >= viewLeft));
            else if(direction === 'vertical')
                return !!clientSize && ((compareBottom <= viewBottom) && (compareTop >= viewTop));
            else if(direction === 'horizontal')
                return !!clientSize && ((compareRight <= viewRight) && (compareLeft >= viewLeft));
        }
    };

	var lazyLoadImage = function($img) {					
		if ( $img.hasClass('administer-lazy-load') && $img.visible(true) ) {
			var src = $img.attr('data-src');
			$img.removeClass('administer-lazy-load').css('visibility', 'visible').attr('src', src);
			$img.children('noscript').css('display', 'none');
		}		
	}
	
    $.fn.administerLazyLoad = function() {
        if (this.length < 1)
			return;
		
		return this.each(function() { 
			var $img = $(this); 
			
			if ($img.prop('tagName').toLowerCase() == 'img') {
				lazyLoadImage($img);
			}
			else {
				$img.find('img.administer-lazy-load').each(function() {	
					lazyLoadImage($(this));
				});
			}				
		});
	}
	
	var loadVisibleImages = function() {
		$('img.administer-lazy-load').administerLazyLoad();
	}
	
	$(document).ready(function() {
		$w.on({'scroll': loadVisibleImages});
		$w.on({'resize': loadVisibleImages});
		$w.on({'touchmove': loadVisibleImages});
	});
	
	$w.on({'load': loadVisibleImages});
})(jQuery);

function administerLazyLoad() {
	jQuery('img.administer-lazy-load').administerLazyLoad();
}


// tCycle Functionality
/*! tCycle (c) 2013 M.Alsup MIT/GPL 20131130 */
(function($){
	"use strict";
	$.fn.tcycle = function(){

		return this.each(function(){
			var debug=false, i=0, c=$(this), s=c.children(), o=$.extend({speed:500,timeout:4000},c.data()), f=o.fx!='scroll',
				l=s.length, w=c.width(), z=o.speed, t=o.timeout, css={overflow:'hidden'}, p='position', a='absolute',
				tid, st=(new Date()).getTime(), rt=0, tstep=t, getRemainingTime=function(){ return  tstep - ((new Date()).getTime() - st); },
				tfn=function(){ clearTimeout(tid); st = (new Date()).getTime(); tstep = t; tid = setTimeout(tx,t); }, scss = $.extend({position:a,top:0}, f?{left:0}:{left:w}, o.scss);

			var pauseOnMouseOver = function(index, elem) {
				var $slide = $(elem);
				
				// Pause slide on mouseover
				$slide.on('mouseenter', function() {
					rt = getRemainingTime();
					clearTimeout(tid);
					
					if (debug) { 
						console.log('pauseOnMouseOver: Stopped timer');
					}
				});
				$slide.on('mouseleave', function() {
					st = (new Date()).getTime(); 
					tstep = rt;
					tid = setTimeout(tx, rt); 

					if (debug) { 
						console.log('pauseOnMouseOver: Restarted timer');
					}
				});				
			}
			
			var restartAnimation = function($slide) {			
				function reloadSrc($elem, src_attr) {
					var src = $elem.attr(src_attr);
					$elem.attr(src_attr, ''); 
					$elem.attr(src_attr, src);
					
					if (debug) {
						console.log('restartAnimation: Source reloaded - ' + src);
					}
				};
			
				// Restart animations by reloading src attribute	
				$slide.find('img[src$=".gif"]').each(function() {
					reloadSrc($(this), 'src');
				});
				$slide.find('object[type="application/x-shockwave-flash"]').each(function() {
					reloadSrc($(this), 'data');
				});
				$slide.find('object > param[name="movie"]').each(function() {
					reloadSrc($(this), 'value');
				});
			}
					
			if (c.css(p)=='static')
				css[p]='relative';
			c.prepend($(s[0]).clone().css('visibility','hidden').css('opacity','0')).css(css);
			s.css(scss);
			if(f)
				s.hide().eq(0).show();
			else
				s.eq(0).css('left',0);
			s.each(pauseOnMouseOver);
			setTimeout(tx,t);
			
			function tx(){
				var n = i==(l-1) ? 0 : (i+1), w=c.width(), a=$(s[i]), b=$(s[n]);
				if (f){
					a.fadeOut(z);
					b.fadeIn(z,tfn);	
				}else{
					a.animate({left:-w},z,function(){
						a.hide();
					});
					b.css({'left':w,display:'block'}).animate({left:0},z,tfn);
				}
				i = i==(l-1) ? 0 : (i+1);
				
				if ( $.isFunction($.fn.administerLazyLoad) ) {
					b.administerLazyLoad();
				}
				
				// Restart any animation		
				//restartAnimation(b);
			}
		});

	};
	$(function(){$('.tcycle').tcycle();});
})(jQuery);