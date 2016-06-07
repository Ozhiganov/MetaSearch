<?php

namespace app\Models\parserSkripte;
use App\Models\Searchengine;

class Ecoshopper extends Searchengine 
{
	public $results = [];

	function __construct (\SimpleXMLElement $engine,  \App\MetaGer $metager)
	{
		parent::__construct($engine, $metager);
	}

	public function loadResults ($result)
	{
		try {
			$content = simplexml_load_string($result);
		} catch (\Exception $e) {
			abort(500, "$result is not a valid xml string");
		}
		
		if(!$content)
		{
			return;
		}
		$results = $content->xpath('//response/result[@name="response"]/doc');
		foreach($results as $result)
		{
			$result = simplexml_load_string($result->saveXML());
			$title = $result->xpath('//doc/str[@name="artikelName"]')[0]->__toString();
			$link = $result->xpath('//doc/str[@name="artikelDeeplink"]')[0]->__toString();
			$anzeigeLink = parse_url($link);
			if( isset($anzeigeLink['query']) )
			{
	                        parse_str($anzeigeLink['query'], $query);
				if( isset($query['diurl']) )
		                        $anzeigeLink = $query['diurl'];
				else
					$anzeigeLink = $link;
			}else
			{
				$anzeigeLink = $link;
			}
			$descr = $result->xpath('//doc/str[@name="artikelBeschreibung"]')[0]->__toString();
			$this->counter++;
			$this->results[] = new \App\Models\Result(
				$this->engine,
				$title,
				$link,
				$anzeigeLink,
				$descr,
				$this->gefVon,
				$this->counter
			);
		}
	}
}