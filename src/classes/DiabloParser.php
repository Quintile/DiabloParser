<?php

namespace Excessive\DiabloParser;

use PHPHtmlParser\Dom;

class DiabloParser
{
	protected $url;

	protected $type;
	protected $level;
	protected $class;
	protected $name;
	protected $seasonal;

	protected $life;
	protected $damage;
	protected $toughness;
	protected $recovery;

	protected $str;
	protected $int;
	protected $vit;
	protected $dex;

	public function __construct($url)
	{
		if(!filter_var($url, FILTER_VALIDATE_URL))
			throw new \Exception('Invalid URL supplied');
		
		$this->url = $url;
	}

	public function parse()
	{
		$source = file_get_contents($this->url);

		$dom = new Dom();
		$dom->load($source);

		$this->type = $dom->find('strong.d3-color-hardcore')->text ? 'hardcore' : 'softcore';
		$this->seasonal = $dom->find('strong.d3-color-seasonal')->text ? true : false;
		
		$this->level = (int) $dom->find('h2.class a span strong')->text;
		$this->class = trim($dom->find('h2.class a span')->text);

		$this->name = $dom->find('div.profile-sheet h2.name')->text;

		$this->life = (int) $this->convertLife($dom->find('.resource-life span.value')->text);
		$this->damage = (double) $dom->find('ul.attributes-core li[data-tooltip=#tooltip-dps-hero] span.value')->text;
		$this->toughness = (int) $dom->find('ul.attributes-core li[data-tooltip=#tooltip-toughness-hero] span.value')->text;
		$this->recovery = (int) $dom->find('ul.attributes-core li[data-tooltip=#tooltip-healing-hero] span.value')->text;
		$this->str = $dom->find('ul.attributes-core li[data-tooltip=#tooltip-strength-hero] span.value')->text;
		$this->int = $dom->find('ul.attributes-core li[data-tooltip=#tooltip-intelligence-hero] span.value')->text;
		$this->vit = $dom->find('ul.attributes-core li[data-tooltip=#tooltip-vitality-hero] span.value')->text;
		$this->dex = $dom->find('ul.attributes-core li[data-tooltip=#tooltip-dexterity-hero] span.value')->text;

		return true;
	}

	private function convertLife($value)
	{
		$numerals = (double)substr($value, 0, -1);
		return $numerals * 1000;
	}
}