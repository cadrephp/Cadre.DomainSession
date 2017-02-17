<?php
declare(strict_types=1);
namespace Cadre\DomainSession;

class SessionLockedId extends SessionId
{
    public static function createFromSessionId(SessionId $id)
    {
        $lockedId = new static($id->startingValue());
        $lockedId->value = $id->value();
        return $lockedId;
    }

    public function regenerate(int $length = 16): string
    {
        throw new SessionException('Cannot update a locked session');
    }
}
