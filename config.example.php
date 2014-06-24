<?php

/**
 * Konata season summary (version 1.0 released on 24.6.2014, http://www.konata.cz)
 *
 * Copyright (c) 2014 Václav Vrbka (aurielle@aurielle.cz)
 */

/**
 * IMPORTANT!
 *
 * Modify this file and save it as config.php. Don't modify other files unless you know what are you doing.
 * You can adjust the look of generated HTML by modifying the template - file template.latte.
 */

// Text file with AIDs (anime IDs on AniDB), one per line
// Note: __DIR__ means current directory
$source = __DIR__ . '/list.txt';

// Path where the output CSV file will be put
$output = __DIR__ . '/summary.csv';

// Path to directory where images of figures will be downloaded
// Must have write permissions
$imgDir = __DIR__ . '/images';

// Path to template file - adjust resulting HTML in this file
$template = __DIR__ . '/template.latte';

// Timeout - how long will the script wait to download each xml
$timeout = 30;

// AniDB client identification string
$client = '';

// Valid version of registered client
$version = '';