# Pingsess

[![Latest Version on Packagist](https://img.shields.io/github/release/odan/session.svg)](https://github.com/odan/session/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Build Status](https://github.com/odan/session/workflows/build/badge.svg)](https://github.com/odan/session/actions)
[![Code Coverage](https://scrutinizer-ci.com/g/odan/session/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/odan/session/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/odan/session/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/odan/session/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/odan/session.svg)](https://packagist.org/packages/odan/session/stats)

A middleware (PSR-15) oriented session and flash message handler for PHP, based on the wonderful [odan/session](https://github.com/odan/session).

## Common example

```
// Start the session
$session->start();

// Set a session variable
$session->set('user_id', 123);

// Get a session variable
$user_id = $session->get('user_id');

// Check if a session variable exists
if ($session->has('user_id')) {
    // ...
}

// Delete a session variable
$session->delete('user_id');

// Clear all session variables
$session->clear();

// Regenerate the session ID
$session->regenerateId();

// Destroy the session
$session->destroy();
```

## Specific examples

```
$config = [
    'name' => 'app',
];

// Create a standard session handler
$session = new \Odan\Session\PhpSession($config);
```

```
use Odan\Session\MemorySession;

$session = new MemorySession();
```

```
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Odan\Session\FilesystemSession;

// Create a Filesystem instance
$adapter = new Local(__DIR__.'/sessions');
$filesystem = new Filesystem($adapter);

// Create a FilesystemSession instance
$session = new FilesystemSession($filesystem, [
    'name' => 'my_session',
]);
```

```
use Odan\Session\RedisSession;
use Redis;

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$session = new RedisSession($redis, [
    'name' => 'my_app_session',
    'lifetime' => 3600,
]);
```

```
use Odan\Session\MemcachedSession;
use Memcached;

$memcached = new Memcached();
$memcached->addServer('localhost', 11211);

$session = new MemcachedSession($memcached, [
    'name' => 'my_app_session',
    'lifetime' => 3600,
]);
```

```
use Odan\Session\PdoSession;
use PDO;

$dsn = 'mysql:host=localhost;dbname=my_database';
$username = 'my_username';
$password = 'my_password';

$pdo = new PDO($dsn, $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$session = new PdoSession($pdo, [
    'name' => 'my_app_session',
    'lifetime' => 3600,
    'db_table' => 'my_sessions',
]);
```
