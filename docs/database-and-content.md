# Database and content

Git moves files. It does not move anything stored in the database. On this site most of the build lives in the database, so the rules below matter as much as the deploy.

## What lives where

| In git | In the database |
|---|---|
| Child theme: CSS, PHP, design tokens | Page content |
| Must-use plugin and Insights plugin | Header, navigation and footer, which are Kadence posts |
| Block patterns saved as files | Kadence Elements, queries and query cards |
| | Color palette, Customizer settings, Kadence Blocks settings |
| | Custom post type entries, form entries, media library |

## Who owns the database

**Staging's database is the reference for content.** How a page gets there is up to whoever builds it: directly in the staging admin, or on Local and then brought over with one of the methods below.

Two things are fixed, because they lose work:

- Do not push a whole database from Local to staging. A database push replaces everything. It has no merge and no table selection, so it erases whatever was built on staging since the last pull.
- Nobody pushes a database to production from LocalWP.

Local pulls the database from staging with LocalWP whenever it needs fresh content.

The rebuild itself was set up on staging with the theme commands over WP-CLI (see "Theme commands"), not with a database push.

### LocalWP settings

- Connect the Local site to `stagevelocity`, not to production. Check the target every time before you confirm a push or pull.
- Pull: database, plus files if you need third-party plugins or uploads refreshed.
- Push: do not use it for files. Code goes through git.

A database pull overwrites your Local database, including your Local admin password and anything you built only on Local.

## Moving something you built on Local to staging

Pick whatever suits the job. Some options:

1. **Build it on staging directly.** No moving at all.
2. **Copy and paste the block markup.** Handy for one page. In the Local editor, open the options menu and choose Copy all blocks. Paste into the page on staging. Only that page changes, and WordPress keeps a revision.
3. **A block pattern in the theme.** Handy for anything reusable. Save the section as a pattern file in the child theme, commit and deploy. It appears in the block inserter on staging.
4. **WP-CLI over the SSH Gateway.** Handy for many pieces at once, or for settings stored in the database. WP Engine gives SSH access with WP-CLI to each environment, once your SSH key is added to your WP Engine profile.

Whatever the method, watch two things:

- **Images.** Blocks store the media library ID of each image, and IDs differ between environments. After pasting, select the images again on staging. In patterns, use images that ship with the theme.
- **Header and navigation.** The Kadence header block points to its navigation posts by ID. Edit the header and its menus on staging only.

Before pasting over a page on staging, check its revisions to see whether someone else changed it.

## How Kadence stores its settings

Kadence keeps configuration in the WordPress options table, in three places that behave differently.

| Setting | Stored in | Follows the theme? |
|---|---|---|
| Typography, buttons, layout, everything else in the Customizer | `theme_mods_<theme-folder>` | Yes. A new child theme starts empty |
| Global color palette | `kadence_global_palette` | No. It survives a theme switch |
| Kadence Blocks colors and settings | `kadence_blocks_colors` and related options | No |

The child theme sets its design tokens as defaults through the filters `kadence_theme_options_defaults` and `kadence_global_palette_defaults`.

**A value saved in the database always wins over a default.** A default applies only while nothing is saved for that setting. Two consequences:

- When switching to a new child theme, the old palette option has to be removed or overwritten, or the old colors stay.
- If someone changes a token in the Customizer, that value hides the one in code from then on.

## Header, menus and footer

They are block content, edited in the block editor like any page.

| What | Where to edit it |
|---|---|
| Header | Kadence Blocks > Headers, "Global Header" |
| Menus | Kadence Blocks > Navigations. One for the header and the mobile drawer, one per footer column, one for the legal links |
| Footer | Appearance > Elements, "Global Footer" |

The logos and icons are files in the theme, under `assets/images`, not media library items. That keeps them identical on every environment.

A few details cannot be expressed in block settings and live in the theme's `style.css`: the pill shape and size of every Kadence button, the rule above the footer disclosure, the featured menu item, and how the logo, buttons and mobile menu behave while the header is sticky. To feature an item in a dropdown, give its navigation link the CSS class `vm-menu-featured`. The sticky header is explained in [components.md](components.md), "Sticky header and mobile menu".

The header and the mobile drawer share one menu, "Header". It turns vertical on tablet and mobile through its own responsive settings, so a link is added once. The footer columns are separate menus, one per column.

### Creating them on a new environment

