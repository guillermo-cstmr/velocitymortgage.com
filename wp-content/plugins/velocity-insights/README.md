# Velocity Insights

Custom plugin for velocitymortgage.com. It adds the Insights article section for articles written by CSTMR.
Owner: Yurii Boiko (Velocity). It isn't a WordPress.org plugin, so it never auto-updates.

## What it does

| Feature | How |
|---|---|
| `/insights/` index + `/insights/{article}/` | `insight` post type (Insights menu in wp-admin) |
| Newest first, 9 per page, pagination | `pre_get_posts` in `velocity-insights.php` |
| Topic filter (`/insights/topic/{slug}/`) | `insight_topic` taxonomy + links above the list |
| SEO title/meta | Yoast (plugin's own fields turn off when Yoast is active) |
| CTA under every article | Synced pattern titled **Insights CTA** (default: "Ready to finance…" + Get a Loan Quote → generatevelocity.com, set in `VMI_CTA_URL`) |
| Agency role | **Insights Contributor (Agency)**: draft, upload images, submit for review, can't publish |
| Publishers | wp-admin → Insights → Settings: only ticked users can publish / edit published Insights and they get the review emails (none ticked = Administrators) |
| Review email | Branded HTML email (plus plain-text fallback) sent to Publishers when an article is set to Pending. Template: `vmi_review_email_html()` in `velocity-insights.php`; wording also in `vmi_review_email_text()` |
| Byline box | "Byline (Author)" box in the editor sidebar for Publishers/Editors; server-rendered so it works even though /wp-json/wp/v2/users is blocked |
| Layout | Kadence Customizer → Custom Post Types → Insights |

## Settings (top of `velocity-insights.php`)

- `VMI_SLUG`: directory name (`insights` / `blog`). **After changing it: Settings → Permalinks → Save.**
- Articles per page: wp-admin → Insights → Settings (default 9, `VMI_PER_PAGE`).
- `VMI_TOPIC_FILTER`: show or hide the topic links.
- `VMI_CTA_PATTERN_TITLE`: name of the CTA pattern.
- `VMI_USE_PLUGIN_TEMPLATES`: leave `false` on Kadence.

## Releasing a change

1. Edit the code, then bump `Version:` in the header and the CSS version in `vmi_styles()`.
2. Zip the `velocity-insights` folder.
3. On staging: Plugins → Add New → Upload → "Replace current with uploaded", and run the checks below.
4. Do the same on production. Settings → Permalinks → Save if the slug changed.

## After WordPress / Kadence / Yoast updates, check

- `/insights/` loads, page 2 works, and a topic page works.
- An article shows the image, H1, byline, date and CTA.
- View source: `<title>` and `<meta name="description">` come from Yoast.
- A CSTMR test account and a non-Publisher Editor can't see a Publish button.

## Removing it

Deactivating it hides the section, and the articles stay in the database. Reactivating brings them back.
Deleting the plugin does **not** delete articles or the role.
