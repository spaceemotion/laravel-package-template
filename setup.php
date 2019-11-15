#!/bin/php
<?php

//
// Since this is a PHP repository template, using PHP for a wizard instead of
// bash makes reading it a bit easier and more cross-platform compatible.
//
// Since this is a one-time script without any composer dependencies,
// it's also not as pretty it can be with fancy libraries.
//

function execOrFail(string $command): string {
    $output = exec($command, $lines, $return);

    if ($return !== 0) {
        throw new RuntimeException("Could execute command: '$command' (exit code $return)");
    }

    return $output;
}

/** Detect as many variables from the git repository as possible. */
function detectGitSettings(): array {
    $name = '';
    $repo = '';

    echo '- Detecting git remote URL...';
    $origin = execOrFail('git remote get-url origin');

    if (preg_match('/github\.com[\/:](\w+)\/([^.\/]+)/i', $origin, $matches) !== false) {
        [, $name, $repo] = $matches;
        echo 'OK';
    } else {
        echo 'NO MATCH';
    }

    echo PHP_EOL;

    echo '- Detecting author email...';
    $mail = execOrFail('git config user.email || git config --global user.email || echo');
    echo 'OK' . PHP_EOL;

    return ['name' => $name, 'repo' => $repo, 'email' => $mail];
}

function getDisplayName(string $str): string {
    $str = ucwords(preg_replace('/[^a-z]+/i', ' ', $str));

    return trim(preg_replace('/\s{2,}/', '', $str));
}

function getTitleCase(string $str): string {
    return str_replace(' ', '', getDisplayName($str));
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

function replaceInFolder(array $vars) {
    $files = rtrim(exec('git ls-tree --full-tree -r --full-name --name-only -z HEAD'), "\x00");
    $files = explode("\x00", $files);

    foreach ($files as $entry) {
        echo "  ./$entry" . PHP_EOL;
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
    $vars['author_mail'] = getInput('Author email address', $gitSettings['email']);
    $vars['package_name'] = getInput('Github repository name', $gitSettings['repo']);
    $vars['package_description'] = getInput('Project description (short sentence)');
    $vars['display_name'] = getInput('Display name', getDisplayName($vars['package_name']));
    $vars['composer_tags'] = getInput('List of tags (separated by comma)', 'laravel');
    $vars['vendor_name'] = getInput('Composer Package vendor', getTitleCase($vars['author_name']));
    $vars['vendor_package'] = getInput('Composer Package vendor', getTitleCase($vars['package_name']));

    echo PHP_EOL;

    foreach ($vars as $key => $value) {
        printf('  %-20.20s %s' . PHP_EOL, $key, $value);
    }

    echo PHP_EOL;

    if (strtolower(getInput('Are the above settings ok? (y/N)')) !== 'y') {
        exit(1);
    }

    $vars['composer_tags'] = implode('","', array_map('trim', explode(',', $vars['composer_tags'])));

    echo PHP_EOL;
    echo '- Replacing variables:' . PHP_EOL;

    replaceInFolder($vars);

    echo PHP_EOL . 'Don\'t forget to add the codecov token!' . PHP_EOL;

    echo PHP_EOL . 'OK.' . PHP_EOL;
}

// Off we go
try {
    runWizard();
} catch (Exception $e) {
    echo "ERROR: {$e->getMessage()}" . PHP_EOL;
}
