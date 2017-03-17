<?php

// backward compatibility for php 5.5 and low (with phpunit < v.6)
if (!class_exists('\PHPUnit\Framework\TestCase') && class_exists('\PHPUnit_Framework_TestCase')) {
  class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

require __DIR__ . '/../src/PropertyTrait.php';
require __DIR__ . '/../src/IP.php';
require __DIR__ . '/../src/Network.php';
require __DIR__ . '/../src/Range.php';
