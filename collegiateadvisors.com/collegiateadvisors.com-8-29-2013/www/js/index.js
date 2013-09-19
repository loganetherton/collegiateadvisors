//------------------------------------------------------------------------
// Name: Financial_Facts
// Desc: Handles the retrieving and showing of financial facts.
//------------------------------------------------------------------------
Financial_Facts =
{

	//----------------------------------------------------------------------
	// Public Variables
	//----------------------------------------------------------------------
	facts_array:      null,   // Populated with the financial facts that will be displayed.
	current_index:    0,      // The current index into the facts array.
	update_speed:     6000,   // The speed at which the facts will update.
	anim_sequence:    null,   // The animation sequence.


	//----------------------------------------------------------------------
	// Public Member Functions
	//----------------------------------------------------------------------
	//------------------------------------------------------------------------
	// Name: initialize()
	// Desc: Used to initialize the system. Must be called before anything
	//       else.
	//------------------------------------------------------------------------
	initialize: function()
	{

		// Set up the array.
		Financial_Facts.facts_array = [ "The cost of education has increased more than 330 percent over the last 20 years.",
		                                "The median family income has increased 10 percent over the last 20 years.",
										"Only 18 percent of students now graduate within 4 years.",
										"Over 50 percent of students change majors at least once.",
										"70 percent of America believes that in the near future they will not be able to afford a college education.",
										"Families are now spending at least 30 percent of their annual income to afford a private college.",
										"52 percent of students are using credit cards to pay for books and supplies.",
										"23 percent of students are using credit cards to pay for tuition.",
										"33 percent of students who have credit cards have balances of over $5,000.",
										"Student loan debt is rising faster than the cost of living or health care costs.",
										"Between 1993 and 2004, the average debt for college graduates with loans increased by 107 percent.",
										"Only 53 percent of students who enter college emerge with a bachelor's degree.",
										"The FAFSA form is the most difficult and confusing federal form to fill out.",
										"The student aid system is not working for most people.",
										"I took a calculus class and never saw the professor.",
										"It's time to put consumer behavior into the college education.",
										"The class of 2006 recorded the sharpest drop in SAT scores in 31 years.",
										"Over the last 8 years, the cost of college has increased 42 percent while median household income has decreased 2 percent." ];
		
		// Set the financial facts to invisible initially.
		LW_DOM_Library.setStyle( document.getElementById( "financial_fact" ), "opacity", 0 );
		
		// Set up the animation objects.
		var in_anim = new LW_Animation( document.getElementById( "financial_fact" ), 500 );
		in_anim.controller.opacity.to = 1;
		
		var out_anim = new LW_Animation( document.getElementById( "financial_fact" ), 500 );
		out_anim.controller.delay = Financial_Facts.update_speed;
		out_anim.controller.opacity.to = 0;
		
		// Set up the animation sequence.
		Financial_Facts.anim_sequence = new LW_Animation_Sequence( { loop: true } );
		
		// Whenever we restart the sequence, update the fact.
		Financial_Facts.anim_sequence.onStart = Financial_Facts.update;
		
		// Add the animations.
		Financial_Facts.anim_sequence.addAnimation( in_anim );
		Financial_Facts.anim_sequence.addAnimation( out_anim );
		
		// Start the animation sequence.
		Financial_Facts.anim_sequence.start();
		
	},
	
	
	//------------------------------------------------------------------------
	// Name: update()
	// Desc: Update the text.
	//------------------------------------------------------------------------
	update: function()
	{
		
		// Get the new index.
		Financial_Facts.current_index++;
		
		if ( Financial_Facts.current_index >= Financial_Facts.facts_array.length )
			Financial_Facts.current_index = 0;
		
		// Change the text.
		document.getElementById( "financial_fact" ).innerHTML = '"' + Financial_Facts.facts_array[Financial_Facts.current_index] + '"';
		
	}

}

