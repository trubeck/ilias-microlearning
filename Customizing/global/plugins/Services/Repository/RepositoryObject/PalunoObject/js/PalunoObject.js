
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

// ILIAS Object related functions
il.PalunoObject = {
	/*
	current_wrapper: '',
	current_wrapper_num: 0,
	wrappers: [],
	
	initItems: function() {
		$(".ilPalunoObjectPreviewPic").click(function() {
			il.PalunoObject.itemClicked(this);
  		});
  		$('.ilLightboxWrapper').each(function() {
  			il.PalunoObject.wrappers[il.PalunoObject.wrappers.length] = this.id;
  		});
	},
	
	itemClicked: function(item) {
		var id = item.id.substring(5);
		
		this.activateLightboxView();
		this.loadWrapper("player_wrapper_" + id);
	},

	activateLightboxView: function() {
		// copy hidden lightbox content div to the end
		$('#ilLightbox').appendTo('body');
		
		// hide content
		$("#ilAll").css("display", "none");
		
		// show lightbox content div
		$('#ilLightbox').css("display", "block");
	},

	deactivateLightboxView: function() {
		// hide lightbox content div
		$('#ilLightbox').css("display", "none");

		// show content
		$("#ilAll").css("display", "block");
		
		this.unloadWrapper();
	},

	loadWrapper: function(wrapper_id) {
		this.current_wrapper = wrapper_id;
		
		// determine number of selected wrapper
		this.current_wrapper_num = 0;
		for (var i = 0; i < il.PalunoObject.wrappers.length; i++)
		{
			if (il.PalunoObject.wrappers[i] == wrapper_id)
			{
				this.current_wrapper_num = i + 1;
			}
		}
		
		$("#" + wrapper_id).children().appendTo('#ilLightboxContent');
	},
	
	unloadWrapper: function() {
		
		if (this.current_wrapper != "") {
			$('#ilLightboxContent').children().appendTo('#' + this.current_wrapper);
		}
		this.current_wrapper = '';
	},
	
	next: function() {
		if (this.current_wrapper_num + 1 <= this.wrappers.length) {
			this.unloadWrapper();
			this.loadWrapper(this.wrappers[this.current_wrapper_num]);
		}
	},

	previous: function() {
		if (this.current_wrapper_num - 1 > 0) {
			this.unloadWrapper();
			this.loadWrapper(this.wrappers[this.current_wrapper_num - 2]);
		}
	}*/
}

//il.Util.addOnLoad(il.PalunoObject.initItems);
