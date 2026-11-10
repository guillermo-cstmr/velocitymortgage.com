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

## The blocks to use

| For | Use |
|---|---|
| A section, a grid, columns | Row Layout |
| A column, a card, a colored area | Section, the block inside a Row Layout |
| Any text: headings and paragraphs | Text (Adv) |
| An image | Image (Adv) |
| A button | Advanced Button |

Text (Adv) is used for all text instead of the core Heading and Paragraph blocks, because it has size, weight, line height, color and margin in the sidebar.

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

### Images

Images used by the seeded header, footer and home page live in the theme under `assets/images`, not in the media library. A media library ID differs between environments, a theme file is the same everywhere.

Images added later in the editor go to the media library as usual.

## CSS classes

The exceptions. Each one exists because no block setting can do the job. To use one, select the block, open Advanced in the sidebar and type the class in "Additional CSS class(es)".

| Class | Put it on | Why it is a class |
|---|---|---|
| `vm-eyebrow` | Text (Adv) | Adds the orange dot before a small label. Text has no setting for that |
| `vm-btn-on-dark` | A single button set to Outline | White outline for dark backgrounds. The theme and Kadence both restyle the outline button on hover, so per-button colors do not hold |
| `vm-stats` | Row Layout | On tablet and mobile the items wrap centered, so an odd one out sits in the middle. A row's grid leaves it on the left |
| `vm-deals` | Query Loop over the properties | Shows them as deal cards in three columns. Query Loop is made of core blocks, which have no card settings in this theme |
| `vm-deal-card` | The Group around each post in that Query Loop | The card itself. Inside it go a Featured Image block and a Shortcode block with `[vm_deal_info]` |
| `vm-faq` | Details | Styles a frequently asked question. Details has no styling settings. Questions placed one after another act as a group: opening one closes the others, and one always stays open. Tick "Open by default" on the first |
| `vm-menu-featured` | A navigation link inside a dropdown | Sets the item apart with a rule, bold text and an arrow. Navigation has no per-item styling |
| `vm-placeholder` | Text (Adv) | Marks where something not built yet will go. Delete the block when the real thing exists |
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

## The style guide page

`/?velocity-styleguide=1` renders the tokens as the theme outputs them. It is visible to logged-in administrators, and to anyone on a local environment.
