# Description

This repository contains scripts designed to be used with Markdown documents.

# Adding a table of content to a Markdown document

The script `mdtoc.php` adds a table of content to a given Markdown document.

Usage:

    php mdtoc.php /path/to/input/file /path/to/output/file

Within the (Markdown) input file you indicate where you want to insert a table of content by inserting a special tag "`INSERT-TOC-HERE`".

Example:

    php mdtoc.php mdtoc-example.md mdtoc-example-result.md

See the example of input file: [mdtoc-example.md](https://github.com/dbeurive/markdown-tools/blob/master/mdtoc-example.md)

See the example of output file: [mdtoc-example-result.md](https://github.com/dbeurive/markdown-tools/blob/master/mdtoc-example-result.md)
