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
2. **Content travels only through the database, and staging owns it.** Pages, the header, the footer and Kadence settings are edited on staging. Local pulls the database from staging. Local never pushes its database over staging once the build has started.
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

## More documentation

- [CONTRIBUTING.md](CONTRIBUTING.md): branches, commit messages, coding standards, where each kind of code belongs.
- [docs/deployment.md](docs/deployment.md): how the deploy works, how to verify it, how to roll back.
- [docs/database-and-content.md](docs/database-and-content.md): how the database moves, how to move content between environments, how Kadence stores its settings.
- [docs/components.md](docs/components.md): how pages are built. The block settings behind each piece of the home page, and the few CSS classes.
