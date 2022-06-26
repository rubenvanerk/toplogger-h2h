<?php

namespace App\Services;

use Barryvdh\Debugbar\Facades\Debugbar;

class GradeConverterService
{
    public function toFont(string $grade, $rounded = false): string
    {
        if ($rounded && isset(config('grades.rounded')[$grade])) {
            return config('grades.rounded')[$grade];
        }

        if (isset(config('grades')[$grade])) {
            return config('grades')[$grade];
        }

        $gradeAsNumber = round((float)$grade * 100) / 100;

        $mainGrade = floor($gradeAsNumber);
        $subGrade = config('grades.sub_grades')[floor(($gradeAsNumber - $mainGrade) / (1 / 6))] ?? '?';

        return $mainGrade . $subGrade;
    }

    public function getProgress(float $grade): int
    {
        $grade = (round($grade * 100) / 100);

        $mainGrade = floor($grade);
        $subGradeScore = $grade - $mainGrade;
        return (int)round(fmod($subGradeScore, 1 / 6) / (1 / 6) * 100);
    }
}
