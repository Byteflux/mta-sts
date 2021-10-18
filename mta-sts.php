#!/usr/bin/env php
<?php

function info($message, ...$args)
{
    printf("$message\n", ...$args);
}

function error($message, ...$args)
{
    fwrite(STDERR, sprintf("$message\n", ...$args));
    exit(1);
}

foreach (['POLICY_MODE', 'POLICY_MX', 'POLICY_MAX_AGE'] as $env_var) {
    if (!isset($_SERVER[$env_var])) {
        error("Environment variable $env_var is required.");
    }
    if (($_SERVER[$env_var] = trim($_SERVER[$env_var])) === '') {
        error("Environment variable $env_var must not be empty.");
    }
}

$mode = strtolower($_SERVER['POLICY_MODE']);
$mx = array_map('trim', preg_split('/ *[,:;\n] */', strtolower($_SERVER['POLICY_MX'])));
$max_age = $_SERVER['POLICY_MAX_AGE'];

$valid_modes = ['testing', 'enforce', 'none'];
$age_min = 86400;
$age_max = 31557600;

if (!in_array($mode, $valid_modes)) {
    error('Invalid POLICY_MODE. Valid modes: %s', implode(', ', $valid_modes));
}

if (array_search('', $mx, true) !== false) {
    error('Invalid POLICY_MX. Empty values detected.');
}

if (!ctype_digit($max_age) || $max_age < $age_min || $max_age > $age_max) {
    error('Invalid POLICY_MAX_AGE. Valid range: >= %d or <= %d', $age_min, $age_max);
}

$dir = 'dist/.well-known';
$file = "$dir/mta-sts.txt";

if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
    error('Failed to create directory: %s', $dir);
}

$fp = fopen($file, 'w');
if (!$fp) {
    error('Failed to open file for writing: %s', $file);
}

function generate_policy($mode, $mx, $max_age)
{
    yield "version: STSv1";
    yield "mode: $mode";
    foreach ($mx as $host) {
        yield "mx: $host";
    }
    yield "max_age: $max_age";
}

info("Writing policy file...\n");

foreach (generate_policy($mode, $mx, $max_age) as $line) {
    info('    %s', $line);
    if (!fwrite($fp, "$line\r\n")) {
        error("\nFailed to write policy data to %s", $file);
    }
}

info("\nPolicy file generated at %s\n", $file);

info("Remember to update the policy ID in your '_mta-sts' TXT records.");
info("Example record using the current date and time:\n");

info("    v=STSv1; id=%s", date('YmdHis'));
