<?php declare(strict_types=1);

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
    private const LINUX_LIBRARY = __DIR__ . '/Resources/libgetch.so';
    private const WINDOWS_LIBRARY = 'ucrtbase.dll';

    private static ?FFI $ffi = null;

    public function __construct(string $linuxLibrary = null)
    {
        if (null === $linuxLibrary) {
            $linuxLibrary = self::LINUX_LIBRARY;
        }

        if (self::$ffi === null) {
            $osFamily = PHP_OS_FAMILY;
            if ($osFamily === 'Windows') {
                self::$ffi = FFI::cdef('char _getch();', self::WINDOWS_LIBRARY);
            } elseif ($osFamily === 'Linux') {
                if (!file_exists($linuxLibrary)) {
                    throw new RuntimeException(sprintf('Could not find library file %s.', $linuxLibrary));
                }

                self::$ffi = FFI::cdef('char _getch(); int _ungetch(int ch);', $linuxLibrary);
            } else {
                throw new RuntimeException(sprintf('Sorry, %s is not supported yet.', $osFamily));
            }
        }
    }

    public function getch(): string
    {
        return self::$ffi->_getch();
    }

    public function ungetch(string $char)
    {
        return self::$ffi->_ungetch($char);
    }
}
