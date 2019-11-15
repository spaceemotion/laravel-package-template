#!/bin/php
<?php

//
// Since this is a PHP repository template, using PHP for a wizard instead of
// bash makes reading it a bit easier and more cross-platform compatible.
//
// Since this is a one-time script without any composer dependencies,
// it's also not as pretty it can be with fancy libraries.
//

/** Detect as many variables from the git repository as possible. */
function detectGitSettings(): array {
    $name = '';
    $repo = '';

    echo '- Detecting git configuration for smart defaults...';

    $output = exec('git remote get-url origin', $lines, $return);

    if ($return !== 0) {
        throw new RuntimeException('Could execute git command');
    }

    if (preg_match('/github\.com[\/:](\w+)\/([^.\/]+)/i', $output, $matches) !== false) {
        [, $name, $repo] = $matches;
        echo 'OK';
    } else {
        echo 'NO MATCH';
    }

    echo PHP_EOL;

    return ['name' => $name, 'repo' => $repo];
}

function getDisplayName(string $str): string {
    $str = preg_replace('/[^a-z]+/i', ' ', $str);
    $str = ucwords($str);

    return trim(preg_replace('/\s{2,}/', '', $str));
}

/** Grabs user input and allows script cancellation */
function getInput(string $label, ?string $default = ''): string {
    echo "> $label"
        . ($default !== '' ? " [default: $default]" : '')
        . ': ';

    $input = fgets(STDIN);

    if ($input === false) {
        // Signal interrupt (e.g. by CTRL+C)
        exit(1);
    }

    $input = trim($input);

    if ($input !== '') {
        return $input;
    }

    if ($default === '') {
        return getInput($label, $default);
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

    $gitSettings = detectGitSettings();
    $vars = [
        'date_year' => date('Y'),
    ];

    echo PHP_EOL;

    $vars['author_name'] = getInput('Github user or organization name', $gitSettings['name']);
    $vars['package_name'] = getInput('Github repository name', $gitSettings['repo']);
    $vars['display_name'] = getInput('Display name', getDisplayName($vars['package_name']));

    echo PHP_EOL;

    foreach ($vars as $key => $value) {
        printf('  %-20.20s %s' . PHP_EOL, $key, $value);
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
