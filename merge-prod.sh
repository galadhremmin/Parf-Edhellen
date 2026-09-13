#!/bin/bash
set -euo pipefail

# Opens a pull request from master into the production branch rather than merging
# locally, so what reaches production has a reviewable diff and a record.

BASE="parf-edhellen-prod-v2"
HEAD="master"
ENV_FILE="$(dirname "$0")/src/.env"

if ! command -v gh >/dev/null 2>&1; then
  echo "Refusing to continue: the GitHub CLI (gh) is not installed." >&2
  exit 1
fi

# The version names the release and decides the asset path the deploy publishes,
# so the title states which one this is.
if [ ! -f "$ENV_FILE" ]; then
  echo "Refusing to continue: ${ENV_FILE} does not exist." >&2
  exit 1
fi

ED_VERSION=$(sed -n 's/^ED_VERSION=[[:space:]]*//p' "$ENV_FILE" | tail -n 1 | tr -d "\"' \r")
if ! [[ "$ED_VERSION" =~ ^[A-Za-z0-9._-]+$ ]]; then
  echo "Refusing to continue: could not read a usable ED_VERSION from ${ENV_FILE}." >&2
  exit 1
fi

# A pull request describes what is committed, so uncommitted work would be a lie.
if [ -n "$(git status --porcelain)" ]; then
  echo "Refusing to continue: the working tree has uncommitted changes." >&2
  git status --short >&2
  exit 1
fi

git checkout "$HEAD"
git push origin "$HEAD"
git fetch origin "$BASE"

COMMITS=$(git log --oneline "origin/${BASE}..origin/${HEAD}")
if [ -z "$COMMITS" ]; then
  echo "${BASE} already contains everything in ${HEAD}. Nothing to propose."
  exit 0
fi

EXISTING=$(gh pr list --base "$BASE" --head "$HEAD" --state open --json url --jq '.[0].url // empty')
if [ -n "$EXISTING" ]; then
  echo "A pull request is already open, and the push above updated it:"
  echo "  ${EXISTING}"
  exit 0
fi

echo
echo "${HEAD} -> ${BASE}, $(printf '%s\n' "$COMMITS" | wc -l | tr -d ' ') commit(s):"
printf '%s\n' "$COMMITS" | sed 's/^/  /'
echo

if [ ! -t 0 ]; then
  echo "Refusing to continue: no terminal to confirm on." >&2
  exit 1
fi

read -r -p "Are you sure you want to release this as v${ED_VERSION}? [y/N] " CONFIRM
case "$CONFIRM" in
  [yY] | [yY][eE][sS]) ;;
  *)
    echo "Cancelled. Nothing was opened."
    exit 0
    ;;
esac

gh pr create \
  --base "$BASE" \
  --head "$HEAD" \
  --title "Release v${ED_VERSION} to production" \
  --body "$(printf 'Merges `%s` into `%s`.\n\nED_VERSION is `%s`, so the deploy publishes to `public/v%s`:\n\n    ./patch-on-nginx.sh --version=%s <asset-directory>\n\n## Commits\n\n%s\n' \
    "$HEAD" "$BASE" "$ED_VERSION" "$ED_VERSION" "$ED_VERSION" "$COMMITS")"
