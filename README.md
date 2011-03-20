monkeyphp
=========

Class that adds basic monkey patch support for PHP 

Requires PHP 5.3

Usage
-----

class MyClass extends Monkey {
  public $init = false;
};

$object = new MyClass;

// Add a method dynamically
$object->addMethod('init', function($arg1, $arg2, $_this) {
  // do stuff with $arg1 and $arg2
  ...
  $_this->init = true;  // Anonymous functions in PHP 5.3 do not capture $this
                        // so we must pass in $_this as an argument
});

$object->init("testa", testb");
echo $object->init; // now true

// Add a regex-matching method dynamically
$object->addMethod("/findBy(\w+)/", function($arg1, $arg2, $_matches, $_this) {
  echo $_matches[0];
});

$object->findByUsername(); // echos 'Username';
$object->findByEmail(); // echos 'Email';


Performance
-----------
Dynamic methods is slightly slower than hardwired class methods because of the 
overhead of PHP's __call. Chances are that your methods are performing 
non-trivial operations so you won't notice the overhead. But obviously, don't 
use dynamic methods in a tight loop.

Worst case scenario where the method is a simple getter, dynamic methods 
takes 4 microsecs to execute while hardwired class methods take 1.1 
microsecs (as tested on an Intel 2.4 Ghz Core 2 Duo). The same dynamic regex 
method takes about 7 microsecs to execute.
