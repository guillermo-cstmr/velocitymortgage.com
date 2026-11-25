<?php
/**
 * Builds the page of one loan program as block content.
 *
 * One builder for the four program pages: 30-Year Fixed, Fast50, Flex I/O
 * and Super Jumbo. They share every section, and differ in their copy,
 * their photo and icons, and in whether the terms come in one table or in
 * two, "1-4 Units" and "Commercial", behind a pair of tabs.
 *
 * Like the other pages, this only produces the first version.
 * After `wp velocity seed-program-pages` each page is edited in the block
 * editor. What a block setting can express is set on the block.
 *
 * Figma: page "Broker Programs", one frame per program. The copy is the
 * one in the copy documents (V1) approved by the client, which win over
 * the text in the design.
 *
 * Kept compatible with PHP 7.4, see class-velocity-cli.php.
 *
 * @package Velocity_Kadence
 */

defined( 'ABSPATH' ) || exit;

/**
 * Program page seed content.
 */
class Velocity_Program {

	/**
	 * Slug of the page the program pages hang from: Programs Overview.
	 */
	const PARENT_SLUG = 'loan-programs';

	/**
	 * Taxonomy that ties a case study, or a closed deal, to its program.
	 */
	const TAXONOMY = 'velocity-property-program';

	/**
	 * Address of a program's page.
	 *
	 * @param string $key Program key: 30-year-fixed, fast50, flex-io or super-jumbo.
	 * @return string
	 */
	public static function url( $key ) {
		$programs = self::programs();

		return home_url( '/' . self::PARENT_SLUG . '/' . $programs[ $key ]['slug'] . '/' );
	}

	/**
	 * Path of a program's page, for the menus.
	 *
	 * @param string $key Program key.
	 * @return string
	 */
	public static function path( $key ) {
		$programs = self::programs();

		return '/' . self::PARENT_SLUG . '/' . $programs[ $key ]['slug'] . '/';
	}

	/**
	 * The card that sends to a program from the other programs' pages.
	 * Each: icon, title, text, button.
	 *
	 * @return array[] By program key.
	 */
	private static function invitations() {
		return array(
			'30-year-fixed' => array( 'icon-calendar-clock.svg', 'When the Client Is Keeping the Property', 'The 30-Year Fixed offers a payment that never changes with no lump sum at the end.', 'Explore 30-Year Fixed' ),
			'fast50'        => array( 'icon-acute.svg', 'Equity Does the Talking', 'Like the 30-Year Fixed, with the weight on property equity—up to 50% of the property’s value.', 'Explore Fast50' ),
			'super-jumbo'   => array( 'icon-diamond.svg', 'When the Loan is Over $2MM', 'Deal over $2MM? Super Jumbo carries the same 30-year loan up to $10MM.', 'Explore Super Jumbo' ),
			'flex-io'       => array( 'icon-gesture.svg', 'When the Client Needs a Bridge', 'Flex I/O frees up cash between projects with two years of interest-only payments.', 'Explore Flex I/O' ),
		);
	}

