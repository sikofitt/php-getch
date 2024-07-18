<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020-2024  https://sikofitt.com sikofitt@gmail.com
 *
 * This Source Code Form is subject to the
 * terms of the Mozilla Public License, v. 2.0.
 *
 * If a copy of the MPL was not distributed with this file,
 * You can obtain one at https://mozilla.org/MPL/2.0/.
 */

namespace Sikofitt\Console;

final class Getch
{
    // Special key codes
    // Extended scan code used to indicate special keyboard functions
    public const KEY_RESERVED = 0;
    public const KEY_E0 = 0;
    public const KEY_E1 = 224;

    // Supported scan codes.
    public const KEY_F1 = 59;
    public const KEY_F2 = 60;
    public const KEY_F3 = 61;
    public const KEY_F4 = 62;
    public const KEY_F5 = 63;
    public const KEY_F6 = 64;
    public const KEY_F7 = 65;
    public const KEY_F8 = 66;
    public const KEY_F9 = 67;
    public const KEY_F10 = 68;
    public const KEY_F11 = 87;
    public const KEY_F12 = 88;
    public const KEY_UP = 72;
    public const KEY_LEFT = 75;
    public const KEY_RIGHT = 77;
    public const KEY_DOWN = 80;
    public const KEY_DELETE = 83;
    public const KEY_HOME = 71;
    public const KEY_PAGEUP = 73;
    public const KEY_END = 79;
    public const KEY_PAGEDOWN = 81;
    public const KEY_INSERT = 82;

    private const LINUX_LIBRARY = __DIR__.'/Resources/libgetch.so';
    private const WINDOWS_LIBRARY = 'ucrtbase.dll';
    private const DECLARATIONS = <<<DECLARATIONS
        int _getch();
        int _ungetch(int c);
    DECLARATIONS;

    private static ?\FFI $ffi = null;

    public static function resetFFI(): void
    {
        self::$ffi = null;
    }

    public function __construct(?string $linuxLibrary = null)
    {
        if (null === $linuxLibrary) {
            $linuxLibrary = self::LINUX_LIBRARY;
        }

        if (null === self::$ffi) {
            $osFamily = PHP_OS_FAMILY;
            if ('Windows' === $osFamily) {
                $declarations = self::DECLARATIONS.' int _kbhit();';
                self::$ffi = \FFI::cdef($declarations, self::WINDOWS_LIBRARY);
            } elseif ('Linux' === $osFamily) {
                if (!file_exists($linuxLibrary)) {
                    throw new \RuntimeException(sprintf('Could not find library file %s.', $linuxLibrary));
                }
                $declarations = self::DECLARATIONS.' int cinPeek();';
                self::$ffi = \FFI::cdef($declarations, $linuxLibrary);
            } else {
                throw new \RuntimeException(sprintf('Sorry, %s is not supported yet.', $osFamily));
            }
        }
    }

    public function peek(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            if (self::$ffi->_kbhit()) {
                $result = self::$ffi->_getch();
                self::$ffi->_ungetch($result);

                return $result;
            }

            return -1;
        }

        return self::$ffi->cinPeek();
    }

    public function getch(): int
    {
        return self::$ffi->_getch();
    }

    public function ungetch(string|int $char): int
    {
        if (is_string($char)) {
            $char = ord($char[0]);
        }

        return self::$ffi->_ungetch($char);
    }
}
