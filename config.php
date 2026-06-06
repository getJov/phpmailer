<?php
function loadEnvFile($path)
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function envValue($key, $default = '')
{
    $value = getenv($key);

    return $value === false ? $default : $value;
}

function requiredEnv($key)
{
    return trim(envValue($key, ''));
}

loadEnvFile(__DIR__ . '/.env');
?>
