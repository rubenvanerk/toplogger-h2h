<?php

namespace App\Services;

class GradeConverterService
{
    protected array $subgrades = [
        0 => 'a',
        1 => 'a+',
        2 => 'b',
        3 => 'b+',
        4 => 'c',
        5 => 'c+',
    ];

    public function toFont(float $grade): string
    {
        $mainGrade = floor($grade);
        $subGrade = $this->subgrades[floor(($grade - $mainGrade) / 0.16)] ?? '?';
        return $mainGrade . $subGrade;
    }

    public function getProgress(float $grade): int
    {
        $mainGrade = floor($grade);
        $subGradeScore = $grade - $mainGrade;
        return (int)round(fmod($subGradeScore, 1 / 6) / (1 / 6) * 100);
    }
}
