# Contributing

Standards for anyone writing code in this repository.

## Branches

| Branch | Purpose | Deploys to |
|---|---|---|
| `main` | Reviewed code. The reference for every other branch | Nothing |
| `staging` | What is running on staging | WP Engine `stagevelocity` |
| `feature/*`, `fix/*` | Work in progress | Nothing |

- Branch from `main`. Merge back into `main` through a pull request.
- `staging` only ever receives merges from `main`. Do not commit directly to it.
- Keep `main` and `staging` identical unless a deploy is in progress.

## Commits

- Write the subject as a plain sentence that says what the commit does, in English. Example: `Add the program card pattern`.
- Use the body to explain why, when the reason is not obvious from the diff.
- One logical change per commit. A token change and a new pattern are two commits.
- Never commit secrets, API keys, database dumps or files from `uploads`.

## Where code belongs

| If you are adding | Put it in |
|---|---|
| Styles, design tokens, block patterns, templates | `wp-content/themes/velocity-kadence/` |
| A custom post type, a taxonomy, custom fields | `wp-content/mu-plugins/velocity/` |
| Anything about the Insights section | `wp-content/plugins/velocity-insights/` |
| Tracking or analytics scripts | `wp-content/mu-plugins/velocity/` |

Custom post types stay in the must-use plugin on purpose. Content must survive a theme switch, so data structures never live in a theme.

## PHP

- Staging and Local run PHP 8.4. Confirm the production version before using syntax newer than 7.4 in the must-use plugin, which also loads on production.
- Follow the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).
- Prefix functions, hooks and option names with `vm_` or `velocity_`.
- Escape on output, sanitize on input. Use `esc_html`, `esc_attr`, `esc_url` and `wp_kses_post`.
- The deploy runs `php -l` on every PHP file. A syntax error blocks the deploy.

## Design tokens

Colors, fonts, heading sizes and button styles come from the Figma style guide and are defined in code, in the child theme.

- Do not change a token from the Customizer. A value saved there overrides the code and the repository stops matching the site.
- To change a token, edit it in the theme and deploy.
- In CSS, use the Kadence palette variables, `--global-palette1` through `--global-palette9`, instead of hex values.

The Customizer stays free for everything that is not a style guide token.

## Blocks and patterns

- Pages are built with the block editor and Kadence Blocks.
- If the editor sidebar can express a style, set it on the block. Borders, radius, shadows, padding, backgrounds and text sizes are block settings, not CSS. A CSS class is the exception, for what no block setting can do. [docs/components.md](docs/components.md) has the recipes and the list of classes.
- A section that is used on more than one page becomes a block pattern, saved as a file in the theme. Patterns travel through git and show up in the block inserter on every environment.
- Patterns must not reference media library images by ID. IDs differ between environments. Use images that ship inside the theme.

## Before you push

- The site loads on Local without PHP errors.
- You tested the front end and the block editor for anything you touched.
- `git status` shows only files you meant to change.
