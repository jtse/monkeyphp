<?php
/**
 * Monkey class
 *
 * @author jtse
 *
 */
class Monkey {
	private $_methods = array();
	private $_regexMethods = array();

	function __call($name, $arguments) {
		if (isset($this->_methods[$name])) {
			$arguments[] = $this;
			return call_user_func_array($this->_methods[$name], $arguments);
		}

		foreach($this->_regexMethods as $pattern => $function) {
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

		parent::__call($name, $arguments);
	}

	/**
	 * Adds a method to the object
	 *
	 * @param string $nameOrRegex
	 * @param function $callback
	 * @return Monkey (useful for fluent syntax)
	 */
	function addMethod($nameOrRegex, $callback) {
		if (preg_match('/\/.*\//', $nameOrRegex)) {
			$this->_regexMethods[$nameOrRegex] = $callback;
		} else {
			$this->_methods[$nameOrRegex] = $callback;
		}

		return $this;
	}
}