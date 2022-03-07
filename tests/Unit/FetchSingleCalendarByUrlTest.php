<?php

$ac = new \CCUFFS\Scrap\AcademicCalendarUFFS();

it('tests single calendar fetch by url', function() use ($ac) {
    $url = 'https://www.uffs.edu.br/atos-normativos/portaria/gr/2022-2042';
    $calendars = $ac->fetchCalendarByUrl($url);

    expect($calendars)->toBeArray();
    $this->assertCount(11, $calendars);
});
