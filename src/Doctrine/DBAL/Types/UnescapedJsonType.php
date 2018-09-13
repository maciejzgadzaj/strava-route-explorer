<?php

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use const JSON_ERROR_NONE;

/**
 * Type generating json objects values with unescaped Unicode characters.
 */
class UnescapedJsonType extends \Doctrine\DBAL\Types\JsonType
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw ConversionException::conversionFailedSerialization($value, 'json', json_last_error_msg());
        }

        return $encoded;
    }
}
