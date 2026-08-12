<?php

namespace App\services;

use PDO;

class DatabaseSetupService
{
    public function runSchema(string $host, int $port, string $username, string $password, string $schemaPath): array
    {
        $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port);
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $sql = file_get_contents($schemaPath);
        if ($sql === false) {
            throw new \RuntimeException('Failed to read schema file.');
        }

        $statements = $this->splitStatements($sql);
        $executed = 0;

        foreach ($statements as $statement) {
            $trimmed = trim($statement);
            if ($trimmed === '') {
                continue;
            }
            $pdo->exec($trimmed);
            $executed++;
        }

        return [
            'success' => true,
            'executed_statements' => $executed,
        ];
    }

    private function splitStatements(string $sql): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $sql) ?: [];
        $clean = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }
            $clean[] = $line;
        }

        $normalized = implode("\n", $clean);

        return array_filter(array_map('trim', explode(';', $normalized)));
    }
}
