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

The home, header and footer pass axe-core (WCAG 2.2 A and AA) except for color contrast of the brand orange, which is a design decision pending with the designer. Keep it that way when building pages:

- **Headings are structure, not style.** One H1 per page, then H2 for sections and H3 inside them, without skipping levels. A highlighted sentence that only needs to look big is a Text (Adv) block with the HTML tag set to `p` and the size set in its settings.
- **Menus are named after their title.** Screen readers list every menu as "navigation", so `inc/accessibility.php` gives each Kadence Navigation the title of its menu as its accessible name ("Header", "Footer: Legal"). Rename the menu to change it.
- **The footer is one `<footer>` element.** In the Global Footer element, both rows sit inside a Group whose HTML element (Advanced) is `footer`. Keep new footer rows inside that Group.
- **Images:** describe images that carry information in their Alt text. Leave Alt empty only for decoration, such as the card icons.
- **Moving backgrounds keep their pause button.** See "Hero video".
- **Text over images or video:** check contrast against the lightest part of the background. Raise the row's overlay opacity if needed.

## The style guide page

`/?velocity-styleguide=1` renders the tokens as the theme outputs them. It is visible to logged-in administrators, and to anyone on a local environment.
