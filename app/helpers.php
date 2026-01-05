<?php

use App\Models\Settings;

function botsEnabled()
{
    return Settings::get('bots_enabled', false);
}