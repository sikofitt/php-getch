# getch

This simply uses the FFI extension to enable _getch and _ungetch in Windows and linux.

[![Pipeline status](https://code.bgemi.net/olive/PHP/getch/badges/1.x/pipeline.svg)](https://code.bgemi.net/olive/PHP/getch/-/commits/1.x)
[![Coverage report](https://code.bgemi.net/olive/PHP/getch/badges/1.x/coverage.svg)](https://code.bgemi.net/olive/PHP/getch/-/commits/1.x)
[![Latest Release](https://code.bgemi.net/olive/PHP/getch/-/badges/release.svg)](https://code.bgemi.net/olive/PHP/getch/-/releases)

```shell script
$ composer require olivebbs/getch
```

```php
 use Olive\Console\Getch;
 $g = new Getch($linuxLibrary = null); // can also be a library that implements a function called _getch;
                                       // by default uses the bundled Resources/libgetch.so
                                       // on windows uses the built in _getch function.
 $ord = $g->getch();
 print \chr($ord);
 
 $ord = $g->ungetch('A');
 $res = $g->getch();
 $ord === $res // 65

```

Note that if you want to put a word into the STDIN stack, you need to do it in reverse.

```php

    foreach(\str_split(\strrev('Hello World!')) as $char) {
        ungetch($char);
    }

    $result = '';

    do {
        $ord = getch();
        $result .= \chr($ord);
   } while($ord !== ord('!'));

   print $result; // Hello World!

```

There are also helper functions called getch() and ungetch();

```php
use function Olive\Console\getch;
$ord = getch($linuxLibrary = null);
print \chr($ord);

$ord = ungetch('B');
$res = getch();
 $ord === $res // 66
```

## Tests

vendor/bin/phpunit