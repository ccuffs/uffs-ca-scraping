<?php

$ac = new \CCUFFS\Scrap\AcademicCalendarUFFS();

it('tests valid user authentication', function() use ($ac) {
    $calendars = $ac->fetchCalendars();

    /*expect($info)->toHaveProperty('username');
    expect($info)->toHaveProperty('uid');
    expect($info)->toHaveProperty('email');
    expect($info)->toHaveProperty('pessoa_id');
    expect($info)->toHaveProperty('name');
    expect($info)->toHaveProperty('cpf');
    expect($info)->toHaveProperty('token_id');
    expect($info)->toHaveProperty('authenticated');*/

});