	/**
	 * The four programs: what differs from one page to the next.
	 *
	 * slug is the page's, under /loan-programs/. term is the slug of the
	 * program in Properties, Programs, which case studies are filed under.
	 *
	 * A row of terms is its label, the value for 1-4 units and, only when it
	 * differs, the value for commercial property. A value is a string, or a
	 * list of items.
	 *
	 * @return array[] By program key.
	 */
	public static function programs() {
		$fixed_term = array( 'Fixed for 30 years, paid down to zero with no lump sum at the end', 'Shorter terms available' );
		$itin       = 'An ITIN works in place of a Social Security number';
		$commercial = array( 'Multifamily with five or more units', 'Mixed-use', 'Office, retail, warehouse, self-storage, automotive' );

		return array(
			'30-year-fixed' => array(
				'slug'    => '30-year-fixed',
				'term'    => '30-year-fixed',
				'name'    => '30-Year Fixed',
				'eyebrow' => 'Long-term loan',
				'title'   => '30-Year Fixed, Built to Buy and Hold',
				'intro'   => 'Help your client buy, refinance, or cash out on residential or commercial property, while giving them the certainty of predictable payments for 30 years.',
				'photo'   => array( 'photo-30-year-fixed.webp', 'Two brokers talking beside an office window' ),
				'stats'   => array(
					array( '$75K to $2MM', '1-4 unit loan amounts' ),
					array( 'From $100K', 'Commercial loan amounts' ),
					array( 'Up to 75% on 1-4 units', 'Maximum LTV' ),
					array( '1-4 units to commercial', 'Property types' ),
					array( '2 wks to 45 days', 'Time to close' ),
				),
				'why'     => array(
					'With one 30-year loan for both residential and commercial properties, we help you both build your book and keep your clients for the long-term.',
					array(
						array( 'icon-filter-1.svg', '30 Years, One Monthly Payment', 'A predictable plan that takes the loan down to zero. No need to refinance after a year.' ),
						array( 'icon-home-work.svg', 'Property-First Underwriting', 'Simple documentation, with eligibility focused on the value of the property.' ),
						array( 'icon-linear-scale.svg', 'One Loan, Multiple Uses', 'Fund everything from rentals to mixed-use to commercial—all with a single lender.' ),
					),
				),
				'terms'   => array(
					'Everything you need to know about our 30-Year Fixed program. Terms differ for 1-4 unit and commercial properties. See both in the table below.',
					true,
					array(
						array( 'What It’s For', 'Long-term financing to buy, keep, or cash out of investment property' ),
						array( 'Property Types', array( 'Single-family, condo, 2-4 units', 'Assisted living homes included' ), $commercial ),
						array( 'Loan Amounts', array( '$75K to $2MM' ), array( '$100K minimum' ) ),
						array( 'Maximum LTV', array( 'Purchases: up to 75%', array( 'Refinances:', array( '70% with a credit score of 650-674', '65% with a credit score of 600-649', '60% with a credit score below 600' ) ) ) ),
						array( 'Combined Financing', array( 'On purchases, up to 90% of value combining Velocity’s first loan with a seller carryback or other second lien' ) ),
						array( 'Term and Payments', $fixed_term ),
						array( 'Credit Minimum', array( 'Ask your account executive' ) ),
						array( 'Loan Purpose', array( 'Purchase', 'Rate-and-term refinance', 'Cash-out refinance' ) ),
						array( 'Several Rentals on One Loan', array( 'Two or more 1-4 unit properties in the same county on one loan with one payment' ), array( 'Not applicable' ) ),
						array( 'Borrower Experience', array( 'First-time investors: maximum LTV is 5 percentage points lower' ) ),
						array( 'Qualifies On', array( 'The property’s value and investments characteristics, with simple documentation' ) ),
						array(
							'Paying It Off Early',
							array(
								'For most states, a fee that steps down over five years, 5% of the balance in year one to 1% in year five, then none',
								'NJ (1-6 units) and PA (1-4 units): no prepay fee on non-entity loans',
								'NM, KS, and AK (1-4 units): no prepay fee on non-entity loans, with a 1% rate or 1.5% fee add-on',
							),
							array(
								'For most states, a fee that steps down over five years, 5% of the balance in year one to 1% in year five, then none',
								'NJ (1-6 units) and PA (1-4 units): no prepay fee on non-entity loans',
							),
						),
						array(
							'Foreign Borrowers',
							array(
								'A foreign national (lives in the U.S. part of the year, has U.S. credit) up to 75% LTV',
								'A foreign investor (lives abroad, no U.S. credit) up to 65% LTV',
								$itin,
							),
							array(
								'A foreign national (lives in the U.S. part of the year, has U.S. credit): up to 75% on multifamily and mixed-use, 70% on office, retail, and the rest',
								'A foreign investor (lives abroad, no U.S. credit): up to 65%',
								$itin,
							),
						),
						array( 'Where Velocity Lends', array( 'Nationwide' ) ),
						array( 'Time to Close', array( '2 to 4 weeks' ), array( '30 to 45 days' ) ),
					),
				),
				'clients' => array(
					'The Clients Who Fit the 30-Year Fixed',
					'Clients who want long-term certainty but can’t—or would rather not—qualify through a bank’s income paperwork. Now you can move forward with them.',
					array(
						array( 'The Buy-and-Hold Investor', 'Keeps the property for the long run and wants payments that never change.' ),
						array( 'The Self-Employed Borrower', 'Has strong property, healthy cash flow, but needs a loan with simpler documentation than a bank requires.' ),
						array( 'The Investor Stuck in Short-Term Debt', 'Has a 12-month private money loan coming due and needs 30 years of certainty.' ),
					),
				),
				'faq'     => array(
					array(
						'What is the 30-Year Fixed’s prepayment penalty?',
						array(
							'If your client pays off the loan in its first five years, a penalty will be applied. The amount of this penalty declines with each year. It’s 5% of the balance in year one, 4% in year two, and so on. After year five, there is no penalty for prepayment.',
							'If your client expects to pay off the loan early (usually by selling or refinancing sooner), talk to your account executive before you submit. The right structure depends on the plan for the property, and knowing it up front keeps the terms from surprising anyone.',
						),
					),
					array(
						'How large can a 30-Year Fixed loan be?',
						array(
							'Our 30-Year Fixed loans range from $75,000 to $2 million on a 1-4 unit property, while commercial properties range from $100,000 and have no stated ceiling. For a 1-4 unit loan above $2 million, Super Jumbo loans range from $2 million to $10 million.',
							'Super Jumbo is the same loan at a larger size. It lends up to 65% of the property’s value, asks for a credit score of at least 600, and is for seasoned investors in major metropolitan areas. Your account executive can confirm the fit on a specific deal.',
						),
					),
					array(
						'What property types does the 30-Year Fixed cover?',
						array(
							'It covers 1-4 unit rentals (such as single-family homes, condos, and assisted living homes), multifamily with five or more units, mixed-use buildings, and commercial property (such as office, retail, warehouse, self-storage, and automotive). This is our widest range of coverage.',
							'For a rental a client plans to keep, the best type of loan is the one that lasts as long as the plan, which is what the 30-Year Fixed loan is. Loan amounts run from $75,000 to $2 million on 1-4 unit property and from $100,000 and up on commercial property.',
						),
					),
					array(
						'How long does the 30-Year Fixed take to close?',
						array(
							'Two weeks to 45 days from a complete submission to funding. Once you send in the documents your account manager requests, our operations team reviews and signs off. Sending us a complete file as soon as possible is the fastest path to closing.',
							'If you turn in the documents while the appraisal is still out, your file will be ready to close the moment it’s back. At any time, you can ask your account executive for an update on timing for a specific deal so that you can keep your client informed.',
						),
					),
					array(
						'Can a foreign national use the 30-Year Fixed?',
						array(
							'Yes. A foreign national (someone from abroad who lives in the U.S. part of the year and has U.S. credit) can borrow up to 75% of the property’s value. A foreign investor (someone living abroad with no U.S. credit) can borrow up to 65%.',
							'The property must be an investment, never a residence, and the loan goes through you as the broker. Our team includes Spanish, Farsi, Chinese, and Vietnamese speakers, which helps when your client’s first language is not English.',
						),
					),
				),
			),

			'fast50'        => array(
				'slug'    => 'fast50',
				'term'    => 'fast50',
				'name'    => 'Fast50',
				'eyebrow' => 'Long-term loan',
				'title'   => 'Fast50, <strong>Equity Does the Talking</strong>',
				'intro'   => 'The same term as the 30-Year Fixed, with the weight on property equity—up to 50% of the property’s value. Use it to buy, refinance, or cash out on commercial or residential property.',
				'photo'   => array( 'photo-fast50.webp', 'A broker smiling with his arms crossed' ),
				'stats'   => array(
					array( '$75K to $2MM', '1-4 unit loan amounts' ),
					array( 'From $100K', 'Commercial loan amounts' ),
					array( 'Up to 50%', 'Maximum LTV' ),
					array( 'No minimum', 'Credit score' ),
					array( '1-4 units to commercial', 'Property types' ),
					array( '2 to 4 weeks', '1-4 unit time to close' ),
				),
				'why'     => array(
					// The last five words stay together, so the second line is not just "30 years."
					'Credit score doesn’t decide the deal. Along the way, we’ll help you earn a client for&nbsp;the&nbsp;next&nbsp;30&nbsp;years.',
					array(
						array( 'icon-airwave.svg', 'Looser Credit Rules,<br>Same 30-Year Loan', 'A discharged bankruptcy or late mortgage payment doesn’t rule a loan out.' ),
						array( 'icon-hourglass-disabled.svg', 'No Waiting Period to Refinance', 'Refinance a property right after purchase so that cash goes back to work.' ),
						array( 'icon-readiness-score.svg', 'No Minimum<br>Credit Score', 'We review credit history, but the property’s equity drives the deal.' ),
					),
				),
				'terms'   => array(
					'Everything you need to know about our Fast50 program. Terms differ for 1-4 unit and commercial properties. See both in the table below.',
					true,
					array(
						array( 'What It’s For', 'Long-term financing when credit history is the problem and equity is not' ),
						array( 'Property Types', array( 'Single-family, condo, 2-4 units' ), $commercial ),
						array( 'Loan Amounts', array( '$75K to $2MM' ), array( '$100K minimum' ) ),
						array( 'Maximum LTV', array( '50%, for every credit score and on every property type' ) ),
						array( 'Term and Payments', $fixed_term ),
						array( 'Credit Score and History', array( 'Every score qualifies, including below 600', 'A bankruptcy or notice of default discharged as recently as the day before does not stop the loan', 'Late mortgage payments don’t either' ) ),
						array( 'Waiting Period After Purchase', array( 'One day' ) ),
						array( 'Loan Purpose', array( 'Purchase', 'Refinance', 'Cash-out refinance' ) ),
						array( 'Borrower Experience', array( 'First-time buyers not eligible' ) ),
						array( 'Qualifies On', array( 'The property’s value, not the borrower’s personal income' ) ),
						array( 'Paying It Off Early', array( 'A fee that steps down over five years, 5% of the balance in year one to 1% in year five, then none' ) ),
						array( 'Foreign Borrowers', array( '50% LTV', $itin ) ),
						array( 'Where Velocity Lends', array( 'Nationwide' ) ),
						array( 'Time to Close', array( '2 to 4 weeks' ), array( '30 to 45 days' ) ),
					),
				),
				'clients' => array(
					'The Clients Who Fit Fast50',
					'Clients with plenty of equity, whose credit history or recent purchase would stop a bank. Now you can move forward with them.',
					array(
						array( 'The Investor With Equity, Not Credit', 'Owns a property outright, took a credit hit, and needs their equity working again.' ),
						array( 'The Borrower Just Out of Bankruptcy', 'Has a cleared bankruptcy or notice of default and needs a flexible lending partner.' ),
						array( 'The Investor Who Bought Last Week', 'Paid cash to win the property and now needs a refinance to get that cash back out.' ),
					),
				),
				'faq'     => array(
					array(
						'Is there really no minimum credit score?',
						array(
							'Correct. Fast50 has no minimum credit score and looser credit rules than the 30-Year Fixed. Your client brings the equity, we bring a loan for the same length and property types as the 30-Year Fixed. The only difference is Fast50 must be no more than 50% of the property’s value.',
							'That’s the program’s tradeoff: a smaller loan in exchange for looser credit rules. It’s why an investor who owns a rental free and clear can pull cash out without a conversation about the score. If your client qualifies to borrow more, the 30-Year Fixed is the better fit.',
						),
					),
					array(
						'Does a recent bankruptcy or foreclosure disqualify my client?',
						array(
							'No. A bankruptcy or notice of default that has cleared won’t disqualify a Fast50 borrower—even if it happened the day before. The same with late mortgage payments. Once the discharge is in hand, your clients can use Fast50 to buy a new investment property.',
							'When you bring the deal, just keep the request at or below half the property’s value. Your account executive will confirm the fit and walk you through what the file needs before it goes to processing, so nothing surprises anyone later.',
						),
					),
					array(
						'Can my client refinance a property they just bought?',
						array(
							'Yes, they can. With a waiting period of just one day after a purchase, Fast50 lets your client refinance a property 24 hours after buying it. That makes it a fit for an investor who closed a deal with cash and now wants to put that capital to work on their next property.',
							'The loan can’t be worth more than half the property’s value, and the property must be an investment, never the borrower’s home. Bring the purchase details and your client’s plan for the equity to your account executive and they’ll map it to the right structure.',
						),
					),
					array(
						'How fast does Fast50 close?',
						array(
							'It takes two weeks to 45 days from a complete submission. In addition to speed, Fast50 also delivers certainty. Once your documents are in and the appraisal comes back, your file starts moving.',
							'Remember to ask your account executive for current timing on a specific deal, since property type and location can change the date. As with every program, turning in the documents while the appraisal is out is the fastest path to close.',
						),
					),
					array(
						'What is the most my client can borrow on Fast50?',
						array(
							'Up to half the property’s value, a 50% LTV mortgage, with loan amounts up to $2 million on 1-4 unit property and from $100,000 on commercial. The property range matches the 30-Year Fixed, from 1-4 unit rentals to multifamily, mixed-use, and commercial property.',
							'If your client needs to borrow more than 50% of the value and has the credit history for it, the 30-Year Fixed lends up to 75% on the same 30-year loan. Your account executive can tell you which program fits, and the LTV limit for every program sits on one page.',
						),
					),
				),
			),

			'flex-io'       => array(
				'slug'    => 'flex-io',
				'term'    => 'flex-io',
				'name'    => 'Flex I/O',
				'eyebrow' => 'Short-term loan',
				'title'   => 'Flex I/O, a Bridge Loan <strong>Between Projects</strong>',
				'intro'   => 'A two-year loan for a house or small rental, interest-only until they sell or refinance. Get your client the cash they need between projects.',
				'photo'   => array( 'photo-flex-io.webp', 'A broker smiling as she shakes hands with a client across a desk' ),
				'stats'   => array(
					array( '$75K to $2MM', '1-4 unit loan amounts' ),
					array( 'Up to 75%', 'Maximum LTV' ),
					array( '24 months', 'Interest-only term' ),
					array( '675+', 'Credit minimum' ),
					array( '1-4 units only', 'Property types' ),
					array( '2 to 4 weeks', 'Time to close' ),
				),
				'others'  => array( '30-year-fixed', 'super-jumbo', 'fast50' ),
				'why'     => array(
					'We help you place your client’s short-term deals, alongside their long-term ones. That means they never have to start over with a stranger.',
					array(
						array( 'icon-counter-2.svg', 'Two Years of Interest-Only Payments', 'Deliver your client breathing room with lower payments while they prepare to sell or refi.' ),
						array( 'icon-local-atm.svg', 'Purchase or Cash-Out Refinance', 'Help your client get cash for their next property or tap into equity of one they own.' ),
						array( 'icon-report.svg', 'No Penalty for<br>Paying Off Early', 'Your client can sell or refi any time in the two years and pay nothing extra to close it out.' ),
					),
				),
				'terms'   => array(
					'Everything you need to know about our Flex I/O program. Flex I/O is available for 1-4 unit properties only. See the terms in the table below.',
					false,
					array(
						array( 'What It’s For', 'Short-term money on a 1-4 unit property the investor will sell or refinance' ),
						array( 'Property Types', array( 'Single-family, condo, 2-4 units' ) ),
						array( 'Loan Amounts', array( '$75K to $2MM' ) ),
						array( 'Maximum LTV', array( '75%', '70% on loans above $1MM', '65% for a first-time investor' ) ),
						array( 'Term and Payments', array( '24 months, interest-only for the whole term' ) ),
						array( 'Credit Score', array( '675 minimum' ) ),
						array( 'Loan Purpose', array( 'Purchase', 'Refinance', 'Cash-out', 'Rate and term' ) ),
						array( 'Paying It Off Early', array( 'No fee' ) ),
						array(
							'Foreign Borrowers',
							array(
								'A foreign national (lives in the U.S. part of the year, has U.S. credit): ask your account executive',
								'A foreign investor (lives abroad, no U.S. credit): up to 65%',
								$itin,
							),
						),
						array( 'Where Velocity Lends', array( 'Nationwide' ) ),
						array( 'Time to Close', array( '2 to 4 weeks' ) ),
					),
				),
				'clients' => array(
					'The Clients Who Fit Flex I/O',
					'Clients who need short-term money on a 1-4 unit property they plan to sell or refinance. Now you can move forward with them.',
					array(
						array( 'The Investor Between Projects', 'Owns a finished house listed for sale and needs cash out of it before it sells.' ),
						array( 'The Owner of a Free-and-Clear Rental', 'Owns a paid-off rental they’re keeping and needs cash out of it for another project.' ),
						array( 'The Investor Who Will Keep the Property', 'Needs funding now and plans to refinance into a 30-Year Fixed with the same lender.' ),
					),
				),
				'faq'     => array(
					array(
						'How long is the Flex I/O term?',
						array(
							'Two years (24 months), or until the owner sells or refinances. Flex I/O acts just like a bridge loan in real estate, except it’s meant for investment properties. Payments are interest-only for the whole term.',
							'It takes two to four weeks for a Flex I/O to close, after which the 24 months of its term begin. If your client wants to keep their property after this term is up, they can transition smoothly into our 30-Year Fixed loan.',
						),
					),
					array(
						'Is there a prepayment penalty?',
						array(
							'No, Flex I/O has no prepayment penalty. That means if your client’s plans change, or the property is ready sooner than expected, they can sell or refinance at any point in the two years without paying anything extra.',
							'This is the key difference between Flex I/O and a typical short-term private money loan. Clients get the same flexibility, but have more time and can use the same lender to refinance the property on the 30-Year Fixed, if long-term ownership is their plan.',
						),
					),
					array(
						'Can my client take cash out?',
						array(
							'Yes, they can. Flex I/O works for either a purchase or a refinance, including cash out, on 1-4 unit investment property. This allows an investor with equity in a rental to free up that capital for the next opportunity while the property continues to perform.',
							'Loan amounts run from $75,000 to $2 million, or up to 75% of the property’s value (70% above $1 million), and are available to those with a credit score of 675 or better. The property must be an investment, never your client’s home. Within 24 months the loan is paid off by a sale or a refinance.',
						),
					),
					array(
						'Can Flex I/O work as a bridge to the 30-Year Fixed?',
						array(
							'Yes, and that’s a common way it gets used. It’s what the market calls a “bridge to perm” loan. It works like this: Flex I/O carries the property while your client gets it ready or waits for the right market. Afterwards, the 30-Year Fixed refinances it with a long-term loan.',
							'This works well because both loans are ours. Your client won’t have to start over with a new lender at the end of the two years. Tell your account executive what your client wants at the start and we’ll map the refinance from day one.',
						),
					),
					array(
						'What property types does Flex I/O cover?',
						array(
							'Flex I/O is a residential bridge loan, so it covers 1-4 unit properties (meaning single-family homes), condos, and 2-4 unit buildings held as rentals or listed for sale. Multifamily with five or more units and commercial property belong on the 30-Year Fixed or Fast50 instead.',
							'Loan amounts run from $75,000 to $2 million, and the loan closes in two to four weeks from a complete submission. First-time investors can borrow up to 65% of value, and foreign investors are eligible at 65% as well, with no U.S. credit history required.',
						),
					),
				),
			),

			'super-jumbo'   => array(
				'slug'    => 'jumbo',
				'term'    => 'super-jumbo',
				'name'    => 'Super Jumbo',
				'eyebrow' => 'Long-term loan',
				'title'   => 'Super Jumbo, <strong>Large-Balance Financing</strong>',
				'intro'   => 'The same term as our 30-Year Fixed, but for amounts ranging from $2MM to $10MM. Made for experienced investors funding luxury residential properties.',
				'photo'   => array( 'photo-super-jumbo.webp', 'A broker in a suit shaking hands with a client' ),
				'stats'   => array(
					array( '$2MM to $10MM', '1-4 unit loan amounts' ),
					array( 'Up to 65%', 'Maximum LTV' ),
					array( '600', 'Minimum credit score' ),
					array( '1-4 units only', 'Property types' ),
					array( 'Major metros', 'Where Velocity lends' ),
					array( '2 to 4 weeks', 'Time to close' ),
				),
				'why'     => array(
					'As we judge based on the property, we make it possible for you to offer clients long-term certainty on loan amounts other lenders won’t touch.',
					array(
						array( 'icon-diamond-shine.svg', 'Loan Amounts Made for Luxury Properties', 'Larger loans and less paperwork keep your client moving forward at their pace.' ),
						array( 'icon-apartment.svg', 'Fixed for 30 Years', 'Long-term predictable payments with our simple documentation process.' ),
						array( 'icon-cheer.svg', 'Built on Years of Experience With Large Loans', 'After lending $2MM+ loans for years, we know how to help you close successfully.' ),
					),
				),
				'terms'   => array(
					'Everything you need to know about our Super Jumbo program. Super Jumbo is available for 1-4 unit properties only. See the terms in the table below.',
					false,
					array(
						array( 'What It’s For', 'Long-term financing to buy, keep, or cash out of investment property' ),
						array( 'Property Types', array( 'Single-family, condo, 2-4 units' ) ),
						array( 'Loan Amounts', array( '$2MM to $10MM' ) ),
						array( 'Maximum LTV', array( '65%', '60% for credit scores 600–649' ) ),
						array( 'Term and Payments', array( 'Fixed for 30 years and fully paid off over that time', 'Shorter terms available' ) ),
						array( 'Credit Minimum', array( '600' ) ),
						array( 'Loan Purpose', array( 'Purchase', 'Rate-and-term refinance', 'Cash-out refinance' ) ),
						array( 'Several Rentals on One Loan', array( 'Two or more 1-4 unit properties in the same county on one loan with one payment' ) ),
						array( 'Borrower Experience', array( 'Seasoned investors only' ) ),
						array( 'Qualifies On', array( 'The property and the investor’s experience, through our simple documentation process' ) ),
						array(
							'Paying It Off Early',
							array(
								'A fee that steps down over five years (5%, 4%, 3%, 2%, 1%) in most states',
								'No prepay on non-entity loans in NJ (1–6 units) and PA (1–4 units)',
								'No prepay on non-entity loans in NM, KS, and AK (1–4 units), where a rate or fee add-on applies',
							),
						),
						array( 'Foreign Borrowers', array( 'A foreign national (lives in the U.S. part of the year, has U.S. credit) up to 65% LTV' ) ),
						array( 'Where Velocity Lends', array( 'Major metropolitan areas only' ) ),
						array( 'Time to Close', array( '2 to 4 weeks' ) ),
					),
				),
				'clients' => array(
					'The Clients Who Fit the Super Jumbo',
					'Clients in major metros looking for $2MM+ loans for luxury houses, condos, or small rentals. Now you can help get these projects off the ground.',
					array(
						array( 'Investors Buying Luxury Property', 'Seasoned investors purchasing a home, condo, or 2-4 unit rental in a major metro area.' ),
						array( 'Investors Refinancing Large Property', 'Clients who want cash from a high-value 1-4 unit property without a ton of paperwork.' ),
						array( 'Clients With Lenders That Stop at $2MM', 'People who have the experience and the reserves—and a deal that’s outgrown their last lender.' ),
					),
				),
				'faq'     => array(
					array(
						'What is a Super Jumbo loan?',
						array(
							'A jumbo loan refers to any loan above $2 million. At Velocity, our Super Jumbo loan has the same terms as our 30-Year Fixed, but ranges from $2 million to $10 million. Seasoned investors can use it to finance 1-4 unit investment properties in major metropolitan areas.',
							'For most loans above $2 million, lenders pile on documentation requirements. Velocity keeps it simple: we focus on the property and the investor’s track record, with a streamlined process that we’ve used successfully for years before making it a program.',
						),
					),
					array(
						'How large can a Super Jumbo loan be?',
						array(
							'The Super Jumbo loan limit is $10 million on a 1-4 unit investment property, while the smallest Super Jumbo loan is $2 million. Our 30-Year Fixed loan covers amounts below $2 million. That way, a broker never has to choose a different kind of loan when the amount changes.',
							'The Super Jumbo lends up to 65% of the property’s value (i.e., 65% LTV) on all properties up to the loan limit of $10 million. With a second loan on the property, such as a seller carryback, combined financing can reach 90% (i.e., 95% CLTV).',
						),
					),
					array(
						'Is Super Jumbo a 30-year fixed loan?',
						array(
							'Yes! Super Jumbo is the 30-Year Fixed fully amortized loan, just at a larger size. The rate and the payment stay the same for all 30 years, the loan pays down to zero with no lump sum at the end, and shorter terms are available if the plan calls for one.',
						),
					),
					array(
						'Who can use Super Jumbo?',
						array(
							'It can be used by seasoned investors buying or refinancing a 1-4 unit property in a major metropolitan area who have a credit score of 600 or higher. It must be used for an investment property, never for a home the borrower lives in, and never for a commercial building.',
							'Those two terms, a major metro and an experienced investor, are non-negotiable. Foreign investors are eligible up to 65%, with an ITIN in place of a Social Security number. For a commercial building, the 30-Year Fixed and Fast50 are the programs to see.',
						),
					),
					array(
						'How long does a Super Jumbo loan take to close?',
						array(
							'Just like the 30-Year Fixed, it takes between two and four weeks from a complete submission to funding. You work directly with your account executive’s account manager, send in the documents on the needs list, and then our operations team reviews and signs off.',
							'Submitting the required loan documents early in the process can speed up the loan significantly. At any time, you can ask your account executive to confirm timing on a specific deal before you promise your client a date.',
						),
					),
				),
			),
		);
	}

