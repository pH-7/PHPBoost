<?php

/**
 * Type that maps a PHP array to a clob SQL type.
 *
 * @since 2.0
 */
class ArrayType extends Type
{
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getClobDeclarationSql($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return TextHelper::serialize($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return TextHelper::unserialize($value);
    }

    public function getName()
    {
        return 'Array';
    }
}

?>