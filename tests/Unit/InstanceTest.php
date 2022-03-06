<?php

it('tests construtor with no params', function() {
    $ac = new CCUFFS\Scrap\AcademicCalendarUFFS();
    expect($ac)->toBeInstanceOf(CCUFFS\Scrap\AcademicCalendarUFFS::class);
});
