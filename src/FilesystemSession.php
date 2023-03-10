<?php

namespace Odan\Session;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\Adapter\Local;

/**
 * A session handler adapter that stores data on the filesystem.
 */
final class FilesystemSession implements SessionInterface, SessionManagerInterface
{
    private array $options = [
        'name' => 'app',
        'lifetime' => 7200,
    ];

    private array $storage;

    private Flash $flash;

    private string $id = '';

    private bool $started = false;
	
	private FilesystemInterface $filesystem;

    public function __construct(array $options = [], FilesystemInterface $filesystem)
    {
        $keys = array_keys($this->options);
        foreach ($keys as $key) {
            if (array_key_exists($key, $options)) {
                $this->options[$key] = $options[$key];
            }
        }

        $session = [];
        $this->storage = &$session;
        $this->flash = new Flash($session);
        $this->filesystem = $filesystem;
    }

    public function getFlash(): FlashInterface
    {
        return $this->flash;
    }

    public function start(): void
    {
        if (!$this->id) {
            $this->regenerateId();
        }

        if ($this->filesystem->has($this->id)) {
            $data = $this->filesystem->read($this->id);
            if ($data !== false) {
                $this->storage = unserialize($data);
            }
        }

        $this->started = true;
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
        $keys = array_keys($this->storage);
        foreach ($keys as $key) {
            unset($this->storage[$key]);
        }
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
        return $this->storage[$key] ?? $default;
    }

    public function all(): array
    {
        return (array)$this->storage;
    }

    public function set(string $key, mixed $value): void
    {
        $this->storage[$key] = $value;
    }

    public function setValues(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->storage[$key] = $value;
        }
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->storage);
    }

    public function delete(string $key): void
    {
        unset($this->storage[$key]);
    }

    public function clear(): void
    {
        $keys = array_keys($this->storage);
        foreach ($keys as $key) {
            unset($this->storage[$key]);
        }
    }

    public function save(): void
    {
        $this->filesystem->put($this->id, serialize($this->storage));
    }
	
}
