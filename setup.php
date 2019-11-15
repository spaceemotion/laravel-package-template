#!/bin/php
<?php

//
// Since this is a PHP repository template, using PHP for a wizard instead of
// bash makes reading it a bit easier and more cross-platform compatible.
//
// Since this is a one-time script without any composer dependencies,
// it's also not as pretty it can be with fancy libraries.
//

/** Grabs user input and allows script cancellation */
function getInput(string $label, bool $required = false, ?string $default = ''): string {
    echo "> $label"
        . ($required ? ' (required)' : '')
        . ($default !== '' ? " [default: $default]" : '')
        . ': ';

    $input = fgets(STDIN);

    if ($input === false) {
        // Signal interrupt (e.g. by CTRL+C)
        exit(1);
    }

    $input = trim($input);

    if (!$required || $input !== '') {
        return $input;
    }

    if ($default === '') {
        return getInput($label, true);
    }

    return $default;
}

function replaceInFolder(string $directory, array $vars) {
    static $except = ['.', '..', '.git', '.idea'];

    $offset = strlen(__DIR__);

    foreach (array_diff(scandir($directory, SCANDIR_SORT_NONE), $except) as $entry) {
        $path = $directory . DIRECTORY_SEPARATOR . $entry;

        if (is_dir($path)) {
            replaceInFolder($path, $vars);
            continue;
        }

        echo '  ' . substr($path, $offset) . PHP_EOL;
    }
}

function runWizard() {
    echo 'Welcome to the setup assistant of this package template.'.PHP_EOL;
    echo 'This wizard will ask a few questions to fill in the placeholders.'.PHP_EOL;
    echo '(you can use CTRL+C to stop at any time).'.PHP_EOL;
    echo PHP_EOL;

    $vars = [
        'date_year' => date('Y'),
    ];

    echo PHP_EOL;

    $vars['author_name'] = getInput('Github user or organization name', true);
    $vars['package_name'] = getInput('Github repository name', true);
    $vars['display_name'] = getInput('Display name', true);

    echo PHP_EOL;

    foreach ($vars as $key => $value) {
        echo "  $key:\t$value" . PHP_EOL;
    }

    echo PHP_EOL;

    if (strtolower(getInput('Are the above settings ok? (y/N)')) !== 'y') {
        exit(1);
    }

    echo PHP_EOL;
    echo '- Replacing variables:' . PHP_EOL;

    replaceInFolder(__DIR__, $vars);

    echo PHP_EOL . 'OK.' . PHP_EOL;
}

// Off we go
try {
    runWizard();
} catch (Exception $e) {
    echo "ERROR: {$e->getMessage()}" . PHP_EOL;
}
