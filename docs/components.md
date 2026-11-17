# Building pages

Pages are built in the block editor with Kadence Blocks. This page explains how the home page is put together, so the next pages follow the same approach.

## The rule

There are three layers. Each thing lives in exactly one of them.

| Layer | What goes there | Where |
|---|---|---|
| Design tokens | Colors, fonts, heading sizes, button style | Code, `inc/tokens.php`. Apply on their own, everywhere |
| Block settings | Borders, radius, shadows, padding, backgrounds, alignment, text sizes | The editor sidebar, on each block |
| CSS classes | Only what the sidebar cannot do | `style.css`, listed under "CSS classes" below |

**If the sidebar can do it, do it in the sidebar.** A style set there is visible to whoever opens the block, and the editor shows exactly what the site shows. Reach for a CSS class only when a block has no setting for what you need.

When such a CSS rule has to be chosen block by block by an editor, it is registered as a **block style**, so it shows up as an option in the block's Styles panel instead of a class to type. The program images and the heading animation work this way. See "Where block styles live in the sidebar".

## An empty field means "inherit"

Select a section title in the editor and its Color, Font Size and Line Height are all empty. That is correct. The title is navy and 56px because every H2 on the site is, by token. Nothing is set on the block, so nothing shows in the block.

An empty field inherits from the nearest place that sets it:

1. The Row Layout or Section that contains the block, if that one has a text color. The hero and the trust strip work this way: the row sets the text color once and every text inside follows.
2. Otherwise, the global style of the site.

To find where a color comes from, select the block and use "Select parent block", the first button in the floating toolbar, until you reach a Row Layout or Section with a value.

| You see in the sidebar | It means |
|---|---|
| An empty field | The block inherits, from its row or from the global style |
| A value | This block overrides the global style, on purpose |

To see the global values, open Appearance, Customize, Colors & Fonts. They are listed there, read-only in practice: change them in `inc/tokens.php`, not in the Customizer.

On the home page, these inherit and show empty fields: every H1, H2 and H3 at its normal size, body text, and the primary and outline buttons. These carry their own values: intro texts, card titles, the closing line, numbers and labels, cards, panels and the buttons on dark backgrounds.

## The blocks to use

| For | Use |
|---|---|
| A section, a grid, columns | Row Layout |
| A column, a card, a colored area | Section, the block inside a Row Layout |
| Any text: headings and paragraphs | Text (Adv) |
| An image | Image (Adv) |
| A button | Advanced Button |

Text (Adv) is used for all text instead of the core Heading and Paragraph blocks, because it has size, weight, line height, color and margin in the sidebar.

## Colors

In a block's color control, pick from the **Style Library** tab, not from Custom. Those are the site's colors, and a block that uses one follows the palette if it ever changes.

| Style Library name | Color |
|---|---|
| Main 1 | Action Orange |
| Main 2 | Deep Rust |
| Strongest, Strong | Brand Navy |
| Medium | Indigo |
| Subtle | Gray |

The list has a second group for backgrounds, where the light blue, the off white and the white are. The names repeat between groups, so check the swatch before picking.

Three entries in the list do not belong to this site's palette and still hold Kadence's own defaults: Main 3, Button and Button Hover. Do not use them.

Supporting Orange, used by the trust strip and the step numbers, has no entry: type `#f18521` in the Custom tab.

Kadence Blocks stores these as design tokens, written like `{primitive.color.brand.secondary}`. Older content uses `palette2` instead. That older format still shows the right color on the site, but the color control displays it as empty, which looks like the block has no color set. If you see an empty color control on a block that is clearly colored, pick the color again from the Style Library.

## Recipes

These are the settings behind each piece of the home page. Open the home page in the editor and select the block to see them live.

### A section

Row Layout, one column.

- Align: Full width.
- Content max width: Inherit from theme.
- Padding: 96 top and bottom. Mobile: 64.
- Background: white, or whatever the section needs.

### A panel

The rounded, colored area that sits inset from the page edges, as in Programs and in the five steps.

- **Outer Row Layout**: full width, no background. Max width 1920. Padding 32 left and right. Mobile padding 0.
- **Section inside it**: carries the look. Background color or gradient. Border radius 30, mobile 0. Padding 112 top and bottom, 64 sides. Tablet 80 and 32. Mobile 64 and 20.

On mobile the gutters and the radius drop to zero, so the panel runs edge to edge. That is the rule for any section wider than the content column.

On very wide screens a panel stops growing at 1920px, gutters included, and stays centered. A row inside a panel gets its own max width, 1280 for a full row, so the content lines up with the rest of the page while the panel grows. A section with a plain full-width background, such as the people on the About Us page, has no limit.