	/**
	 * A Kadence Image block showing a file from assets/images/programs.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique ID.
	 * @param string $file    File name.
	 * @param int    $width   Max width in px.
	 * @param string $alt     Alternative text. Empty for decorative images.
	 * @return string
	 */
	private static function image( $post_id, $key, $file, $width, $alt = '' ) {
		return Velocity_Home::image( $post_id, $key, $file, $width, $alt, '', 'programs' );
	}

	/**
	 * An icon in a Section of its own. Directly inside a card with a set
	 * height, Kadence would stretch an SVG to fill it.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $key     Key for the unique IDs.
	 * @param string $file    File name.
	 * @param int    $width   Width in px.
	 * @return string
	 */
	private static function icon( $post_id, $key, $file, $width = 35 ) {
		return Velocity_Seed::column(
			$post_id,
			$key . '-icon',
			self::image( $post_id, $key, $file, $width ),
			array( 'metadata' => array( 'name' => 'Icon' ) )
		);
	}

	/**
	 * Hero: eyebrow, title, intro and the main button beside the photo.
	 *
	 * The photo comes from the design in one piece, with the orange shape.
	 * The label over it, the program's property types, is a block of its own
	 * (see Velocity_Home::photo_badges()).
	 *
	 * @param int   $post_id Page ID.
	 * @param array $program One of programs().
	 * @return string
	 */
	public static function hero( $post_id, $program ) {
		// The label reads the same figure as the band under the hero.
		$types = '';
		foreach ( $program['stats'] as $stat ) {
			if ( 'Property types' === $stat[1] ) {
				$types = $stat[0];
			}
		}

		$photo = self::image( $post_id, 'hero-photo', $program['photo'][0], 488, $program['photo'][1] );

		if ( '' !== $types ) {
			$photo = Velocity_Home::photo_badges(
				$post_id,
				'hero-photo',
				$photo,
				array(
					array(
						'icon'    => 'cottage',
						'at'      => 'vm-badge--bottom',
						'padding' => array( '4', '23', '5', '23' ),
						'lines'   => array(
							array( 'Property types', '300' ),
							array( $types, '500' ),
						),
					),
				),
				488
			);
		}

		$text =
			// vm-eyebrow adds the orange dot. Everything else is block settings.
			Velocity_Home::t(
				$post_id,
				'hero-eyebrow',
				$program['eyebrow'],
				array(
					'size'      => array( 14, '', '' ),
					'lh'        => 1.1,
					'transform' => 'uppercase',
					'margin'    => array( '0', '', '24', '' ),
					'class'     => 'vm-eyebrow',
				)
			) . "\n\n" .
			Velocity_Home::t(
				$post_id,
				'hero-title',
				$program['title'],
				array(
					'tag'           => 'h1',
					// 764px wide in the design, 20px more than its column: the
					// title reaches into the space before the photo.
					'margin'        => array( '0', '-20', '24', '' ),
					'tablet_margin' => array( '', '0', '', '' ),
					'class'         => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle(
				$post_id,
				'hero',
				$program['intro'],
				array(
					// As wide as the title.
					'margin'        => array( '0', '-20', '24', '' ),
					'tablet_margin' => array( '', '0', '', '' ),
				)
			) . "\n\n" .
			Velocity_Home::buttons( $post_id, 'hero', array( array( 'Become a Partner', home_url( '/become-a-partner/' ) ) ), 'left' );

		return Velocity_Seed::row(
			$post_id,
			'hero',
			array(
				Velocity_Seed::column( $post_id, 'hero-text', $text, array( 'verticalAlignment' => 'middle' ) ),
				Velocity_Seed::column(
					$post_id,
					'hero-photo-col',
					$photo,
					array(
						'id'                => 2,
						'verticalAlignment' => 'middle',
					)
				),
			),
			array(
				'align'             => 'full',
				'bgColor'           => Velocity_Seed::color( 'palette3' ),
				'textColor'         => Velocity_Seed::color( 'palette9' ),
				'inheritMaxWidth'   => true,
				'firstColumnWidth'  => 60,
				'secondColumnWidth' => 40,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 48, 40, '' ),
				'verticalAlignment' => 'middle',
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'padding'           => array( '80', '', '80', '' ),
				'tabletPadding'     => array( '64', '', '64', '' ),
				'mobilePadding'     => array( '48', '', '48', '' ),
				'metadata'          => array( 'name' => 'Hero' ),
			)
		);
	}

	/**
	 * Trust strip: the program's figures on the orange band.
	 *
	 * @param int   $post_id Page ID.
	 * @param array $program One of programs().
	 * @return string
	 */
	public static function stats( $post_id, $program ) {
		// Five or six figures take the whole width of the design's band, 64px
		// from each side (1440 less the side padding), at a slightly smaller
		// size. vm-stats--spread sets them as the design does: each as wide as
		// its text, with the same space between one and the next.
		return Velocity_Home::stats(
			$post_id,
			$program['stats'],
			array(
				'maxWidth'     => 1440,
				'columnGutter' => 'custom',
				'customGutter' => array( 8, '', '' ),
				'className'    => 'vm-stats vm-stats--spread',
			),
			22
		);
	}

	/**
	 * Why brokers bring their deals here: three cards and a closing line.
	 *
	 * @param int   $post_id Page ID.
	 * @param array $program One of programs().
	 * @return string
	 */
	public static function why( $post_id, $program ) {
		$columns = array();

		foreach ( $program['why'][1] as $index => $card ) {
			$key   = 'why-' . $index;
			$attrs = Velocity_Home::card(
				array_merge(
					array(
						// As tall as the design's on desktop and tablet, as tall as its text on phones.
						'height'            => array( 263, '', 0 ),
						'textAlign'         => array( 'center', '', '' ),
						'justifyContent'    => array( 'center', '', '' ),
						'verticalAlignment' => 'middle',
						'metadata'          => array( 'name' => $card[1] ),
					),
					Velocity_Home::card_animation( $index )
				)
			);
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = Velocity_Seed::column(
				$post_id,
				$key,
				self::icon( $post_id, $key, $card[0] ) . "\n\n" .
				Velocity_Home::card_title( $post_id, $key, $card[1] ) . "\n\n" .
				Velocity_Home::body( $post_id, $key, $card[2] ),
				$attrs
			);
		}

		$inner =
			Velocity_Home::section_header( $post_id, 'why', 'Why Brokers Bring Their Deals Here', $program['why'][0], 854 ) . "\n\n" .
			Velocity_Seed::row(
				$post_id,
				'why-cards',
				$columns,
				array(
					'columnGutter'       => 'custom',
					'customGutter'       => array( 20, '', '' ),
					'columnsInnerHeight' => true,
					'tabletLayout'       => 'row',
					'mobileLayout'       => 'row',
					'padding'            => array( '0', '', '0', '' ),
					'metadata'           => array( 'name' => 'Cards' ),
				)
			) . "\n\n" .
			Velocity_Home::t(
				$post_id,
				'why-closing',
				'We help brokers move forward when others hit limits.',
				array(
					'align'  => 'center',
					'family' => 'heading',
					'color'  => Velocity_Seed::color( 'palette2' ),
					'size'   => array( 30, 26, 22 ),
					'weight' => '700',
					'lh'     => 1.1,
					'margin' => array( '88', '', '0', '' ),
				)
			);

		return Velocity_Home::section(
			$post_id,
			'why',
			$inner,
			'Why brokers bring their deals here',
			array(
				'padding'       => array( '80', '', '88', '' ),
				'mobilePadding' => array( '64', '', '64', '' ),
			)
		);
	}

	/**
	 * A table of terms for one kind of property: a Kadence Table of two columns.
	 *
	 * @param int     $post_id Page ID.
	 * @param string  $key     Key for the unique IDs.
	 * @param string  $name    Name of the column: "1-4 Units" or "Commercial".
	 * @param array[] $rows    Rows as programs() gives them.
	 * @param int     $column  Which value of a row to read: 1, or 2 for commercial.
	 * @return string
	 */
	private static function terms_table( $post_id, $key, $name, $rows, $column ) {
		$lines = array();

		foreach ( $rows as $row ) {
			$lines[] = array( $row[0], isset( $row[ $column ] ) ? $row[ $column ] : $row[1] );
		}

		return Velocity_Programs::table(
			$post_id,
			$key,
			array( 'Term', $name ),
			$lines,
			array(
				'size'  => 16,
				// The label column is 344px of 1035 in the design.
				'first' => 34,
				'label' => '600',
				'class' => 'vm-terms',
				'name'  => 'Terms: ' . $name,
			)
		);
	}

	/**
	 * Two tables behind a pair of tabs: a Kadence Tabs block.
	 *
	 * The tabs are drawn as one control with two halves. Their colors at
	 * rest, on hover and selected, their border and padding are settings of
	 * the block. vm-toggle joins them and rounds the two ends. See style.css.
	 *
	 * @param int      $post_id Page ID.
	 * @param string   $key     Key for the unique IDs.
	 * @param string[] $tabs    Tab title => its content.
	 * @return string
	 */
	private static function tabs( $post_id, $key, $tabs ) {
		$uid    = Velocity_Seed::uid( $post_id, $key );
		$navy   = Velocity_Seed::color( 'palette3' );
		$orange = Velocity_Seed::color( 'palette1' );
		$none   = array( '', '', 0 );
		$titles = array();
		$items  = '';
		$panes  = array();
		$index  = 0;

		foreach ( $tabs as $title => $content ) {
			++$index;
			$pane_uid = Velocity_Seed::uid( $post_id, $key . '-' . $index );
			// Kadence's own anchor for a tab: its title in lower case, without spaces.
			$anchor   = 'tab-' . strtolower( str_replace( ' ', '', $title ) );
			$titles[] = array(
				'text'     => $title,
				'icon'     => '',
				'iconSide' => 'right',
				'onlyIcon' => false,
				'subText'  => '',
				'anchor'   => '',
			);

			$items .= sprintf(
				'<li id="%1$s" class="kt-title-item kt-title-item-%2$d kt-tabs-svg-show-always kt-tabs-icon-side-right kt-tab-title-%3$s"><a href="#%1$s" data-tab="%2$d" class="kt-tab-title kt-tab-title-%2$d "><span class="kt-title-text">%4$s</span></a></li>',
				$anchor,
				$index,
				1 === $index ? 'active' : 'inactive',
				esc_html( $title )
			);

			$attrs = array( 'uniqueID' => $pane_uid );
			if ( $index > 1 ) {
				$attrs = array( 'id' => $index ) + $attrs;
			}

			$panes[] = Velocity_Seed::block(
				'kadence/tab',
				$attrs,
				'<div class="wp-block-kadence-tab kt-tab-inner-content kt-inner-tab-' . $index . ' kt-inner-tab' . $pane_uid . '"><div class="kt-tab-inner-content-inner">' . $content . '</div></div>'
			);
		}

		return Velocity_Seed::block(
			'kadence/tabs',
			array(
				'uniqueID'            => $uid,
				'tabCount'            => count( $tabs ),
				// No frame around the content, and no padding but the gap under the tabs.
				'contentBorderStyles' => array(
					array(
						'top'    => $none,
						'right'  => $none,
						'bottom' => $none,
						'left'   => $none,
						'unit'   => 'px',
					),
				),
				'innerPadding'        => array( '40', '0', '0', '0' ),
				'tabAlignment'        => 'center',
				'titles'              => $titles,
				'titleColor'          => $navy,
				'titleColorHover'     => $navy,
				'titleColorActive'    => Velocity_Seed::color( 'palette9' ),
				'titleBg'             => '',
				'titleBgHover'        => Velocity_Seed::color( 'palette9' ),
				'titleBgActive'       => $orange,
				'titleBorder'         => $navy,
				'titleBorderHover'    => $navy,
				'titleBorderActive'   => $orange,
				'titleBorderWidth'    => array( 1, 1, 1, 1 ),
				'titleBorderRadius'   => array( 0, 0, 0, 0 ),
				'titlePadding'        => array( 14, 28, 14, 28 ),
				'titleMargin'         => array( 0, 0, 0, 0 ),
				'size'                => 16,
				'lineHeight'          => 1.47,
				'lineType'            => '',
				'showPresets'         => false,
				'gutter'              => array( 0, '', '' ),
				'className'           => 'vm-toggle',
				'metadata'            => array( 'name' => 'Terms by property type' ),
			),
			'<div class="wp-block-kadence-tabs alignnone vm-toggle"><div class="kt-tabs-wrap kt-tabs-id' . $uid . ' kt-tabs-has-' . count( $tabs ) . '-tabs kt-active-tab-1 kt-tabs-layout-tabs kt-tabs-tablet-layout-inherit kt-tabs-mobile-layout-inherit kt-tab-alignment-center "><ul class="kt-tabs-title-list">' . $items . '</ul><div class="kt-tabs-content-wrap">' . implode( "\n\n", $panes ) . '</div></div></div>'
		);
	}

	/**
	 * The program at a glance: its terms, on the light blue panel.
	 *
	 * A program for 1-4 units and commercial property shows a table for
	 * each, behind two tabs. A program for 1-4 units only shows one table.
	 *
	 * @param int   $post_id Page ID.
	 * @param array $program One of programs().
	 * @return string
	 */
	public static function terms( $post_id, $program ) {
		list( $intro, $both, $rows ) = $program['terms'];

		if ( $both ) {
			$terms = self::tabs(
				$post_id,
				'terms-tabs',
				array(
					'1-4 Units'  => self::terms_table( $post_id, 'terms-units', '1-4 Units', $rows, 1 ),
					'Commercial' => self::terms_table( $post_id, 'terms-commercial', 'Commercial', $rows, 2 ),
				)
			);
		} else {
			$terms = self::terms_table( $post_id, 'terms-units', '1-4 Units', $rows, 1 );
		}

		$inner =
			Velocity_Home::section_header( $post_id, 'terms', $program['name'] . ' at a Glance', $intro, 858 ) . "\n\n" .
			Velocity_Seed::row(
				$post_id,
				'terms-wrap',
				array( Velocity_Seed::column( $post_id, 'terms-wrap-col', $terms ) ),
				array(
					'maxWidth' => 1035,
					'padding'  => array( '0', '', '0', '' ),
					'metadata' => array( 'name' => 'Terms' ),
				)
			);

		return Velocity_Home::panel(
			$post_id,
			'terms',
			$inner,
			'The program at a glance',
			array(
				'background' => Velocity_Seed::color( 'palette7' ),
				'padding'    => array( '80', '64', '80', '64' ),
			),
			// The space between this panel and the next one.
			array(
				'padding'       => array( '0', '32', '46', '32' ),
				'mobilePadding' => array( '0', '0', '0', '0' ),
			)
		);
	}

	/**
	 * The clients who fit the program: three profiles on the navy panel.
	 *
	 * @param int   $post_id Page ID.
	 * @param array $program One of programs().
	 * @return string
	 */
	public static function clients( $post_id, $program ) {
		list( $title, $intro, $profiles ) = $program['clients'];

		$white = Velocity_Seed::color( 'palette9' );
		// "Darker orange" in the design. It passes AA on the card's background.
		$rust    = '#b04c11';
		$columns = array();

		foreach ( $profiles as $index => $profile ) {
			$key   = 'client-' . $index;
			$attrs = array_merge(
				array(
					'background'        => '#f8e8de',
					'borderStyle'       => Velocity_Home::border( Velocity_Seed::color( 'palette2' ) ),
					'borderRadius'      => array( 8, 8, 8, 8 ),
					'padding'           => array( '24', '24', '24', '24' ),
					'height'            => array( 210, '', 0 ),
					'verticalAlignment' => 'middle',
					'rowGapVariable'    => array( 'custom', '', '' ),
					'rowGap'            => array( 13, '', '' ),
					'metadata'          => array( 'name' => $profile[0] ),
				),
				Velocity_Home::card_animation( $index )
			);
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = Velocity_Seed::column(
				$post_id,
				$key,
				self::icon( $post_id, $key, 'icon-check-circle.svg', 32 ) . "\n\n" .
				Velocity_Seed::column(
					$post_id,
					$key . '-text',
					Velocity_Home::t(
						$post_id,
						$key . '-title',
						$profile[0],
						array(
							'tag'    => 'h3',
							'family' => 'body',
							'size'   => array( 20, '', '' ),
							'weight' => '700',
							'lh'     => 1.31,
							'color'  => $rust,
							'margin' => array( '0', '', '0', '' ),
						)
					) . "\n\n" .
					Velocity_Home::t(
						$post_id,
						$key . '-body',
						$profile[1],
						array(
							'color'  => $rust,
							'margin' => array( '0', '', '0', '' ),
						)
					),
					array( 'metadata' => array( 'name' => 'Text' ) )
				),
				$attrs
			);
		}

		$inner =
			Velocity_Seed::row(
				$post_id,
				'clients-header',
				array(
					Velocity_Seed::column(
						$post_id,
						'clients-header-col',
						Velocity_Home::t(
							$post_id,
							'clients-title',
							$title,
							array(
								'tag'    => 'h2',
								'align'  => 'center',
								'color'  => $white,
								'margin' => array( '0', '', '16', '' ),
								'class'  => 'is-style-vm-reveal-lines',
							)
						) . "\n\n" .
						Velocity_Home::subtitle(
							$post_id,
							'clients',
							$intro,
							array(
								'align' => 'center',
								'color' => $white,
							)
						)
					),
				),
				array(
					'maxWidth' => 854,
					'padding'  => array( '0', '', '40', '' ),
					'metadata' => array( 'name' => 'Section header' ),
				)
			) . "\n\n" .
			Velocity_Seed::row(
				$post_id,
				'clients-cards',
				$columns,
				array(
					'maxWidth'           => 1216,
					'columnGutter'       => 'custom',
					'customGutter'       => array( 16, '', '' ),
					'columnsInnerHeight' => true,
					'tabletLayout'       => 'row',
					'mobileLayout'       => 'row',
					'padding'            => array( '0', '', '0', '' ),
					'metadata'           => array( 'name' => 'Client profiles' ),
				)
			);

		return Velocity_Home::panel(
			$post_id,
			'clients',
			$inner,
			'The clients who fit',
			array(
				'backgroundType' => 'gradient',
				'background'     => Velocity_Seed::color( 'palette3' ),
				'gradient'       => 'linear-gradient(144deg,rgb(1,7,57) 31%,rgb(43,53,120) 80%)',
				'borderRadius'   => array( 20, 20, 20, 20 ),
				'padding'        => array( '96', '80', '96', '80' ),
			),
			// The space under the panel, which shows while there is no case study.
			array(
				'padding'       => array( '0', '32', '46', '32' ),
				'mobilePadding' => array( '0', '0', '0', '0' ),
			)
		);
	}

	/**
	 * The case study of each program: a Kadence query (Kadence, Queries)
	 * that lists the latest case study filed under the program. The four
	 * share one card (Kadence, Query Cards).
	 *
	 * @return array[] By program key, as Velocity_Seed::query_meta() takes them.
	 */
	public static function queries() {
		$queries = array();

		foreach ( self::programs() as $key => $program ) {
			$queries[ $key ] = array(
				'title'      => 'Program case study: ' . $program['name'],
				'post_type'  => 'velocity-case-study',
				'term'       => array( self::TAXONOMY, $program['term'] ),
				'order_by'   => 'date',
				'order'      => 'desc',
				'per_page'   => '1',
				'columns'    => array( '1', '1', '1' ),
				'column_gap' => array( '0', '0', '0' ),
				'row_gap'    => array( '0', '0', '0' ),
				'card'       => array( __CLASS__, 'case_card' ),
				// One card for the four queries.
				'card_key'   => 'case-study',
				'card_title' => 'Program case study card',
			);
		}

		return $queries;
	}

	/**
	 * The card of a program's case study: its title, its photo, three
	 * figures and the button to the whole story.
	 *
	 * Every part reads the case study being listed, with Kadence's dynamic
	 * content. The section's own space is part of the card, so a program
	 * with no case study shows nothing at all.
	 *
	 * The design leaves the three boxes to be defined. Until then they show
	 * what a case study already holds: loan amount, days to close, location.
	 *
	 * @param int $card_id Query card post ID.
	 * @return string
	 */
	public static function case_card( $card_id ) {
		$photo_uid = Velocity_Seed::uid( $card_id, 'photo' );

		$photo = Velocity_Seed::block(
			'kadence/image',
			array(
				// A fixed shape, three wide by four tall, whatever the photo's own,
				// where the photo sits above the text. Beside the text,
				// vm-photo-match makes it as tall as the text, down to the button.
				'ratio'           => 'port34',
				'useRatio'        => true,
				'className'       => 'vm-photo-match',
				'imgMaxWidth'     => 473,
				'linkDestination' => 'none',
				'uniqueID'        => $photo_uid,
				'marginDesktop'   => array( '0', '', '0', '' ),
				'borderRadius'    => array( 30, 30, 30, 30 ),
				'inQueryBlock'    => true,
				'kadenceDynamic'  => array(
					'url' => array(
						'enable'   => true,
						'source'   => '',
						'field'    => 'post|post_featured_image',
						'custom'   => '',
						'para'     => '',
						// The image size.
						'before'   => 'large',
						'after'    => '',
						'fallback' => '',
					),
				),
			),
			sprintf(
				'<figure class="wp-block-kadence-image kb-image%1$s kb-image-is-ratio-size vm-photo-match"><div class="kb-is-ratio-image kb-image-ratio-port34"><img src="%2$s" alt="" class="kb-img"/></div></figure>',
				$photo_uid,
				esc_url( includes_url( 'images/blank.gif' ) )
			)
		);

		$figures = array(
			// The amount through its own field, which gives it its dollar sign.
			array( 'Loan amount', 'post|vm_case_amount' ),
			array( 'Days to close', array( 'post|post_custom_field', 'velocity_case_study_days_to_close' ) ),
			array( 'Location', array( 'post|post_custom_field', 'velocity_case_study_location' ) ),
		);

		$boxes = array();
		foreach ( $figures as $index => $figure ) {
			$key     = 'case-figure-' . $index;
			$boxes[] = Velocity_Seed::column(
				$card_id,
				$key,
				Velocity_Home::t(
					$card_id,
					$key . '-label',
					$figure[0],
					array(
						'size'   => array( 14, '', '' ),
						'lh'     => 1.1,
						'color'  => Velocity_Seed::color( 'palette5' ),
						'margin' => array( '0', '', '0', '' ),
					)
				) . "\n\n" .
				Velocity_Home::t(
					$card_id,
					$key . '-value',
					'',
					array(
						'family'  => 'heading',
						'size'    => array( 24, '', '' ),
						'weight'  => '700',
						'lh'      => 1.1,
						'margin'  => array( '0', '', '0', '' ),
						'dynamic' => $figure[1],
					)
				),
				array(
					'background'     => Velocity_Seed::color( 'palette7' ),
					'borderStyle'    => Velocity_Home::border( '#6885ee' ),
					'borderRadius'   => array( 12, 12, 12, 12 ),
					'padding'        => array( '19', '36', '19', '36' ),
					'mobilePadding'  => array( '16', '20', '16', '20' ),
					'rowGapVariable' => array( 'custom', '', '' ),
					'rowGap'         => array( 12, '', '' ),
					'inQueryBlock'   => true,
					'metadata'       => array( 'name' => $figure[0] ),
				)
			);
		}

		$link   = "[kb-dynamic field='post|post_url' type='url']";
		$button = Velocity_Home::buttons(
			$card_id,
			'case',
			array(
				array(
					'Read the Full Story',
					$link,
					'fill',
					array(
						'inQueryBlock'   => true,
						'kadenceDynamic' => array(
							'link' => array(
								'enable'   => true,
								'source'   => '',
								'field'    => 'post|post_url',
								'custom'   => '',
								'para'     => '',
								'before'   => '',
								'after'    => '',
								'fallback' => '',
							),
						),
					),
				),
			),
			'left'
		);

		$story = Velocity_Seed::row(
			$card_id,
			'case-row',
			array(
				Velocity_Seed::column(
					$card_id,
					'case-photo',
					$photo,
					array(
						'inQueryBlock' => true,
						'metadata'     => array( 'name' => 'Photo' ),
					)
				),
				Velocity_Seed::column(
					$card_id,
					'case-figures',
					implode( "\n\n", $boxes ) . "\n\n" . $button,
					array(
						'id'             => 2,
						'rowGapVariable' => array( 'custom', '', '' ),
						'rowGap'         => array( 29, '', 20 ),
						'inQueryBlock'   => true,
						'metadata'       => array( 'name' => 'Figures and button' ),
					)
				),
			),
			array(
				'firstColumnWidth'  => 36,
				'secondColumnWidth' => 64,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 48, 32, 32 ),
				'mobileLayout'      => 'row',
				'padding'           => array( '0', '', '0', '' ),
				'inQueryBlock'      => true,
				'metadata'          => array( 'name' => 'Photo and figures' ),
			)
		);

		return Velocity_Seed::column(
			$card_id,
			'case',
			Velocity_Home::t(
				$card_id,
				'case-title',
				'',
				array(
					'tag'     => 'h2',
					'align'   => 'center',
					'margin'  => array( '0', '', '48', '' ),
					'dynamic' => 'post|post_title',
				)
			) . "\n\n" . $story,
			array(
				// The section's space lives here: no case study, no empty band.
				'padding'       => array( '80', '', '80', '' ),
				'mobilePadding' => array( '64', '', '64', '' ),
				'inQueryBlock'  => true,
				'metadata'      => array( 'name' => 'Case study' ),
			)
		);
	}

