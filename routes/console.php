<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:expire-inventory-holds')->everyMinute();
