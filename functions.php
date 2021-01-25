<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020  https://rewiv.com sikofitt@gmail.com
 *
 * This Source Code Form is subject to the
 * terms of the Mozilla Public License, v. 2.0.
 *
 * If a copy of the MPL was not distributed with this file,
 * You can obtain one at https://mozilla.org/MPL/2.0/.
 */

use Sikofitt\Console\Getch;

if (!function_exists('getch')) {
    function getch(string $linuxLibrary = null): int
    {
        $g = new Getch($linuxLibrary);

        return $g->getch();
    }
}

if (!function_exists('ungetch')) {
    function ungetch($char, string $linuxLibrary = null): int
    {
        $g = new Getch($linuxLibrary);

        return $g->ungetch($char);
    }
}

if (!function_exists('ungetchString')) {
    function ungetchString(string $string, string $linuxLibrary = null): bool
    {
        $g = new Getch($linuxLibrary);

        return $g->ungetchString($string);
    }
}