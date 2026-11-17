# Seed classes

The files in this folder build the first version of the site's pages, header,
footer and menus as block content. They only load for WP-CLI, behind the
`wp velocity ...` commands: nothing here runs when a visitor opens the site.

| File | What it holds |
|---|---|
| `class-velocity-cli.php` | The commands themselves |
| `class-velocity-seed.php` | Shared pieces: header, footer, menus, block helpers, Kadence queries |
| `class-velocity-home.php` | The home page, and the helpers every page builder uses |
| `class-velocity-contact.php` | Contact |
| `class-velocity-company.php` | About Us |
| `class-velocity-programs.php` | Programs Overview |
| `class-velocity-process.php` | Process |

They exist so the same page can be created on Local, staging and production
with one command. Once a page is created it is edited in the block editor, and
its file here is no longer its source: a correction made in the editor is not
copied back, and running a command with `--force` would overwrite it.

They have to stay in the repository until the pages exist on production: the
deploy is what carries them to staging and production, where the commands
run. After launch they can be removed.

See `docs/database-and-content.md`, "Theme commands".
