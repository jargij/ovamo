<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//use fabpot\goutte;
// use Vendor\Fabpot\Goutte;
//use vendor\fabpot\goutte\Goutte;
//use App\Helpers\Goutte\Goutte;
use App\Helpers\phpcrawl\libs;

class ScraperController extends Controller
{
    //

	public function index() {

		//$scraper = new \App\Helpers\Goutte\Goutte\Client;
		$scraper2 = new \App\Helpers\phpcrawl\libs\PHPCrawler.Class;

		//$crawler = $scraper->request('GET', 'http://www.symfony.com/blog/');

		//$result = file_get_contents('http://www.vivente.nu/index.php?p=vacatures');

		//echo "<pre><xmp>";
		//print_r($result);
	//	echo "</xmp></pre>";

		//$crawler->filter('body')->each(function ($node) {
   	 	//	print $node->text()."\n";
		//});

		//$result = $crawler->response;
		//echo "<br>result:<br>".$result;
		//echo $scraperResult;

		 //$FmyFunctions1 = new \App\library\myFunctions;
 		 //$is_ok = ($FmyFunctions1->is_ok());

		//return view('scraper.index'); 
	}	

}
