# Components

Pages are built with the block editor. A few pieces of the design repeat across pages and cannot be expressed with block settings alone. Those are CSS classes defined in the theme's `style.css`.

To use one, select the block, open Advanced in the sidebar and type the class in "Additional CSS class(es)".

Design tokens are a separate thing: colors, fonts, heading sizes and button styles are set in `inc/tokens.php` and apply on their own. See [CONTRIBUTING.md](../CONTRIBUTING.md).

## Sections

Every section is a Kadence Row Layout with full-width alignment, the content max width inherited from the theme, and 96px of padding top and bottom.

| Class | Put it on | What it does |
|---|---|---|
| `vm-panel` | Row Layout, full width | A rounded section inset from the page edges, as in Programs. On mobile it goes edge to edge with square corners |
| `vm-panel vm-panel--gradient` | Row Layout, full width | The same panel with the navy to indigo gradient. Set the row's text color to white |
| `vm-cta` | Row Layout with a background image | The rounded call-to-action band |
| `vm-hero` | Row Layout | Spacing under the hero headline and intro |

## Cards

| Class | Put it on | What it does |
|---|---|---|
| `vm-card` | Section (Kadence column) | White card with border, rounded corners and shadow. Content centered |
| `vm-card vm-card--left` | Section (Kadence column) | Light gray card, content left-aligned and pushed to the bottom |
| `vm-program` | Row Layout with two columns | A program: text beside an image. Give the columns `vm-program-text` and `vm-program-image` |
| `vm-program vm-program--featured` | Row Layout with two columns | The same, inside a white bordered card |
| `vm-step` | Section (Kadence column) | One numbered step |
| `vm-deals` | Query Loop | Shows posts as deal cards in three columns. Wrap each post's blocks in a Group with `vm-deal-card`. The whole card is clickable through the Post Title block, which needs the class `vm-deal-location` and "Make title a link" turned on |

Cards lift slightly on hover. That comes with the class, there is nothing to set.

## Text

| Class | Put it on | What it does |
|---|---|---|
| `vm-subtitle` | Paragraph | Intro text under a title, 20px |
| `vm-body-small` | Paragraph | Small text, 14px |
| `vm-eyebrow` | Paragraph | Small uppercase label with an orange dot, above a title |
| `vm-tag` | Paragraph | Light blue pill label |
| `vm-note` | Paragraph | Gray supporting line under a block of content |
| `vm-note vm-note--rust` | Paragraph | The same in a rust tone |
| `vm-h3-small` | Heading | Card title, 22px bold, whatever the heading level |
| `vm-h6` | Heading | 24px bold, whatever the heading level |
| `vm-closing-line` | Heading | The Deep Rust line that closes a section |
| `vm-stat-number` and `vm-stat-label` | Two paragraphs | A big number with its label, as in the trust strip |
| `vm-step-number` | Paragraph | The large orange number of a step |
| `vm-cta-note` | Paragraph | The bold line under the buttons in the call-to-action band |

A headline with two weights: write it normally and make the second half bold. In H1 and H2 bold renders as semibold, which is what the design uses.

Heading levels follow the page outline, not the look. A card title is an H3 with `vm-h3-small`, even though it does not look like the default H3.

## Buttons

Use the Kadence Advanced Button block. It already follows the style guide: pill shape, 20px semibold.

| Style | How |
|---|---|
| Primary, orange fill | The default |
| Outline, orange | Set the button's style to Outline |
| Outline on a dark background | Outline, plus the class `vm-btn-on-dark` on the single button |
| Arrow | Add the "arrow right" icon on the right side |

## Other

| Class | Put it on | What it does |
|---|---|---|
| `vm-faq` | Details block | A frequently asked question. Tick "Open by default" on the first one. Questions placed one after another act as a group: opening one closes the others, and one always stays open |
| `vm-menu-featured` | Navigation link inside a dropdown | Sets the item apart with a rule, bold text and an arrow |
| `vm-placeholder` | Paragraph | Marks where something not built yet will go. Delete the block when the real thing exists |

## Images

Images used by the seeded header, footer and home page live in the theme under `assets/images`, not in the media library. A media library ID differs between environments, a theme file is the same everywhere.

Images added later in the editor go to the media library as usual.

## The style guide page

`/?velocity-styleguide=1` renders the tokens as the theme outputs them. It is visible to logged-in administrators, and to anyone on a local environment.
