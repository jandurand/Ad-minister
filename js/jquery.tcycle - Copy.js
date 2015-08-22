/*! tCycle (c) 2013 M.Alsup MIT/GPL 20131130 */
(function($){
	"use strict";
	$.fn.tcycle = function(){
		return this.each(function(){
			var i=0, c=$(this), s=c.children(), o=$.extend({speed:500,timeout:4000},c.data()), f=o.fx!='scroll',
				l=s.length, w=c.width(), z=o.speed, t=o.timeout, css={overflow:'hidden'}, p='position', a='absolute',
				rt=0, tstep=0, getRemainingTime=function(){return  tstep - ((new Date()).getTime() - ms);},
				ms=0, tid, startTimer=function(){ms = (new Date()).getTime(); tstep = t; tid = setTimeout(tx,t);},
				tfn=function(){startTimer();}, scss = $.extend({position:a,top:0}, f?{left:0}:{left:w}, o.scss);
			if (c.css(p)=='static')
				css[p]='relative';
			c.prepend($(s[0]).clone().css('visibility','hidden')).css(css);
			s.css(scss);
			if(f)
				s.hide().eq(0).show();
			else
				s.eq(0).css('left',0);
			startTimer();
			//setTimeout(tx,t);			
			
			var reload_elem_src = function(elem, src_attr) {
				var src = elem.attr(src_attr);
				elem.attr(src_attr, ''); 
				elem.attr(src_attr, src);
				//console.log('Source reloaded: ' + src);				
			};
			
			var transitionOut = function(slide) {
				slide.off('mouseenter');
				slide.off('mouseleave');
				if (f){
					slide.fadeOut(z);
				}else{
					slide.animate({left:-w},z,function(){slide.hide();});
				}			
			};
			
			var transitionIn = function(slide) {
				var w=c.width();
				slide.on('mouseenter', function(){
					rt = getRemainingTime();
					clearTimeout(tid);
				});
				slide.on('mouseleave', function(){
					ms = (new Date()).getTime(); 
					tstep = rt;
					tid = setTimeout(tx, rt); 
				});
				if (f){
					slide.fadeIn(z,tfn);
				}else{
					slide.css({'left':w,display:'block'}).animate({left:0},z,tfn);
				}

				// Reload animations
				slide.find('img[src$=".gif"]').each(function() {
					reload_elem_src($(this), 'src');
				});
				slide.find('object[type="application/x-shockwave-flash"]').each(function() {
					reload_elem_src($(this), 'data');
				});
				slide.find('object > param[name="movie"]').each(function() {
					reload_elem_src($(this), 'value');
				});				
			};
			
			function tx(){
				var n = i==(l-1) ? 0 : (i+1), w=c.width(), a=$(s[i]), b=$(s[n]);
				/*if (f){
					a.fadeOut(z);
					b.fadeIn(z,tfn);
				}else{
					a.animate({left:-w},z,function(){
						a.hide();
					});
					b.css({'left':w,display:'block'}).animate({left:0},z,tfn);
				}*/
				clearTimeout(tid);
				transitionOut(a);
				transitionIn(b);
				i = i==(l-1) ? 0 : (i+1);
			}
		});
	};
	$(function(){$('.tcycle').tcycle();});
})(jQuery);