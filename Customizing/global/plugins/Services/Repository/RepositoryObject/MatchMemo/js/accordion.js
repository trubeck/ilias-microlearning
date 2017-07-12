var Dom = YAHOO.util.Dom;
var Event = YAHOO.util.Event;

YAHOO.namespace("ilias");

YAHOO.ilias.accordion = {
	properties : {
		animation : true,
		animationDuration : 10,
		multipleOpen : false
	},

	init : function(animation,animationDuration,multipleOpen) {
		if(animation) {
			this.animation = animation;
		}
		if(animationDuration) {
			this.animationDuration = animationDuration;
		}
		if(multipleOpen) {
			this.multipleOpen = multipleOpen;
		}

		var accordionObjects = Dom.getElementsByClassName("accordion");

		if(accordionObjects.length > 0) {

			for(var i=0; i<accordionObjects.length; i++) {
				if(accordionObjects[i].nodeName == "DL") {
					var headers = accordionObjects[i].getElementsByTagName("dt");
					for (j = 1; j < headers.length; j++) headers[j].className = '';
					var bodies = headers[i].parentNode.getElementsByTagName("dd");
					for (j = 1; j < bodies.length; j++) bodies[j].className = '';
				}
				this.attachEvents(headers,i);
			}
		}
	},

	attachEvents : function(headers,nr) {
		for(var i=0; i<headers.length; i++) {
			var headerProperties = {
				objRef : headers[i],
				nr : i,
				jsObj : this
			}
			
			Event.addListener(headers[i],"click",this.clickHeader,headerProperties);
		}
	},
	
	openHeader: function(nr) {
		var accordionObjects = Dom.getElementsByClassName("accordion");
		if(accordionObjects.length > 0) {
			for (var i = 0; i < accordionObjects.length; i++) {
				if (accordionObjects[i].nodeName == "DL") {
					var headers = accordionObjects[i].getElementsByTagName("dt");
					this.clickHeader(null, {
						objRef : headers[nr],
						nr : nr,
						jsObj : this
					});
					break;
				}
			}
		}
	},

	clickHeader : function(e,headerProperties) {
		var parentObj = headerProperties.objRef.parentNode;
		var headers = parentObj.getElementsByTagName("dd"); 
		var header = headers[headerProperties.nr];

		if(Dom.hasClass(header,"open")) {
			headerProperties.jsObj.collapse(header);
		} else {
			if(headerProperties.jsObj.properties.multipleOpen) {
				headerProperties.jsObj.expand(header);
			} else {
				for(var i=0; i<headers.length; i++) {
					if(Dom.hasClass(headers[i],"open")) {
						headerProperties.jsObj.collapse(headers[i]);
					}
				}
				headerProperties.jsObj.expand(header);
			}
		}
	},
	
	collapse : function(header) {
		Dom.removeClass(Dom.getPreviousSibling(header),"selected");
		if(!this.properties.animation) {
			Dom.removeClass(header,"open");
		} else {
			this.initAnimation(header,"close");
		}
	},
	expand : function(header) {
		Dom.addClass(Dom.getPreviousSibling(header),"selected");
		if(!this.properties.animation) {
			Dom.addClass(header,"open");
		} else {
			this.initAnimation(header,"open");
		}
	},
	
	initAnimation : function(header,dir) {
		if(dir == "open") {
			Dom.setStyle(header,"visibility","hidden");
			Dom.setStyle(header,"height","auto");
			Dom.addClass(header,"open");
			var attributes = {
				height : {
					from : 0,
					to : header.offsetHeight
				}
			}
			Dom.setStyle(header,"height",0);
			Dom.setStyle(header,"visibility","visible");
			
			var animation = new YAHOO.util.Anim(header,attributes);
			animationEnd = function() {
				// leave it here
			}
			animation.duration = this.properties.animationDuration;
			animation.useSeconds = false;
			animation.onComplete.subscribe(animationEnd);
			animation.animate();
		} else if ("close") {
			var attributes = {
				height : {
					to : 0
				}
			}			
			animationEnd = function() {
				Dom.removeClass(header,"open");
			}
			var animation = new YAHOO.util.Anim(header,attributes);
			animation.duration = this.properties.animationDuration;
			animation.useSeconds = false;
			animation.onComplete.subscribe(animationEnd);
			animation.animate();
		}
	}
}

initPage = function() {
	YAHOO.ilias.accordion.init(true, 10, true);
}

Event.on(window,"load",initPage);