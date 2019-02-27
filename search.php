<?php
include("config.php");

	if(isset($_GET["term"])) {
		$term = $_GET["term"];
	} else {
		exit("Access Denied");
	}
	if ($term == '') {
		header("Location: http://localhost:8080/Xi-Search/index.php");
		exit();
	}

	if(isset($_GET["type"])) {
		$type = $_GET["type"];
	} else {
		$type = "all";
	}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Welcome to Xi-Search</title>
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>

	<div class="wrapper">
		<div class="header">
			<div class="header-content">
				<div class="logo">
					<a href="index.php">
						<img src="assets/images/home-logo.png">
					</a>
				</div>
				<div class="search-container">
					<form action="search.php" method="GET">
						<div class="search-bar-container">
							<input type="text" class="search-box" name="term">
							<button class="search-button">
								<img src="assets/images/search-icon.png">
							</button>
						</div>
					</form>
				</div>
			</div>

			<div class="tabs-container">
				<ul class="tabs-list">
					<li class="<?php echo $type == 'all' ? 'active' : ''; ?>">
						<a href='<?php echo "search.php?term=$term&type=all"; ?>'>
							All
						</a>
					</li>
					<li class="<?php echo $type == 'images' ? 'active' : ''; ?>">
						<a href='<?php echo "search.php?term=$term&type=images"; ?>'>
							Images
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>

</body>
</html>