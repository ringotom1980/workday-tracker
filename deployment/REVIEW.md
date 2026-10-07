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
Root `deployment/root-staged.htaccess` is a separate operator-reviewed control
and is NOT in the artifact. Change only `E=WORKDAY_NATIVE_RUNTIME:0` to `:1`. Stage0 preserves original public app; stage1 internally routes
only exact manifest public URLs, /, /admin/ and /workday-deployment.json.
Unknown PHP and path-info are denied in stage1. Direct runtime, includes,
SQL/cron/logs/cache/uploads/vendor paths and dotfiles are denied in both stages.
The sole dotpath exception is `.well-known/acme-challenge/[A-Za-z0-9_-]+`;
it serves original root static tokens without rewriting. Other .well-known
paths, PHP-like challenge names and directory listing remain denied. Validate
any previously public uploads requirement before installing root rules. Existing server/ancestor rules are unknown.
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
3. Approve source proposal push/merge and CI contents-write permission. The
   main-only workflow activates on approved merge/push; no repository variable
   gate exists. A proposal-branch push does not trigger it. A manual run must
   select main. Never merge until first artifact branch creation is authorized.
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

Change the single staged flag back to 0 to serve original root app immediately; root config,
cron and data remain untouched. Disable the GitHub workflow and native
Git automatic deployment when authorized. For runtime code rollback, generate a
new child artifact commit containing previous approved tree; ordinary FF only.
Do not rewind or force artifact branch. Keep blocked sensitive paths on rollback.

## Validation

Local Debian Apache 2.4.68 + PHP 8.4.26 were extracted under /tmp; system package
install was denied by ordinary filesystem permission, with no escalation.
`python3 tests/test_native_runtime.py`: lint all artifact PHP; synthetic original
full config/local loader, missing-config failure, distinct old/new asset mtime;
Apache stage0/stage1/stage0: 214 assertions counted by test code (129 exact
public file responses = 43 routes x 3 stages; 6 directory entries; 30 sensitive
and direct/encoded runtime denials; 1 marker; 3 unknown PHP/path-info denials;
3 ACME token responses; 15 other dotpath denials; 15 method/query/body checks;
12 original index redirects). GET/POST/PUT/DELETE/PATCH on synthetic echo API
verify unchanged query string, body bytes and method. Unmodified repository
index.php uses synthetic auth for logged-out/logged-in Location checks at / and
/index.php. No real auth, session or DB implementation runs in redirect tests.
No production settings loaded. Earlier 169 count was 129+6+30+1+3; an earlier
prose table mistakenly said 45 public files instead of 43.
`python3 tests/test_publish.py`: real temporary bare Git first orphan, second FF,
stale source no mutation, conflicting non-FF rejected, inherited auth isolated.
Publisher GitHub authentication/native webhook remains untested until approval.

GitHub read-only evidence: actions/runs returned total_count 0. Workflows
collection was rejected by the connector URL allowlist; webhook metadata is
not supported by available tools. No bypass attempted. Repository main tree
and visible deployment-path history have no FTPS workflow/script evidence;
external FTPS source remains unknown and requires user clarification.

Artifact: 55 explicit manifest files, config.php substituted in place, plus one
source marker = 56 files. Branch: hostinger-workday-runtime-v1. No root staged
control or private file is in the artifact.
