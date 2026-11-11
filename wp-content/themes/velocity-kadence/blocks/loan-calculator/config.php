<?php
/**
 * Loan scenario calculator: every option, program, rule and message.
 *
 * This file is the single place to change how the calculator behaves.
 * render.php prints it, view.js applies it. Nothing else holds a rule.
 *
 * Source: Figma "Velocity Website", Homepage, notes beside "calculator-visual".
 *
 * ASSUMPTIONS, pending confirmation from Velocity. Each one is marked
 * "Assumption" below, so it is easy to find and change:
 *  1. Only 1–4 unit properties have a program. Multifamily, mixed-use and
 *     commercial always lead to an account executive.
 *  2. Recent credit events rule out every program except Fast50.
 *  3. Super Jumbo is long-term only.
 *  4. "First-time buyer" qualifies like any other investor, except for Super
 *     Jumbo, which is for seasoned investors only.
 *  5. "Under $100K" qualifies, although the program minimum is $75K.
 *  6. Super Jumbo needs a 600 credit score. This comes from the program card,
 *     not from the rule in the notes.
 *
 * How a result is chosen, in view.js:
 *  - Property other than 1–4 units, or a loan over $10MM: no program.
 *  - Bridge plan: Flex I/O is the candidate.
 *  - Long-term and $2MM–$10MM: Super Jumbo is the candidate.
 *  - Long-term up to $2MM, with recent credit events or a score under 650:
 *    Fast50 is the candidate. Otherwise 30-Year Fixed is.
 *  The candidate is shown if it passes all its rules. If not, the first rule
 *  it fails gives the message on the "talk to an account executive" card.
 *
 * Kept compatible with PHP 7.4.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

return array(

	// The form, in order. The first key of each list is not special: "default" is.
	'fields'          => array(
		'plan'       => array(
			'label'   => 'What’s your investor’s plan?',
			'default' => 'long',
			'options' => array(
				'long'   => 'Long-term (fixed for 30 years)',
				'bridge' => 'Bridge (up to 24 months)',
			),
		),
		'property'   => array(
			'label'   => 'Property type',
			'default' => '1-4',
			'options' => array(
				'1-4'        => '1–4 units',
				'multi'      => 'Multifamily or mixed-use',
				'commercial' => 'Commercial (office, retail, warehouse…)',
			),
		),
		'amount'     => array(
			'label'   => 'Loan amount',
			'default' => '100k-2mm',
			'options' => array(
				'under-100k' => 'Under $100K',
				'100k-2mm'   => '$100K–$2MM',
				'2mm-10mm'   => '$2MM–$10MM',
				'over-10mm'  => 'Over $10MM',
			),
		),
		'ltv'        => array(
			'label'   => 'LTV',
			'default' => '66-75',
			'options' => array(
				'up-50'   => 'Up to 50%',
				'51-65'   => '51–65%',
				'66-75'   => '66–75%',
				'over-75' => 'Over 75%',
			),
		),
		'credit'     => array(
			'label'   => 'Investor’s credit score',
			'default' => '675',
			'options' => array(
				'675'       => '675+',
				'650'       => '650–674',
				'600'       => '600–649',
				'below-600' => 'Below 600',
			),
		),
		'experience' => array(
			'label'   => 'Investor’s experience',
			'default' => 'seasoned',
			'options' => array(
				'seasoned'       => 'Seasoned investor',
				'first-investor' => 'First-time investor',
				'first-buyer'    => 'First-time buyer',
			),
		),
	),

	// The checkbox under the credit score.
	'events_label'    => 'Recent bankruptcy, notice of default or late mortgage payments',

	// The card shown before anyone presses Calculate. Must match the defaults above.
	'default_program' => '30yf',

	/*
	 * Programs. "term" is the program in Properties, Programs: its name,
	 * description, terms and page link come from there, so they are edited
	 * in one place. Run `wp velocity seed-programs` to fill them the first time.
	 *
	 * "rules" are checked in order. Each rule is:
	 * field, the values that pass, and the message shown when it fails.
	 * The field "events" is "yes" when the checkbox is ticked, "no" when not.
	 */
	'programs'        => array(
		'30yf'        => array(
			'term'        => '30-year-fixed',
			'rules'       => array(
				array( 'ltv', array( 'up-50', '51-65', '66-75' ), 'Needs at least 25% down or equity.' ),
			),
		),
		'fast50'      => array(
			'term'        => 'fast50',
			'rules'       => array(
				array( 'ltv', array( 'up-50' ), 'Needs at least 50% down or equity.' ),
			),
		),
		'super-jumbo' => array(
			'term'        => 'super-jumbo',
			'rules'       => array(
				// Assumption 4.
				array( 'experience', array( 'seasoned' ), 'For seasoned investors only.' ),
				array( 'ltv', array( 'up-50', '51-65' ), 'Needs at least 35% down or equity.' ),
				// Assumption 6.
				array( 'credit', array( '675', '650', '600' ), 'Needs a credit score of 600 or higher.' ),
				// Assumption 2.
				array( 'events', array( 'no' ), 'Recent credit events need a closer look with an account executive.' ),
			),
		),
		'flex-io'     => array(
			'term'        => 'flex-io',
			'rules'       => array(
				// Assumption 5: "Under $100K" passes.
				array( 'amount', array( 'under-100k', '100k-2mm' ), 'Flex I/O loans go up to $2MM.' ),
				array( 'ltv', array( 'up-50', '51-65', '66-75' ), 'Needs at least 25% down or equity.' ),
				array( 'credit', array( '675' ), 'Needs a credit score of 675 or higher.' ),
				// Assumption 2.
				array( 'events', array( 'no' ), 'Recent credit events need a closer look with an account executive.' ),
			),
		),
	),

	// Reasons that apply before any program is considered.
	'reasons'         => array(
		// Assumption 1.
		'property'  => 'For multifamily, mixed-use and commercial properties, let’s look at the deal together.',
		'over-10mm' => 'Loans over $10MM are reviewed case by case.',
	),

	// The card shown when no program fits.
	'no_fit'          => array(
		'title'   => 'Talk to an account executive',
		'default' => 'This scenario needs a closer look.',
		'url'     => home_url( '/contact-us/' ),
		'link'    => 'Talk to an account executive',
	),
);
