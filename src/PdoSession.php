<?php

namespace Odan\Session;

use PDO;

/**
 * A PDO session handler adapter.
 */
final class PdoSession implements SessionInterface, SessionManagerInterface
{
    private array $options = [
        'name' => 'app',
        'lifetime' => 7200,
        'db_table' => 'sessions',
    ];

    private PDO $pdo;

    private string $id = '';

    private bool $started = false;

    public function __construct(PDO $pdo, array $options = [])
    {
        $this->pdo = $pdo;

        $keys = array_keys($this->options);
        foreach ($keys as $key) {
            if (array_key_exists($key, $options)) {
                $this->options[$key] = $options[$key];
            }
        }
    }

    public function getFlash(): FlashInterface
    {
        throw new \RuntimeException('Flash messages are not supported in PdoMysqlSession.');
    }

    public function start(): void
    {
        if (!$this->id) {
            $this->regenerateId();
        }

        if (!$this->started) {
            $this->load();
            $this->started = true;
        }
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function regenerateId(): void
    {
        $this->id = str_replace('.', '', uniqid('sess_', true));
    }

    public function destroy(): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->options['db_table'] . ' WHERE id = :id');
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();

        $this->regenerateId();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->options['name'];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $stmt = $this->pdo->prepare('SELECT value FROM ' . $this->options['db_table'] . ' WHERE id = :id AND name = :name');
        $stmt->bindValue(':id', $this->id);
        $stmt->bindValue(':name', $key);
        $stmt->execute();

        $value = $stmt->fetchColumn();
        return ($value !== false) ? $value : $default;
    }

    public function all(): array
    {
        $stmt = $this->pdo->prepare('SELECT name, value FROM ' . $this->options['db_table'] . ' WHERE id = :id');
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[$row['name']] = $row['value'];
        }

        return $data;
    }

    public function set(string $key, mixed $value): void
    {
        $stmt = $this->pdo->prepare('REPLACE INTO ' . $this->options['db_table'] . ' (id, name, value, expiry) VALUES (:id, :name, :value, :expiry)');
        $stmt->bindValue(':id', $this->id);
        $stmt->bindValue(':name', $key);
        $stmt->bindValue(':value', $value);
        $stmt->bindValue(':expiry', time() + $this->options['lifetime']);
        $stmt->execute();
    }

    public function setValues(array $values): void
    {
        $stmt = $this->pdo->prepare('REPLACE INTO ' . $this->options['db_table'] . ' (id, name, value, expiry) VALUES (:id, :name, :value, :expiry)');

        foreach ($values as $key => $value) {
            $stmt->bindValue(':id', $this->id);
            $stmt->bindValue(':name', $key);
            $stmt->bindValue(':value', $value);
            $stmt->bindValue(':expiry', time() + $this->options['lifetime']);
            $stmt->execute();
        }
    }

    public function has(string $key): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM ' . $this->options['db_table'] . ' WHERE id = :id AND name = :name');
        $stmt->bindValue(':id', $this->id);
        $stmt->bindValue(':name', $key);
        $stmt->execute();

        return ($stmt->fetchColumn() > 0);
    }

    public function delete(string $key): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->options['db_table'] . ' WHERE id = :id AND name = :name');
        $stmt->bindValue(':id', $this->id);
        $stmt->bindValue(':name', $key);
        $stmt->execute();
    }

    public function clear(): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM ' . $this->options['db_table'] . ' WHERE id = :id');
        $stmt->bindValue(':id', $this->id);
        $stmt->execute();
    }

    public function save(): void
    {
        $data = $this->all();
        $stmt = $this->pdo->prepare('REPLACE INTO ' . $this->options['db_table'] . ' (id, name, value, expiry) VALUES (:id, :name, :value, :expiry)');

        foreach ($data as $key => $value) {
            $stmt->bindValue(':id', $this->id);
            $stmt->bindValue(':name', $key);
            $stmt->bindValue(':value', $value);
            $stmt->bindValue(':expiry', time() + $this->options['lifetime']);
            $stmt->execute();
        }
    }

}
