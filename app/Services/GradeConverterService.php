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

    protected array $gradesRounded = [
        '2' => '2',
        '2.33' => '2+',
        '2.5' => '2+',
        '2.75' => '3-',
        '2.67' => '3-',
        '3.0' => '3',
        '3.33' => '3+',
        '3.67' => '4-',
        '4.0' => '4',
        '4.33' => '4+',
        '4.67' => '5-',
        '5.0' => '5',
        '5.5' => '5+'
    ];

    public function toFont(string $grade, $rounded = false): string
    {
        if ((float) $grade < 3) {
            return $this->gradesRounded[$grade] ?? $grade;
        }

        if ($rounded && isset($this->gradesRounded[$grade])) {
            return $this->gradesRounded[$grade];
        }

        $gradeAsNumber = (float)$grade;

        $mainGrade = floor($gradeAsNumber);
        $subGrade = $this->subgrades[floor(($gradeAsNumber - $mainGrade) / 0.16)] ?? '?';
        return $mainGrade . $subGrade;
    }

    public function getProgress(float $grade): int
    {
        $mainGrade = floor($grade);
        $subGradeScore = $grade - $mainGrade;
        return (int)round(fmod($subGradeScore, 1 / 6) / (1 / 6) * 100);
    }
}
