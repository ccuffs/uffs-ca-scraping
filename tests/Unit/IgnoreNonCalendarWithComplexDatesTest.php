<?php

$ac = new \CCUFFS\Scrap\AcademicCalendarUFFS();

it('tests ignore of non-calendar data with some dates in it', function() use ($ac) {
    $url = 'https://www.uffs.edu.br/atos-normativos/portaria/gr/2019-0620';
    $calendars = $ac->fetchCalendarByUrl($url);

    expect($calendars)->toBeArray();
    $this->assertCount(0, $calendars);
});
