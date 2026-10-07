#!/usr/bin/env bash
set -euo pipefail
# Run only in authorized CI. SOURCE_SHA, ARTIFACT_DIR, REPOSITORY and GH_TOKEN required.
: "${SOURCE_SHA:?}" "${ARTIFACT_DIR:?}" "${REPOSITORY:?}" "${GH_TOKEN:?}"
[[ "$REPOSITORY" == ringotom1980/workday-tracker ]] || exit 1
[[ "$SOURCE_SHA" =~ ^[0-9a-f]{40}$ ]] || exit 1
export GIT_CONFIG_NOSYSTEM=1 GIT_CONFIG_GLOBAL=/dev/null
export GIT_CONFIG_COUNT=1 GIT_CONFIG_KEY_0=http.https://github.com/.extraheader
GIT_CONFIG_VALUE_0="AUTHORIZATION: basic $(printf 'x-access-token:%s' "$GH_TOKEN" | base64 -w0)"; export GIT_CONFIG_VALUE_0
remote="https://github.com/${REPOSITORY}.git"
branch=hostinger-workday-runtime-v1
check_source() { [[ "$(git ls-remote "$remote" refs/heads/main | cut -f1)" == "$SOURCE_SHA" ]]; }
check_source
work=$(mktemp -d); trap 'rm -rf "$work"' EXIT
git init -q "$work"
git -C "$work" config user.name 'github-actions[bot]'
git -C "$work" config user.email '41898282+github-actions[bot]@users.noreply.github.com'
git -C "$work" remote add origin "$remote"
tip=$(git ls-remote "$remote" "refs/heads/$branch" | cut -f1)
if [[ -n "$tip" ]]; then
 git -C "$work" fetch --no-tags origin "refs/heads/$branch"
 [[ "$(git -C "$work" rev-parse FETCH_HEAD)" == "$tip" ]] || exit 1
 git -C "$work" checkout -q -b "$branch" "$tip"
 git -C "$work" rm -r --ignore-unmatch . >/dev/null
else
 git -C "$work" checkout -q --orphan "$branch"
fi
cp -a "$ARTIFACT_DIR/." "$work/"
git -C "$work" add --all
git -C "$work" commit -q -m "Deploy source $SOURCE_SHA"
check_source
# Ref changed concurrently? Stop, including first bootstrap races.
[[ "$(git ls-remote "$remote" "refs/heads/$branch" | cut -f1)" == "$tip" ]] || exit 1
git -C "$work" push origin "HEAD:refs/heads/$branch"
