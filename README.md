# Cadre.DomainSession

Library for tracking session data within the domain (no cookie handling).

## Installation

I do not currently have this package on [Packagist](https://packagist.org/). 
You can install via composer by specifying the following repository:

```json
{
  "repositories": [{
    "type": "composer",
    "url": "https://packages.cadrephp.com"
  }]
}
```

## Example

```php
$storage = new \Cadre\DomainSession\DomainSessionStorageFiles('sessions');
$manager = new \Cadre\DomainSession\DomainSessionManager($storage);

$id = isset($_COOKIE['PHP_SESSION'])
    ? $_COOKIE['PHP_SESSION']
    : null;

$session = $manager->start($id);
$session->set('username', 'tester');

// Regenerate ID
$session->id()->regenerate();

// Renew Session (new expires timestamp)
$session->renew();

$manager->finish($session);

if ($session->id()->hasUpdatedId()) {
    setcookie('PHP_SESSION', $session->id()->value());
}
```
