<?php

/*
 * Copyright (c) 2020-2024  https://sikofitt.com sikofitt@gmail.com
 *
 * This Source Code Form is subject to the
 * terms of the Mozilla Public License, v. 2.0.
 *
 * If a copy of the MPL was not distributed with this file,
 * You can obtain one at https://mozilla.org/MPL/2.0/.
 */

namespace Sikofitt\Tests\Console\Getch;

use PHPUnit\Framework\TestCase;
use Sikofitt\Console\Getch;

class GetchTest extends TestCase
{
    private \FFI $ffi;
    private const HOME_KEY = "\x1b[H";

    public function setUp(): void
    {
        $this->ffi = \FFI::load(__DIR__.'/../test.h');
        $file = $this->ffi->stdin;
        $stdin = $this->ffi->fopen('/dev/stdin', 'a+');

        foreach (range('D', 'A') as $character) {
            $this->ffi->ungetc(ord($character), $file);
        }
    }

    /**
     * @preserveGlobalState disabled
     *
     * @backupStaticAttributes false
     *
     * @backupGlobals false
     */
    public function testFailureOnInvalidLibrary()
    {
        Getch::resetFFI();
        $this->expectException(\RuntimeException::class);
        \getch(__DIR__.'/library.so');
    }

    public function testGetchClass()
    {
        $getch = new Getch();
        foreach (range('A', 'D') as $character) {
            self::assertSame(\ord($character), $getch->getch());
        }
    }

    public function testGetchFunction()
    {
        foreach (range('A', 'D') as $character) {
            self::assertSame(\ord($character), getch());
        }
    }

    public function testUnsupportedOS()
    {
        if (PHP_OS_FAMILY !== 'Linux' || PHP_OS_FAMILY !== 'Windows') {
            self::markTestSkipped('This test only applies to non Linux or Windows systems.');
        }
        $this->expectException(\RuntimeException::class);
        \getch();
    }

    public function testHomeKey()
    {
        self::markTestSkipped('This seems impossible to test, since it relies on someone actually pressing keys on the keyboard.');

        $stdin = $this->ffi->stdin;

        foreach (str_split(strrev(self::HOME_KEY)) as $character) {
            $this->ffi->ungetc(ord($character), $stdin);
        }
        $g = new Getch();
        self::assertEquals(0, $g->getch());
        self::assertEquals(71, $g->getch());
    }

    public function setPeek()
    {
        $g = new Getch();
        $g->ungetch('q');
        $peek = $g->peek();
        $getch = $g->getch();
        self::assertEquals($peek, $getch);
        self::assertEquals(113, $peek);
    }
}
