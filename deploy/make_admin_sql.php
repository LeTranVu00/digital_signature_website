<?php

function parseEnvFile(string $path): array
{
    $values = [];

    foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $value = trim($value);

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $values[$key] = $value;
    }

    return $values;
}

$env = parseEnvFile(__DIR__.'/../.env.production');
$email = $env['ADMIN_EMAIL'] ?? '';
$password = $env['ADMIN_PASSWORD'] ?? '';
$name = $env['ADMIN_NAME'] ?? 'Administrator';

if ($email === '' || $password === '') {
    fwrite(STDERR, "Missing ADMIN_EMAIL or ADMIN_PASSWORD.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$now = gmdate('Y-m-d H:i:s');
$quote = fn (string $value): string => "'".str_replace("'", "''", $value)."'";

$sql = "SET NAMES utf8mb4;\n";
$sql .= "INSERT INTO `users` (`name`, `email`, `role`, `status`, `email_verified_at`, `password`, `created_at`, `updated_at`) VALUES\n";
$sql .= '('.implode(', ', [
    $quote($name),
    $quote($email),
    $quote('admin'),
    $quote('active'),
    $quote($now),
    $quote($hash),
    $quote($now),
    $quote($now),
]).")\n";
$sql .= "ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `role` = 'admin', `status` = 'active', `email_verified_at` = VALUES(`email_verified_at`), `password` = VALUES(`password`), `updated_at` = VALUES(`updated_at`);\n";

file_put_contents(__DIR__.'/create_admin.sql', $sql);

echo "created=deploy/create_admin.sql\n";
echo "admin_email={$email}\n";
