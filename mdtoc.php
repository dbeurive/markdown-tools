<?php

/**
 * This file implements a very simple tool that adds a table of content to Markdown documents.
 *
 * The original file (with no table of content) should present the following "tag":
 *
 *        INSERT-TOC-HERE
 *
 * The tag will be replaced by the table of content.
 *
 * Usage:
 *
 *        php mdtoc.php <input file> <output file>
 *
 * Example:
 *
 *        php mdtoc.php README-SRC.md README.md
 *
 * @note The script is designed to be used with GitHub's flavour of Markdown.
 *       It should work fine for other flavours of Markdown.
 *       However, it has not been tested with other Markdown flavours, other that GitHub's.
 *
 * @note This script os voluntarily minimalist in its conception, so that it can be easily adapted.
 *
 * @see http://stackoverflow.com/questions/6695439/how-to-link-to-a-named-anchor-in-multimarkdown
 * @see http://stackoverflow.com/questions/4823468/comments-in-markdown
 */

define('TOCTAG', 'INSERT-TOC-HERE');

// -------------------------------------------------------------------
// Process the command line.
// -------------------------------------------------------------------

if (count($argv) != 3) {
    print "Usage is:\nphp mdtoc.php <input-file> <output-file>\n";
    exit(1);
}

$inputFile  = $argv[1];
$outputFile = $argv[2];

// -------------------------------------------------------------------
// Process the input file.
// -------------------------------------------------------------------

if (false === $input = fopen($inputFile, 'r')) {
    error("ERROR: can not open file <$inputFile> - " . getLastErrorMessage());
}

// In the following section we load all the lines within the file.
// We record the following data:
// - The index of the line that contains the tag that indicates where, in the document, to insert the table of content.
// - The index og the lines that contain titles, along with: the level of the title and the text of the title.

/** @var array $document The entire document. */
$document = array();
/** @var array $titles The information about the titles (position, level, text). */
$titles = array();
/** @var int $tocPosition The position, within the document, where to insert the table of content. */
$tocPosition = null;

$matches = array();
$n = 0;
while (($line = fgets($input)) !== false) {

    $line = preg_replace('/\r?\n/', '', $line);

    if (0 === strpos($line, TOCTAG)) {
        // The position of the tag that indicates where to insert the table of content is found.
        $tocPosition = $n;
    } elseif (1 === preg_match('/^(#+)\s+([^#].+)$/i', $line, $matches)) {
        // One title has been found.
        $titles[] = array(
            'level' => strlen($matches[1]),
            'position' => $n,
            'content' => $matches[2]
        );
    }

    $document[] = $line;
    $n++;
}

if (false === fclose($input)) {
    error("ERROR: can not close file <$inputFile> - " . getLastErrorMessage());
}

// -------------------------------------------------------------------
// Create the table of content (and modify the titles). 
// -------------------------------------------------------------------

$n = 0;
/** @var array $_title */
foreach ($titles as $_title) {
    $level    = $_title['level'];
    $position = $_title['position'];
    $content  = $_title['content'];
    $tag      = 'a' . $n;
    $toc[]    = getTocPrefix($level) . ' [' . $content . "](#${tag})";
    $document[$position] = getTitleMark($level) .  " <a name=\"${tag}\"></a>" . $content;
    $n++;
}
$toc[] = '';

if (! is_null($tocPosition)) {
    $document[$tocPosition] = implode(PHP_EOL, $toc);
}

// -------------------------------------------------------------------
// Create the output file.
// -------------------------------------------------------------------

if (false === $output = fopen($outputFile, 'w')) {
    error("ERROR: can not open file <$outputFile> - " . getLastErrorMessage());
}

fwrite($output, implode(PHP_EOL, $document));

if (false === fclose($output)) {
    error("ERROR: can not close file <$outputFile> - " . getLastErrorMessage());
}

exit(0);

/**
 * Return the Markdown's prefix for a given TOC's entry, defined by its level.
 * - For a level 1 title: "-".
 * - For a level 2 title: "  *".
 * - For a level 3 title: "    +".
 * - ...
 * @param int $inLevel The title's level.
 * @return string Markdown's prefix for a given TOC's entry.
 */
function getTocPrefix($inLevel) {
    switch ($inLevel) {
        case 1: return '-';
        case 2: return '  *';
        case 3: return '    +';
        case 4: return '      -';
    }

    $prefix = '';
    for ($i=1; $i<$inLevel; $i++) {
        $prefix .= '  ';
    }
    return "${prefix}-";
}

/**
 * Return the Markdown's tag for a title defined by its level.
 * - For a level 1 title: "#".
 * - For a level 2 title: "##".
 * - For a level 3 title: "###".
 * - ...
 * @param int $inLevel The title's level.
 * @return string The Markdown's tag.
 */
function getTitleMark($inLevel) {
    $mark = '#';
    for ($i=1; $i<$inLevel; $i++) {
        $mark .= '#';
    }
    return $mark;
}

/**
 * Return the message associated with the last error message.
 * @return string The error message.
 */
function getLastErrorMessage() {
    $error = error_get_last();
    return $error['message'];
}

/**
 * Prints an error message and exits.
 * @param string $inMessage Error message to print.
 */
function error($inMessage) {
    print $inMessage . PHP_EOL;
    exit(1);
}
