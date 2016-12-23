<?php
namespace Cadre\Domain_Session;

class IdFactory implements IdFactoryInterface
{
    public function __invoke()
    {
        if (function_exists('random_bytes')) {
            $prefix = random_bytes(16);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $prefix = openssl_random_pseudo_bytes(16);
        } elseif (function_exists('mcrypt_create_iv')) {
            $prefix = mcrypt_create_iv(16);
        } else {
            $prefix = '' . rand(10000000, 99999999) . rand(10000000, 99999999);
        }

        return password_hash(uniqid($prefix, true), PASSWORD_DEFAULT);
    }
}
