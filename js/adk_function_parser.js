/*
global

window: false,
jQuery: false
*/

/**
 * Text parser
 * Replaces 'functionName(args)' by that function output, if applied
 * @class
 * @param {object} data Initializing data
 * @returns {void}
 */
window.ADKFunctionParser = function ADKFunctionParser( data ) {

	/**
	 * @type {object}
	 * @description Alias for this
	 */
	var klass = this;

	if ( typeof data !== 'object' ) {
		window.console.error( 'Parser: invalid initializing data' );

		return;
	}

	/**
	 * @type {object}
	 * @description Replacement functions list
	 */
	this.replacements = {};

	jQuery.each( data, function iterateOverFinctions( i, v ) {

		var args = [],
			x = 1;

		for (; x <= v.args; x++ ) {
			args.push( 'A' + x );
		}

		klass.replacements[ i ] = new Function( args.join( ',' ), v.body );
	} );

	/**
	 * Parses text and returns text with substitutions
	 *
	 * @param {string} text Input text
	 * @returns {string} Output text
	 */
	this.parse = function parce( text ) {

		 // Get all the func(args) from string and pass it to replacement
		 // functions.
		return text.replace(

			// Search for myFuncName( arguments )
			/([_$a-zA-Z]+[_$a-zA-Z0-9]*)\s*\(([^\)]*)\)/g,

			/**
			 * Iterate over all the replacement functions, fetch theirs
			 * names and arguments.
			 *
			 * @param {string} match - Entire function match
			 * @param {string} funcName - Function name
			 * @param {string} funcArgs - function arguments
			 * @returns {string} - Text replacement made by replacement function
			 */
			function doTextReplace( match, funcName, funcArgs ) {

				// Initialize arguments
				var args = funcArgs,
					ret = '';

				args = args ? args.split( ',' ).map(function makeString( v ) {

					return String( v ).trim();

				}) : [];

				// If replacement function exists - pass the arguments to it
				// and run it.
				if ( klass.replacements[ funcName ] ) {

					ret = klass.replacements[ funcName ].apply( null, args );
					if ( ret !== false ) {

						return ret;
					}
				}

				return match;
			}
		);
	};
};
