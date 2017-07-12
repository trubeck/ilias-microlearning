	var showleft = null;
	var showright = null;
	var leftid = null;
	var rightid = null;
	var clickcounter = 0;
	var locked = false;
	var modal = false;

	function increaseClickCounter()
	{
		clickcounter++;
		var counters = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'movecount') ? true : false; }, 'span', document);
		for (var i = 0; i < counters.length; i++)
		{
			children = counters[i].childNodes;
			for (j = 0; j < children.length; j++)
			{
				counters[i].removeChild(children[j]);
			}
			txtNode = document.createTextNode(clickcounter); 
			counters[i].appendChild(txtNode);
		}
		var movecount = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'hidden') ? true : false; }, 'input', document);
		for (var i = 0; i < movecount.length; i++)
		{
			if (movecount[i].name.indexOf('movecount') >= 0)
			{
				movecount[i].value = clickcounter;
			}
			if (movecount[i].name.indexOf('stop') >= 0)
			{
				movecount[i].value = new Date().getTime()/1000;
			}
		}
		var moves = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'moves') ? true : false; }, 'span', document);
		for (var i = 0; i < moves.length; i++)
		{
			moves[i].innerHTML = clickcounter;
		}
		if (!cardsAvailable())
		{
			finishGame();
		}
	}
	
	function cardsAvailable()
	{
		var cards = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'card') ? true : false; }, 'div', document);
		for (i = 0; i < cards.length; i++)
		{
			if ((cards[i].style.visibility == 'visible') || (cards[i].style.visibility == '')) return true;
		}
		return false;
	}

	function hidePanels()
	{
		if (showleft && showright)
		{
			YAHOO.example.container.leftcard.hide();
			YAHOO.example.container.rightcard.hide();
			showleft.removeClass('cardinactive');
			showright.removeClass('cardinactive');
			showleft = null;
			showright = null;
			increaseClickCounter();
		}
	}
	
	function removeCards()
	{
		YAHOO.example.container.leftcard.hide();
		YAHOO.example.container.rightcard.hide();
		showleft.removeClass('cardinactive');
		showright.removeClass('cardinactive');
		showleft.setStyle('visibility', 'hidden');
		showright.setStyle('visibility', 'hidden');
		showleft = null;
		showright = null;
		increaseClickCounter();
		locked = false;
	}
	
	function finishGame()
	{
		var elem = YAHOO.util.Dom.get('memoryfinished');

		var fmgsg = $("#finishedmsg").clone();
		fmgsg.attr("id", "");
		fmgsg.text(fmgsg.text().replace(/%s/g, clickcounter));
		fmgsg.css("display", "block");
		$("#memoryfinished").prepend(fmgsg);

		elem.style.display = 'block';
	}

	var handleYes = function() {
		modal = false;
		removeCards();
		this.hide();
	};
	
	function showPanel(e, obj)
	{
		if (locked || modal) return;
		var elem = (e.target) ? e.target : e.srcElement;
		ye = new YAHOO.util.Element(elem);
		if (ye.hasClass('card') && !ye.hasClass('cardinactive'))
		{
			if (ye != showleft && ye != showright)
			{
				if (!showleft)
				{
					leftid = elem.id;
					YAHOO.example.container.leftcard.setBody(memoryCards[leftid]);
					YAHOO.example.container.leftcard.moveTo(YAHOO.util.Event.getPageX(e), YAHOO.util.Event.getPageY(e));
					YAHOO.example.container.leftcard.show();
					ye.addClass('cardinactive');
					showleft = ye;
				}
				else if (!showright)
				{
					rightid = elem.id;
					YAHOO.example.container.rightcard.setBody(memoryCards[rightid]);
					YAHOO.example.container.rightcard.moveTo(YAHOO.util.Event.getPageX(e), YAHOO.util.Event.getPageY(e));
					var region_l = YAHOO.util.Dom.getRegion('leftcard');
					var region_r = YAHOO.util.Dom.getRegion('rightcard');
					var contains = region_l.intersect(region_r);
					if (contains != null)
					{
						if (region_r.height < region_l.top)
						{
							YAHOO.example.container.rightcard.moveTo(region_r.left, region_l.top-region_r.height);
						}
						else
						{
							YAHOO.example.container.rightcard.moveTo(region_l.right, region_r.top);
						}
					}
					YAHOO.example.container.rightcard.show();
					ye.addClass('cardinactive');
					showright = ye;
					for (j = 0; j < pairs.length; j++)
					{
						if ((pairs[j][0] == leftid && pairs[j][1] == rightid) || (pairs[j][0] == rightid && pairs[j][1] == leftid))
						{
							if (pairs[j][2].length > 0)
							{
								modal = true;
								YAHOO.example.container.simpledialog1.setBody(pairs[j][2]);
								YAHOO.example.container.simpledialog1.show();
							}
							else
							{
								locked = true;
								setTimeout('removeCards()', 1500);
							}
						}
					}
				}
				else
				{
					hidePanels();
				}
			}
		}
		else
		{
			if ((elem.id != 'rightcard_h') && (elem.id != 'leftcard_h'))
			{
				hidePanels();
			}
		}
	}

	YAHOO.namespace("example.container");

	function init() {
		// Build leftcard based on markup
		fadeEffect = YAHOO.widget.ContainerEffect.FADE;
		YAHOO.example.container.leftcard = new YAHOO.widget.Panel("leftcard", { xy:[100,100],
			visible:false,
			width:"300px",
			zIndex:1000,
			draggable: true,
			constraintoviewport: true,
			close: false,
			effect:{effect:fadeEffect,duration:0.25} } );
		YAHOO.example.container.leftcard.render("memory");

		YAHOO.example.container.leftcard.dd.subscribe('startDragEvent', function(e) 
		{ 
			elem = YAHOO.util.Dom.get(leftid);
			elem.style.border = '2px black solid';
		});
		YAHOO.example.container.leftcard.dd.subscribe('endDragEvent', function(e) 
		{ 
			elem = YAHOO.util.Dom.get(leftid);
			elem.style.border = '1px black solid';
		});


		// Build rightcard based on markup
		YAHOO.example.container.rightcard = new YAHOO.widget.Panel("rightcard", { xy:[500,100],
			visible:false,
			width:"300px",
			zIndex:1000,
			draggable: true,
			constraintoviewport: true,
			close: false,
			effect:{effect:fadeEffect,duration:0.25} } );
			YAHOO.example.container.rightcard.render("memory");

		YAHOO.example.container.rightcard.dd.subscribe('startDragEvent', function(e) 
		{ 
			elem = YAHOO.util.Dom.get(rightid);
			elem.style.border = '2px black solid';
		});
		YAHOO.example.container.rightcard.dd.subscribe('endDragEvent', function(e) 
		{ 
			elem = YAHOO.util.Dom.get(rightid);
			elem.style.border = '1px black solid';
		});

		YAHOO.example.container.simpledialog1 = new YAHOO.widget.SimpleDialog("found", 
		{ 
				width: "500px",
				fixedcenter: true,
				visible: false,
				draggable: true,
				close: false,
				icon: YAHOO.widget.SimpleDialog.ICON_INFO,
				modal: true,
				zIndex:2000,
				constraintoviewport: true,
				buttons: [ { text:continueButton, handler:handleYes, isDefault:true }]
			} );
			YAHOO.example.container.simpledialog1.render("memory");
			YAHOO.util.Event.addListener(document, 'click', showPanel);
			initMatchMemoCards();
			initMatchMemoPairs();
		}
		
YAHOO.util.Event.onDOMReady(init);