### A card

A Section with these settings:

| Setting | Value |
|---|---|
| Background | White, palette 9 |
| Border | 1px, `#e5e5e5` |
| Border on hover | 1px, `rgba(43,53,120,0.24)` |
| Border radius | 8 |
| Padding | 32 on all sides |
| Box shadow | On. Black at 8%, vertical offset 12, blur 16, spread -4 |
| Box shadow on hover | On. `#010739` at 18%, vertical offset 24, blur 32, spread -8 |
| Gap between its blocks | Custom, 16 |

For a centered card add: text align center, horizontal align center, vertical align middle.

The resource cards are the same card with an off-white background, a white border, padding 36 and 24, a 24 gap, and vertical align bottom.

To keep cards in a row the same height, turn on "Inner column height 100%" on the Row Layout that holds them.

**Reuse a card instead of rebuilding it.** Select one, then use Copy styles and Paste styles from the block menu, or save it as a pattern.

### Text styles

| Style | Text (Adv) settings |
|---|---|
| Section title | H2. Nothing else, the token does the rest |
| Intro under a title | Paragraph, size 20, mobile 18, line height 1.31 |
| Card title | H3, size 22, weight 700 |
| Closing line of a section | Paragraph (HTML tag `p`), size 30, mobile 22, weight 600, color Deep Rust. It looks like a heading but is not one |
| Big number | Paragraph, heading font, size 24, weight 700 |
| Small label | Paragraph, size 14, line height 1.1 |
| Pill label | Paragraph, size 14, with the word highlighted. Highlight: indigo text, light blue background, padding 4 and 8, radius 5, weight 600 |

Heading levels follow the page outline, not the look. A card title is an H3 at 22px, not a paragraph made to look big.

A headline with two weights: write it normally and make the second half bold. In H1 and H2 bold renders as semibold, which is what the design uses.

### Buttons

Advanced Button. It follows the style guide on its own: pill shape, 20px semibold.

| Style | How |
|---|---|
| Primary, orange fill | The default |
| Outline, orange | Set the button style to Outline |
| Arrow | Add the "arrow right" icon on the right side |
| Outline on a dark background | Outline, then in the button's Style tab: text color white, border white 1px. Under Hover: text Brand Navy, background white, border white 1px |

### Images

Images used by the seeded header, footer and home page live in the theme under `assets/images`, not in the media library. A media library ID differs between environments, a theme file is the same everywhere.

Images added later in the editor go to the media library as usual.

## CSS classes

The exceptions. Each one exists because no block setting can do the job. To use one, select the block, open Advanced in the sidebar and type the class in "Additional CSS class(es)".

| Class | Put it on | Why it is a class |
|---|---|---|
| `vm-eyebrow` | Text (Adv) | Adds the orange dot before a small label. Text has no setting for that |
| `vm-stats` | Row Layout | On tablet and mobile the items wrap centered, so an odd one out sits in the middle. A row's grid leaves it on the left |
| `vm-tag` | Text (Adv) used as a small filled label | Makes it as wide as its text, and hides it, keeping its space, when its dynamic content is empty |
| `vm-menu-featured` | A navigation link inside a dropdown | Sets the item apart with a rule, bold text and an arrow. Navigation has no per-item styling |
| `vm-card-link` | Section that is a card, with a Text (Adv) linked to a page | Stretches that link over the whole card, so the card is one link with a name |
| `vm-people` | Row around a Kadence query | Centers a last line of cards that is not full. See "The About Us page" |
| `vm-carousel` | Section around a Kadence query, or around a `vm-carousel-track` | Makes the list scroll sideways, with arrows when it does not fit. Kadence's query has no carousel |
| `vm-carousel-track` | Horizontal Section of cards inside a `vm-carousel` | The cards scroll sideways and keep their width. See "The Process page" |
| `vm-compare` | Kadence Table | Keeps the table at a readable width on small screens, scrolling sideways. Kadence has no minimum width setting |
| `vm-toggle` | Kadence Tabs | Joins the tabs into one control and rounds only its two ends. The block has one radius setting for every tab. See "The program pages" |
| `vm-hide-empty` | Text (Adv) with dynamic content | The text is not printed when it has no value. Kadence would print it empty, with its background and margins |
| `vm-toolkit-inline`, `vm-toolkit-form`, `vm-toolkit-doc`, `vm-toolkit-wl`, `vm-toolkit-branded` | Broker Tools page | Where the unlock form lands, and what hides by itself. Their rules are in the must-use plugin. See [database-and-content.md](database-and-content.md), "Broker Toolkit" |
| `vm-story-media`, `vm-story-stat` | Section around a photo, and the figures laid over it | Places the figures over the photo. Sections have no setting for overlapping |
| `vm-footer-main`, `vm-footer-legal` | The two rows of the footer | Line heights, the rule above the legal text, and the underline on legal links |

