# Workday native Git proposal — approval pending

Baseline: main 81e122a00d0e9462d8d783a3b3e8d1e8d366c4f5. This branch is local only.
No production requests, credentials, user records, DB writes or calendar job execution occurred.

## Architecture and prerequisite decisions

Hostinger must deploy artifact branch `hostinger-workday-runtime-v1` ONLY into
`public_html/workday-runtime-v1`, never public_html. Hostinger deletes untracked
files in its target: this target must contain code only. Original root app,
includes/config.php, includes/config-local.php, logs, cron and all data remain
in place. Artifact config.php requires the original complete root config.php;
its private semantics were not inspected. Missing root config fails closed.
No cron modification, migration, seed, composer install, mail or DB test occurs.

Asset versions use the runtime's own files. Public URLs remain unchanged.
The exact manifest contains current pages/admin/API/includes/CSS/JS; builder
substitutes only runtime config and adds source marker. No SQL, cron, logs,
config-local, environment files, packages, cache or user uploads are shipped.
Root stage .htaccess files are separate operator-reviewed controls and are NOT
in the artifact. Stage0 preserves original public app; stage1 internally routes
only exact manifest public URLs, /, /admin/ and /workday-deployment.json.
Unknown PHP and path-info are denied in stage1. Direct runtime, includes,
SQL/cron/logs/cache/uploads/vendor paths and dotfiles are denied in both stages.
This may change previously public uploads or ACME paths: validate requirements
before installing root rules. Existing server/ancestor rules are unknown.
Hostinger LiteSpeed compatibility with END, overrides, handler and relative
PHP paths must be confirmed; local Apache evidence does not prove production.
Confirm open_basedir allows original root config, session save path remains
outside the deployment target, and required PHP extensions without phpinfo.

Parent metadata: monthly cron `10 3 1 * *` invokes original root script and
appends `/home/u327657097/logs/workday_calendar_sync.log`. Timezone unknown.
Keep it unchanged. Root cache/uploads/vendor listings returned nonexistent;
permissions/session settings unknown. Do not fetch sensitive config via HTTP.

## Ordered cutover (each external write requires specific approval)

1. Preserve root .htaccess state (currently absent per parent), root code rollback
   snapshot and Hostinger settings metadata outside target; never dump secrets.
2. Identify old FTPS trigger externally. Repository/history has no evidence of it;
   do not assert it is disabled. Prevent concurrent writers before enabling Git.
3. Approve source proposal push/merge, CI contents-write permission and feature
   variable separately. Workflow remains inert until variable is true.
4. Build artifact and verify exact tree. Publish branch: first orphan only when
   absent; subsequent commit parent is fetched tip. CI serializes, rechecks source
   main and destination tip immediately before ordinary push. No force. Checkout
   does not persist credentials; isolated publisher has exactly one auth header.
5. Install stage0 root rules after compatibility review. Authorize native Git
   target only workday-runtime-v1, verify target metadata and deployed marker/file
   tree via permitted Hostinger read. Stage0 continues old public pages.
6. Confirm original root config accessible to PHP through mock-equivalent checks,
   without reading value or doing live DB writes. Approve stage1 root rules.
7. Validate /workday-deployment.json source SHA, public assets and cookie-free
   anonymous page/redirect. Never login/register, POST/PUT/DELETE, request user
   data, execute cron, sync or send mail. Even anonymous PHP starts session.
8. Approve second comment-only source commit. Verify new marker, artifact commit
   parent is first tip, normal FF and automatic Hostinger update. Only then report
   two-deployment success. No comment was pushed in this review.

## Rollback

Restore stage0 root rules to serve original root app immediately; root config,
cron and data remain untouched. Disable workflow feature variable and native
Git automatic deployment when authorized. For runtime code rollback, generate a
new child artifact commit containing previous approved tree; ordinary FF only.
Do not rewind or force artifact branch. Keep blocked sensitive paths on rollback.

## Validation

Local Debian Apache 2.4.68 + PHP 8.4.26 were extracted under /tmp; system package
install was denied by ordinary filesystem permission, with no escalation.
`python3 tests/test_native_runtime.py`: lint all artifact PHP; synthetic original
full config/local loader, missing-config failure, distinct old/new asset mtime;
Apache stage0/stage1/stage0 with fake routes, direct/encoded runtime denial,
sensitive canaries, unknown PHP and path-info. No production settings loaded.
`python3 tests/test_publish.py`: real temporary bare Git first orphan, second FF,
stale source no mutation, conflicting non-FF rejected, inherited auth isolated.
Publisher GitHub authentication/native webhook remains untested until approval.
