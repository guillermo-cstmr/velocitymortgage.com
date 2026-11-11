# velocitymortgage.com

Custom code for the Velocity Mortgage Capital WordPress site, maintained by CSTMR.

This repository does **not** contain WordPress. It tracks only the code we write and own. WordPress core, uploads, Kadence and every third-party plugin live on the server and are ignored by git.

## What is tracked

| Path | What it is |
|---|---|
| `wp-content/mu-plugins/velocity.php` and `wp-content/mu-plugins/velocity/` | Must-use plugin: custom post types, CMB2 fields, tracking scripts |
| `wp-content/plugins/velocity-insights/` | Insights article section |
| `wp-content/themes/kadence-child/` | Old child theme, kept until the rebuild replaces it |
| `wp-content/themes/velocity-kadence/` | New child theme for the rebuild |
| `.github/workflows/` | Deploy automation |
| `docs/`, `README.md`, `CONTRIBUTING.md` | Documentation. Never deployed |

The `.gitignore` is a whitelist. A new plugin or theme folder is ignored until you add it there.

## Environments

| Environment | Where | PHP | How code gets there |
|---|---|---|---|
| Local | LocalWP on your machine | 8.4 | You edit files directly |
| Staging | WP Engine `stagevelocity` | 8.4 | Push to the `staging` branch |
| Production | WP Engine | To be confirmed | Not connected to this repo yet |

Staging is password protected. The credentials are in the WP Engine portal, on the environment Overview page.

## The three rules

1. **Code travels only through git.** Do not upload tracked files by SFTP and do not push files from LocalWP. The next deploy would overwrite them.
2. **Staging's database is the reference for content.** Pages, the header, the footer and Kadence settings live in the database. Build them on staging directly or on Local and bring them over, as suits you ([docs/database-and-content.md](docs/database-and-content.md) lists the options). Never push a whole Local database over staging: it erases what was built there.
3. **Nothing goes to production from a laptop.** No LocalWP push to production, ever.

## Getting started

1. Ask for access to this repository and to the Velocity account in WP Engine.
2. Create a site in LocalWP and pull from the `stagevelocity` environment, files and database.
3. Set the LocalWP site to PHP 8.4.
4. In the site's `app/public` folder, connect the repository:

   ```bash
   git init -b main
   git remote add origin git@github.com:guillermo-cstmr/velocitymortgage.com.git
   git fetch origin
   git reset origin/main
   git status
   ```

   `git status` should be clean. If it lists modified files, staging has code that is not in the repository. Stop and ask before going further.

## Everyday workflow

```bash
git checkout main && git pull
git checkout -b feature/short-description
# work, test on Local
git commit
git push -u origin feature/short-description
```

Open a pull request into `main`. To put the change on staging, merge `main` into `staging` and push. That push runs the deploy.

## Project status (November 2026)

Built and on staging:

- The `velocity-kadence` child theme, with the Figma style guide as design tokens, and a style guide page.
- Header (sticky, shrinking on scroll), menus and footer, as Kadence blocks.
- The home page, including the background video, the animations and the loan scenario calculator.
- The loan program details, stored on the programs (Properties > Programs) and read by the calculator.
- Automatic deploy to staging.

Open:

- The calculator rules marked "Assumption" in `blocks/loan-calculator/config.php` wait for confirmation from Velocity.
- Many links in menus and buttons point to `#` until their pages exist. The rest of the pages are not built yet.
- The brand orange is below the WCAG AA contrast ratio. That was kept on purpose, as a design decision.
- The hero video is the file exported from Figma. A higher quality source may replace it after QA.
- Production deploys and the launch plan are not set up. See [docs/deployment.md](docs/deployment.md) and [docs/database-and-content.md](docs/database-and-content.md).

## More documentation

- [CONTRIBUTING.md](CONTRIBUTING.md): branches, commit messages, coding standards, where each kind of code belongs.
- [docs/deployment.md](docs/deployment.md): how the deploy works, how to verify it, how to roll back.
- [docs/database-and-content.md](docs/database-and-content.md): how the database moves, how to move content between environments, how Kadence stores its settings.
- [docs/components.md](docs/components.md): how pages are built. The block settings behind each piece of the home page, the few CSS classes, the block styles, the animations, the hero video and the sticky header, with where to find each setting in the editor.
