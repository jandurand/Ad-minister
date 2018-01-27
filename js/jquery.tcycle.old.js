/*! tCycle (c) 2013 M.Alsup MIT/GPL 20131130 */
(function($){
	"use strict";
	$.fn.tcycle = function(){
		return this.each(function(){
			var i=0, c=$(this), s=c.children(), o=$.extend({speed:500,timeout:4000},c.data()), f=o.fx!='scroll',
				l=s.length, w=c.width(), z=o.speed, t=o.timeout, css={overflow:'hidden'}, p='position', a='absolute',
				tfn=function(){setTimeout(tx,t);}, scss = $.extend({position:a,top:0}, f?{left:0}:{left:w}, o.scss);
			if (c.css(p)=='static')
				css[p]='relative';
			c.prepend($(s[0]).clone().css('visibility','hidden')).css(css);
			s.css(scss);
			if(f)
				s.hide().eq(0).show();
			else
				s.eq(0).css('left',0);
			setTimeout(tx,t);

			var reload_elem_src = function(elem, src_attr) {
				var src = elem.attr(src_attr);
				elem.attr(src_attr, ''); 
				elem.attr(src_attr, src);			
			};
			
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
				i = n;
				
				// Reload animations
				b.find('img[src$=".gif"]').each(function() {reload_elem_src($(this), 'src');});
				b.find('object[type="application/x-shockwave-flash"]').each(function() {reload_elem_src($(this), 'data');});
				b.find('object > param[name="movie"]').each(function() {reload_elem_src($(this), 'value');});
				
				/*
				b.find('img[src$=".gif"]').each(function() {
					var elem = $(this), src = elem.attr( 'src' );	
					//if ( src.match( /\.gif$/i ) ) {
						elem.attr( 'src', '' );
						elem.attr( 'src', src );
						//console.log( 'Image reloaded: ' + src );
					//}
				});
				b.find('object[type="application/x-shockwave-flash"]').each(function() {
					var elem = $(this), src = elem.attr('data');
					elem.attr('data', ''); 
					elem.attr('data', src);
				});
				b.find('object > param[name=movie]').each(function() {
					var elem = $(this), src = elem.attr('value');
					elem.attr('value', ''); 
					elem.attr('value', src);
				});
				*/
			}
		});
	};
	$(function(){$('.tcycle').tcycle();});
})(jQuery);