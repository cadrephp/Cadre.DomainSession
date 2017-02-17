<?php
declare(strict_types=1);
namespace Cadre\DomainSession;

class SessionId
{
    protected $value;
    protected $startingValue;

    public function __construct(string $id = '')
    {
        $this->startingValue = $this->value = $id;
    }

    public static function createWithNewValue(int $length = 16): SessionId
    {
        $id = new static();
        $id->regenerate($length);
        $id->startingValue = $id->value;
        return $id;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function startingValue(): string
    {
        return $this->startingValue;
    }

    public function hasUpdatedValue(): bool
    {
        return $this->startingValue !== $this->value;
    }

    public function regenerate(int $length = 16): string
    {
        $this->value = random_bytes($length);
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
