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

## Theme commands

All of them are dry runs unless called with `--execute`.

| Command | What it does |
|---|---|
| `wp velocity cleanup` | Removes the old Kadence and Beaver Builder front end. Keeps all content, and keeps the header, menus and footer made by `seed-chrome` |
| `wp velocity seed-chrome` | Creates the header, the menus and the footer |
| `wp velocity seed-home` | Fills the home page, if it is empty. `--force` rebuilds it and discards every change made in the editor |
| `wp velocity seed-programs` | Creates the loan programs the site needs and fills their empty details. Never overwrites a filled field |

On staging they run through the SSH Gateway. The gateway may run an older PHP than the website, so the theme's command code stays compatible with PHP 7.4.

## Launch

Production keeps receiving form entries and content edits while the rebuild happens on staging. Replacing the production database with the staging one would lose that data. The launch plan has to import the new pages and Kadence posts into production, or agree a content freeze. This is not decided yet.
