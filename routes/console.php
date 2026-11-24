<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about:api', function () {
    $this->comment('Student Records API is ready.');
})->purpose('Display a short API status message');
