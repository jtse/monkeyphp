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
class Monkey {
	private static $_CLASS_METHODS = array();
	private static $_CLASS_REGEX_METHODS = array();

	private $_methods = array();
	private $_regexMethods = array();

	function __call($name, $arguments) {
		// Is instance method?
		if (isset($this->_methods[$name])) {
			$arguments[] = $this;
			return call_user_func_array($this->_methods[$name], $arguments);
		}

		$class = get_class($this);
		// Is class method?
		if (isset(self::$_CLASS_METHODS[$class][$name])) {
			$arguments[] = $this;
			return call_user_func_array(self::$_CLASS_METHODS[$class][$name], $arguments);
		}

		// Is instance/class pattern matching method
		$patterns = array_merge(
			isset(self::$_CLASS_REGEX_METHODS[$class])
				? self::$_CLASS_REGEX_METHODS[$class]
				: array()
			, $this->_regexMethods
		);

		foreach($patterns as $pattern => $function) {
			$_matches = array();
			if (preg_match($pattern, $name, $_matches)) {
				array_shift($_matches);

				// Cache the regex match
				$this->_methods[$name] = function() use ($function, $_matches) {
					$arguments = func_get_args();
					$_this = array_shift($arguments);
					$arguments[] = $_matches;
					$arguments[] = $_this;
					return call_user_func_array($function, $arguments);
				};

				$arguments[] = $this;
				return call_user_func_array($this->_methods[$name], $arguments);
			}
		}
	}

	/**
	 * Adds a method to the object
	 *
	 * @param string $nameOrRegex
	 * @param function $callback
	 * @return Monkey (useful for fluent syntax)
	 */
	function addMethod($nameOrRegex, $callback) {
		if (self::is_regex($nameOrRegex)) {
			$this->_regexMethods[$nameOrRegex] = $callback;
		} else {
			$this->_methods[$nameOrRegex] = $callback;
		}

		return $this;
	}

	/**
	 * Adds a method to all instances of the class.
	 *
	 * Note that this cannot be a static method call because the class will
	 * always refer to this class
	 * @param string $nameOrRegex
	 * @param function $callback
	 * @return Monkey (useful for fluent syntax)
	 */
	function addClassMethod($nameOrRegex, $callback) {
		$class = get_class($this);

		if (self::is_regex($nameOrRegex)) {
			self::$_CLASS_REGEX_METHODS[$class][$nameOrRegex] = $callback;
		} else {
			self::$_CLASS_METHODS[$class][$nameOrRegex] = $callback;
		}
		return $this;
	}

	static private function is_regex($string) {
		return preg_match('/\/.*\//', $string);
	}
}