<?php

namespace App\Services;

class GradeConverter
{
    public static array $subgrades = [
        0 => 'a',
        1 => 'a+',
        2 => 'b',
        3 => 'b+',
        4 => 'c',
        5 => 'c+',
    ];

    public static function toFont(float $grade): string
    {
        $mainGrade = floor($grade);
        $subGrade = self::$subgrades[floor(($grade - $mainGrade) / 0.16)] ?? '?';
        return $mainGrade . $subGrade;
    }

    public static function getProgress(float $grade): int
    {
        $mainGrade = floor($grade);
        $subGradeScore = $grade - $mainGrade;
        return (int)round(fmod($subGradeScore, 1/6) / (1/6) * 100);
    }
}
