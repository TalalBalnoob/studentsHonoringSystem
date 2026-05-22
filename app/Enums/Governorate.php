<?php

namespace App\Enums;

class Governorate {
    const RIYADH = 1;
    const MECCA = 2;
    const MEDINA = 3;
    const EASTERN_PROVINCE = 4;
    const ASIR = 5;
    const QASSIM = 6;
    const HAIL = 7;
    const NORTHERN_BORDERS = 8;
    const JAZAN = 9;
    const NAJRAN = 10;
    const BAHA = 11;
    const AL_JOUF = 12;

    /**
     * Get all governorates as id => name mapping
     */
    public static function all(): array {
        return [
            self::RIYADH => 'Riyadh',
            self::MECCA => 'Mecca',
            self::MEDINA => 'Medina',
            self::EASTERN_PROVINCE => 'Eastern Province',
            self::ASIR => 'Asir',
            self::QASSIM => 'Qassim',
            self::HAIL => 'Hail',
            self::NORTHERN_BORDERS => 'Northern Borders',
            self::JAZAN => 'Jazan',
            self::NAJRAN => 'Najran',
            self::BAHA => 'Baha',
            self::AL_JOUF => 'Al-Jouf',
        ];
    }

    /**
     * Get governorate name by ID
     */
    public static function getName(int $id): string {
        return self::all()[$id] ?? 'Unknown';
    }

    /**
     * Get all valid IDs
     */
    public static function ids(): array {
        return array_keys(self::all());
    }
}