## The deal card

"Deals Other Brokers Already Placed" on the home page is a Kadence query, "Home: closed deals" (Kadence > Queries), with its card, "Home: closed deals card" (Kadence > Query Cards). The card is made of normal blocks with Kadence's dynamic content, so its look is set in the sidebar:

| On the card | Block | Dynamic content |
|---|---|---|
| Photo | Image, with a fixed 2:1 shape | Post featured image |
| Closing time, with the bolt | Text (Adv) with an icon, a background and the class `vm-tag` | "Deal: closing time". The property field "Processed Details Text" |
| Title | Text (Adv), linked to the post | "Deal: property type and purpose". Falls back to the property name when both are empty |
| Program | Text (Adv) | "Deal: loan program" |
| Name | Text (Adv) | "Deal: account executive" |

The four "Deal: ..." fields are in the dynamic content list under "Velocity". They exist because Kadence has no field for a taxonomy term as plain text. They are defined in `inc/dynamic-content.php`, where a new one takes a line and a small function.

A text is left out when the property has no value for it, so an incomplete card means incomplete data on that property. The one exception is the closing time: without a value it keeps its space, invisibly, so the titles line up across the cards. The title links to the property, and `vm-card-link` on the card stretches that link over the whole card.

`wp velocity seed-home` creates the query and its card.

## Frequently asked questions

A Kadence Accordion block. The look of a question at rest, on hover and open, the gap between questions, the plus and minus icon and the answer's padding are settings of the Accordion, under Style. Each question is a pane; its answer is made of Paragraph blocks.

Opening a question closes the others, and "Enable FAQ Schema" is on, so search engines can read the questions and answers. To add a question, select the Accordion and add a pane.

## Forms

Forms are Gravity Forms. A page never embeds one by its number. It uses a pattern, found in the inserter under Patterns, "Velocity":

| Pattern | What it places |
|---|---|
| Contact form | The card with the "Contact Us" title and the contact form |
| Newsletter sign-up | Heading, intro and the newsletter form. Put it inside a section or panel, which gives it its background |

Inside each pattern the form is a Shortcode block: `[vm_form name="contact"]` or `[vm_form name="newsletter"]`. The shortcode finds the right form on each environment, so a page keeps working when it moves from staging to production. In the editor the block shows the shortcode text, not the form. Use Preview to see it.

The card, the heading and the spacing around a form are normal blocks, edited in the sidebar. Do not rebuild the fields, the consent text or the button in the page.

| Form | Edited in | Notes |
|---|---|---|
| Contact | Forms, "Contact Us Page" (form 12) | The consent checkbox is required. Notifications are the ones the form already had |
| Newsletter | Forms, "Newsletter Signup" | Stores each sign-up as an entry and sends no email. The newsletter service is connected to this form once it is chosen. The consent line under the field is printed by the shortcode |
| Broker Toolkit unlock | Forms, "Broker Toolkit Item Request" (form 14) | Opens by itself from the toolkit buttons. See [database-and-content.md](database-and-content.md), "Broker Toolkit" |

The look of every Gravity Form is in the theme's `style.css`, "Forms": fields, labels, checkbox, errors and the submit button, which follows the primary button and gets its arrow from `inc/forms.php`. A form with the CSS class `vm-form-inline`, set in its Gravity Forms settings, puts the field and the button on one line.

`wp velocity seed-forms` prepares both forms on a new environment. See [database-and-content.md](database-and-content.md), "Theme commands".

## Offices map

The map on the Contact page is the block **Offices Map**. It is one of the theme's own blocks, in `blocks/offices-map`, like the loan calculator.

### Editing the offices

Select the map in the editor. The block settings in the sidebar list the offices, one panel each.

| Field | Use |
|---|---|
| City | The title of the card, for example "Las Vegas, NV" |
| Address | As it reads on the card, one line per row: street and suite, then city, state and ZIP code |
| Latitude, Longitude | Filled in from the address. They can be typed by hand |
| Move pin right, Move pin down | Optional. To separate pins that sit on top of each other, in pixels of the full size map, which is 904 wide |

To add an office, press "Add office", type the city and the address, and click outside the address field. The pin appears on the map in the editor. "Move up" and "Move down" change the order of the cards in the list shown on phones.

