<?php

namespace Excessive\DiabloParser;

use PHPHtmlParser\Dom;

class DiabloParser
{
	protected $url;

	protected $life;

	public function __construct($url)
	{
		$this->url = $url;
	}

	public function parse()
	{
		$source = file_get_contents($this->url);

		$dom = new Dom();
		$dom->load($source);

		$this->life = $this->convertLife($dom->find('.resource-life span.value')->text);
	}

	private function convertLife($value)
	{
		$numerals = (double)substr($value, 0, -1);
		return $numerals * 1000;
	}
}