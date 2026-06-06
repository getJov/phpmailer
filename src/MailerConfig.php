<?php

require_once __DIR__ . '/EnvLoader.php';

class MailerConfig
{
    private $host;
    private $port;
    private $secure;
    private $username;
    private $password;
    private $from;
    private $fromName;

    public function __construct(
        string $host,
        int $port,
        string $secure,
        string $username,
        string $password,
        string $from,
        string $fromName = ''
    ) {
        $this->host = trim($host);
        $this->port = $port;
        $this->secure = strtolower(trim($secure));
        $this->username = trim($username);
        $this->password = trim($password);
        $this->from = trim($from);
        $this->fromName = trim($fromName);
    }

    public static function fromEnv(string $envPath): MailerConfig
    {
        EnvLoader::load($envPath);

        return self::fromArray([
            'SMTP_HOST' => self::envValue('SMTP_HOST'),
            'SMTP_PORT' => self::envValue('SMTP_PORT'),
            'SMTP_SECURE' => self::envValue('SMTP_SECURE'),
            'SMTP_USERNAME' => self::envValue('SMTP_USERNAME'),
            'SMTP_PASSWORD' => self::envValue('SMTP_PASSWORD'),
            'SMTP_FROM' => self::envValue('SMTP_FROM'),
            'SMTP_FROM_NAME' => self::envValue('SMTP_FROM_NAME'),
        ]);
    }

    public static function fromArray(array $values): MailerConfig
    {
        return new self(
            self::arrayValue($values, 'SMTP_HOST'),
            (int) self::arrayValue($values, 'SMTP_PORT'),
            self::arrayValue($values, 'SMTP_SECURE'),
            self::arrayValue($values, 'SMTP_USERNAME'),
            self::arrayValue($values, 'SMTP_PASSWORD'),
            self::arrayValue($values, 'SMTP_FROM'),
            self::arrayValue($values, 'SMTP_FROM_NAME')
        );
    }

    public function validate(): array
    {
        $errors = [];

        if ($this->host === '') {
            $errors[] = 'SMTP_HOST is required.';
        }

        if ($this->port <= 0) {
            $errors[] = 'SMTP_PORT must be greater than zero.';
        }

        if ($this->secure === '' || !in_array($this->secure, ['ssl', 'tls'], true)) {
            $errors[] = 'SMTP_SECURE must be ssl or tls.';
        }

        if ($this->username === '') {
            $errors[] = 'SMTP_USERNAME is required.';
        }

        if ($this->password === '') {
            $errors[] = 'SMTP_PASSWORD is required.';
        }

        if (!filter_var($this->from, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'SMTP_FROM must be a valid email address.';
        }

        if (self::containsLineBreak($this->fromName)) {
            $errors[] = 'SMTP_FROM_NAME cannot contain line breaks.';
        }

        return $errors;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getSecure(): string
    {
        return $this->secure;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public static function containsLineBreak(string $value): bool
    {
        return strpbrk($value, "\r\n") !== false;
    }

    private static function envValue(string $key): string
    {
        $value = getenv($key);

        return $value === false ? '' : trim((string) $value);
    }

    private static function arrayValue(array $values, string $key): string
    {
        if (!array_key_exists($key, $values)) {
            return '';
        }

        return trim((string) $values[$key]);
    }
}
