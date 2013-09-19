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

		var financial_fact = $$( 'financial_fact' );

		// Set up the array.
		Financial_Facts.facts_array =
		[
			"The average student is graduating with over $4000 in credit card debt",
			"Only 66% of freshmen return to college",
			"40% of students entering college need remedial help",
			"Financial Aid appeals are up 500%",
			"Low income aid is being sacrificed for other aid programs",
			"ACT and SAT scores are down for three years in a row",
			"529 Plans are flawed at their core",
			"Only 62% of parents have saved for their children’s education",
			"Over 50 percent of students change majors at least once",
			"Student loan debt is rising faster than the cost of living or health care costs",
			"I took a calculus class and never saw the professor",
			"70% of America believes that in the near future they will not be able to afford a college education.",
			"The 38% of parents that save for college save less than $3,000 a year",
			"The FAFSA form is the most difficult and confusing federal form to fill out",
			"Families spend at least 30 percent of their income to afford a private college"
		];

		// Set the financial facts to invisible initially.
		financial_fact.opacity( 0 );

		// Set up the animation objects.
		var in_anim = new Legato_Animation( financial_fact, 500 );
		in_anim.controller.opacity.to = 1;

		var out_anim = new Legato_Animation( financial_fact, 500 );
		out_anim.controller.delay = Financial_Facts.update_speed;
		out_anim.controller.opacity.to = 0;

		// Set up the animation sequence.
		Financial_Facts.anim_sequence = new Legato_Animation_Sequence( { loop: true } );

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
		$$( 'financial_fact' ).innerHTML = '"' + Financial_Facts.facts_array[Financial_Facts.current_index] + '"';

	}

}

// Set to initialize when the page loads.
Legato_Events_Handler.DOMReady( Financial_Facts.initialize );