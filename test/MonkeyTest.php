<?php
/**
 * Copyright 2011 Jim Tse
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * Monkey class
 *
 * @author jtse
 *
 */
class MonkeyTest extends PHPUnit_Framework_TestCase {
	/**
	 * @test
	 */
	function addMethodToObject() {
		$monkey = new Monkey();
		$monkey->addMethod("methodA", function($sadf, $lkajsdf)  {
			return true;
		});

		self::assertTrue($monkey->methodA('asdf', ''));
	}

	/**
	 * @test
	 */
	function newlyAddedMethodPassesThis() {
		$monkey = new Monkey();
		$monkey->addMethod("getThis", function($blah, $_this)  {
			return $_this;
		});

		self::assertEquals($monkey, $monkey->getThis('alskjdf'));
	}

	/**
	 * @test
	 */
	function addRegexMethodToObject() {
		$object = new Monkey();
		$object->addMethod("/findAllBy(Email)And(Username)And(Password)/", function($arg1, $_matches, $_this) {
			return $_matches;
		});

		$expected = array('Email', 'Username', 'Password');
		$actual = $object->findAllByEmailAndUsernameAndPassword('asdf');
		self::assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	function addClassMethodPassesThis() {
		$m = new Monkey5;

		$m->addClassMethod("getThis", function($_this) {
			return $_this;
		});

		$expected = new Monkey5;
		$actual = $expected->getThis();

		self::assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	function addClassMethodToOneClassDoesNotAddToAnotherClass() {
		$m = new Monkey5;
		$m->addClassMethod("getMe", function() {
			return 1;
		});

		$n = new Monkey4;
		$n->addClassMethod("getMe", function() {
			return 2;
		});

		$i = new Monkey5;
		$j = new Monkey4;
		self::assertEquals(1, $i->getMe());
		self::assertEquals(2, $j->getMe());
	}

	/**
	 * @test
	 * @group benchmark
	 */
	function benchmarkMonkeyPatchedMethodVersusDirectCall() {
		$object = new Monkey2();
		$object->addMethod("magic", function($_this) {
			return $_this;
		});

		self::benchmark(1000,
			"Direct Call1", function() use ($object) {
				$object->direct();
			},
			"Magic Call1", function() use ($object) {
				$object->magic();
			}
		);
	}

	/**
	 * @test
	 * @group benchmark
	 */
	function benchmarkMonkeyPatchedMethodVersusDirectCallWithFiveLevelsOfInheritance() {
		$object = new Monkey5();
		$object->addMethod("magic", function($_this) {
			return $_this;
		});

		self::benchmark(10000,
			"Direct Call2", function() use ($object) {
				$object->direct();
			},
			"Magic Call2", function() use ($object) {
				$object->magic();
			}
		);
	}

	/**
	 * @test
	 */

	function benchmarkMonkeyPatchVersusRegexMonkeyPatch() {
		$object = new Monkey5();

		$object->addMethod("magic", function($_this) {
			return $_this;
		});

		$object->addMethod("/findBy(\w+)/", function($_matches, $_this) {
			return $_matches;
		});

		self::benchmark(10000,
			"Magic Call", function() use ($object) {
				$object->magic();
			},
			"Regex Magic Call", function() use ($object) {
				$object->findByMe();
			}
		);
	}

	/**
	 * @param string $name
	 * @param integer $n
	 * @param function $callback
	 */
	static function benchmark($n, $name1, $callback1, $name2, $callback2) {
		$time = microtime(true);
		self::times($n, $callback1);
		$delta1 = microtime(true) - $time;


		$time = microtime(true);
		self::times($n, $callback2);
		$delta2 = microtime(true) - $time;
		echo "\n";
		echo "'$name1' @ $n times: " . round(($n / $delta1 * 0.001), 2)
			. "/msec (or 1 call in " . round($delta1 / $n * 1000000, 2) . " microsec)\n";
		echo "'$name2' @ $n times: " . round(($n / $delta2 * 0.001), 2)
			. "/msec (or 1 call in " . round($delta2 / $n * 1000000, 2) . " microsec)\n";

		echo "'$name1' is " . round($delta2/$delta1, 2) . " times faster than '$name2'\n";
	}

	static function times($n, $callback) {
		for($i = 0; $i < $n; $i++) {
			call_user_func_array($callback, array());
		}
	}
}

class Monkey2 extends Monkey {
	function direct() {
		return $this;
	}
}

class Monkey3 extends Monkey2 {
}

class Monkey4 extends Monkey3 {
}

class Monkey5 extends Monkey4 {
}

class MissingMethodException extends Exception {
}