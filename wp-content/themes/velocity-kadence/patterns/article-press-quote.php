<?php
/**
 * Title: Article: Press Quote
 * Slug: velocity-kadence/article-press-quote
 * Categories: velocity
 * Keywords: quote, press, headshot, insight, article
 * Post Types: insight, velocity-case-study
 * Description: A quote with the photo of the person, for an article or a press release of News and Insights: the photo, the quote, and the name and title in the citation line. Delete the image for a quote with no photo. Its look comes from the class vm-press-quote.
 *
 * @package Velocity_Kadence
 */

?>
<!-- wp:group {"metadata":{"name":"Press Quote"},"className":"vm-press-quote","layout":{"type":"constrained"}} -->
<div class="wp-block-group vm-press-quote"><!-- wp:image -->
<figure class="wp-block-image"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><!-- wp:paragraph -->
<p>“The quote, in the person’s own words.”</p>
<!-- /wp:paragraph --><cite>First Last, Title</cite></blockquote>
<!-- /wp:quote --></div>
<!-- /wp:group -->
