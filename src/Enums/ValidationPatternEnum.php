<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Enums;

enum ValidationPatternEnum: string
{
    case EMAIL = 'email';
    case PHONE_INTERNATIONAL = 'phone_international';
    case PHONE_DE = 'phone_de';
    case POSTAL_CODE_DE = 'postal_code_de';
    case POSTAL_CODE_AT_CH = 'postal_code_at_ch';
    case NAME = 'name';
    case STREET = 'street';
    case IBAN = 'iban';
    case VAT_ID_EU = 'vat_id_eu';
    case NUMERIC = 'numeric';
    case DECIMAL = 'decimal';
    case ALPHANUMERIC = 'alphanumeric';

    public function getRegex(): string
    {
        return match ($this) {
            self::EMAIL => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            self::PHONE_INTERNATIONAL => '/^\+?[0-9\s\-\(\)]{7,20}$/',
            self::PHONE_DE => '/^(\+49|0)[1-9][0-9\s\-\/]{6,14}$/',
            self::POSTAL_CODE_DE => '/^[0-9]{5}$/',
            self::POSTAL_CODE_AT_CH => '/^[0-9]{4}$/',
            self::NAME => '/^[\p{L}\s\-\'\.]{2,50}$/u',
            self::STREET => '/^[\p{L}0-9\s\-\.,\/]{3,100}$/u',
            self::IBAN => '/^[A-Z]{2}[0-9]{2}[A-Z0-9]{11,30}$/',
            self::VAT_ID_EU => '/^[A-Z]{2}[A-Z0-9]{2,12}$/',
            self::NUMERIC => '/^[0-9]+$/',
            self::DECIMAL => '/^[0-9]+([,.][0-9]{1,2})?$/',
            self::ALPHANUMERIC => '/^[a-zA-Z0-9]+$/',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::PHONE_INTERNATIONAL => 'Phone (International)',
            self::PHONE_DE => 'Phone (DE)',
            self::POSTAL_CODE_DE => 'Postal Code (DE)',
            self::POSTAL_CODE_AT_CH => 'Postal Code (AT/CH)',
            self::NAME => 'Name',
            self::STREET => 'Street',
            self::IBAN => 'IBAN',
            self::VAT_ID_EU => 'VAT ID (EU)',
            self::NUMERIC => 'Numeric',
            self::DECIMAL => 'Decimal',
            self::ALPHANUMERIC => 'Alphanumeric',
        };
    }

    /**
     * Get options for use in Filament Select
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }
}