The offices are saved in the block, with the page. Copying the block to another environment brings them along.

### How a pin is placed

1. When an address is typed or changed, the editor asks the site for its coordinates. The site looks the exact address up in the US Census Bureau geocoder.
2. The Census Bureau does not know every street, office parks in particular. For those, the last line of the address (city, state and ZIP code) is looked up in OpenStreetMap instead, and the pin goes on the town. On a map of the whole country that is the same place. The editor says which of the two happened.
3. The theme turns the coordinates into a position on the drawing.

Neither service needs a key. Nothing is looked up when a visitor opens the page: the coordinates are stored in the block.

Only US addresses are found. For any other place, or if both lookups fail, type the latitude and longitude by hand.

### The seven offices of the design

The design moves some pins away from their real place so that neighboring offices can be told apart, mostly in Southern California. The offices created by `wp velocity seed-contact` carry those moves in "Move pin right" and "Move pin down", so they sit exactly where the design draws them. A new office starts at its real place.

### The drawing

`assets/images/us-map.svg` is the map from Figma, without the pins. It follows the Albers USA projection, with Alaska and Hawaii drawn smaller in the bottom left corner. `inc/offices.php` projects a place the standard way and then scales it onto the drawing, with one scale for the lower 48 states, one for Alaska and one for Hawaii. Those numbers were measured against this drawing: for the lower 48, a state's outline and its projection agree within about 3 pixels.

If the drawing is replaced with a different one, the numbers have to be measured again, or the pins will be off.

### On the page

- Hovering a pin, focusing it with the keyboard or clicking it opens the office's card above it. A click keeps it open until another pin is clicked, Escape is pressed or the visitor clicks elsewhere.
- Below 768px there is no room for that. The pins are decoration and the cards are listed under the map.
- The card's look and the pin are in the block's `style.css`.

## The About Us page

The page at `/about/` is built from the recipes above plus four pieces of its own: a timeline, a photo with figures over it, a list of people and a carousel. The two lists are Kadence queries. `wp velocity seed-company` creates its first version.

### Timeline

Two block styles, picked in the Styles panel of the block.

| Style | Put it on | What it does |
|---|---|---|
| Timeline | The Section that holds the texts | Draws the line down its left side. Give the Section 26px of left padding |
| Timeline point | A Text (Adv) inside that Section | Draws a dot on the line, level with the text's first line |

A text without "Timeline point" belongs to the point above it. To add a date to the story, duplicate a text that has the dot.

### Photo with figures

In "The Velocity Story", the photo and the three orange figures are normal blocks: a Kadence Image and three Sections, inside a Section with the class `vm-story-media`. Their texts, colors and sizes are block settings. The classes only place them:

| Class | Put it on | Where it goes |
|---|---|---|
| `vm-story-media` | The Section around the photo and the figures | |
| `vm-story-stat vm-story-stat--volume` | A figure's Section | Top left, over the photo's corner |
| `vm-story-stat vm-story-stat--years` | A figure's Section | Bottom right |
| `vm-story-stat vm-story-stat--offices` | A figure's Section | The wide one, bottom left |

On phones nothing overlaps: the figures line up under the photo. In the editor they show stacked under the photo; use Preview to see them in place.

The labels under the figures are 14px. The design draws them at 9.5px and 8px, which is too small to read.

### People

"The People Moving Deals Forward" is a Kadence query, the same kind of block as the careers listing page. It has two parts, both edited in the block editor:

| Part | Where | What it holds |
|---|---|---|
| The query | Kadence > Queries, "About Us: people" | What is listed: Staff, of the type "Sales Directors", by their Order field, lowest first |
| The card | Kadence > Query Cards, "About Us: people card" | The look of one person, and how many show per line: 4, 3 on tablet, 2 on phones |

The card is made of normal blocks with Kadence's dynamic content, so each part is styled in the sidebar:

| On the card | Block | Dynamic content |
|---|---|---|
| Photo | Image | Post featured image, size Thumbnail |
| Name | Text (Adv), linked to the post | Post title |
| Line under the name | Text (Adv) | Custom field `velocity_staff_title_short`. For sales directors that is their office |
| LinkedIn icon | Icon, with a link | Custom field `velocity_staff_linkedin_url` |

To add someone, create the person in Staff with that type. Nothing changes on the page.

Two classes do what the blocks cannot:

- `vm-card-link`, on the card's Section, stretches the name's link over the whole card. Kadence's own "link the Section" option lays an empty link over the card, with no name for screen readers.
- `vm-people`, on the section's row, centers a last line of people that is not full. If the cards per line or the gaps change on the query card, the numbers in `style.css` under "vm-people" have to follow.

