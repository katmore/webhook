#!/bin/sh
# wrapper to peform unit tests
#

ME_ABOUT='wrapper to peform unit tests'
ME_USAGE='[<TEST-SUITE: phpunit>]'
ME_COPYRIGHT='Copyright (c) 2016-2018, Doug Bird. All Rights Reserved.'
ME_NAME='tests.sh'
ME_DIR="/$0"; ME_DIR=${ME_DIR%/*}; ME_DIR=${ME_DIR:-.}; ME_DIR=${ME_DIR#/}/; ME_DIR=$(cd "$ME_DIR"; pwd)
ME_SOURCE="$ME_DIR/$0"


#
# paths
#
PHPUNIT_BIN=$ME_DIR/vendor/bin/phpunit
PHPUNIT_TESTS_ROOT=$ME_DIR/tests

#
# exit codes
#
ME_ERROR_USAGE=2
ME_ERROR_ONE_OR_MORE_TESTS_FAILED=4
ME_ERROR_MISSING_DEP=3

CMD_STATUS_DONTUSE="255 $ME_ERROR_USAGE $ME_ERROR_ONE_OR_MORE_TESTS_FAILED $ME_ERROR_MISSING_DEP"

print_hint() {
	echo "  Hint, try: $ME_NAME --usage"
}

OPTION_IGNORE=0
OPTION_STATUS=0
while getopts :?qhua-: arg; do { case $arg in
   h|u|a) HELP_MODE=1;;
   -) LONG_OPTARG="${OPTARG#*=}"; case $OPTARG in
      help|usage|about) HELP_MODE=1;;
      *) OPTION_IGNORE=1; break;;
      #*) >&2 echo "$ME_NAME: unrecognized long option --$OPTARG"; OPTION_STATUS=$ME_ERROR_USAGE;;
   esac ;; 
   *) OPTION_IGNORE=1; break;;
   #*) >&2 echo "$ME_NAME: unrecognized option -$OPTARG"; OPTION_STATUS=$ME_ERROR_USAGE;;
esac } done
shift $((OPTIND-1-OPTION_IGNORE)) # remove parsed options and args from $@ list
[ "$OPTION_STATUS" != "0" ] && { >&2 echo "$ME_NAME: (FATAL) one or more invalid options"; >&2 print_hint; exit $OPTION_STATUS; }

if [ "$HELP_MODE" ]; then
   echo "$ME_NAME"
   echo "$ME_ABOUT"
   echo "$ME_COPYRIGHT"
   echo ""
   echo "Usage:"
   echo "  $ME_NAME $ME_USAGE"
   echo ""
   echo "Arguments:"
   echo "  <TEST-SUITE>"
   echo "  Optionally specify a test suite; otherwise all test suites are performed."
   echo "  Test suites:"
   echo "    phpunit: \"Unit\" phpunit test suite; see phpunit.xml"
   echo ""
   echo "Exit code meanings:"
   echo "    $ME_ERROR_USAGE: command-line usage error"
   echo "    $ME_ERROR_MISSING_DEP: missing required dependency"
   echo "    $ME_ERROR_ONE_OR_MORE_TESTS_FAILED: one or more tests failed"
   exit 0
fi

cmd_status_filter() {
   cmd_status=$1
   ! [ "$cmd_status" -eq "$cmd_status" ] 2> /dev/null && return 1
   test "${CMD_STATUS_DONTUSE#*$cmd_status}" != "$CMD_STATUS_DONTUSE" && return 1
   ( [ "$cmd_status" -lt "126" ] || [ "$cmd_status" -gt "165" ] ) && return $cmd_status
   return 1
}

enforce_phpunit_sanity() {
   if [ ! -f "$PHPUNIT_BIN" ]; then
      >&2 echo "$ME_NAME: phpunit binary '$PHPUNIT_BIN' is missing or inaccessible, have you run composer?"
      exit $ME_ERROR_MISSING_DEP
   fi
   if [ ! -x "$PHPUNIT_BIN" ]; then
      >&2 echo "$ME_NAME: phpunit binary '$PHPUNIT_BIN' is not executable"
      exit $ME_ERROR_MISSING_DEP
   fi
}

#
# phpunit wrapper function
#
phpunit() {
   $PHPUNIT_BIN "$@" || {
      cmd_status=$?
      >&2 echo "$ME_NAME: phpunit failed with exit code $cmd_status"
      cmd_status_filter $cmd_status
      return
   }
   return 0
}

cd $ME_DIR || {
   >&2 echo "$ME_NAME: failed to change to app root directory"
   exit 1
}

case "$1" in
	-*) ;;
	*) ACTION=$1 ;;
esac

#
# determine if wrapper mode specified by ACTION
#
if [ -n "$ACTION" ]; then
   
   #
   # apply phpunit wrapper mode
   #
   if [ "$ACTION" = "phpunit" ]; then
      shift
      enforce_phpunit_sanity
      phpunit "$@"
      cmd_status_filter $?
      exit
   fi
   case $ACTION in
      phpunit-*)
      if [ -f "$ACTION.xml" ]; then
         shift
         enforce_phpunit_sanity
         phpunit -c "$ACTION.xml" "$@"
         cmd_status_filter $?
         exit
      fi
      ;; 
   esac

   >&2 echo "$ME_NAME: (FATAL) one or more unrecognized arguments '$@'"
   >&2 print_hint
   exit $ME_ERROR_USAGE
fi

#
# no ACTION specified: perform ALL tests
#

#
# sanity check test commands
#
enforce_phpunit_sanity

#
# 
#
TESTS_STATUS=0

echo "$ME_NAME: PHPUnit OPTIONS: "$@""

#
# phpunit tests command
#
phpunit "$@" || TESTS_STATUS=$ME_ERROR_ONE_OR_MORE_TESTS_FAILED
for file in phpunit-*.xml; do
   [ -f "$file" ] || break
   phpunit -c $(basename $file) "$@" || TESTS_STATUS=$ME_ERROR_ONE_OR_MORE_TESTS_FAILED
   
done
#
# Mocha (javascript) tests
#

#
# shunit2 (shell script) tests
# @link https://github.com/kward/shunit2
#















[ "$TESTS_STATUS" -eq "0" ] || {
   >&2 echo "$ME_NAME: one or more tests failed"
   exit $TESTS_STATUS
}