#!/usr/bin/env bash
#
# Compile both CSS pipelines.
#
# Two traps this works around:
#
#  1. There are TWO pipelines. winter.css comes from `winter:util compile less`;
#     storm.css comes from mix. storm.less is NOT imported by winter.less, and
#     since tokens.less is imported by storm.less, the :root block only ships in
#     storm.css. Running one without the other leaves the tokens inert or stale.
#
#  2. `winter:util compile less` aborts the WHOLE run on the first plugin whose
#     LESS fails, and exits quietly enough to look successful. Winter.Builder's
#     buildingarea.less calls .clearfix() with no import and does not compile
#     against this core (pre-existing; reproduces on pristine core). So plugins
#     are disabled for the compile and restored to their exact prior state.
#
set -uo pipefail
# Do NOT derive the root from BASH_SOURCE: this script normally lives behind the
# symlink plugins/winter/tailwindui -> plugins-src/Winter/TailwindUI, and
# plugins-src is the same directory as the sibling Plugins/ checkout, so walking
# up from the resolved path leaves the core tree entirely.
if [ -n "${WINTER_ROOT:-}" ]; then
  cd "$WINTER_ROOT"
else
  while [ ! -f artisan ] || [ ! -f modules/system/assets/ui/storm.less ]; do
    [ "$PWD" = "/" ] && { echo "run from the Winter core root, or set WINTER_ROOT" >&2; exit 1; }
    cd ..
  done
fi

STATE=$(mktemp)
RESTORED=0

# Put plugins back the way they were. Runs from the EXIT trap too, so an
# interrupted or failed build never leaves previously enabled plugins disabled.
restore_plugins() {
  [ "$RESTORED" -eq 1 ] && return
  while IFS=$'\t' read -r n e; do
    [ "$e" = "Yes" ] && php artisan plugin:enable "$n" >/dev/null 2>&1
  done < "$STATE"
  RESTORED=1
}
trap 'restore_plugins; rm -f "$STATE"' EXIT
trap 'exit 130' INT TERM

php artisan plugin:list 2>/dev/null \
  | awk -F'|' 'NF>3{gsub(/ /,"",$2); gsub(/ /,"",$5); if($2!="" && $2!="Pluginname") print $2"\t"$5}' > "$STATE"
ENABLED=$(grep -c 'Yes$' "$STATE" || true)

while IFS=$'\t' read -r n e; do
  [ "$e" = "Yes" ] && php artisan plugin:disable "$n" >/dev/null 2>&1
done < "$STATE"

# Keep each compiler's exit status: a compiler killed mid-run can print nothing
# the error patterns below recognise, and must still fail the build.
LESS_OUT=$(php artisan winter:util compile less 2>&1)
LESS_RC=$?
MIX_OUT=$(php artisan mix:compile -p module-system --production --no-progress --no-interaction 2>&1)
MIX_RC=$?

restore_plugins

LE=$(echo "$LESS_OUT" | grep -icE 'error|undefined|exception|not found' || true)
ME=$(echo "$MIX_OUT"  | grep -icE '^ERROR|Module build failed' || true)
if [ "$LE" -gt 0 ] || [ "$LESS_RC" -ne 0 ]; then
  echo "  LESS: exit $LESS_RC, $LE error line(s)"
  echo "$LESS_OUT" | grep -iE 'error|undefined|exception|not found' | head -4 | sed 's/^/    /'
else
  echo "  LESS: clean"
fi
if [ "$ME" -gt 0 ] || [ "$MIX_RC" -ne 0 ]; then
  echo "  MIX:  exit $MIX_RC, $ME error line(s)"
  echo "$MIX_OUT" | grep -iE '^ERROR|Module build failed' | head -4 | sed 's/^/    /'
else
  echo "  MIX:  clean"
fi
echo "  plugins restored: $ENABLED enabled"
[ "$LE" -eq 0 ] && [ "$ME" -eq 0 ] && [ "$LESS_RC" -eq 0 ] && [ "$MIX_RC" -eq 0 ]
