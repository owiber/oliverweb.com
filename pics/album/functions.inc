<?
function CreateImage($size,$source,$dest,$border=0) {
	$sourcedate = 0;
	$destdate = 0;
	global $convert;
	if (file_exists($dest)) {
		clearstatcache();
		$sourceinfo = stat($source);
		$destinfo = stat($dest);
		$sourcedate = $sourceinfo[10];
		$destdate = $destinfo[10];
	}
	if (!file_exists("$dest") or ($sourcedate > $destdate)) {
		global $ImageTool;
		$imgsize = GetImageSize($source);
		$width = $imgsize[0];
		$height = $imgsize[1];

		$new_width = $size;
		$new_height = ceil($size * $height / $width);
		if ($ImageTool == "gd") {
			$im = ImageCreateFromJPEG($source); 
			$new_im = ImageCreateTrueColor($new_width,$new_height);
	
			ImageCopyResized($new_im,$im,0,0,0,0,$new_width,$new_height,ImageSX($im),ImageSY($im));

			ImageJPEG($new_im,$dest,75);
		} elseif ($ImageTool == "im") {
            // Older ImageMagick
			//system("$convert -quality 80 -antialias -sample $new_width" . "x" . "$new_height -bordercolor black -border $border \"$source\" \"$dest\" 2>&1");
            // Newer ImageMagick
            system("$convert -quality 80 -antialias -sample $new_width" . "x" . "$new_height -bordercolor black -border $border" . "x" . "$border \"$source\" \"$dest\" 2>&1");
		}
	}
}

function NavBar($mode,$path,$start) {
	$endpoint = basename($path);
	global $pix_base;
	global $realbase;
	global $dispsize;
	global $sitehome;
	if ($sitehome != '') {
		print "<A HREF=\"$sitehome\">Home</a> -> \n";
	}
	print "<A HREF=" . dirname($SCRIPT_NAME) . "?dispsize=$dispsize>Albums</a>\n";
	$doo = split("/",$path);
	$prepath = '';
	while (list($foo,$filename) = each($doo)) {
		if ($prepath) {
			$fullname = $prepath . "/" . $filename;
		} else {
			$fullname = $filename;
		}
		if ($filename == $endpoint) { 
			print " -> <b>$filename</b>\n";
		} else {
			print " -> <A HREF=?mode=album&album=" . urlencode($fullname) . "&dispsize=$dispsize&start=$start>$filename</a>\n";
		}
		$prepath = $fullname;
	}
}
function checkDir($pathname) {
	$darray = split("/",$pathname);
	for ($x = 1; $x < sizeof($darray); $x++) {
		$checkdir = '';
		for($y = 1; $y <= $x; $y++) {
			$checkdir .= "/" . $darray[$y];
		}
		if (file_exists($checkdir)) {
		} else {
			mkdir($checkdir,0755);
		}
	}
}
?>
