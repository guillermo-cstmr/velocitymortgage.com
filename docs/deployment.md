# Deployment

Code reaches staging through GitHub Actions. There is no manual upload step.

## How it works

The workflow is `.github/workflows/deploy-staging.yml`.

1. A push to the `staging` branch starts it. It can also be started by hand from the Actions tab.
2. It checks out the repository.
3. It runs a PHP syntax check on every PHP file under `wp-content/`. Any error stops the deploy before a single file is copied.
4. It copies `wp-content/` from the repository to `wp-content/` on the WP Engine environment `stagevelocity`, using rsync over the WP Engine SSH Gateway.
5. It clears the WP Engine cache.

What the copy does and does not do:

- It adds new files and updates changed ones.
- It **never deletes** anything on the server. The sync runs without the delete flag.
- It only touches files tracked in this repository. Kadence, third-party plugins and uploads are never sent.
- Files and folders whose name starts with a dot are skipped.
- `README.md`, `CONTRIBUTING.md`, `docs/` and `.github/` sit outside `wp-content/`, so they are never deployed.

`main` does not deploy anywhere. Production is not connected to this repository.

## Deploying to staging

```bash
git checkout staging
git merge main
git push
git checkout main
```

Watch the run in the repository's Actions tab. A normal run takes under a minute.

The deploy only moves files. When a change also needs something stored in the database on staging, such as a header setting or a block on a page, that step is done separately: in the staging admin, or with WP-CLI over the SSH Gateway. Write it down in the pull request so it is not forgotten for production.

## Verifying a deploy

- The run shows a green check.
- Open staging and test what changed, front end and editor.
- With SSH Gateway access you can confirm a file arrived:

  ```bash
  ssh stagevelocity@stagevelocity.ssh.wpengine.net "ls -la sites/stagevelocity/wp-content/themes/velocity-kadence"
  ```

## Rolling back

Revert the commit and deploy again:

```bash
git checkout main
git revert <commit>
git push
git checkout staging && git merge main && git push
```

If a deploy broke the site badly enough that the admin does not load, restore a backup point from the WP Engine portal, under Backups for `stagevelocity`. Then revert the commit so the next deploy does not bring the problem back.

## Removing a file from the server

Deleting a file in git does not delete it on staging. After the deploy, remove it by hand over SFTP or SSH. Record what you removed in the pull request.

This matters most for PHP files in the must-use plugin and for theme files, which keep running as long as they exist on the server.

## Access and secrets

- The workflow authenticates with a dedicated SSH key. The private half is stored as the repository secret `WPE_SSHG_KEY_PRIVATE`. The public half is registered in a WP Engine user profile under SSH keys, with the name `velocity-github-actions-deploy`.
- Nobody needs that key to deploy. Pushing to `staging` is enough.
- The WP Engine user that holds the key can reach every environment that user can reach, production included. Keep this repository private and never print the secret in a workflow.
- To rotate the key: generate a new ed25519 key pair, add the public key in WP Engine, replace the secret in GitHub, run a deploy, then remove the old public key from WP Engine.

## Troubleshooting

| Symptom in the run log | Cause | Fix |
|---|---|---|
| `error in libcrypto: unsupported` followed by `Permission denied (publickey)` | The secret does not contain a valid private key, usually a bad paste | Replace `WPE_SSHG_KEY_PRIVATE` with the full key, including the BEGIN and END lines and a trailing newline |
| `Permission denied (publickey)` with no libcrypto error | The public key is not registered in WP Engine, or was removed | Add the public key to the WP Engine profile and wait a few minutes |
| `PHP Parse error` during linting | A syntax error in a tracked PHP file | Fix the file. Nothing was deployed |

## Known quirk

The WP Engine SSH Gateway runs in its own container. It can report an older PHP version than the one serving the website. Check the real version in WordPress under Tools, Site Health, Info, Server.

## Production

Production deploys are not set up. They will be added for launch as a separate workflow with a manual approval step. Until then, nothing in this repository can change production.