The section has the HTML anchor `team`, so `/about/#team` lands on it. "Our Team" in the menu uses that address.

### Carousel

A Section with the class `vm-carousel` turns the Kadence query inside it into a row that scrolls sideways: three cards in view on desktop, two on tablet, one and a bit of the next on phones. It scrolls by touch, trackpad or keyboard. When the cards do not fit, `assets/js/carousel.js` adds a previous and a next arrow at the bottom right of the Section, beside its last block. With few cards there are no arrows. Kadence's query has no carousel of its own.

"Careers at Velocity" uses it with the query "About Us: open positions" and its card, "About Us: open positions card":

| On the card | Block | Dynamic content |
|---|---|---|
| Title | Text (Adv), linked to the post | Post title |
| Offices | Text (Adv) | "Career: cities on one line", under Velocity in the list. It prints the "City/Cities" field with a dot between cities. Defined in `inc/dynamic-content.php` |
| Summary | Text (Adv) | Custom field `career-short-description` |

Only published careers show. The card's Section has `vm-card-link` too. The label of the arrows is set in `inc/carousel.php`.

### Colors that differ from the design

- **Mission and promise cards**: the text is 19px bold instead of 16px regular. Deep Rust on that background has a contrast of 4.1 to 1. Body text needs 4.5, large text (19px bold and up) needs 3. To go back to a regular weight, the text needs a darker color.
- **Intro texts in gray** use the Gray of the palette (`#666666`) instead of `#606060`.

### What is still open

- **Order of the people**: the design shows Marco Castaneda first. The page follows each person's Order field.

## The Programs Overview page

The page at `/loan-programs/` reuses the home page's pieces: the trust strip, the cards, the panel, the call to action band and the frequently asked questions. `wp velocity seed-programs-page` creates its first version.

### The four programs

Each program is the same pair of rows, inside the panel:

- **Image and text**: a two-column row, like the programs of the home page. The photo is a plain rectangle with the block style "Program: cut right" when it sits on the left and "Program: cut left" when it sits on the right (see "Program images"). The small label above the title has the class `vm-eyebrow`.
- **Figures**: a three-column row with a top and a bottom border, set in the row's Border settings. Each column holds three texts: the label, the figure and a note.

To add a program, duplicate a whole "Program" row and replace its photo and texts. Photos are 1084 by 762 pixels.

### The comparison table

A Kadence Table block. Its look is all block settings: the striped rows (Even and Odd row backgrounds), the cell padding, the text sizes, the first column's width, and the first row and first column as headers. Each cell holds blocks of its own: a Text (Adv), or a List when it has several items. The program names in the header are links to each program's page.

The table has the class `vm-compare`. Kadence has no setting for a minimum width, so on small screens that class keeps the table at a readable width and lets it scroll sideways.

### The hero illustration

The photo with the Velocity mark and the four program labels is one image, `assets/images/programs/hero.webp`, exported from Figma. The labels are part of the picture, so a change to a program's name means a new export.

### What is still open

- **The links to each program's page** ("Explore ..." and the table header) point to `#`. Those pages are not built yet. The addresses of the program pages are in one place, `Velocity_Programs::links()`.
- **Flex I/O's label** reads "Short-term loan". The design repeats "Long-term loan" there.

## The Process page

The page at `/process/` reuses the cards, the panel, the call to action band and the frequently asked questions. `wp velocity seed-process` creates its first version. Three things are its own.

### Numbered steps

Each step is a horizontal Section with a rule above it, set as its top border. Inside it sit two Sections:

- **The number**: 48 by 48, with its background, border and radius in the block settings, and the number as a Text (Adv).
- **Title and text**: set to grow, with a 12px gap between its two texts.

To add a step, duplicate one and change its number and texts. The rules are Deep Rust in "Partner With Us in Five Steps" and gray in "How Your First Deal Moves Forward"; that is the border color of each step.

### Document lists

The two lists of the approval package are Kadence Icon List blocks: the bullet is the icon (a small filled circle), and the gap between items, the text size and the colors are block settings.

### Cards that scroll sideways

"What You’ll Need From Your Client" uses the carousel with cards typed into the page, instead of a query:

| Class | Put it on | What it does |
|---|---|---|
| `vm-carousel` | The Section around the cards | Adds the arrows when the cards do not fit |
| `vm-carousel--below` | The same Section | Puts the arrows under the cards. Without it they sit at the bottom right, beside the Section's last block |
| `vm-carousel-track` | A horizontal Section inside it, holding the cards | Makes the cards scroll sideways and keep the width set in their block settings (410px here; never more than 86% of the list, so the next card shows) |