The first version of all of them is built by a command, so nobody has to rebuild them by hand on each environment.

```bash
wp velocity seed-chrome            # dry run: shows what it would create
wp velocity seed-chrome --execute  # creates whatever is missing
```

It never touches something that already exists, so it is safe to run again. The `--force` flag rebuilds everything from scratch and **discards every change made in the editor**. Use it only on a database you are willing to lose.

Menu links to pages that do not exist yet are created as `#` placeholders. Replace them in the editor as the pages get built.

## Careers

The careers listing and the job pages are not part of the redesign. They are the production version, moved to the new theme: same content, with the site's colors, content width and buttons.

| What | Where to edit it |
|---|---|
| Listing page | Pages > Careers. Its job list is the Kadence query "Careers" |
| Job card in the list | Kadence Blocks > Query Cards, "Career" |
| Job page layout | Appearance > Elements, "Careers Single". Shows on every job |
| Breadcrumb on job pages | Appearance > Elements, "Career Breadcrumb row" |
| The jobs themselves | Careers, in the admin menu. City and short description are fields on each job |

The application form on the job page is Gravity Form 3, "Career Application". On production it notifies HR. On staging no email leaves the site (see "Staging does not send email or leads").

`wp velocity cleanup` keeps all of these. The list is in `PROTECTED_POSTS` and `PAGE_SLUGS_TO_KEEP` in `inc/class-velocity-cli.php`.

## Deals and case studies

Both are defined in the must-use plugin: deals in `cpt-properties.php`, case studies in `cpt-case-studies.php`.

### Deals

Deals are the posts under Properties. Featured Deals lists them.

Their taxonomies are Program, Purpose, Type, AE and Region.

Region starts empty. It gets its terms once the client sends the list of regions, and then each deal is tagged.

"Days to Close" is a number of days. "Processed Details Text" stays as free text for the card's closing time tag.

### Sorting

Loan amounts are typed as text, for example `1,120,000`, which does not sort as a number. On every save, the plugin keeps a plain number copy next to it. The Kadence sort options use these fields:

| Sort | Field | Type |
|---|---|---|
| Amount, deals | `velocity_property_loan_amount_number` | Numeric |
| Date, deals | `velocity_property_date_funded` | Text. Dates are saved as `2026-04-13`, which sorts correctly as text |
| Amount, case studies | `velocity_case_study_loan_amount_number` | Numeric |
| Date, case studies | `velocity_case_study_date` | Text |

### Case studies

A case study tells the story of one deal. In its "Deal at a glance" box, choose that deal. On save, the case study copies from it:

- the program, purpose, type, AE and region terms;
- the amount, date, days to close and location.

Those fields are then locked on the case study. They are edited on the deal, and every case study linked to it updates when the deal is saved.

A story that is not one of the deals is entered by hand: leave "Deal" on "None" and fill the fields and the terms on the case study.

The copies exist because Kadence query cards, filters and sorting only read the post they list. They cannot reach into a linked deal.

For Kadence dynamic content, a case study also has these fields:

| Field | Holds |
|---|---|
| `velocity_case_study_program` | Program name |
| `velocity_case_study_program_url` | The program's page, from the program's "Program page" field |
| `velocity_case_study_purpose` | Purpose name |
| `velocity_case_study_ae_name` | AE name |
| `velocity_case_study_ae_title` | AE title, from the AE's profile |
| `velocity_case_study_ae_photo`, `velocity_case_study_ae_photo_id` | AE photo, from the AE's profile |

The AE's title and photo need the AE linked to their profile once. Open Properties > AEs, edit the AE, and choose the profile in "AE profile". The profiles are the posts under AEs.

The summary on the case study cards and header is the case study's excerpt.

## Broker Toolkit

Every file on the Broker Toolkit page is a post under **Broker Toolkit** in the admin menu. Code in `cpt-toolkit.php` and `resource-modal.php`, in the must-use plugin.

### A document

| Field | Use |
|---|---|
| Title | Name shown on the page |
| Excerpt | Description shown under the name |
| Featured image | The flyer's cover. Upload it as an image; it is not taken from the PDF |
| PDF | The Velocity branded file. To replace a file, choose the new PDF here |
| White Label PDF | Optional. When set, the document has a second button |
| Section | Where it is listed: Matrices, Program Flyers, Foreign Investor Flyers, About Velocity, How to Customize a Flyer, or one of the two forms libraries |
| Requires unlock | Ticked: the visitor fills the toolkit form once before downloading. Unticked: anyone can download it |
| New | Shows the "New" tag |
| Request ID | Saved with each form entry and sent to Salesforce, to know which file was asked for. Filled from the title when empty. Do not change it once the document is in use |

