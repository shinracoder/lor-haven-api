<?php


namespace App\Constants;


abstract class AbstractConstant
{
    /**
     * @return array
     */
    public static function getConstantValues(): array
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return array_values($oClass->getConstants());
    }
}
