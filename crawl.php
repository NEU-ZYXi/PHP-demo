<?php 
include("config.php");
include("classes/DomDocumentParser.php");

// recursively crawl websites
$crawled = array();
$crawling = array();
$imagesFound = array();

function insertLink($url, $title, $description, $keywords) {
	global $con;

	// use placeholder in prepare syntax, and bind actual parameter values separatly which is more secure
	$query = $con->prepare("INSERT INTO sites(url, title, description, keywords) VALUES(:url, :title, :description, :keywords)");
	$query->bindParam(":url", $url);
	$query->bindParam(":title", $title);
	$query->bindParam(":description", $description);
	$query->bindParam(":keywords", $keywords);

	return $query->execute();
}

function checkDuplicate($url) {
	global $con;

	$query = $con->prepare("SELECT * FROM sites WHERE url=:url");
	$query->bindParam(":url", $url);
	$query->execute();

	return $query->rowCount() != 0;
}

function insertImage($url, $src, $alt, $title) {
	global $con;

	$query = $con->prepare("INSERT INTO images(url, src, alt, title) VALUES(:url, :src, :alt, :title)");
	$query->bindParam(":url", $url);
	$query->bindParam(":src", $src);
	$query->bindParam(":alt", $alt);
	$query->bindParam(":title", $title);

	return $query->execute();
}

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

	global $imagesFound;
	
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
	if (checkDuplicate($url)) {
		echo "$url already exists<br>";
	} else if (insertLink($url, $title, $description, $keywords)) {
		echo "successful $url inserted<br>";
	} else {
		echo "error<br>";
	}

	$imageArray = $parser->getImages();
	foreach ($imageArray as $image) {
		$src = $image->getAttribute("src");
		$title = $image->getAttribute("title");
		$alt = $image->getAttribute("alt");

		if (!$title && !$alt) {
			continue;
		}

		$src = createLink($src, $url);

		if (!in_array($src, $imagesFound)) {
			$imagesFound[] = $src;

			insertImage($url, $src, $alt, $title);
		}
	}
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
		} 

		// for early terminate testing
		else return;
	}

	array_shift($crawling);

	foreach ($crawling as $site) {
		followLinks($site);
	}
}

$startUrl = "http://www.google.com";
followLinks($startUrl);

 ?>