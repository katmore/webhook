#!/bin/sh
# wrapper to generate documentation
#

ME_ABOUT='wrapper to peform unit tests'
ME_USAGE='[<...OPTIONS>] [[--]<...passthru args>]'
ME_COPYRIGHT='Copyright (c) 2018, Doug Bird. All Rights Reserved.'
ME_NAME='phpdoc.sh'
ME_DIR="/$0"; ME_DIR=${ME_DIR%/*}; ME_DIR=${ME_DIR:-.}; ME_DIR=${ME_DIR#/}/; ME_DIR=$(cd "$ME_DIR"; pwd)

#
# paths
#
HTML_ROOT=$ME_DIR/web
DOC_ROOT=$ME_DIR/docs
PHPDOC_ROOT=$DOC_ROOT/phpdoc
PHPDOC_SYMLINK=$HTML_ROOT/.phpdoc

print_hint() {
   echo "  Hint, try: $ME_NAME --usage"
}

CLEAN=0
GENERATE_MD=0
GENERATE_PDF=0
SKIP_PHPDOC=0
OPTION_STATUS=0
while getopts :?qhua-: arg; do { case $arg in
   h|u|a) HELP_MODE=1;;
   -) LONG_OPTARG="${OPTARG#*=}"; case $OPTARG in
      help|usage|about) HELP_MODE=1;;
      clean) CLEAN=1;;
      skip-phpdoc) SKIP_PHPDOC=1;;
      generate-pdf) GENERATE_PDF=1;;
      generate-md) GENERATE_MD=1;;
      *) >&2 echo "$ME_NAME: unrecognized long option --$OPTARG"; OPTION_STATUS=2;;
   esac ;; 
   *) >&2 echo "$ME_NAME: unrecognized option -$OPTARG"; OPTION_STATUS=2;;
esac } done
shift $((OPTIND-1)) # remove parsed options and args from $@ list
[ "$OPTION_STATUS" != "0" ] && { >&2 echo "$ME_NAME: (FATAL) one or more invalid options"; >&2 print_hint; exit $OPTION_STATUS; }

if [ "$HELP_MODE" ]; then
   echo "$ME_NAME"
   echo "$ME_ABOUT"
   echo "$ME_COPYRIGHT"
   echo ""
   echo "Usage:"
   echo "  $ME_NAME $ME_USAGE"
   echo ""
   echo "Options:"
   echo "  --clean"
   echo "   Erase existing documentation contents before proceeding."
   echo ""
   echo "  --generate-pdf"
   echo "   Use "
   exit 0
fi

if [ "$SKIP_PHPDOC" = "0" ]; then
   phpdoc --version > /dev/null 2>&1 || {
      >&2 echo "$ME_NAME: (FATAL) system is missing the 'phpdoc' command"
      exit 1 
   }
fi

if [ "$ME_DIR" != "$(pwd)" ]; then
  cd $ME_DIR || {
     >&2 echo "$ME_NAME: failed to change to app root directory"
     exit 1
  }
fi

if [ "$SKIP_PHPDOC" = "0" ]; then
   if [ "$CLEAN" = "1" ]; then
      rm -rf "$PHPDOC_ROOT"
      rm -f "$PHPDOC_SYMLINK"
      rm -f "$DOC_ROOT/structure.xml"
      rm -f "$DOC_ROOT/classes.svg"
      rm -f "$DOC_ROOT/phpdoc.pdf"
   fi
   phpdoc "$@" || exit
fi

if ( [ ! -e "$PHPDOC_SYMLINK" ] && [ -d "$PHPDOC_ROOT" ] ); then
   ln -s $PHPDOC_ROOT $PHPDOC_SYMLINK
fi 

if [ -f "$PHPDOC_ROOT/structure.xml" ]; then
   mv "$PHPDOC_ROOT/structure.xml" "$DOC_ROOT/structure.xml"
fi

if [ -f "$PHPDOC_ROOT/classes.svg" ]; then
   cp -f "$PHPDOC_ROOT/classes.svg" "$DOC_ROOT/classes.svg"
else
   if [ -f "$PHPDOC_ROOT/graphs/classes.svg" ]; then
      cp -f "$PHPDOC_ROOT/graphs/classes.svg" "$DOC_ROOT/classes.svg"
   fi
fi

if [ "$GENERATE_PDF" = "1" ]; then

   wkhtmltopdf -V > /dev/null 2>&1 || {
      >&2 echo "$ME_NAME: --generate-pdf failed because the command 'wkhtmltopdf' is missing"
   }
   
   pdfunite -v > /dev/null 2>&1 || {
      >&2 echo "$ME_NAME: --generate-pdf failed because the command 'pdfunite' is missing"
   }

   rm -rf $DOC_ROOT/.pdf
   mkdir -p $DOC_ROOT/.pdf
   
   rm -rf $DOC_ROOT/.pdf-html
   cp -rp $PHPDOC_ROOT $DOC_ROOT/.pdf-html
   rm -rf $DOC_ROOT/.pdf-html/css
   
   for filename in $DOC_ROOT/.pdf-html/namespaces/*.html; do
      [ -e "$filename" ] || continue
      wkhtmltopdf \
        --disable-external-links \
        --disable-internal-links \
        --load-error-handling ignore \
        $filename \
        $DOC_ROOT/.pdf/$(basename $filename).pdf
   done
   
   pdfunite $DOC_ROOT/.pdf/*.pdf $DOC_ROOT/phpdoc.pdf || {
      >&2 echo "$ME_NAME: 'pdfunite' failed with exit status $?"
      exit 1
   }
   
   rm -rf $DOC_ROOT/.pdf
   mkdir -p $DOC_ROOT/.pdf
   
   mv $DOC_ROOT/phpdoc.pdf $DOC_ROOT/.pdf/000.pdf
   
   for filename in $DOC_ROOT/.pdf-html/classes/*.html; do
      [ -e "$filename" ] || continue
      wkhtmltopdf \
        --disable-external-links \
        --disable-internal-links \
        --load-error-handling ignore \
        $filename \
        $DOC_ROOT/.pdf/$(basename $filename).pdf
   done
   
   pdfunite $DOC_ROOT/.pdf/*.pdf $DOC_ROOT/phpdoc.pdf || {
      >&2 echo "$ME_NAME: 'pdfunite' failed with exit status $?"
      exit 1
   }
   
   rm -rf $DOC_ROOT/.pdf
   rm -rf $DOC_ROOT/.pdf-html
   
   echo "$ME_NAME: successfully generated '$DOC_ROOT/phpdoc.pdf'"

fi

if [ "$GENERATE_MD" = "1" ]; then
   
   html2markdown --version > /dev/null 2>&1 || {
      >&2 echo "$ME_NAME: --generate-md failed because the command 'html2markdown' is missing"
   }
   
   > "$DOC_ROOT/.phpdoc.md"
   
   rm -rf $DOC_ROOT/.md-html
   cp -rp $PHPDOC_ROOT $DOC_ROOT/.md-html
   rm -rf $DOC_ROOT/.md-html/css
   
   for filename in $DOC_ROOT/.md-html/namespaces/*.html; do
      [ -e "$filename" ] || continue
      #html2text -style pretty "$filename" >> "$DOC_ROOT/.phpdoc.md"
      html2markdown --mark-code --ignore-links "$filename" >> "$DOC_ROOT/.phpdoc.md"
   done
   
   for filename in $DOC_ROOT/.md-html/classes/*.html; do
      [ -e "$filename" ] || continue
      #html2text -style pretty "$filename" >> "$DOC_ROOT/.phpdoc.md"
      html2markdown --ignore-links "$filename" >> "$DOC_ROOT/.phpdoc.md"
   done
   
   sed '/^$/N;/^\n$/D' "$DOC_ROOT/.phpdoc.md" > "$DOC_ROOT/..phpdoc.md"
   
   mv "$DOC_ROOT/..phpdoc.md" "$DOC_ROOT/.phpdoc.md"
   
   sed '1{/^$/d}' "$DOC_ROOT/.phpdoc.md" > "$DOC_ROOT/..phpdoc.md"
   
   mv "$DOC_ROOT/..phpdoc.md" "$DOC_ROOT/.phpdoc.md" 
   
   mv "$DOC_ROOT/.phpdoc.md" "$DOC_ROOT/phpdoc.md"
   
   echo "$ME_NAME: successfully generated '$DOC_ROOT/phpdoc.md'"
   
   rm -rf $DOC_ROOT/.md-html
fi