The order on the page is the order in the admin list. Drag the rows there to change it.

Documents have no page of their own and stay out of search and the sitemap.

### Building the page

One Kadence query loop per section: post type "Toolkit Documents", filtered by the section, ordered by "Menu Order", ascending. In the query card:

| Element | Source |
|---|---|
| Button | Link: post custom field `velocity_toolkit_link`. Text: "Unlock to Download" |
| White Label button | Link: post custom field `velocity_toolkit_link_wl`. Put it, with its "White Label" label, inside a block with the CSS class `vm-toolkit-wl`. That block hides itself when the document has no White Label file |
| "New" tag | Post custom field `velocity_toolkit_new` |

The buttons never carry the PDF's address. They point to the site's download address for that document.

### Unlocking

1. A visitor clicks a document that requires unlock. The "Unlock the Toolkit" form opens instead of the download. It is Gravity Form 14.
2. On sending it, a cookie remembers the unlock on that browser for a year, and the file they asked for downloads.
3. From then on every button says "Download" and downloads straight away, on this visit and the next ones. The page also gets the class `vm-toolkit-unlocked`.

The cookie is per browser. Another device, a private window or cleared cookies ask for the form again.

The form always shows the message from the design. Its 32 old conditional confirmations, one per file, are no longer used.

Old links of the form `#pdfrequest&resource_id=matricesrs` still open the form and download that file.

### Creating the documents on a new environment

```bash
wp velocity seed-toolkit            # dry run: shows what it would create
wp velocity seed-toolkit --execute  # creates whatever is missing
```

It takes each PDF from the toolkit form's old confirmations, and each image and description from the old Broker Toolkit page or its last revision. It skips documents that already exist. ARV Pro, Fix and Flip, and Fast 50 Enhanced are in the form but were not on the page, so they are created as drafts.

The two forms libraries start empty: their files were not on the old toolkit page.

## Staging does not send email or leads

Staging started as a copy of production, so its forms pointed at real inboxes and at the production lead pipeline. Two settings in the staging database stop that:

| Setting | Where | State on staging |
|---|---|---|
| Gravity SMTP test mode | Gravity SMTP > Settings | On. No email is sent. Each one is kept under Gravity SMTP > Email Log |
| Webhook "Salesforce Lead Integration" on Form 14, Broker Toolkit | Forms > Broker Toolkit Item Request > Settings > Webhooks | Off. Toolkit requests do not reach Salesforce |

To check what a form would have sent, open the Email Log.

Both are database settings. They travel with any copy of the staging database, including a LocalWP pull. If staging content is ever moved to production, these two must be on again in production, or production stops sending email and leads.

## Theme commands

All of them are dry runs unless called with `--execute`.

| Command | What it does |
|---|---|
| `wp velocity cleanup` | Removes the old Kadence and Beaver Builder front end. Keeps all content, the header, menus and footer made by `seed-chrome`, and the careers pages (see "Careers") |
| `wp velocity seed-chrome` | Creates the header, the menus and the footer |
| `wp velocity seed-home` | Fills the home page, if it is empty. `--force` rebuilds it and discards every change made in the editor |
| `wp velocity seed-programs` | Creates the loan programs the site needs and fills their empty details. Never overwrites a filled field |
| `wp velocity seed-toolkit` | Creates the Broker Toolkit documents from the old toolkit form and page. Skips the ones that exist (see "Broker Toolkit") |

On staging they run through the SSH Gateway. The gateway may run an older PHP than the website, so the theme's command code stays compatible with PHP 7.4.

## Launch

Production keeps receiving form entries and content edits while the rebuild happens on staging. Replacing the production database with the staging one would lose that data. The launch plan has to import the new pages and Kadence posts into production, or agree a content freeze. This is not decided yet.

Whatever the plan, production needs these once the code is there:

- `wp velocity seed-programs --execute` and `wp velocity seed-toolkit --execute`. They read production's own data, so they do not depend on the staging database.
- The loan amounts of existing deals saved as numbers, so Featured Deals sorts by amount. Saving each deal once does it, or a script can.
