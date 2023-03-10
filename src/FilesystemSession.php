<?php

namespace Odan\Session;

use League\Flysystem\Filesystem;

/**
 * A Flysystem session handler adapter.
 */
final class FilesystemSession implements SessionInterface, SessionManagerInterface
{
    private array $options = [
        'name' => 'app',
        'lifetime' => 7200,
    ];

    private Filesystem $filesystem;

    private array $storage = [];

    private Flash $flash;

    private string $id = '';

    private bool $started = false;

    public function __construct(Filesystem $filesystem, array $options = [])
    {
        $keys = array_keys($this->options);
        foreach ($keys as $key) {
            if (array_key_exists($key, $options)) {
                $this->options[$key] = $options[$key];
            }
        }

        $this->filesystem = $filesystem;

        if (!$this->id) {
            $this->regenerateId();
        }

        $sessionFilename = $this->options['name'] . '/' . $this->id . '.json';
        if ($this->filesystem->has($sessionFilename)) {
            $content = $this->filesystem->read($sessionFilename);
            $this->storage = json_decode($content, true);
        }

        $this->flash = new Flash($this->storage);
    }

    public function getFlash(): FlashInterface
    {
        return $this->flash;
    }

    public function start(): void
    {
        if (!$this->started) {
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
        $this->filesystem->delete($this->options['name'] . '/' . $this->id . '.json');
        $this->regenerateId();
        $this->storage = [];
        $this->flash = new Flash($this->storage);
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
        if (array_key_exists($key, $this->storage)) {
            return $this->storage[$key];
        } else {
            return $default;
        }
    }

    public function all(): array
    {
        return $this->storage;
    }

    public function set(string $key, mixed $value): void
    {
        $this->storage[$key] = $value;
        $this->filesystem->write($this->options['name'] . '/' . $this->id . '.json', json_encode($this->storage));
    }

    public function setValues(array $values): void
    {
        $this->storage = array_merge($this->storage, $values);
        $this->filesystem->write($this->options['name'] . '/' . $this->id . '.json', json_encode($this->storage));
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->storage);
    }

    public function delete(string $key): void
    {
        unset($this->storage[$key]);
        $this->filesystem->write($$this->options['name'] . '/' . $this->id . '.json', json_encode($this->storage));
    }

    public function clear(): void
    {
        $this->filesystem->delete($this->options['name'] . '/' . $this->id . '.json');
        $this->storage = [];
    }

    public function save(): void
    {
        // The session data is saved automatically on each set(), setValues(), delete() and clear() call.
        // This method does not need to do anything.
    }

}
