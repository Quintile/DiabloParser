<?php

namespace Excessive\DiabloParser;

use PHPHtmlParser\Dom;

define('DIABLO_PATH', 'http://us.battle.net/d3/en/profile/');

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

	public function getHero()
	{
		$source = @file_get_contents($this->url);
		if(!$source)
			return false;

		$hero = new DiabloHero();

		$dom = new Dom();
		$dom->load($source);

		$hero->hardcore = $dom->find('strong.d3-color-hardcore')->text ? true : false;
		$hero->seasonal = $dom->find('strong.d3-color-seasonal')->text ? true : false;
		
		$hero->level = (int) $dom->find('h2.class a span strong')->text;
		$hero->class = trim($dom->find('h2.class a span')->text);

		$hero->name = $dom->find('div.profile-sheet h2.name')->text;

		$hero->life = (int) $this->convertLife($dom->find('.resource-life span.value')->text);
		$hero->damage = (double) $dom->find('ul.attributes-core li[data-tooltip=#tooltip-dps-hero] span.value')->text;
		$hero->toughness = (int) $dom->find('ul.attributes-core li[data-tooltip=#tooltip-toughness-hero] span.value')->text;
		$hero->recovery = (int) $dom->find('ul.attributes-core li[data-tooltip=#tooltip-healing-hero] span.value')->text;
		$hero->str = $dom->find('ul.attributes-core li[data-tooltip=#tooltip-strength-hero] span.value')->text;
		$hero->int = $dom->find('ul.attributes-core li[data-tooltip=#tooltip-intelligence-hero] span.value')->text;
		$hero->vit = $dom->find('ul.attributes-core li[data-tooltip=#tooltip-vitality-hero] span.value')->text;
		$hero->dex = $dom->find('ul.attributes-core li[data-tooltip=#tooltip-dexterity-hero] span.value')->text;
		$hero->lastUpdated = $this->parseUpdated($dom->find('p.last-updated')->text);
		return $hero;
	}

	public function getCareer()
	{
		$source = @file_get_contents($this->url);
		if(!$source)
			return array();

		$dom = new Dom();
		$dom->load($source);

		$career = new DiabloCareer();
		$career->lifetimeKills = (int)$dom->find('div.lifetime span.num-kills')->text;
		$career->eliteKills = (int)$dom->find('div.elite span.num-kills')->text;
		$career->paragon = (int)$this->extractParagon($dom->find('div.paragon span.num-kills')->text);
		$career->hcParagon = (int)$dom->find('div.paragon span.num-kills span.d3-color-hardcore')->text;
		$career->fallen = count($dom->find('div.fallen-hero'));
		return $career;
	}

	public function getCharacters()
	{

		$source = @file_get_contents($this->url);
		if(!$source)
			return array();

		$dom = new Dom();
		$dom->load($source);

		$heroes = $dom->find('ul.hero-tabs li');
		$results = array();
		foreach($heroes as $h)
		{
			$data = explode(' ', $h->find('a')->getAttribute('class'));
			if(count($data) <= 1)
				continue;

			$class = $this->extractClass($data[1]);
			$gender = $this->extractGender($data[1]);

			$hero = new DiabloHero($class);
			$hero->trimmedClass = str_replace(' ', '', $class);
			$hero->name = $h->find('span.name')->text;
			if(is_null($hero->name))
				continue;
			$hero->href = $h->find('a')->getAttribute('href');

			$hero->level = (int)$h->find('span.level')->text;
			$hero->gender = $gender;
			$hero->hardcore = (count($data) == 3 && $data[2] !== "") ? true : false;
			$hero->lastUpdated = $this->parseUpdated($dom->find('p.last-updated')->text);

			$results[] = $hero;
		}

		return $results;
	}

	protected function extractParagon($string)
	{
		return substr($string, 0, strpos($string, ' '));
	}

	protected function extractClass($string)
	{
		return strtolower(str_replace('-', ' ', substr($string, 0, strrpos($string, '-'))));
	}

	protected function extractGender($string)
	{
		return substr($string, strrpos($string, '-')+1);
	}

	protected function convertLife($value)
	{
		$numerals = (double)substr($value, 0, -1);
		return $numerals * 1000;
	}

	public function parseUpdated($string)
	{
		$start = strpos($string, 'on') + 2;
		$stop = strpos($string, 'PDT');

		return new \DateTime(trim(substr($string, $start, $stop-$start)), new \DateTimeZone('America/Los_Angeles'));

	}
}