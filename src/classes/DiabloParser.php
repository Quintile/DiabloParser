<?php

namespace Excessive\DiabloParser;

use PHPHtmlParser\Dom;

class DiabloParser
{
	protected $url;

	protected $life;
	protected $damage;
	protected $toughness;
	protected $recovery;


	public function __construct($url)
	{
		$this->url = $url;
	}

	public function parse()
	{
		$source = file_get_contents($this->url);

		$dom = new Dom();
		$dom->load($source);

		$this->life = (int) $this->convertLife($dom->find('.resource-life span.value')->text);
		$this->damage = (double) $dom->find('ul.attributes-core li[data-tooltip=#tooltip-dps-hero] span.value')->text;
		$this->toughness = (int) $dom->find('ul.attributes-core li[data-tooltip=#tooltip-toughness-hero] span.value')->text;
		$this->recovery = (int) $dom->find('ul.attributes-core li[data-tooltip=#tooltip-healing-hero] span.value')->text;
		dd($this);
	}

	private function convertLife($value)
	{
		$numerals = (double)substr($value, 0, -1);
		return $numerals * 1000;
	}
}