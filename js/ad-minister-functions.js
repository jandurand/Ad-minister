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
				
				// Restart any animation		
				//restartAnimation(b);
			}
		});

	};
	$(function(){$('.tcycle').tcycle();});
})(jQuery);