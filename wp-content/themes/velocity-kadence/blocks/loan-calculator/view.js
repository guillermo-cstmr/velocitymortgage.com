/**
 * Loan scenario calculator: picks the result card when Calculate is pressed.
 *
 * The rules come from config.php, printed by render.php in data-rules.
 * This file holds no copy and no thresholds, only the order in which the
 * candidates are considered. See the comment at the top of config.php.
 */
( function () {
	'use strict';

	function read( form ) {
		var values = {};

		Array.prototype.forEach.call( form.querySelectorAll( 'select' ), function ( select ) {
			values[ select.name ] = select.value;
		} );

		values.events = form.querySelector( 'input[name="events"]' ).checked ? 'yes' : 'no';

		return values;
	}

	// The program that fits these answers, or the reason none does.
	function evaluate( config, v ) {
		if ( v.property !== '1-4' ) {
			return { reason: config.reasons.property };
		}

		if ( v.amount === 'over-10mm' ) {
			return { reason: config.reasons[ 'over-10mm' ] };
		}

		var candidate;

		if ( v.plan === 'bridge' ) {
			candidate = 'flex-io';
		} else if ( v.amount === '2mm-10mm' ) {
			candidate = 'super-jumbo';
		} else if ( v.events === 'yes' || v.credit === '600' || v.credit === 'below-600' ) {
			candidate = 'fast50';
		} else {
			candidate = '30yf';
		}

		var rules = config.programs[ candidate ].rules;

		for ( var i = 0; i < rules.length; i++ ) {
			if ( rules[ i ][ 1 ].indexOf( v[ rules[ i ][ 0 ] ] ) === -1 ) {
				return { reason: rules[ i ][ 2 ] };
			}
		}

		return { program: candidate };
	}

	function show( root, config, result ) {
		var key = result.program || 'none';

		Array.prototype.forEach.call( root.querySelectorAll( '.vm-calc__card' ), function ( card ) {
			card.hidden = card.getAttribute( 'data-program' ) !== key;
		} );

		if ( ! result.program ) {
			root.querySelector( '.vm-calc__reason' ).textContent = result.reason || config.noFit;
		}

		root.querySelector( '.vm-calc__status' ).textContent = result.program
			? 'Result: ' + config.programs[ result.program ].name + ' fits this scenario.'
			: 'Result: no program fits. ' + ( result.reason || config.noFit );
	}

	function init( root ) {
		var config;

		try {
			config = JSON.parse( root.getAttribute( 'data-rules' ) );
		} catch ( e ) {
			return;
		}

		var form = root.querySelector( '.vm-calc__form' );

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			show( root, config, evaluate( config, read( form ) ) );
		} );
	}

	Array.prototype.forEach.call( document.querySelectorAll( '.vm-calc[data-rules]' ), init );

	// Exposed for testing only.
	window.vmLoanCalculator = { evaluate: evaluate };
}() );
