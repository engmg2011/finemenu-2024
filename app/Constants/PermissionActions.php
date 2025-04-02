<?php

namespace App\Constants;

class PermissionActions
{
    const Read = 'read';
    const Create = 'create';
    const Update = 'update';
    const Delete = 'delete';

    public static function getConstants(): array
    {
        $reflectionClass = new \ReflectionClass(self::class);
        return $reflectionClass->getConstants();
    }
}
