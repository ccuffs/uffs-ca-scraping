<?php

$ac = new \CCUFFS\Scrap\AcademicCalendarUFFS();

it('tests ignore of non-calendar data', function() use ($ac) {
    $url = 'https://www.uffs.edu.br/atos-normativos/resolucao/consuni/2021-0090';
    $calendars = $ac->fetchCalendarByUrl($url);

    expect($calendars)->toBeArray();
    $this->assertCount(0, $calendars);
});