To add a card, duplicate one inside the track.

### The hero drawing

The four steps on the line are one SVG, `assets/images/process/hero-steps.svg`, exported from Figma. The animation lives inside the file, in its `<style>`: the line draws from the first step to the last and each step appears as the line reaches it, once, when the image loads. Visitors who ask for reduced motion see the finished drawing. A new export from Figma loses the animation: copy the `<style>`, the mask and the `step` classes over from the current file.

### What is still open

- **"How long does approval last?"** answers 30 days, as the copy document has it. The rest of the page, and the call to action on every page, say a year. One of the two is wrong.
- **The design lets the call to action band overlap the section above it.** Here the band starts after that section.

## The program pages

30-Year Fixed, Fast50, Flex I/O and Super Jumbo are four pages under Programs Overview: `/loan-programs/30-year-fixed/`, `/loan-programs/fast50/`, `/loan-programs/flex-i-o/` and `/loan-programs/jumbo/`. `wp velocity seed-program-pages` creates their first version. One builder makes the four, so they share every section and differ in copy, photo and icons. After that each page is edited on its own in the editor.

### The terms

"... at a Glance" holds a Kadence Table of two columns, with the same settings as the comparison table. 30-Year Fixed and Fast50 have terms for 1-4 units and for commercial property, so they show two tables inside a Kadence Tabs block, "1-4 Units" and "Commercial". The tabs are drawn as one control with two halves: their colors at rest, on hover and selected, their border and padding are settings of the Tabs block, under Style. The class `vm-toggle` on the block joins the two tabs and rounds only the ends, which the block has no setting for. Flex I/O and Super Jumbo lend on 1-4 units only and show one table, with no tabs.

A term that reads the same for both kinds of property is typed twice, once in each table.

### The case study

"A Deal That Fits" is a Kadence query per program, "Program case study: ..." (Kadence > Queries). It lists the latest case study filed under that program in Properties, Programs. The four queries share one card, "Program case study card" (Kadence > Query Cards), so a change to the card shows on the four pages.

| On the card | Block | Dynamic content |
|---|---|---|
| Title | Text (Adv), H2 | Post title |
| Photo | Image, with a fixed 3:4 shape | Post featured image |
| Three boxes | Section with two Text (Adv) each | The case study fields "Loan Amount", "Days to Close" and "Location" |
| Button | Button | Post URL |

**A program with no case study shows nothing.** The section's space is part of the card, so the page closes up, with no empty band. To show the section, publish a case study and choose its program. To change which one shows when a program has several, edit the query: it takes the most recent.

The design leaves the three boxes, and a "Result" label over the photo, to be defined. The boxes show what a case study already holds. The label is not built.

### The other programs

Three cards on the peach band, one for each of the other programs. Each is a Section with a set height on desktop and its content pushed apart: icon at the top, text and button at the bottom. Their buttons have less side padding than the rest, so the longest label fits on one line.

The call to action band sits half over the peach. No CSS: the background of its section is a gradient with a hard stop, peach for the first 136px and white after.

### The hero photo

Each hero photo is one image exported from Figma, `assets/images/programs/hero-*.webp`, with the orange shape and the small label ("Property types ...") in it. The label's text is in the image's alternative text.

### What is still open

- **The copy documents (V1) differ from the text in Figma** in the titles, the figures on the orange band and some terms. The pages follow the copy documents.
- **Fast50, "How fast does Fast50 close?"** says two to six weeks. The table and the orange band say two to four weeks for 1-4 units and 30 to 45 days for commercial.
- **Super Jumbo, "How long does a Super Jumbo loan take to close?"** says two to four weeks. The table and the orange band say two weeks to 45 days.
- **Super Jumbo, "How large can a Super Jumbo loan be?"** says combined financing can reach "90% (i.e., 95% CLTV)". One of the two figures is wrong.
- **The Flex I/O hero photo is a stock preview with a watermark**, and a comment in Figma asks for another photo.
- **The Kadence Table has no setting for top alignment**, so a row's label is centered beside a long list, where the design aligns it to the top.

## The loan calculator

A block of the theme, "Loan Scenario Calculator", in `blocks/loan-calculator`. It has no settings in the editor: the editor shows it as it looks on the site, but it cannot be clicked there. To try it, use Preview or the published page.

What it decides comes from `blocks/loan-calculator/config.php`: the options of each select, the rules of each program, and the messages shown when no program fits.

