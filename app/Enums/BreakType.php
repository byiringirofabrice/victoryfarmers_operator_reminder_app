<?php

namespace App\Enums;

enum BreakType: string
{
    case EyeBreak = 'eye_break';
    case LunchBreak = 'lunch_break';
    public function label(): string
{
    return match ($this) {
        self::EyeBreak => 'Eye Break',
        self::LunchBreak => 'Lunch Break',
    };
}
}
