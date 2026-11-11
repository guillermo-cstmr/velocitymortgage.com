# Building pages

Pages are built in the block editor with Kadence Blocks. This page explains how the home page is put together, so the next pages follow the same approach.

## The rule

There are three layers. Each thing lives in exactly one of them.

| Layer | What goes there | Where |
|---|---|---|
| Design tokens | Colors, fonts, heading sizes, button style | Code, `inc/tokens.php`. Apply on their own, everywhere |
| Block settings | Borders, radius, shadows, padding, backgrounds, alignment, text sizes | The editor sidebar, on each block |
| CSS classes | Only what the sidebar cannot do | `style.css`, listed at the end of this page |

**If the sidebar can do it, do it in the sidebar.** A style set there is visible to whoever opens the block, and the editor shows exactly what the site shows. Reach for a CSS class only when a block has no setting for what you need.

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

- **Outer Row Layout**: full width, no background. Padding 32 left and right. Mobile padding 0.
- **Section inside it**: carries the look. Background color or gradient. Border radius 30, mobile 0. Padding 112 top and bottom, 64 sides. Tablet 80 and 32. Mobile 64 and 20.

On mobile the gutters and the radius drop to zero, so the panel runs edge to edge. That is the rule for any section wider than the content column.

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
| Closing line of a section | H3, size 30, mobile 22, weight 600, color Deep Rust |
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
| `vm-deals` | Query Loop over the properties | Shows them as deal cards in three columns. Query Loop is made of core blocks, which have no card settings in this theme |
| `vm-deal-card` | The Group around each post in that Query Loop | The card itself. Inside it go a Featured Image block and a Shortcode block with `[vm_deal_info]` |
| `vm-faq` | Details | Styles a frequently asked question. Details has no styling settings. Questions placed one after another act as a group: opening one closes the others, and one always stays open. Tick "Open by default" on the first |
| `vm-menu-featured` | A navigation link inside a dropdown | Sets the item apart with a rule, bold text and an arrow. Navigation has no per-item styling |
| `vm-footer-main`, `vm-footer-legal` | The two rows of the footer | Line heights, the rule above the legal text, and the underline on legal links |

## The deal card

The text of a deal card comes from the shortcode `[vm_deal_info]`, defined in `inc/shortcodes.php`. It prints, for the property being shown:

| Line | Where it comes from |
|---|---|
| Closing time tag, with the bolt | The property field "Processed Details Text" |
| Title | Property type and purpose. Falls back to the property name when both are empty |
| Program | Loan program |
| Name | Account executive |

A line is left out when the property has no value for it, so an incomplete card means incomplete data on that property. The one exception is the closing time tag: without a value it still keeps its space, invisibly, so the titles line up across the cards. The title links to the property, and that link covers the whole card.

It is a shortcode because core blocks cannot print a custom field, or a taxonomy term without turning it into a link. In the editor the block shows the shortcode text, not the result.

## The loan calculator

A block of the theme, "Loan Scenario Calculator", in `blocks/loan-calculator`. It has no settings in the editor: the editor shows it as it looks on the site, but it cannot be clicked there. To try it, use Preview or the published page.

What it decides comes from `blocks/loan-calculator/config.php`: the options of each select, the rules of each program, and the messages shown when no program fits.

What it shows comes from the programs themselves, in Properties, Programs: name, short description, loan amounts, max LTV, term, time to close and the link to the program page. Those are the same programs the deals are tagged with, so a program's details are written once. A field left empty is left out of the card.

The rules that are assumptions, pending confirmation from Velocity, are marked "Assumption" in that file.

## The style guide page

`/?velocity-styleguide=1` renders the tokens as the theme outputs them. It is visible to logged-in administrators, and to anyone on a local environment.
