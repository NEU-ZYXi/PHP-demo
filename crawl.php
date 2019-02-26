<?php 

include("classes/DomDocumentParser.php");

// recursively crawl websites
$crawled = array();
$crawling = array();

function createLink($src, $url) {

	$scheme = parse_url($url)["scheme"];
	$host = parse_url($url)["host"];

	// for case 1: //www.xxx.com -> http://www.xxx.com
	if (substr($src, 0, 2) == "//") {
		$src = $scheme . ":" . $src; 
	}

	// for case 2: /xxx -> scheme+host+xxx
	else if (substr($src, 0, 1) == "/") {
		$src = $scheme . "://" . $host . $src;
	}

	// for case 3: ./xxx -> scheme+host+current dirname+xxx
	else if (substr($src, 0, 2) == "./") {
		$src = $scheme . "://" . $host . dirname(parse_url($url))["path"] . substr($src, 1);
	}

	// for case 4: ../xxx -> scheme+host+/../+xxx
	else if (substr($src, 0, 3) == "../") {
		$src = $scheme . "://" . $host . "/" . $src; 
	}

	// for case 5: xxx -> scheme+host+/+xxx
	else if (substr($src, 0, 5) != "https" && substr($src, 0, 4) != "http") {
		$src = $scheme . "://" . $host . "/" . $src; 	
	}

	return $src;
}

function getDetails($url) {
	
	$parser = new DomDocumentParser($url);
	
	$titleArray = $parser->getTitles();
	if (sizeof($titleArray) == 0 || $titleArray->item(0) == NULL) {
		return;
	}
	$title = $titleArray->item(0)->nodeValue;  // take the first item in the array
	$title = str_replace("\n", "", $title);

	if ($title == "") {
		return;
	}

	$description = "";
	$keywords = "";

	$metaArray = $parser->getMetaTags();
	foreach ($metaArray as $meta) {
		if ($meta->getAttribute("name") == "description") {
			$description = $meta->getAttribute("content");
		}

		if ($meta->getAttribute("name") == "keywords") {
			$keywords = $meta->getAttribute("content");
		}
	}

	$description = str_replace("\n", "", $description);
	$keywords = str_replace("\n", "", $keywords);

	// insert details in database

}

function followLinks($url) {

	// define the global reference
	global $crawled;
	global $crawling;

	$parser = new DomDocumentParser($url);

	$linkList = $parser->getLinks();

	foreach ($linkList as $link) {
		$href = $link->getAttribute("href");

		if (strpos($href, "#") !== false) {
			continue;
		} else if (substr($href, 0, 11) == "javascript:") {
			continue;
		}

		$href = createLink($href, $url);

		if (!in_array($href, $crawled)) {
			$crawled[] = $href;  // push item into the array
			$crawling[] = $href;

			getDetails($url);
		} else {
			return;
		}

		// insert links in database
		
	}

	array_shift($crawling);

	foreach ($crawling as $site) {
		followLinks($site);
	}
}

$startUrl = "http://www.bbc.com";
followLinks($startUrl);

 ?>