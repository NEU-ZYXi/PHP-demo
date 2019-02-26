<?php 

class DomDocumentParser {

	private $doc;

	// constructor
	public function __construct($url) {

		// the return array of requesting a website with method=GET
		// User-Agent specifies who is visiting the website
		$options = array(
			'http'=>array('method'=>"GET", 'header'=>"User-Agent: doodleBot/0.1\n")
		);

		$context = stream_context_create($options);

		$this->doc = new DomDocument();
		@$this->doc->loadHTML(file_get_contents($url, false, $context));
	}

	public function getLinks() {
		return $this->doc->getElementsByTagName("a");
	}

	public function getTitles() {
		return $this->doc->getElementsByTagName("title");
	}

	public function getMetaTags() {
		return $this->doc->getElementsByTagName("meta");
	}
}

 ?>