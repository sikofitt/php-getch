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

namespace Sikofitt\Console;

use FFI;
use RuntimeException;

final class Getch
{
    // Special key codes
    public const GETCH_SPECIAL = 0;
    public const GETCH_F1 = 59;
    public const GETCH_F2 = 60;
    public const GETCH_F3 = 61;
    public const GETCH_F4 = 62;
    public const GETCH_F5 = 63;
    public const GETCH_F6 = 64;
    public const GETCH_F7 = 65;
    public const GETCH_F8 = 66;
    public const GETCH_F9 = 67;
    public const GETCH_F10 = 68;
    public const GETCH_F11 = 87;
    public const GETCH_F12 = 88;
    public const GETCH_UP_ARROW = 72;
    public const GETCH_LEFT_ARROW = 75;
    public const GETCH_RIGHT_ARROW = 77;
    public const GETCH_DOWN_ARROW = 80;
    public const GETCH_DELETE = 83;
    public const GETCH_HOME = 102;
    public const GETCH_PGUP = 104;
    public const GETCH_END = 107;
    public const GETCH_PGDN = 109;
    public const GETCH_INSERT = 110;

    private const LINUX_LIBRARY = __DIR__.'/Resources/libgetch.so';
    private const WINDOWS_LIBRARY = 'ucrtbase.dll';
    private const DECLARATIONS = <<<DECLARATIONS
        int _getch();
        int _ungetch(int c);
    DECLARATIONS;

    private static ?FFI $ffi = null;

    public static function resetFFI(): void
    {
        static::$ffi = null;
    }

    public function __construct(string $linuxLibrary = null)
    {
        if (null === $linuxLibrary) {
            $linuxLibrary = self::LINUX_LIBRARY;
        }

        if (null === self::$ffi) {
            $osFamily = PHP_OS_FAMILY;
            if ('Windows' === $osFamily) {
                self::$ffi = FFI::cdef(self::DECLARATIONS, self::WINDOWS_LIBRARY);
            } elseif ('Linux' === $osFamily) {
                if (!file_exists($linuxLibrary)) {
                    throw new RuntimeException(sprintf('Could not find library file %s.', $linuxLibrary));
                }

                self::$ffi = FFI::cdef(self::DECLARATIONS, $linuxLibrary);
            } else {
                throw new RuntimeException(sprintf('Sorry, %s is not supported yet.', $osFamily));
            }
        }
    }

    public function getch(): int
    {
        return self::$ffi->_getch();
    }

    public function ungetch($char): int
    {
        if (!is_string($char) && !is_int($char)) {
            throw new \TypeError('ungetch takes a parameter of int or string.');
        }

        if (is_string($char)) {
            $char = ord($char[0]);
        }

        return self::$ffi->_ungetch($char);
    }
}
