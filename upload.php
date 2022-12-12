<?php
$input_name = 'file';
// Разрешенные расширения файлов.
$allow = array();
// Запрещенные расширения файлов.
$deny = array(
	'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp', 
	'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html', 
	'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi'
);
 
// Директория куда будут загружаться файлы.
$path = __DIR__ . '/images/';

if (isset($_FILES[$input_name])) {
	// Проверим директорию для загрузки.
	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}
 
	// Преобразуем массив $_FILES в удобный вид для перебора в foreach.
	$files = array();
	$diff = count($_FILES[$input_name]) - count($_FILES[$input_name], COUNT_RECURSIVE);
	if ($diff == 0) {
		$files = array($_FILES[$input_name]);
	} else {
		foreach($_FILES[$input_name] as $k => $l) {
			foreach($l as $i => $v) {
				$files[$i][$k] = $v;
			}
		}		
	}	
	
	foreach ($files as $file) {
		$error = $success = '';
		
		// Проверим на ошибки загрузки.
		if (!empty($file['error']) || empty($file['tmp_name'])) {
			switch (@$file['error']) {
				case 1:
				case 2: $error = 'Превышен размер загружаемого файла.'; break;
				case 3: $error = 'Файл был получен только частично.'; break;
				case 4: $error = 'Файл не был загружен.'; break;
				case 6: $error = 'Файл не загружен - отсутствует временная директория.'; break;
				case 7: $error = 'Не удалось записать файл на диск.'; break;
				case 8: $error = 'PHP-расширение остановило загрузку файла.'; break;
				case 9: $error = 'Файл не был загружен - директория не существует.'; break;
				case 10: $error = 'Превышен максимально допустимый размер файла.'; break;
				case 11: $error = 'Данный тип файла запрещен.'; break;
				case 12: $error = 'Ошибка при копировании файла.'; break;
				default: $error = 'Файл не был загружен - неизвестная ошибка.'; break;
			}
		} elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
			$error = 'Не удалось загрузить файл.';
		}
		else {
			// Оставляем в имени файла только буквы, цифры и некоторые символы.
			$pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
			$name = mb_eregi_replace($pattern, '-', $file['name']);
			$name = mb_ereg_replace('[-]+', '-', $name);
			
			// Т.к. есть проблема с кириллицей в названиях файлов (файлы становятся недоступны).
			// Сделаем их транслит:
			$converter = array(
				'а' => 'a',   'б' => 'b',   'в' => 'v',    'г' => 'g',   'д' => 'd',   'е' => 'e',
				'ё' => 'e',   'ж' => 'zh',  'з' => 'z',    'и' => 'i',   'й' => 'y',   'к' => 'k',
				'л' => 'l',   'м' => 'm',   'н' => 'n',    'о' => 'o',   'п' => 'p',   'р' => 'r',
				'с' => 's',   'т' => 't',   'у' => 'u',    'ф' => 'f',   'х' => 'h',   'ц' => 'c',
				'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',  'ь' => '',    'ы' => 'y',   'ъ' => '',
				'э' => 'e',   'ю' => 'yu',  'я' => 'ya', 
			
				'А' => 'A',   'Б' => 'B',   'В' => 'V',    'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
				'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',    'И' => 'I',   'Й' => 'Y',   'К' => 'K',
				'Л' => 'L',   'М' => 'M',   'Н' => 'N',    'О' => 'O',   'П' => 'P',   'Р' => 'R',
				'С' => 'S',   'Т' => 'T',   'У' => 'U',    'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
				'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',  'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
				'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
			);
 
			$name = strtr($name, $converter);
			$parts = pathinfo($name);
 
			if (empty($name) || empty($parts['extension'])) {
				$error = 'Недопустимое тип файла';
			} elseif (!empty($allow) && !in_array(strtolower($parts['extension']), $allow)) {
				$error = 'Недопустимый тип файла';
			} elseif (!empty($deny) && in_array(strtolower($parts['extension']), $deny)) {
				$error = 'Недопустимый тип файла';
			} elseif (file_exists($path.$name)) {
				// link_bar(1, 100);
				echo "Файл существует";
				header( "Refresh: 3;url=http://galery.localhost/" );
			}
			else {
				// Чтобы не затереть файл с таким же названием, добавим префикс.
				$i = 0;
				$prefix = '';
				while (is_file($path . $parts['filename'] . $prefix . '.' . $parts['extension'])) {
		  			$prefix = '(' . ++$i . ')';
				}
				$name = $parts['filename'] . $prefix . '.' . $parts['extension'];
				
				
							

				// Перемещаем файл в директорию.
				if (move_uploaded_file($file['tmp_name'], $path . $name)) {
					// Далее можно сохранить название файла в БД и т.п.
					
					// создание файла с превью
					$filename = $path . $name;
					
					
					// echo $filename;
					// echo $path;
					$info   = getimagesize($filename);
					$width  = $info[0];
					$height = $info[1];
					$type   = $info[2];
					
					switch ($type) { 
						case 1: 
							$img = imageCreateFromGif($filename);
							$imgTrue = imageCreateFromGif($filename);
							imageSaveAlpha($img, true);
							imageSaveAlpha($imgTrue, true);
							break;					
						case 2: 
							$img = imageCreateFromJpeg($filename);
							$imgTrue = imageCreateFromJpeg($filename);
							break;
						case 3: 
							$img = imageCreateFromPng($filename);
							$imgTrue = imageCreateFromPng($filename);
							imageSaveAlpha($img, true);
							imageSaveAlpha($imgTrue, true);
							break;
					}
					// preview
					$font = __DIR__ . "/fonts/Guardian Pro Italic.ttf";
					$white = imagecolorallocate($img,255,255,255);
					imagettftext($img, 30, 0, 3, 80, $white, $font , date('m/d/Y h:i:s a', time()));
					imageGif($img, $path.'previews/'.$name);

					// watermark
					$img1 = imagecreatefrompng(__DIR__ . "\images\watermark.png");
					$info1   = getimagesize($path."watermark.png");
					$width1  = $info1[0];
					$height1 = $info1[1];
					$type1   = $info1[2];
					imagecopymerge($imgTrue,$img1, $width -$width1, $height -$height1, 0, 0, $width1, $height1, 50);
					imageGif($imgTrue, $filename);

					// echo $file['tmp_name'];
					// move_uploaded_file($file['tmp_name'], $path.'previews/'.$name);

					$success = 'Файл «' . $name . '» успешно загружен.';
				} else {
					$error = 'Не удалось загрузить файл.';
				}
			}
		}
		
		// Выводим сообщение о результате загрузки.
		if (!empty($success)) {
			echo '<p>' . $success . '</p>';
			header("Location: http://galery.localhost/");	
		} else {
			echo '<p>' . $error . '</p>';
		}
		
	}
}
/* function link_bar($page, $pages_count)
{
	for ($j = 1; $j <= $pages_count; $j++)
	{
		$filename ='images/shrek'.$j.'.jpg';
		if (file_exists($filename)){
			echo ' <a href="images/shrek'.$j.'.jpg">
			<img src="images/previews/shrek'.$j.'.jpg" width="400" height="400">
			</a> '.'<br>';}

	}
	return true;
} // Конец функции */



// function link_bar($page, $pages_count)
// {
// for ($j = 1; $j <= $pages_count; $j++)
// {
// // Вывод ссылки
// $filename ='images/shrek'.$j.'.jpg';
// if (file_exists($filename)){
// 	if ($j == $page) {
// 		echo ' <a style="color: #808000;"  href="images/shrek'.$j.'.jpg"><img src="images/previews/shrek'.$j.'.jpg" width="400" height="400"></a> ';
// 	} else {
// 		echo ' <a style="color: #808000;" href="images/shrek'.$j.'.jpg" ><img src="images/previews/shrek'.$j.'.jpg" width="400" height="400"></a> ';
// 	}
// 	// Выводим разделитель после ссылки, кроме последней
// 	// например, вставить "|" между ссылками
// 	if ($j != $pages_count) echo ' ';
// 	}
// }
// return true;
// }
// link_bar(1, 100);
?>