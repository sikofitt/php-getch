<?php

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
     * @backupStaticAttributes false
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
        $stdin = $this->ffi->stdin;

        foreach (str_split(strrev(self::HOME_KEY)) as $character) {
            $this->ffi->ungetc(ord($character), $stdin);
        }
        $g = new Getch();
        self::assertEquals(0, $g->getch());
        self::assertEquals(102, $g->getch());
    }
}
