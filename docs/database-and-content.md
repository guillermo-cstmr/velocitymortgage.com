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

The logos and icons are files in the theme, under `assets/images`, not media library items. That keeps them identical on every environment. The home hero video is the exception: it is content an editor replaces, so the seed copies it from `inc/seed/media` to the media library. The photos still sit in `assets/images` and are due to move the same way.

A few details cannot be expressed in block settings and live in the theme's `style.css`: the pill shape and size of every Kadence button, the rule above the footer disclosure, the featured menu item, and how the logo, buttons and mobile menu behave while the header is sticky. To feature an item in a dropdown, give its navigation link the CSS class `vm-menu-featured`. The sticky header is explained in [components.md](components.md), "Sticky header and mobile menu".

The header and the mobile drawer share one menu, "Header". It turns vertical on tablet and mobile through its own responsive settings, so a link is added once. The footer columns are separate menus, one per column.

### Creating them on a new environment

The first version of all of them is built by a command, so nobody has to rebuild them by hand on each environment.

```bash
wp velocity seed-chrome            # dry run: shows what it would create
wp velocity seed-chrome --execute  # creates whatever is missing
```

It never touches something that already exists, so it is safe to run again. The `--force` flag rebuilds everything from scratch and **discards every change made in the editor**. Use it only on a database you are willing to lose.

Menu links to pages that do not exist yet are created as `#` placeholders. As the pages get built, either replace them in the editor, or set the link in `Velocity_Seed::menu()` and rebuild the menus only:

```bash
wp velocity seed-chrome --menus --force --execute
```

That rebuilds the header menu and the footer menus from the code and leaves the header and the footer layouts alone. Changes made to the menus in the editor are lost, so make sure the code has them first.

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

`wp velocity cleanup` keeps all of these. The list is in `PROTECTED_POSTS` and `PAGE_SLUGS_TO_KEEP` in `inc/seed/class-velocity-cli.php`.

## Deals and case studies

Both are defined in the must-use plugin: deals in `cpt-properties.php`, case studies in `cpt-case-studies.php`.

### Deals

Deals are the posts under Properties. The Featured Deals page lists them, with filters and sorting (see [components.md](components.md), "The Featured Deals page"). That page has the address the automatic list of deals used to have: the plugin no longer makes that list.

Their taxonomies are Program, Purpose, Type, AE and Region.

Region starts empty. It gets its terms once the client sends the list of regions, and then each deal is tagged.

"Days to Close" is a number of days. "Processed Details Text" stays as free text for the card's closing time tag.

### Sorting

Loan amounts are typed as text, for example `1,120,000`, which does not sort as a number. On every save, the plugin keeps a plain number copy next to it. The Kadence sort options use these fields:

| Sort | Field | Type |
|---|---|---|
| Amount, deals | `velocity_property_loan_amount_number` | Numeric |
| Date, deals | The date the deal was published | Kadence's Sort block takes one custom field, and the amount uses it. `velocity_property_date_funded` is saved as `2026-04-13`, which would sort correctly as text |
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

### The page

`/broker-tools/` is built by `wp velocity seed-toolkit-page`, and edited in the block editor after that. It has no file typed into it. Each list is a Kadence query (Kadence > Queries, "Toolkit: ...") that reads one section, in the order of the admin list. The seven queries share two cards (Kadence > Query Cards):

| Card | Used by | Shows |
|---|---|---|
| "Toolkit: form card" | The two forms libraries | Name, file type and a "Download" button, on one line |
| "Toolkit: document card" | Matrices and the four groups of flyers | "New" tag, name and description on the left; the download buttons on the right |

In the cards:

| Element | Source |
|---|---|
| Button | Link: post custom field `velocity_toolkit_link`. Text: "Unlock to Download" |
| White Label button | Link: post custom field `velocity_toolkit_link_wl`, in the Section with the class `vm-toolkit-wl`. That Section hides itself when the document has no White Label file, and so does the "Velocity Branded" label, which has the class `vm-toolkit-branded` |
| "New" tag | "Toolkit: "New" tag", under Velocity in the dynamic content list |
| File type | "Toolkit: file type", in the same list: the file's extension, such as PDF or XLSX |

A text with the class `vm-hide-empty` is not printed when it has no value: the "New" tag of a document that is not new, the description of one with none.

The buttons never carry the PDF's address. They point to the site's download address for that document, which sends the file as a download: the visitor stays on the page.

A section with no documents shows its title and nothing under it. That is the state of the two forms libraries until their files are uploaded.

The links under the title ("Forms", "Matrices", "Flyers", "Links") jump to the sections of the page. Each section's row has that anchor, under Advanced.