// Set to initialize when the page loads.
LW_Events_Handler.addEvent( window, "onload", Financial_Facts.initialize );//------------------------------------------------------------------------
// Name: News_Articles
// Desc: Handles the animation events for news articles.
//------------------------------------------------------------------------
News_Articles =
{

	//----------------------------------------------------------------------
	// Public Variables
	//----------------------------------------------------------------------
	articles:     [],     // The news articles that we are managing..
	anim_speed:   1000,   // The speed at which the animations will play.


	//----------------------------------------------------------------------
	// Public Member Functions
	//----------------------------------------------------------------------
	//------------------------------------------------------------------------
	// Name: addArticle()
	// Desc: Used to add an article to the system.
	//------------------------------------------------------------------------
	addArticle: function( id, onload_data, link, toggle_text )
	{
			
		article_element = document.getElementById( "article" + id );
		toggle_element = document.getElementById( "toggle_article" + id );
		
		if ( !toggle_text )
			toggle_text = [ null, null ];
		else
			toggle_element.innerHTML = toggle_text[0];
		
		// Get the height of the article.
		LW_DOM_Library.setStyle( article_element, "height", "auto" );		
		var article_height = LW_DOM_Library.getHeight( article_element );			
		LW_DOM_Library.setStyle( article_element, "height", "20px" );
		
		// If the article's height is greater than 30 add the toggle element
		// and add it to be managed.
		if ( article_height < 30 && !onload_data && !link )
		{
			
			LW_DOM_Library.setStyle( toggle_element, "display", "none" );
			
			if ( link )
				LW_Events_Handler.addEvent( toggle_element, "onclick", function(){ window.open( link ); return false; } );
			
		}
		else if ( article_height > 30 || onload_data || link )
		{
			
			if ( link )
				LW_Events_Handler.addEvent( toggle_element, "onclick", function(){ window.open( link ); return false; } );
			else
				LW_Events_Handler.addEvent( toggle_element, "onclick", new Function( "return News_Articles.toggleArticle( " + id + ", '" + toggle_text[0] + "', '" + toggle_text[1] + "' );" ) );
		
			News_Articles.articles[id] = [ article_element, toggle_element ];
				
		}

		if ( onload_data )
			article_element.innerHTML += onload_data;
			
		
	},
	
	
	//------------------------------------------------------------------------
	// Name: toggleArticle()
	// Desc: Toggles whether an article is visible or not.
	//------------------------------------------------------------------------
	toggleArticle: function( id, toggle_text_open, toggle_text_close )
	{
		
		// Get the article's element.
		var article_element = News_Articles.articles[id][0];	
		var toggle_element = News_Articles.articles[id][1];

		// Set up the animation.
		var animation = new LW_Animation( article_element, 1000 );		
		animation.controller.height.ease = LW_Animation.STRONG_EASE_OUT;
		
		// Opening or closing?
		if ( LW_DOM_Library.getHeight( article_element ) > 20 )
		{
			
			// Set up the animation.
			animation.controller.height.to = 20;
			animation.controller.height.ease = LW_Animation.STRONG_EASE_OUT;
			
			// Change the toggle text.
			if ( toggle_text_open )
				toggle_element.innerHTML = toggle_text_open;
			else
				toggle_element.innerHTML = "(watch the video by clicking here)";				
			
		}
		else
		{
			
			// Get the new height.
			LW_DOM_Library.setStyle( article_element, "height", "auto" );			
			var article_height = LW_DOM_Library.getHeight( article_element );
			LW_DOM_Library.setStyle( article_element, "height", "20px" );
			
			// Set up the animation.
			animation.controller.height.to = article_height;
			
			// Change the toggle text.
			if ( toggle_text_close )
				toggle_element.innerHTML = toggle_text_close;
			else
				toggle_element.innerHTML = "(hide video)";
			
		}
		
		// Start the animation.
		animation.start();
		
		// Return false so we don't follow the link.
		return false;
		
	}

}