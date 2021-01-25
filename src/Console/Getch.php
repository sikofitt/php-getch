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
    private const LINUX_LIBRARY = __DIR__.'/Resources/libgetch.so';
    private const WINDOWS_LIBRARY = 'ucrtbase.dll';
    private const DECLARATIONS = <<<DECLARATIONS
        int _getch();
        int _ungetch(int c);
    DECLARATIONS;

    private static ?FFI $ffi = null;

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
