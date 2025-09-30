<?php

namespace App\Enums;

enum HourlyRateType: int implements BaseEnum
{
    case REGULAR = 100;
    case REST = 101;
    case SPECIAL_HOLIDAY = 102;
    case REST_SPECIAL_HOLIDAY = 103;
    case LEGAL_HOLIDAY = 104;
    case REST_LEGAL_HOLIDAY = 105;
    case DOUBLE_HOLIDAY = 106;
    case REST_DOUBLE_HOLIDAY = 107;

    case NIGHT_REGULAR = 200;
    case NIGHT_REST = 201;
    case NIGHT_SPECIAL_HOLIDAY = 202;
    case NIGHT_REST_SPECIAL_HOLIDAY = 203;
    case NIGHT_LEGAL_HOLIDAY = 204;
    case NIGHT_REST_LEGAL_HOLIDAY = 205;
    case NIGHT_DOUBLE_HOLIDAY = 206;
    case NIGHT_REST_DOUBLE_HOLIDAY = 207;

    case OVERTIME_REGULAR = 300;
    case OVERTIME_REST = 301;
    case OVERTIME_SPECIAL_HOLIDAY = 302;
    case OVERTIME_REST_SPECIAL_HOLIDAY = 303;
    case OVERTIME_LEGAL_HOLIDAY = 304;
    case OVERTIME_REST_LEGAL_HOLIDAY = 305;
    case OVERTIME_DOUBLE_HOLIDAY = 306;
    case OVERTIME_REST_DOUBLE_HOLIDAY = 307;

    case OVERTIME_NIGHT_REGULAR = 400;
    case OVERTIME_NIGHT_REST = 401;
    case OVERTIME_NIGHT_SPECIAL_HOLIDAY = 402;
    case OVERTIME_NIGHT_REST_SPECIAL_HOLIDAY = 403;
    case OVERTIME_NIGHT_LEGAL_HOLIDAY = 404;
    case OVERTIME_NIGHT_REST_LEGAL_HOLIDAY = 405;
    case OVERTIME_NIGHT_DOUBLE_HOLIDAY = 406;
    case OVERTIME_NIGHT_REST_DOUBLE_HOLIDAY = 407;

    public function label(): string
    {
        return match ($this) {
            self::REGULAR => 'Regular',
            self::REST => 'Rest Day',
            self::SPECIAL_HOLIDAY => 'Special holiday',
            self::REST_SPECIAL_HOLIDAY => 'Special Holiday & Rest Day',
            self::LEGAL_HOLIDAY => 'Legal holiday',
            self::REST_LEGAL_HOLIDAY => 'Legal Holiday & Rest Day',
            self::DOUBLE_HOLIDAY => 'Double holiday',
            self::REST_DOUBLE_HOLIDAY => 'Double Holiday & Rest Day',

            self::NIGHT_REGULAR => 'Night Regular',
            self::NIGHT_REST => 'Night Rest Day',
            self::NIGHT_SPECIAL_HOLIDAY => 'Night Special holiday',
            self::NIGHT_REST_SPECIAL_HOLIDAY => 'Night Special Holiday & Night Rest Day',
            self::NIGHT_LEGAL_HOLIDAY => 'Night Legal holiday',
            self::NIGHT_REST_LEGAL_HOLIDAY => 'Night Legal Holiday & Night Rest Day',
            self::NIGHT_DOUBLE_HOLIDAY => 'Night Double holiday',
            self::NIGHT_REST_DOUBLE_HOLIDAY => 'Night Double Holiday & Night Rest Day',

            self::OVERTIME_REGULAR => 'Overtime Regular',
            self::OVERTIME_REST => 'Overtime Rest Day',
            self::OVERTIME_SPECIAL_HOLIDAY => 'Overtime Special holiday',
            self::OVERTIME_REST_SPECIAL_HOLIDAY => 'Overtime Special Holiday & Overtime Rest Day',
            self::OVERTIME_LEGAL_HOLIDAY => 'Overtime Legal holiday',
            self::OVERTIME_REST_LEGAL_HOLIDAY => 'Overtime Legal Holiday & Overtime Rest Day',
            self::OVERTIME_DOUBLE_HOLIDAY => 'Overtime Double holiday',
            self::OVERTIME_REST_DOUBLE_HOLIDAY => 'Overtime Double Holiday & Overtime Rest Day',

            self::OVERTIME_NIGHT_REGULAR => 'Overtime Night Regular',
            self::OVERTIME_NIGHT_REST => 'Overtime Night Rest Day',
            self::OVERTIME_NIGHT_SPECIAL_HOLIDAY => 'Overtime Night Special holiday',
            self::OVERTIME_NIGHT_REST_SPECIAL_HOLIDAY => 'Overtime Night Special Holiday & Overtime Night Rest Day',
            self::OVERTIME_NIGHT_LEGAL_HOLIDAY => 'Overtime Night Legal holiday',
            self::OVERTIME_NIGHT_REST_LEGAL_HOLIDAY => 'Overtime Night Legal Holiday & Overtime Night Rest Day',
            self::OVERTIME_NIGHT_DOUBLE_HOLIDAY => 'Overtime Night Double holiday',
            self::OVERTIME_NIGHT_REST_DOUBLE_HOLIDAY => 'Overtime Night Double Holiday & Overtime Night Rest Day',
        };
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'text' => $this->label(),
        ];
    }

    public static function all(): array
    {
        return array_map(
            fn(self $case) => $case->toArray(),
            self::cases()
        );
    }
}