What it shows comes from the programs themselves, in Properties, Programs: name, short description, loan amounts, max LTV, term, time to close and the link to the program page. Those are the same programs the deals are tagged with, so a program's details are written once. A field left empty is left out of the card.

The rules that are assumptions, pending confirmation from Velocity, are marked "Assumption" in that file.

## Hero video

The home hero plays a looping background video. It is set in the Hero row's own settings, so anyone can change it without code:

1. Open the home page in the editor and select the **Hero** row (List View helps).
2. In the sidebar, open **Background** and choose the **Video** tab.
3. Replace the video file. Keep "Show play and pause buttons" on.
4. Replace the **Background image** too, with a still of the video's first frame. It is the poster: what visitors see before the video starts, and what they keep if they asked for reduced motion or data saving.
5. Update.

### Preparing the file

WordPress refuses video uploads over 2 MB, with a message that points here. To get a video under that, use [HandBrake](https://handbrake.fr/) (free):

1. Open the video and pick the preset **Web > Creator 1080p60**, or any 1080p preset.
2. **Dimensions:** width 1920 at most. Never enlarge a smaller video, it only adds weight.
3. **Video:** encoder H.264 (x264), framerate "Same as source" and Constant Framerate, Constant Quality **RF 22**. If the file is still over 2 MB, raise RF one step at a time, up to 26. Above that, gradients turn blocky.
4. **Audio:** remove every track. Background videos are muted.
5. **Summary:** tick **Web Optimized**, so the video starts playing before it has fully downloaded.
6. Keep it short, 10 to 15 seconds, and check that the last frame flows into the first: the video loops.

Then export the first frame as a WebP or JPEG image under 50 KB for the poster.

### What the theme does with it

`inc/background-video.php` and `assets/js/bg-video.js` keep the video from slowing the page, for any row with a video background:

- The poster of the first row on a page is preloaded with high priority. It is what Largest Contentful Paint measures.
- The video file is only requested after the page has finished loading.
- Visitors with reduced motion or data saving turned on keep the poster, and the play button is hidden.

The pause button is required by accessibility guidelines (WCAG 2.2.2) for motion longer than five seconds. Kadence has no style settings for it, so its look lives in `style.css`.

## Sticky header and mobile menu

The header stays at the top while the page scrolls, and its main row shrinks: from 103 to 72 px on desktop, from 72 to 56 px on tablet and mobile. The logo and the buttons shrink with it.

Where each part lives:

| Part | Where | Value |
|---|---|---|
| Sticky | Kadence Blocks > Headers, open "Global Header" and select the Header block: **Sticky Header**, and **Sticky Section** | Yes, for desktop, tablet and mobile. Sticky Section: Whole Header |
| Shrink | Same place: **Shrink Middle Row** and **Middle Row Shrink Height**, per device | On. 72 desktop, 56 tablet, 56 mobile |
| Background while stuck | Same place, the header's background for the sticky state | Navy (palette 3). Empty would make the header transparent while stuck, and the white logo and menu would disappear |
| Logo and buttons shrinking | `style.css`, "Sticky header" | Logo to 80%, buttons to 85%. Kadence only shrinks the row |

The mobile and tablet menu (Off Canvas) opens **under** the header, which stays visible on top. Its top edge follows the header's height at that moment: 72 px at the top of the page, 56 px once scrolled. The hamburger turns into an X while the menu is open and closes it. The panel's own close button is hidden, but still reached with the keyboard. `assets/js/header.js` measures the header and adds the closing; the rules are in `style.css`.

`style.css` also fixes a Kadence detail: after the menu closes, the hamburger got the inherited navy color on focus and disappeared on the navy header.

### Menu items that only open a submenu

"Broker Programs", "Resources" and "Company" have no page of their own. In the menu (Kadence > Navigation) each has "Disable link" and "Open dropdown on click" on, in the link's settings: a click on the name opens its submenu, on desktop and in the mobile menu. Without them the item is a link to "#", and a click on it closes the mobile menu and marks the three items as the current page.

## Where block styles live in the sidebar

Program images and heading animations are **block styles**. They are not in the gear tab, where most Kadence settings are. With the block selected, the top of the sidebar shows two icons: the **gear** (settings) and a **half-filled circle** (styles). Click the half-filled circle: the **Styles** section at the top lists the options, with **Default** meaning none.

The **Preset** field under the gear is a Kadence feature unrelated to these styles.

## Program images

The program photos are plain rectangles, so anyone can replace one in the editor without a designer. The diagonal cut, the rounded corners and the orange bar along the cut come from the image's block style.

To set or change it:

1. Click the program image (Image block).
2. In the sidebar, click the **half-filled circle** icon.
3. Under **Styles**, pick the one for that program:

| Style | Use it when | On desktop |
|---|---|---|
| Program: cut left | The image is on the right of the text (Fast50, Super Jumbo) | Cut on the left edge, orange bar along it |
| Program: cut right | The image is on the left of the text (Flex I/O) | Cut on the right edge, orange bar along it |
| Program: wide cut right | The featured program inside the white card (30-Year Fixed) | Wider cut on the right, no bar |

To replace a photo, use **Replace** in the image toolbar: the style stays. Upload photos in landscape, about 1084 x 762 px (the featured one square, about 1043 x 1043). Anything drawn on the photo itself, like the orange arrow on 30-Year Fixed, is part of that photo and goes away when it is replaced.

The cut shows in the editor too. On tablet and mobile the image sits above the text and shows whole, with rounded corners. The geometry, measured from the Figma, is in `style.css`, "Program images" (`inc/block-styles.php` registers the styles).

## Animations

Two kinds, set in different places. Neither plays inside the editor: check them on the page itself (save, then reload the page with Ctrl+F5). Visitors who turned on reduced motion in their system see everything still, and so do you if your computer has it on.

### Headings: "Reveal lines"

Each line slides up from behind a mask, tilted a few degrees and straightening as it lands, one line after the other. It plays on load for a heading on screen (the hero H1) and on scroll for the rest.

To turn it on or off:

1. Click the heading (Text (Adv) or Heading block).
2. In the sidebar, click the **half-filled circle** icon.
3. Under **Styles**, pick **Reveal lines** to animate it, or **Default** to stop.

There is nothing else to set: timing and tilt are the same across the site. On the home it is on the H1 and every H2. Use it on main headings only (H1, H2), not on card titles or body text.

For developers: `inc/animations.php`, `assets/js/reveal-lines.js` and "Reveal lines" in `style.css`. To change the tilt, edit `--vm-reveal-angle` in `style.css` and the angle at the top of the script.

### Cards and program blocks: Animate on Scroll

Kadence Blocks Pro's own setting, on the **Column** that holds the card or the image or text.

1. Click anything inside the card, then select its **Column**: use the breadcrumb at the bottom of the editor, or List View (the icon with three lines at the top left).
2. In the sidebar, stay on the **gear** icon and open the **Advanced** tab.
3. Open **Animate on Scroll** and fill in the values below.

Values used on the home:

| Where | Animation | Duration | Delay | Easing | Once |
|---|---|---|---|---|---|
| Cards in a row (Why Velocity, Resources) | Fade up | 600 | 0, 100, 200 for the first, second and third card | Ease out cubic | On |
| 30-Year Fixed (the white card's Column) | Fade up | 600 | 0 | Ease out cubic | On |
| Other programs, image Column | Fade left if the image is on the right, Fade right if it is on the left | 600 | 0 | Ease out cubic | On |
| Other programs, text Column | Same as its image | 600 | 150 | Ease out cubic | On |

Leave **Offset** empty: the animation starts as the block comes into view. To remove an animation, set Animation to none.

Keep animations for a few blocks per page: too many make the page feel slow. New cards in a row follow the same pattern: the delay grows 100 per card.

## Accessibility

The home, header and footer pass axe-core (WCAG 2.2 A and AA) with one known exception, kept on purpose: the brand orange does not reach the 4.5:1 contrast ratio, on the buttons with white text, on orange text over light backgrounds and on the deal card tag. White text over the brightest frames of the hero video is also below 4.5:1. Both are design decisions. Keep everything else passing when building pages:

- **Headings are structure, not style.** One H1 per page, then H2 for sections and H3 inside them, without skipping levels. A highlighted sentence that only needs to look big is a Text (Adv) block with the HTML tag set to `p` and the size set in its settings.
- **Menus are named after their title.** Screen readers list every menu as "navigation", so `inc/accessibility.php` gives each Kadence Navigation the title of its menu as its accessible name ("Header", "Footer: Legal"). Rename the menu to change it.
- **The footer is one `<footer>` element.** In the Global Footer element, both rows sit inside a Group whose HTML element (Advanced) is `footer`. Keep new footer rows inside that Group.
- **Images:** describe images that carry information in their Alt text. Leave Alt empty only for decoration, such as the card icons.
- **Moving backgrounds keep their pause button.** See "Hero video".
- **Text over images or video:** check contrast against the lightest part of the background. Raise the row's overlay opacity if needed.

## The style guide page

`/?velocity-styleguide=1` renders the tokens as the theme outputs them. It is visible to logged-in administrators, and to anyone on a local environment.