### Unlocking

The form is Gravity Form 14, and the site prints it once. On the Broker Tools page it shows in the "Unlock the Toolkit" panel: the empty Section with the class `vm-toolkit-inline` is where it lands. When a button opens the form in a window, the same form moves into the window, and back when the window closes.

1. A visitor fills the form on the page, or clicks a document that requires unlock: then the form opens in a window instead of the download.
2. On sending it, a cookie remembers the unlock on that browser for a year, and the file they asked for downloads.
3. From then on every button says "Download" and downloads straight away, on this visit and the next ones. The page also gets the class `vm-toolkit-unlocked`.
4. On the next visits the "Unlock the Toolkit" panel is not shown: the panel has the class `vm-toolkit-form`, which hides for a visitor who already unlocked.

A document with "Requires unlock" unticked always says "Download".

**Testing the form.** On staging the Salesforce webhook is off and email is in test mode (see below). A Local database is only safe after the same two settings are checked: a Local copy made from production sends the lead to Salesforce for real.

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

A Local database has its own state of the two: see "Launch", "The toolkit form: what to switch on, and what to check", before sending a form on Local.

Both are database settings. They travel with any copy of the staging database, including a LocalWP pull. If staging content is ever moved to production, these two must be on again in production, or production stops sending email and leads.

## State of the pages

As of 2026-11-17. "Local" means built and reviewed on a LocalWP copy, with its code committed on a branch that is not pushed yet. Nothing in this table is on production.

| Page | Address | Command that creates it | Local | Staging |
|---|---|---|---|---|
| Home | `/` | `seed-home` | Built | Built, 2026-11-18 |
| Programs Overview | `/loan-programs/` | `seed-programs-page` | Built | Not yet |
| 30-Year Fixed, Fast50, Flex I/O, Super Jumbo | `/loan-programs/30-year-fixed/`, `/fast50/`, `/flex-i-o/`, `/jumbo/` | `seed-program-pages` | Built | Not yet |
| Process | `/process/` | `seed-process` | Built | Not yet |
| About Us | `/about/` | `seed-company` | Built | Not yet |
| Contact | `/contact/` | `seed-contact` | Built | Not yet |
| Broker Tools | `/broker-tools/` | `seed-toolkit-page` | Built | Empty page |
| Careers, and each job | `/careers/` | None: posts of the database | Copied from staging | The production version on the new theme. Not part of the redesign |
| Become a Partner, Broker Login | Velocity's portal | None | Not ours to build: Figma marks them "client will develop, CSTMR to QA" | |
| News and Insights, and an article | `/insights/` | None yet | Not built: default template | |
| Featured Deals | `/featured-investment-property-loans/` | `seed-deals-page` | Built | The automatic list, on the default template |
| Terms of Use | `/terms-of-use/` | `seed-legal-pages` | Built | Built, 2026-11-18 |
| Privacy (Privacy Notice for California Residents) | `/privacy/` | `seed-legal-pages` | Built | Built, 2026-11-18. The old text about cookies is gone; a cookie banner replaces it and is not built yet |
| 404 | Any address that does not exist | `seed-404` | Built, as a Kadence Element | Built, 2026-11-18 |
| A deal | `/property/...` | None yet | Not built: default template | |
| Case Studies, and a case study | `/case-studies/` | None yet | Not built: default template | |
| Contact thank-you | `/contact/thank-you/` | None yet | Does not exist | |
| Licenses, Forms Library (footer) | | None yet | Do not exist. Their links point to "#" | |

Branches, each on top of the one before, none pushed: `feature/contact-page`, `feature/company-page`, `feature/process-page`, `feature/program-pages`.

To bring a page to staging: deploy the code, then run its command with `--execute`, then `wp velocity seed-chrome --menus --force --execute` if the menus should point to it. The commands are listed under "Theme commands", and the order for a new environment under "Launch".

Waiting on someone else:

- The address of "Become a Partner" in Velocity's portal.
- The files of the two forms libraries of Broker Tools.
- Case studies: none is published, so "A Deal That Fits" shows on no program page. The three boxes of that section are not defined in the design.
- The new addresses for News and Insights (`/news-insights/`) and Featured Deals (`/featured-deals/`), which need a change in the plugins.
- Copy: the open points listed under "What is still open" of each page in [components.md](components.md).

## Theme commands

All of them are dry runs unless called with `--execute`.

