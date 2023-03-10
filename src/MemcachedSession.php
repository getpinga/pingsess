<?php

namespace Odan\Session;

use Memcached;
use \Pinga\Cookie;

/**
 * A Memcached session handler adapter.
 */
final class MemcachedSession implements SessionInterface, SessionManagerInterface
{
    private array $options = [
        'name' => 'app',
        'lifetime' => 7200,
    ];

    private Memcached $memcached;

    private string $id = '';

    private bool $started = false;

    public function __construct(Memcached $memcached, array $options = [])
    {
        $this->memcached = $memcached;

        $keys = array_keys($this->options);
        foreach ($keys as $key) {
            if (array_key_exists($key, $options)) {
                $this->options[$key] = $options[$key];
            }
        }

        $id = $_COOKIE[$this->options['name']] ?? '';
        if (!$id) {
            $id = str_replace('.', '', uniqid($this->options['prefix'], true));
            Cookie::setcookie($this->options['name'], $id, time() + $this->options['lifetime'], '/', '', false, true);
        }

        session_id($id);
        session_set_save_handler($this, true);
    }

    public function getFlash(): FlashInterface
    {
        throw new \RuntimeException('Flash messages are not supported in MemcachedSession.');
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
        $this->memcached->delete($this->id);
        $this->regenerateId();
        Cookie::setcookie($this->options['name'], '', time() - 3600, '/', '', false, true);
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
        $value = $this->memcached->get($this->id . '.' . $key);
        return ($this->memcached->getResultCode() === Memcached::RES_SUCCESS) ? $value : $default;
    }

    public function all(): array
    {
        $data = $this->memcached->get($this->id);
        return ($this->memcached->getResultCode() === Memcached::RES_SUCCESS && is_array($data)) ? $data : [];
    }

    public function set(string $key, mixed $value): void
    {
        $this->memcached->set($this->id . '.' . $key, $value, $this->options['lifetime']);
    }

    public function setValues(array $values): void
    {
        $this->memcached->set($this->id, $values, $this->options['lifetime']);
    }

    public function has(string $key): bool
    {
        $this->memcached->get($this->id . '.' . $key);
        return ($this->memcached->getResultCode() === Memcached::RES_SUCCESS);
    }

    public function delete(string $key): void
    {
        $this->memcached->delete($this->id . '.' . $key);
    }

    public function clear(): void
    {
        $this->memcached->delete($this->id);
    }

    public function save(): void
    {
        $data = $this->all();
        $this->memcached->set($this->id, $data, $this->options['lifetime']);
        Cookie::setcookie($this->options['name'], $this->id, time() + $this->options['lifetime'], '/', '', false, true);
    }

}
