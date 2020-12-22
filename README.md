# getch

This simply uses the FFI extension to enable _getwch in Windows and an implimentation of _getwch on linux.

```shell script
$ composer require sikofitt/getch:dev-master
```

```php
 use Sikofitt\Console\Getch;
 $g = new Getch($linuxLibrary = null); // can also be a library that implements a function called _getwch;
                                       // by default uses the bundled Resources/libgetwch.so
                                       // on windows uses the built in _getwch function.
 $char = $g->getch();
 print $char;
```

There is also a helper function called getch();

```php
use function Sikofitt\Console\getch;
$char = getch($linuxLibrary = null);
print $char;
```

## Tests
No tests yet.  Just written.