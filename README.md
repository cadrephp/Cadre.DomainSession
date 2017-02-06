# Cadre.Domain_Session

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
$storage = new \Cadre\Domain_Session\DomainSessionStorageFiles('sessions');
$manager = new \Cadre\Domain_Session\DomainSessionManager($storage);

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

## Notes

I removed the concept of being "finished" from DomainSession because something
about it didn't feel right. There might still be a place for it in the library
but not in DomainSession.

In order to work, I put the check for $id->hasUpdatedValue() in
the DomainSessionStorage classes.  This seems wrong, the check should be in
DomainSessionManager.

I'm not happy with DomainSessionId being mutable and tracking startingValue.
I still have to come up with a solution that cleaner. Perhaps I need to reinstate
the rename method on storage, but to do that I don't think I can serialize a
DomainSession object because the id is embedded in it. I might have to do
something special with DomainSessionStorageFiles because I need to store the
DateTime objects as well as the id and data.
