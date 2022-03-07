<?php

$ac = new \CCUFFS\Scrap\AcademicCalendarUFFS();

it('tests fetching all available calendars', function() use ($ac) {
    $calendars = $ac->fetchCalendars();
    expect($calendars)->toBeArray();
});
