# Cadre.Domain_Session

Library for tracking session data within the domain (no cookie handling).

## Example

```php
$idFactory = new \Cadre\Domain_Session\IdFactory();
$storage = new \Cadre\Domain_Session\DomainSessionStorageFiles($idFactory, 'sessions');
$factory = new \Cadre\Domain_Session\DomainSessionFactory($storage);

$id = isset($_COOKIE['PHP_SESSION'])
    ? $_COOKIE['PHP_SESSION']
    : null;

$session = $factory($id);

$session->start();
$session->username = 'tester';
$session->finish();
```