| Command | What it does |
|---|---|
| `wp velocity cleanup` | Removes the old Kadence and Beaver Builder front end. Keeps all content, the header, menus and footer made by `seed-chrome`, and the careers pages (see "Careers") |
| `wp velocity seed-chrome` | Creates the header, the menus and the footer. `--menus` limits it to the menus |
| `wp velocity seed-home` | Fills the home page, if it is empty, and creates the Kadence query of its deal cards. `--force` rebuilds it and discards every change made in the editor |
| `wp velocity seed-programs` | Creates the loan programs the site needs and fills their empty details. Never overwrites a filled field |
| `wp velocity seed-toolkit` | Creates the Broker Toolkit documents from the old toolkit form and page. Skips the ones that exist (see "Broker Toolkit") |
| `wp velocity seed-toolkit-page` | Fills the Broker Tools page, if it is empty, and creates its seven Kadence queries and their two cards. `--force` rebuilds them and discards every change made in the editor (see "Broker Toolkit", "The page") |
| `wp velocity seed-deals-page` | Creates the Featured Deals page if it is missing and fills it, if it is empty, with its two Kadence queries and their cards. `--force` rebuilds them and discards every change made in the editor (see [components.md](components.md), "The Featured Deals page") |
| `wp velocity seed-legal-pages` | Fills Terms of Use and Privacy with the layout and the text of the design, creating a page that is missing. A page with content is left alone without `--force`: both have the old site's text, so they need it. `--page=terms` or `--page=privacy` does one page (see [components.md](components.md), "The legal pages") |
| `wp velocity seed-404` | Creates the Kadence Element that replaces the theme's 404 content. `--force` rebuilds it (see [components.md](components.md), "The 404 page") |
| `wp velocity seed-forms` | Updates the contact form to the design and adds its consent checkbox, and creates the newsletter form. Leaves them alone once done (see [components.md](components.md), "Forms") |
| `wp velocity seed-contact` | Fills the Contact page, if it is empty: heading, ways to reach Velocity, the contact form and the offices map. `--force` rebuilds it and discards every change made in the editor (see [components.md](components.md), "Offices map") |
| `wp velocity seed-company` | Creates the About Us page if it is missing and fills it, if it is empty. Its people and careers sections are Kadence queries, which it creates with their cards (Kadence > Queries and Query Cards); they read Staff and Careers, so the people and the positions need no seeding. `--force` rebuilds the page and discards every change made in the editor (see [components.md](components.md), "The About Us page") |
| `wp velocity seed-process` | Creates the Process page if it is missing and fills it, if it is empty. `--force` rebuilds it and discards every change made in the editor (see [components.md](components.md), "The Process page") |
| `wp velocity seed-program-pages` | Creates the four program pages under Programs Overview, their Kadence queries and the card they share, and fills the pages that are empty. Needs the programs in Properties, Programs (`seed-programs`). `--force` rebuilds them and discards every change made in the editor. `--page=30-year-fixed` (or `fast50`, `flex-io`, `super-jumbo`) does one program and leaves the other three alone (see [components.md](components.md), "The program pages") |
| `wp velocity seed-programs-page` | Fills the Programs Overview page, if it is empty. Not the same as `seed-programs`, which fills in each program's details. `--force` rebuilds the page and discards every change made in the editor (see [components.md](components.md), "The Programs Overview page") |
| `wp velocity rename-pages` | Moves the Contact, Loan Programs and Broker Tools pages to their addresses in the new site (`/contact/`, `/loan-programs/`, `/broker-tools/`), updates the links to them in pages, menus, header and footer, and adds a 301 redirect from each old address in Yoast SEO Premium. Safe to run again |

On staging they run through the SSH Gateway. The gateway may run an older PHP than the website, so the theme's command code stays compatible with PHP 7.4.

## Launch

Production keeps receiving form entries and content edits while the rebuild happens on staging. Replacing the production database with the staging one would lose that data. The launch plan has to import the new pages and Kadence posts into production, or agree a content freeze. This is not decided yet.

Whatever the plan, production needs these once the code is there:

