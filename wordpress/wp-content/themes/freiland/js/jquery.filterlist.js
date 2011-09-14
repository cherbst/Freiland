/*
 * jQuery Filter list Plugin v0.1
 * http://www.github.com/filterlist
 *
 * Copyright (c) 2009-2010 Christoph Herbst
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * Date: 2011-08-10
 */

(function( $ ){


	var methods = {
		filter : function(options){
			var settings = {
				'duration' : 1000,
				horizontal : false,
				container : null,
				stickToCurrentElement : false,
				curElement : null,
				margin: 0,
				before : function(){},
				after : function(){},
				getNewCurElement : function(filter,oldCur,shown){ 
					return (shown.index(oldCur)==-1?shown.first():oldCur); 
				}
			};

	 		if ( options )
        			$.extend( settings, options );
			if ( settings.container ){
				if ( !settings.curElement ){
					settings.curElement = settings.container.data('curElement');
					if ( !settings.curElement )
						settings.curElement = this.filter(isVisible).first();
				}
				settings.stickToCurrentElement = true;

				if ( settings.margin == 0 )
					settings.margin = ( settings.horizontal?
						settings.curElement.offset().left:
						settings.curElement.offset().top);
			}
			settings.curElement = filterElements.apply(this,[settings]);
			if ( settings.container )
				settings.container.data('curElement',settings.curElement);

			return settings.curElement;
		}
	};

	function getExtentCorrection(toShow,options){
		if ( options.curElement.length == 0)
			return 0;
		
		return (options.horizontal?
			options.curElement.offset().left:options.curElement.offset().top) - options.margin;
	}

	// gets the extent of the element from its data field if possible
	// this is done because getting the extent of a hidden element
	// takes a long time
	function getElementExtent(element,horizontal,nocache){
		var dataExtent = (nocache?null:element.data('extent'));
		var extent = (dataExtent?parseInt(dataExtent,10):(horizontal?element.width():element.height()));
		if ( !dataExtent && !nocache ) element.data('extent',extent);
		return extent;
	}

	function setElementExtent(element,extent,horizontal){
		if ( horizontal )
			element.width(extent);
		else
			element.height(extent);
	}

	function getRefExtent(allElements,toHide,toShow,options){
		var curIndex = allElements.index(options.curElement);
		var refExtent = 0;

		toHide.each(function(){
			extent = getElementExtent($(this),options.horizontal,true);
			if ( options.stickToCurrentElement && 
				options.curElement.length > 0 && allElements.index($(this)) < curIndex )
				refExtent -= extent;
		});

		toShow.each(function(){
			curExtent = getElementExtent($(this),options.horizontal,true);
			extent = getElementExtent($(this),options.horizontal) - curExtent;
			if ( options.stickToCurrentElement && 
				options.curElement.length > 0 && allElements.index($(this)) < curIndex )
				refExtent += extent;
		});

		return refExtent;
	}

	function isVisible(){
		return parseFloat($(this).css('opacity'),10) != 0.0;
	}

	function isHidden(){
		return parseFloat($(this).css('opacity'),10) != 1.0;
	}

	// filter elements for according to given filter selector
	function filterElements(options){
		var allElements = this;
		var hidden,shown, toShow;
		var refExtent = 0;

		// keep all elements matching filter
		hidden = allElements.not(options.filter);
		shown = allElements.filter(options.filter);

		allElements.stop(true,false);
		if ( options.stickToCurrentElement )
			options.container.stop(true,false);

		var toShow = shown.filter(isHidden);
		var toHide = hidden.filter(isVisible);

		var elementsToShow = ( allElements.length != hidden.length );
		var allHidden = ( allElements.filter(isVisible).length == 0 );

		options.before(elementsToShow);

		options.curElement = options.getNewCurElement(options.filter,options.curElement,shown);
		if ( options.stickToCurrentElement )
			refExtent = getRefExtent(allElements,toHide,toShow,options) +
				 getExtentCorrection(toShow,options);

		toHide.each(function(){
			obj = {opacity: 0 };
			extent = getElementExtent($(this),options.horizontal);
			if ( elementsToShow ){
				if ( options.horizontal )
					obj.width = 0;
				else
					obj.height = 0;
			}

			$(this).animate(obj,options.duration,function(){
				setElementExtent($(this),0,options.horizontal);
			});
		});

		toShow.each(function(){
			obj = {opacity: 1 };
			extent = getElementExtent($(this),options.horizontal);
			if ( allHidden ){
				setElementExtent($(this),extent,options.horizontal);
			}else{
				if ( options.horizontal )
					obj.width = extent;
				else
					obj.height = extent;
			}

			$(this).animate(obj,options.duration,function(){
				if ( this.style && this.style.removeAttribute)
					this.style.removeAttribute("filter");
			});
		});

		if ( options.stickToCurrentElement && elementsToShow ){
			if ( allHidden ){
				off = options.container.offset();
				if ( options.horizontal )
					off.left -= refExtent;
				else
					off.top -= refExtent;

				options.container.offset(off);
			}else{
				if ( options.horizontal )
					options.container.animate({left: "-="+ refExtent},options.duration);
				else
					options.container.animate({top: "-="+ refExtent},options.duration);
			}
		}

		allElements.promise().done(function(){
			options.after(allElements.filter(isVisible).length > 0);
		});

		return options.curElement;
	};

	$.fn.filterlist = function( method ) {
    
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.filter.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.filterlist' );
		}
	};

})( jQuery );
