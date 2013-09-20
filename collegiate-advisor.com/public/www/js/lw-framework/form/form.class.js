//------------------------------------------------------------------------
// Name: LW_Form
// Desc: A helper class to help with the PHP Form class.
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Some Global Form Variables
//------------------------------------------------------------------------
LW_Form.forms = Object();


//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: LW_Form
// Desc: Class constructor.
//------------------------------------------------------------------------
function LW_Form( form_id, dependants )
{

	// Add this object to a global form array.
	LW_Form.forms[form_id] = this;

	this.form_id     = form_id;    // The form ID.
	this.dependants  = dependants; // The dependants.

	// Loop through each dependant group.
	for ( var element_id in this.dependants )
	{

		// Get the element.
		var element = document.getElementById( element_id );

		// Add the events.
		if ( element.type == "radio" )
		{

			// What is the default value?
			if ( !element.checked )
			{

				// Call the toggleDependants function on these dependants.
				this.toggleDependants( element_id, false );

			}  // End if not checked.

			// Get all the elements with the same name as this.
			var elements = document.getElementsByName( element.name );

			// Loop through each element found.
			for ( var i = 0; i < elements.length; i++ )
			{

				// Add the event.
				if ( elements[i].id != element_id )
					LW_Events_Handler.addEvent( elements[i], "onchange", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "', false );" ) );
				else
					LW_Events_Handler.addEvent( elements[i], "onchange", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "', true );" ) );

			}  // Next element found.

		}  // End if radio button.
		else if ( element.type == "checkbox" )
		{

			// Call the toggleDependants function.
			this.toggleDependants( element_id );

			// Add the event.
			LW_Events_Handler.addEvent( element, "onclick", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "' );" ) );

		}  // End if checkbox.
		else if ( element.type == "select-one" )
		{

			// Call the toggleDependants function.
			this.toggleDependants( element_id );

			// Add the event.
			LW_Events_Handler.addEvent( element, "onchange", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "' );" ) );

		}  // End if select-one.
		else if ( element.type == "select-multiple" )
		{

			// Call the toggleDependants function.
			this.toggleDependants( element_id );

			// Add the event.
			LW_Events_Handler.addEvent( element, "onchange", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "' );" ) );

		}  // End if select-multiple.
		else if ( element.type == "text" || element.type == "password" || element.type == "textarea" || element.type == "file" )
		{

			// Call the toggleDependants function.
			this.toggleDependants( element_id );

			// Add the event.
			LW_Events_Handler.addEvent( element, "onkeyup", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "' );" ) );

		}  // End if text.

	}  // Next dependant group.

}


//------------------------------------------------------------------------
// Name: toggleDependants
// Desc: Toggles the dependants on or off depending upon whether their
//       element is on or off.
//------------------------------------------------------------------------
LW_Form.prototype.toggleDependants = function( element_id, on )
{

	// Get the element.
	var element = document.getElementById( element_id );

	// What type of element.
	if ( element.type == "checkbox" )
	{

		if ( element.checked )
			on = true;
		else
			on = false;

	}  // End if checkbox.
	else if ( element.type == "select-one" )
	{

		if ( element.value == this.dependants[element_id].value[0] )
			on = true;
		else
			on = false;

	}  // End if select-one.
	else if ( element.type == "select-multiple" )
	{

		// Set on to be initially true. We will find if it should
		// really be true later on.
		var on = true;

		// Loop through each value.
		for ( var i = 0; i < this.dependants[element_id].value.length; i++ )
		{

			// Loop through each option.
			for ( var n = 0; n < element.options.length; n++ )
			{

				if ( !element.options[n].selected && element.options[n].value == this.dependants[element_id].value[i] )
					on = false;

			}  // Next option.

		}  // Next value.

	}  // End if select-one.
	else if ( element.type == "text" || element.type == "password" || element.type == "textarea" || element.type == "file" )
	{

		if ( element.value != '' )
			on = true;
		else
			on = false;

	}  // End if other.

	// Are we disabling or enabling?
	if ( on )
	{

		// Loop through each dependant.
		for ( var i = 0; i < this.dependants[element_id].dependants.length; i++ )
		{

			// Get the dependant.
			dependant = this.dependants[element_id].dependants[i];

			// Get the dependant's element.
			var dependant_element = document.getElementById( dependant );

			// Make sure nothing else is controlling this element.
			var free = this.isDependantFree( dependant );

			// Enable the element.
			if ( free ) dependant_element.disabled = false;

		}  // Next dependant.

	}  // End if enabling.
	else if ( !on )
	{

		// Loop through each dependant.
		for ( var i = 0; i < this.dependants[element_id].dependants.length; i++ )
		{

			// Get the dependant.
			dependant = this.dependants[element_id].dependants[i];

			// Get the dependant's element.
			var dependant_element = document.getElementById( dependant );

			// Disable the element.
			dependant_element.disabled = true;

		}  // Next dependant.

	}  // End if disabling.

}


//------------------------------------------------------------------------
// Name: isDependantFree
// Desc: Loops through every dependant being manage by the system and
//       makes sure this dependant is completely free.
//------------------------------------------------------------------------
LW_Form.prototype.isDependantFree = function( dependant )
{

	var ret = true;

	// Loop through each dependant group.
	for ( var element_id in this.dependants )
	{

		// Get the dependant group.
		var dependant_array = this.dependants[element_id].dependants;

		// Loop through each dependant.
		for ( var i = 0; i < dependant_array.length; i++ )
		{

			// If the dependant was found.
			if ( dependant == dependant_array[i] )
			{

				// Get the main dependant element.
				var element = document.getElementById( element_id );

				// If the element's main dependant is not checked, return false.
				if ( element.type == "checkbox" )
				{

					if ( !element.checked )
						ret = false;

				}  // End if checkbox.
				else if ( element.type == "radio" )
				{

					if ( !element.checked )
						ret = false;

				}  // End if radio.
				else if ( element.type == "select-one" )
				{

					if ( element.value != this.dependants[element_id].value )
						ret = false;

				}  // End if select-one.
				else if ( element.type == "text" || element.type == "password" || element.type == "textarea" || element.type == "file" )
				{

					if ( element.value == '' )
						ret = false;

				}  // End if other.

			}  // End if found.

		}  // Next dependant.

	}  // Next dependant group.

	return ret;

}


//------------------------------------------------------------------------
// Static Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: submitForm
// Desc: You pass it a form DOM element and it submits the form, calling
//       the form's obsubmit event.
//------------------------------------------------------------------------
LW_Form.submitForm = function( form )
{

	// Call the onsubmit event of the form.
	form.onsubmit();

}


