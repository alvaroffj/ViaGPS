/**
 * Title blink for web pages that allow to change title page blinks like facebook chat notificacion
 *
 * This file is part of jquery.titleBlink
 *
 * jquery.titleBlink is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * jquery.titleBlink is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with jquery.titleBlink. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Sergio Carracedo Martinez <info@sergiocarracedo.es>
 * @copyright   2010 Sergio Carracedo Martinez
 * @license     http://www.gnu.org/licenses/lgpl-3.0.txt GNU LGPL 3.0
 * @version     SVN: $Id: jquery.titleBlink.js 1 2010-08-25 17:44:00Z gasman406f $
 */

jQuery.extend({
	titleBlink : function(title,options) {
		var defaults = { 
			repeat : 5,
			delay :  800		
		};
	 	var options = $.extend(defaults, options);
		var repeatCount=0;
		var currentTitle=$(document).attr("title");

		var blinkInterval=setInterval(function() {
			if($(document).attr("title")==currentTitle) {
				$(document).attr("title",title);
			} else {				
				$(document).attr("title",currentTitle);
				repeatCount++;
				if (repeatCount==options.repeat) {
					clearInterval(blinkInterval);
				}
			}
		}, options.delay);
			
	}	
})
