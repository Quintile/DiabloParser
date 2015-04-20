<?php

namespace Excessive\DiabloParser;

class DiabloHero
{
	public $class;
	public $name;
	public $level;
	public $hardcore;

	public function __construct($class = null)
	{
		$this->class = $class;
	}
}