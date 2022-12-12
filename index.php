<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Галерея шрека</title>
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/media.css">
</head>

<body>
	<main class="main">
		<h1 class="heading">ШРЕКИ</h1>
		<?php 
			include 'form.html'; 
			$dir = '/images/previews/';
			$dir1 = '/images/';
			$f = scandir($_SERVER['DOCUMENT_ROOT'].$dir);
			foreach ($f as $file){
				if(preg_match('/\.(jpg)/', $file)){ // Выводим только .png
						echo '<a href="'.$dir1.$file.'" class="img_link" target="_blank"><div class="item" style="background-image: url('.$dir.$file.'); align"></div> </a>';
					}
		}
		?>
	</main>
</body>
</html>