	/**
	 * A deal that fits: the program's latest case study.
	 *
	 * The list is the Kadence query "Program case study: ...". It has no
	 * space of its own, so the page closes up while the program has no case
	 * study.
	 *
	 * @param int $post_id  Page ID.
	 * @param int $query_id ID of the program's case study query.
	 * @return string
	 */
	public static function case_study( $post_id, $query_id ) {
		return Velocity_Home::section(
			$post_id,
			'case',
			Velocity_Seed::block(
				'kadence/query',
				array(
					'uniqueID' => Velocity_Seed::uid( $post_id, 'case-query' ),
					'id'       => (int) $query_id,
				)
			),
			'A deal that fits (case study)',
			array(
				'padding'       => array( '0', '', '0', '' ),
				'mobilePadding' => array( '0', '', '0', '' ),
			)
		);
	}

	/**
	 * The other programs: title and intro beside a card for each.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $current Key of this page's program, which is left out.
	 * @return string
	 */
	public static function others( $post_id, $current ) {
		$columns  = array();
		$index    = 0;
		$cards    = self::invitations();
		$programs = self::programs();

		// A program may name the order of its cards. Otherwise, every other
		// program in the order of invitations().
		$order = isset( $programs[ $current ]['others'] ) ? $programs[ $current ]['others'] : array_keys( $cards );

		foreach ( $order as $key ) {
			if ( $key === $current || ! isset( $cards[ $key ] ) ) {
				continue;
			}

			$card = $cards[ $key ];

			$card_key = 'other-' . $key;
			$attrs    = array_merge(
				array(
					'background'        => Velocity_Seed::color( 'palette9' ),
					'borderStyle'       => Velocity_Home::border( Velocity_Seed::color( 'palette1' ) ),
					'borderRadius'      => array( 8, 8, 8, 8 ),
					'padding'           => array( '32', '25', '32', '25' ),
					// As tall as the design's on desktop, as tall as its content below.
					'height'            => array( 475, 0, 0 ),
					// The icon at the top, the text and the button at the bottom.
					'verticalAlignment' => 'space-between',
					'rowGapVariable'    => array( 'custom', '', '' ),
					'rowGap'            => array( 32, '', '' ),
					// The whole card is the title's link, and it grows under the pointer.
					'className'         => 'vm-card-link vm-card-grow',
					'metadata'          => array( 'name' => $card[1] ),
				),
				Velocity_Home::card_animation( $index )
			);
			if ( $index ) {
				$attrs['id'] = $index + 1;
			}

			$columns[] = Velocity_Seed::column(
				$post_id,
				$card_key,
				self::icon( $post_id, $card_key, $card[0] ) . "\n\n" .
				Velocity_Seed::column(
					$post_id,
					$card_key . '-text',
					Velocity_Home::t(
						$post_id,
						$card_key . '-title',
						$card[1],
						array(
							'tag'    => 'h3',
							'size'   => array( 30, 26, 24 ),
							'weight' => '600',
							'lh'     => 1.1,
							'margin' => array( '0', '', '12', '' ),
							'href'   => self::url( $key ),
						)
					) . "\n\n" .
					Velocity_Home::t( $post_id, $card_key . '-body', $card[2], array( 'margin' => array( '0', '', '26', '' ) ) ) . "\n\n" .
					// The design's arrow, 16px: with a bigger one the longest label leaves the card.
					Velocity_Home::buttons( $post_id, $card_key, array( array( $card[3], self::url( $key ), '', array( 'iconSize' => array( 16, '', '' ) ) ) ), 'left' ),
					array( 'metadata' => array( 'name' => 'Text and button' ) )
				),
				$attrs
			);

			++$index;
		}

		$intro =
			Velocity_Home::t(
				$post_id,
				'others-title',
				'Other Ways We’ll Get You to Yes',
				array(
					'tag'    => 'h2',
					'margin' => array( '0', '', '16', '' ),
					'class'  => 'is-style-vm-reveal-lines',
				)
			) . "\n\n" .
			Velocity_Home::subtitle( $post_id, 'others', 'Find Your Fit in Our Other Programs' );

		$row = Velocity_Seed::row(
			$post_id,
			'others-row',
			array(
				Velocity_Seed::column(
					$post_id,
					'others-intro',
					$intro,
					array(
						'verticalAlignment' => 'middle',
						'metadata'          => array( 'name' => 'Title and intro' ),
					)
				),
				Velocity_Seed::column(
					$post_id,
					'others-cards-col',
					Velocity_Seed::row(
						$post_id,
						'others-cards',
						$columns,
						array(
							'columnGutter'       => 'custom',
							'customGutter'       => array( 24, 20, 20 ),
							'columnsInnerHeight' => true,
							'tabletLayout'       => 'row',
							'mobileLayout'       => 'row',
							'padding'            => array( '0', '', '0', '' ),
							'metadata'           => array( 'name' => 'Programs' ),
						)
					),
					array(
						'id'       => 2,
						'metadata' => array( 'name' => 'Cards' ),
					)
				),
			),
			array(
				'firstColumnWidth'  => 25,
				'secondColumnWidth' => 75,
				'columnGutter'      => 'custom',
				'customGutter'      => array( 32, 40, 32 ),
				'verticalAlignment' => 'middle',
				'tabletLayout'      => 'row',
				'mobileLayout'      => 'row',
				'padding'           => array( '0', '', '0', '' ),
				'metadata'          => array( 'name' => 'Other programs' ),
			)
		);

		return Velocity_Home::section(
			$post_id,
			'others',
			$row,
			'The other programs',
			array(
				'bgColor'         => '#f8e8de',
				// 1312px wide in the design, wider than the page's own 1280px: 1440 with
				// the 64px Kadence keeps on each side.
				'inheritMaxWidth' => false,
				'maxWidth'        => 1440,
				'padding'         => array( '80', '', '80', '' ),
				'mobilePadding'   => array( '64', '', '48', '' ),
			)
		);
	}

