<?php
//http://simplehtmldom.sourceforge.net/
require_once __DIR__ . '/vendor/autoload.php';
use Sunra\PhpSimple\HtmlDomParser;

class Parser {
	public function getArticleText($lang,$title) {
		// /w/api.php?action=parse&format=json&page=Lauri%20Husars&prop=text
		$payload = http_build_query([
			'action' => 'parse',
			'format' => 'json',
			'page' => $title,
			'prop' => 'text'
		]);
		
		$url = "https://$lang.wikipedia.org/w/api.php?".$payload;
		
		//http://codular.com/curl-with-php
		//https://www.webcodegeeks.com/php/php-curl-getpost-example/
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url,
			CURLOPT_USERAGENT => 'EST LAT wikitext parser'
		));
		$resp = curl_exec($curl);
		curl_close($curl);
		return json_decode($resp, true)['parse']['text']['*'];
	}
	
	public function getLength($string) {
		$html = HtmlDomParser::str_get_html($string);
		$whatremove = array('table','h2','h3','h4','span[class=noexcerpt]','span[class=mw-editsection]','ol[class=references]' );
		$removed = array();
		foreach($whatremove as $toRemove){
			foreach($html->find($toRemove) as $thisElement){
				//$thisElement->outertext = '';
				$removed[] = $thisElement->plaintext;
			}
		}
		
		$removed[] = "\n";
		
		$sfdsdf = strlen(trim(str_replace($removed, "", $html->plaintext)));
		return $sfdsdf;
	}
	
	public function getLengthForArticle($lang,$title) {
		$text = $this->getArticleText($lang,$title);
		return $this->getLength($text);
	}
}