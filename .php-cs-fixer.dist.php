<?php

use PhpCsFixer\Config;

$header = file_exists(__DIR__.'/header.txt') ? file_get_contents(__DIR__.'/header.txt') : '';
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;
return (new Config())

    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'header_comment' => ['header' => $header],
    ])
    ->setFinder($finder)
;
