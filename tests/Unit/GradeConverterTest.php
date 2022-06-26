<?php

it('Converts 6b grades correctly', function () {
    $gradeConverter = new \App\Services\GradeConverterService();
    $convertedGrade = $gradeConverter->toFont('6.33');

    expect($convertedGrade)->toBe('6b');
});

it('Converts progress grades correctly', function () {
    $gradeConverter = new \App\Services\GradeConverterService();

    $convertedGrade = $gradeConverter->toFont('6.49901');
    expect($convertedGrade)->toBe('6b');

    $progress = $gradeConverter->getProgress('6.49901');
    expect($progress)->toBe(99);
});


it('Converts rounded grades correctly', function () {
    $gradeConverter = new \App\Services\GradeConverterService();

    $convertedGrade = $gradeConverter->toFont('5.5', true);
    expect($convertedGrade)->toBe('5+');
});
