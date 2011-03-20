monkey-php - adding methods to objects and classes dynamically in php
=====================================================================
A PHP class that adds basic monkey patch support, specifically adding 
methods and pattern-matching methods dynamically. Because of the use of lamdas, 
monkeyphp requires PHP 5.3 or higher. The class is small enough that you can 
copy and paste it into the base class of your application.


Usage
-----
To add simple method to an object instance:

    
    class MyClass extends Monkey {
      public $init = false;
    };
    
    $object = new MyClass;
    
    $object->addMethod('init', function($arg1, $arg2, $_this) {
      // do stuff with $arg1 and $arg2
      ...
      $_this->init = true;  // Anonymous functions in PHP 5.3 do not capture $this
                            // so we must pass in $_this as an argument
    });
    
    $object->init("testa", "testb");
    echo $object->init; // now true
    

To add a regex pattern-matching method:

    
    $object->addMethod("/findBy(\w+)/", function($arg1, $arg2, $_matches, $_this) {
      echo $_matches[0];
    });
    
    $object->findByUsername(); // echos 'Username';
    $object->findByEmail(); // echos 'Email';
    
To add a simple method to the class:
    
    $object->addClassMethod("myClassMethod", function() {
      echo 'this is a class method';
    });
    

Performance
-----------
Dynamic methods are slightly slower than hardwired class methods because of the 
overhead of PHP's __call() function. Unless the dynamic method is extremely 
trivial (i.e., a getter), you are probably not going to notice the overhead. 
Obviously, don't use dynamic methods in a tight loop.

Here are the worst case scenario where the method is a simple getter:

- class method: 1.1 microsecs
- dynamic method: 4.0 microsecs
- dynamic pattern-matching method: 7.3 microsecs

Benchmarks were run on an Intel 2.4 Ghz Core 2 Duo MacBook Pro (2010). For details, 
see the benchmarks in test/MonkeyTest.php


Test
----
Run the following command:
    
    phpunit tests/
    

License
-------
Apache License 2.0