- `wp velocity seed-programs --execute`, `wp velocity seed-toolkit --execute` and `wp velocity seed-forms --execute`. They read production's own data, so they do not depend on the staging database.
- `wp velocity rename-pages --execute`, before the menus are rebuilt. The list of pages is `PAGE_SLUG_CHANGES` in `inc/seed/class-velocity-cli.php`.
- The Process page: copy its blocks from staging, or run `wp velocity seed-process --execute`, which also creates the page.
- The Programs Overview page: copy its blocks from staging, or run `wp velocity seed-programs-page --execute` after `rename-pages`.
- The Broker Tools page: run `wp velocity seed-toolkit-page --execute`, after `rename-pages` and `seed-toolkit`. Its queries are posts of their own, so copying the blocks alone is not enough.
- The Featured Deals page: run `wp velocity seed-deals-page --execute`. It creates the page at the address the list of deals had, and its two Kadence queries. Open the page once afterwards, so Kadence builds the index its filters read, and check that the filters list their choices.
- The legal pages and the 404: run `wp velocity seed-legal-pages --force --execute` and `wp velocity seed-404 --execute`. `--force` is needed because both pages already have the old site's content, which it replaces (WordPress keeps it as a revision). The old Privacy page had a text about cookies that the new one does not: the cookie banner has to be in place.
- The four program pages: run `wp velocity seed-program-pages --execute`, after `rename-pages` and `seed-programs`. It creates the pages and their Kadence queries, which are posts of their own, so copying the blocks alone is not enough. Then `wp velocity seed-chrome --menus --force --execute`, so the menus point to them. The "Explore ..." buttons of a home page and a Programs Overview page built before these pages existed still point to "#": set their links in the editor.
- The About Us page: run `wp velocity seed-company --execute`, which also creates the page and its two Kadence queries. Copying the page's blocks alone is not enough: the queries are posts of their own, and their IDs differ between environments. Then `wp velocity seed-chrome --menus --force --execute`, so "About Us" and "Our Team" in the menus point to it.
- The Contact page: copy its blocks from staging, or run `wp velocity seed-contact --execute` after `seed-forms`, which it needs for the form.
- The loan amounts of existing deals saved as numbers, so Featured Deals sorts by amount. Saving each deal once does it, or a script can.
- Super Jumbo is not in the menus, as in the design. To confirm with design whether that is on purpose.
- The careers listing and job pages exist on staging as posts of the database (page, query, card and two Elements). They are not created by any command: they travel with the database.

### The toolkit form: what to switch on, and what to check

The Broker Toolkit form (Gravity Form 14) sends each request to Salesforce through a webhook, and an email to the site's admin address. Both are settings of the database, not of the code, so each environment has its own state:

| Environment | Salesforce webhook | Gravity SMTP test mode | A test submission |
|---|---|---|---|
| Production | Must be on | Must be off | Is a real lead and a real email |
| Staging | Off | On | Stays on staging |
| Local, as set on 2026-11-17 | Off | On | Stays on the machine |
| Local, after a database copied from production | On | Off | **Is a real lead.** Set both before testing |

Where they are: Forms > Broker Toolkit Item Request > Settings > Webhooks, "Salesforce Lead Integration"; and Gravity SMTP > Settings, "Test mode".

**Before launch, on production.** After the code and the pages are there:

1. The webhook is active and Gravity SMTP test mode is off. If production's database was replaced or merged with staging's, both will be in staging's state: wrong for production.
2. Velocity confirms a test request reaches Salesforce with its Request ID. This is the open question to Anand about the webhook.
3. The "Admin Notification" of Form 14 reaches the inbox it should.

**On staging and on production, after each deploy that touches the toolkit.** In a private window, so no cookie is left from an earlier test:

1. `/broker-tools/` shows the "Unlock the Toolkit" form on the page, and every locked document says "Unlock to Download".
2. A click on a locked document opens the form in a window, with no scrollbar on a laptop or on a phone.
3. Sending the form shows the confirmation, downloads the file that was asked for, turns every button into "Download" and hides the form on the page.
4. A document with a White Label file downloads the right file from each of its two buttons.
5. On a phone: two buttons side by side under their labels, reading "Unlock" before unlocking.
6. Reloading keeps the page unlocked. Deleting the cookie `velocity_toolkit_unlocked` brings the form back.
7. An old link, `/broker-tools/#pdfrequest&resource_id=matricesrs`, opens the form, or downloads the file once unlocked.
8. On staging only: the entry is under Forms > Entries, the email is under Gravity SMTP > Email Log as not sent, and nothing reached Salesforce.

Tested this way on Local on 2026-11-17, from the window and from the page. Not tested on staging yet: the page is not there.

**Still open on this page:**

- The two forms libraries are empty until Velocity sends the files. Their titles show with nothing under them.
- "Become a Partner" links, here and on every page, point to `/become-a-partner/`, which does not exist. Figma marks that page and Broker Login as built by Velocity in their portal (generatevelocity.com). The links need the portal's address, and `/become-a-partner/` a redirect to it.
- The "Broker Resources" card links to `/broker-resources/`, an empty page.
- Form 14 asks for the name as first and last, in one column. The design shows one name field and two columns. Changing the fields may change what reaches Salesforce, so it waits for Velocity.
- Google Tag Manager loads on every environment: the check that limits it to production is commented out in `global-scripts.php`. A test on staging or Local can show up in Analytics.

