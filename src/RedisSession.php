namespace Odan\Session;

use Redis;

/**
 * A Redis session handler adapter.
 */
final class RedisSession implements SessionInterface, SessionManagerInterface
{
    private array $options = [
        'name' => 'app',
        'lifetime' => 7200,
    ];

    private Redis $redis;

    private string $id = '';

    private bool $started = false;

    public function __construct(Redis $redis, array $options = [])
    {
        $this->redis = $redis;

        $keys = array_keys($this->options);
        foreach ($keys as $key) {
            if (array_key_exists($key, $options)) {
                $this->options[$key] = $options[$key];
            }
        }
    }

    public function getFlash(): FlashInterface
    {
        throw new \RuntimeException('Flash messages are not supported in RedisSession.');
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
        $this->redis->del($this->id);
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
        $value = $this->redis->hget($this->id, $key);
        return ($value !== false) ? $value : $default;
    }

    public function all(): array
    {
        return $this->redis->hgetall($this->id);
    }

    public function set(string $key, mixed $value): void
    {
        $this->redis->hset($this->id, $key, $value);
    }

    public function setValues(array $values): void
    {
        $this->redis->hMset($this->id, $values);
    }

    public function has(string $key): bool
    {
        return $this->redis->hexists($this->id, $key);
    }

    public function delete(string $key): void
    {
        $this->redis->hdel($this->id, $key);
    }

    public function clear(): void
    {
        $this->redis->del($this->id);
    }

    public function save(): void
    {
        $this->redis->expire($this->id, $this->options['lifetime']);
    }

    private function load(): void
    {
        $data = $this->redis->hgetall($this->id);
        $this->storage = array_combine(array_keys($data), array_values($data));
    }
}
