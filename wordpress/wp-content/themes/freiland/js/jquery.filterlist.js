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

	var filterOptions = {
		'duration' : 1000,
		beforeShow : function(){},
		afterShow : function(){},
		getNewCurElement : function(filter,oldCur,shown){ 
			return (shown.index(oldCur)==-1?shown.first():oldCur); 
		}
	};

	var container;
	var curElement;
	var topmargin;
	var stickToCurrentElement = false;

	var methods = {
		init : function( cur ) { 
			container = this;
			curElement = cur;
			if ( !$.isFunction(this) ){
				topmargin = container.offset().top;
				stickToCurrentElement = true;
			}
			return this;
		},
		filter : function(options){
	 		if ( options )
        			$.extend( filterOptions, options );
			return filterElements.apply(this,[filterOptions]);
		}
	};

	function getHeightCorrection(toShow){
		if ( curElement.length == 0)
			return 0;
		
		if ( toShow.index(curElement) == -1 )
			return curElement.offset().top - topmargin;

		var upper = curElement.prevAll(':visible').first();

		if ( upper.length > 0 )
			return upper.offset().top + upper.height() - topmargin;

		return curElement.closest(':visible').offset().top - topmargin;
	}

	function findElement(element,allElements,toShow,hidden,prev){
		var start = allElements.index(element) + (prev?0:1);
		if ( start < 0 || start >= allElements.length ) return $();

		var elements = allElements.slice((prev?0:start),(prev?start:allElements.length));
		var newElement = $();
		(prev?$(elements.get().reverse()):elements).each(function(){
			if ( toShow.index($(this)) == -1 &&
			     hidden.index($(this)) == -1 ){
				newElement = $(this);
				return false;
			}
		});
		return newElement;
	}

	function findPrevElement(element,allElements,toShow,hidden){
		return findElement(element,allElements,toShow,hidden,true);
	}

	function findNextElement(element,allElements,toShow,hidden){
		return findElement(element,allElements,toShow,hidden,false);
	}

	function getElementId(element,allElements){
		var element_id = element.attr('id'); 
		if ( !element_id ){
			element_id = 'element_'+allElements.index(element);
			element.attr('id',element_id);
		}
		return element_id;
	}

	// returns the group id of the given element
	function getGroupId(element,allElements,toShow,hidden){
		var upper_id = "begin"; 
		var lower_id = "end";

		// find prev element from this not in toShow/toHide
		var upper = findPrevElement(element,allElements,toShow,hidden);

		if ( upper.length > 0 )
			upper_id = getElementId(upper,allElements);

		// find next element from this not in toShow/toHide
		var lower = findNextElement(element,allElements,toShow,hidden);

		if ( lower.length > 0 )
			lower_id = getElementId(lower,allElements); 

		return upper_id+'_'+lower_id;
	}

	// gets the height of the element from its data field if possible
	// this is done because getting the height of a hidden element
	// takes a long time
	function getElementHeight(element){
		var dataHeight = element.data('height');
		var height = (dataHeight?parseInt(dataHeight,10):element.height());
		if ( !dataHeight ) element.data('height',height);
		return height;
	}

	function computeNewHeights(allElements,toHide,toShow,hidden,shown){
		var groups = {};
		// compute show/hide groups

		var refHeight = 0;
		var curIndex = allElements.index(curElement);
		var group_id;
		var oldElement;

		toHide.add(toShow).each(function(){
			// get new group id if this is not the next of the old element	
			if ( !oldElement || $(this).prev().length == 0 ||
				getElementId(oldElement,allElements) != getElementId($(this).prev(),allElements) )
				group_id = getGroupId($(this),allElements,toShow,hidden);

			var group;
			if ( group_id in groups )
				group = groups[group_id];
			else{
				group = {
					toHide : [],
					toShow : [],
					hideHeight: 0,
					showHeight : 0
				};
				groups[group_id] = group;
			}

			var height = getElementHeight($(this));
			if ( toShow.index($(this)) != -1 ){
				if ( stickToCurrentElement && curElement.length > 0 && allElements.index($(this)) < curIndex )
					refHeight += height;
				group.toShow.push($(this));
				group.showHeight += height;
			}else{
				if ( stickToCurrentElement && curElement.length > 0 && allElements.index($(this)) < curIndex )
					refHeight -= height;
				$(this).data('newheight',height);
				group.toHide.push($(this));
				group.hideHeight += height;
			}
			oldElement = $(this);
		});

		if ( stickToCurrentElement ){
			refHeight += getHeightCorrection(toShow);	
			container.data('refHeight',refHeight);
		}

		for(key in groups) {
			console.log('Group:'+key);
			var group = groups[key];
			var diff = group.hideHeight - group.showHeight;
			if ( group.showHeight > group.hideHeight ){
				for (var i = 0,j = group.toShow.length; i < j; i++) {
					var element = group.toShow[i];
					var height = getElementHeight(element);
					element.data('height',height);
					var newHeight = Math.round(Math.abs(
						height + (height / group.showHeight)*diff));
					element.height(newHeight);
				}
			}else{
				for (var i = 0,j = group.toHide.length; i < j; i++) {
					var element = group.toHide[i];
					var height = getElementHeight(element);
					var newHeight = Math.round(Math.abs(
						height - (height / group.hideHeight)*diff));
					element.data('newheight',newHeight);
				}
			}
		}
	}

	// filter elements for according to given filter selector
	function filterElements(options){
		var allElements = this;
		var hidden,shown, toShow;

		// keep all elements matching filter
		hidden = allElements.not(options.filter);
		shown = allElements.filter(options.filter);

		var toShow = shown.not(':visible');
		var toHide = hidden.filter(':visible');
		var elementsToShow = ( allElements.length != hidden.length );

		if ( elementsToShow ){
			curElement = options.getNewCurElement(options.filter,curElement,shown);
			computeNewHeights(allElements,toHide,toShow,hidden,shown);

			toHide.each(function(){
				$(this).animate({opacity: 0, height: parseInt($(this).data('newheight'),10)},
					options.duration);
			});
			if ( stickToCurrentElement ){
				var refHeight = parseInt(container.data('refHeight'),10);
				if ( refHeight < 0 )
					container.animate({top: "-="+ refHeight},options.duration);
			}
		}else
			toHide.fadeOut(options.duration);

		toHide.promise().done(function(){

			toHide.hide();
			toHide.each(function(){
				$(this).css('height',getElementHeight($(this)));
			});

			options.beforeShow(elementsToShow);
			if ( !elementsToShow )
				return;

			toShow.show();

			toShow.each(function(){
				$(this).animate({opacity: 1,height: getElementHeight($(this))},
					options.duration,function(){
					if ( this.style && this.style.removeAttribute)
						this.style.removeAttribute("filter");
				});
			});

			if ( stickToCurrentElement ){
				var refHeight = parseInt(container.data('refHeight'),10);
				if ( refHeight > 0 )
					container.animate({top: "-="+ refHeight},options.duration);
			}

			toShow.promise().done(options.afterShow);
		});

		return curElement;
	};

	$.filterlist = $.fn.filterlist = function( method ) {
    
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.filterlist' );
		}
	};

})( jQuery );
