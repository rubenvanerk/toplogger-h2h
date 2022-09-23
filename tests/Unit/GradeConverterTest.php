<?php

it('Converts 6b grades correctly', function () {
    $gradeConverter = new \App\Services\GradeConverterService();
    $convertedGrade = $gradeConverter->toFont('6.33');

    expect($convertedGrade)->toBe('6ʙ');
});

it('Converts progress grades correctly', function () {
    $gradeConverter = new \App\Services\GradeConverterService();

    $convertedGrade = $gradeConverter->toFont('6.49901');
    expect($convertedGrade)->toBe('6ʙ⁺');

    $progress = $gradeConverter->getProgress('6.49901');
    expect($progress)->toBe(0);
});

it('Converts rounded grades correctly', function () {
    $gradeConverter = new \App\Services\GradeConverterService();

    $convertedGrade = $gradeConverter->toFont('5.5', true);
    expect($convertedGrade)->toBe('5⁺');
});

it('Converts custom grades correctly', function () {
    $customGradesJson = '{"key":"custom_boulder","name":"Custom","description":"Custom grade system for boulders at Mountain Network Dordrecht","types":["boulder"],"data":[{"value":2,"name":"2","level":0},{"value":2.5,"name":"2","level":0,"name0":"2"},{"value":2.75,"name":"2⁺","level":1},{"value":3,"name":"3","level":0,"name0":"3"},{"value":4,"name":"4","level":0,"name0":"4"},{"value":5,"name":"5ᴀ","level":0,"name0":"5","name1":"5ᴀ(⁺)"},{"value":5.17,"name":"5ᴀ⁺","level":2},{"value":5.33,"name":"5ʙ","level":1,"name1":"5ʙ(⁺)"},{"value":5.5,"name":"5ʙ⁺","level":2},{"value":5.67,"name":"5ᴄ","level":1,"name1":"5ᴄ(⁺)"},{"value":5.83,"name":"5ᴄ⁺","level":2},{"value":6,"name":"6ᴀ","level":0,"name0":"6","name1":"6ᴀ(⁺)"},{"value":6.17,"name":"6ᴀ⁺","level":2},{"value":6.33,"name":"6ʙ","level":1,"name1":"6ʙ(⁺)"},{"value":6.5,"name":"6ʙ⁺","level":2},{"value":6.67,"name":"6ᴄ","level":1,"name1":"6ᴄ(⁺)"},{"value":6.83,"name":"6ᴄ⁺","level":2},{"value":7,"name":"7ᴀ","level":0,"name0":"7","name1":"7ᴀ(⁺)"},{"value":7.17,"name":"7ᴀ⁺","level":2},{"value":7.33,"name":"7ʙ","level":1,"name1":"7ʙ(⁺)"},{"value":7.5,"name":"7ʙ⁺","level":2},{"value":7.67,"name":"7ᴄ","level":1,"name1":"7ᴄ(⁺)"},{"value":7.83,"name":"7ᴄ⁺","level":2},{"value":8,"name":"8ᴀ","level":0,"name0":"8","name1":"8ᴀ(⁺)"},{"value":8.17,"name":"8ᴀ⁺","level":2},{"value":8.33,"name":"8ʙ","level":1,"name1":"8ʙ(⁺)"},{"value":8.5,"name":"8ʙ⁺","level":2},{"value":8.67,"name":"8ᴄ","level":1,"name1":"8ᴄ(⁺)"},{"value":8.83,"name":"8ᴄ⁺","level":2},{"value":9,"name":"9ᴀ","level":0,"name0":"9","name1":"9ᴀ(⁺)"},{"value":9.17,"name":"9ᴀ⁺","level":2},{"value":9.33,"name":"9ʙ","level":1,"name1":"9ʙ(⁺)"},{"value":9.5,"name":"9ʙ⁺","level":2}]}';
    $customGrades = json_decode($customGradesJson, false)->data;

    $gradeConverter = new \App\Services\GradeConverterService();

    $convertedGrade = $gradeConverter->toFont('2', false, $customGrades);
    expect($convertedGrade)->toBe('2');

    $convertedGrade = $gradeConverter->toFont('2.5', false, $customGrades);
    expect($convertedGrade)->toBe('2');

    $convertedGrade = $gradeConverter->toFont('2.75', false, $customGrades);
    expect($convertedGrade)->toBe('2⁺');

    $convertedGrade = $gradeConverter->toFont('5', false, $customGrades);
    expect($convertedGrade)->toBe('5ᴀ');

    $convertedGrade = $gradeConverter->toFont('3', false, $customGrades);
    expect($convertedGrade)->toBe('3');
});