	/**
	 * The call to action band that closes the page.
	 *
	 * Its top sits on the peach of the section above: the background of its
	 * own section is a gradient with a hard stop, peach then white.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function cta( $post_id ) {
		return Velocity_Home::cta_band(
			$post_id,
			'Let’s Get Your Deal Funded',
			array(
				array( 'Become a Partner', home_url( '/become-a-partner/' ) ),
				array( 'See How It Works', home_url( '/process/' ), 'outline', Velocity_Home::on_dark() ),
			),
			1280,
			array(
				'backgroundSettingTab' => 'gradient',
				'gradient'             => 'linear-gradient(180deg,rgb(248,232,222) 136px,rgb(255,255,255) 136px)',
				'padding'              => array( '0', '', '48', '' ),
				'mobilePadding'        => array( '0', '', '32', '' ),
			)
		);
	}

	/**
	 * Every section of a program's page, in page order.
	 *
	 * @param int    $post_id  Page ID.
	 * @param string $key      Program key.
	 * @param int    $query_id ID of the program's case study query.
	 * @return string
	 */
	public static function content( $post_id, $key, $query_id ) {
		$programs = self::programs();
		$program  = $programs[ $key ];

		return implode(
			"\n\n",
			array(
				self::hero( $post_id, $program ),
				self::stats( $post_id, $program ),
				self::why( $post_id, $program ),
				self::terms( $post_id, $program ),
				self::clients( $post_id, $program ),
				self::case_study( $post_id, $query_id ),
				self::others( $post_id, $key ),
				self::cta( $post_id ),
				Velocity_Home::faq_section( $post_id, $program['faq'] ),
			)
		);
	}
}
