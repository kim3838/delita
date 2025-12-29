<?php

namespace App\Enums;

enum RegexValidation: string
{
    case NO_WHITESPACE = '/^\S+$/';

    case NUMERIC_12_DIGITS_6_DECIMALS = '/^\d{1,12}(\.\d{1,6})?$/';

    case NUMERIC_1_DECIMAL = '/^\d+(\.\d{1})?$/';
}
