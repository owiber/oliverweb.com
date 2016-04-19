<?php
	// Notes:
	//	none right now
	
	//Config vars
	$textdir = '../../02/'; //directory of the text files
	$extension = '.shtml'; //text file extensions
	$default = "profile"; //name of text file selected by default on update menu (w/o ext.)
	$updatefile = "textbox"; //name of latest textbox data (w/o ext.)
	$counterfile = "counter.txt";
	$imgcounterfile = "imagecounter.txt";
	$momentfile = '../../images02/moment.shtml';
	
	//other vars
	$version = "1.2";
	$author = "Oliver Wong";
	$authormail = 'oliver@oliverweb.com';
	$myname = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/') + 1);
?>
<html>
	<head>
		<title>OliverWEB Update Script v<?= $version ?></title>
		<style><!--
		A:link {font-family: "verdana", sans-serif; text-decoration: none}
		A:visited {font-family: "verdana", sans-serif; text-decoration: none}
		A:active {font-family: "verdana", sans-serif; text-decoration: none}
		a:hover {font-family: "verdana", sans-serif; text-decoration: underline }
		body {font-family: "verdana", sans-serif}
		--></style>
		
	</head>
<body text="#FFFFFF" bgcolor="#515151" link="#C0C0C0" vlink="#C0C0C0" alink="#C0C0C0">
		<a href="<?= $myname ?>"><img src="bubbleman.gif" border="0" width="197" height="218" alt="Bubbleman!" align="left"></a>
		<font face="verdana" size="2">
			<font size="6"><b>OliverWEB Update Script v<?= $version ?></b></font><br>
			Problems? Contact <a href="mailto:<?= $authormail ?>"><?= $author ?></a>
			<br><br>
			<form action="<?= $myname ?>" method="POST" enctype="multipart/form-data">
			<?php
				if(empty($_POST['mode'])){
					$files = getFiles($textdir, $extension); //get list of files
					?>
					Select file to update:<br>
					<select name="file">
					<?php
						foreach ($files as $file){
							$filename = substr($file, strlen($textdir),(-1*strlen($extension)));
							echo "<option value=\"$file\"";
							if($filename == $default)
								echo " SELECTED";
							echo ">$filename</option>\n";
						}
					?>
				</select>
				<input type="hidden" name="mode" value="edit">
				<br><br><input type="submit" value="Edit">
			<?php 
				}
				elseif($_POST['mode'] == "edit"){
					$file = $_POST['file'];
					?>
					<h3><?= $file ?>:</h3>					
					<?php
						$fp = fopen($file, "r");
						$contents = fread ($fp, filesize ($file));
						echo "<textarea name=\"content\" rows=\"20\" cols=\"80\">$contents</textarea>";
						fclose($fp);
					?>
					<input type="hidden" name="file" value="<?= $file ?>">
					<input type="hidden" name="mode" value="doedit">
					<br><br><input type="submit" value="Make Changes">
					<?php
				}
				elseif($_POST['mode'] == "doedit"){
					$file = $_POST['file'];
					$fp = fopen($file, "w");
					$content = stripslashes($_POST['content']);
					fwrite($fp, $content);
					fclose($fp);
					echo "$file has been updated!";
					?>
					<br><br><a href="<?= $myname ?>">Back to Update Main</a>
					<br><br>Updated file:<br>
					<?php
					echo "<textarea rows=\"20\" cols=\"80\">$content</textarea>";
				}
				elseif($_POST['mode'] == "update"){
					$file = "$textdir$updatefile$extension";
					?>
					<h3><?= $file ?>:</h3>					
					<?php
						$fp = fopen($file, "r");
						$contents = fread ($fp, filesize ($file));
						echo "New Picture of the Moment:<br><input type=\"file\" name=\"pic\"> <input type=\"submit\" value=\"Make Update\"><br><br>";
						echo "<textarea name=\"content\" rows=\"20\" cols=\"80\">$contents</textarea>";
						fclose($fp);
					?>
					<input type="hidden" name="mode" value="doupdate">
					<?php
				}
				elseif($_POST['mode'] == "doupdate"){
					$file = "$textdir$updatefile$extension";
					$imgext = substr($_FILES['pic']['name'], -4);
					$image = "$textdir$updatefile$imgext";
					if($imgext != ".jpg" && $imgext != "" && $imgext != ".png"){
						echo "Must give me .jpg or .png or die!<br><br>";
					}
					else{
						$fp = fopen("$textdir$counterfile", "r");
						$contents = fread($fp, filesize($file));
						fclose($fp);
						$contents++;
						if(rename($file, "$textdir$contents$extension")){
							echo "$file renamed to $textdir$contents$extension<br><br>";
						}
						$fp = fopen("$textdir$imgcounterfile", "r");
						$imgcounter = fread($fp, filesize($file));
						fclose($fp);
						$imgcounter++;
						$big = "big";
						$oldimgext = ".jpg";
						if(!file_exists("$textdir$updatefile$oldimgext")){
							$oldimgext = ".png";
						}		
						if($imgext == ".jpg" || $imgext == ".png"){				
							if($contents == $imgcounter){
								rename("$textdir$updatefile$oldimgext", "$textdir$contents$oldimgext");
								rename("$textdir$updatefile$big$oldimgext", "$textdir$contents$big$oldimgext");
							}
							if($imgext == ".jpg"){
								$src_img = imagecreatefromjpeg($_FILES['pic']['tmp_name']);
							}else{
								$src_img = imagecreatefrompng($_FILES['pic']['tmp_name']);
							}
							$src_size = getimagesize($_FILES['pic']['tmp_name']);
							$dst_img = imagecreate(186,162);
							imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, 186, 162, $src_size[0], $src_size[1]);
							if($imgext == ".jpg"){
								imagejpeg($dst_img, "$textdir$updatefile$imgext", 85);
							}else{
								imagepng($dst_img, "$textdir$updatefile$imgext");
							}
							imagedestroy($src_img);
							imagedestroy($dst_img); 
							copy($_FILES['pic']['tmp_name'], "$textdir$updatefile$big$imgext");
							$fp = fopen("$textdir$imgcounterfile", "w");
							fwrite($fp, $contents);
							fclose($fp);							
							echo "Image stuff done. =)<br><br>";
							echo "$textdir$imgcounterfile ($contents)<br><br>";
							
							$fp = fopen($momentfile, "w");
							$content = "<a href=\"http://www.oliverweb.com/02/textboxbig$imgext\" target=\"_blank\"><img src=\"http://www.oliverweb.com/02/textbox$imgext\" width=\"186\" height=\"162\" border=0></a>";
							fwrite($fp, $content);
							fclose($fp);
						}
						else{
							//echo "hiyo $contents $imgcounter";
							if($contents == $imgcounter){
								copy("$textdir$updatefile$oldimgext", "$textdir$contents$oldimgext");
								copy("$textdir$updatefile$big$oldimgext", "$textdir$contents$big$oldimgext");
							}
							$imgcounter--;
							echo "New image not uploaded<br><br>";
							echo "$textdir$imgcounterfile ($imgcounter)<br><br>";
						}
						
						$fp = fopen($file, "w");
						$content = stripslashes($_POST['content']);
						fwrite($fp, $content);
						fclose($fp);
						echo "$file updated";
						$fp = fopen("$textdir$counterfile", "w");
						fwrite($fp, $contents);
						fclose($fp);
						echo "$textdir$counterfile updated ($contents)<br><br>";
					}
				}
			?>
			</form>
			<?php
				if(empty($_POST['mode'])){
				?>
					<form action="<?= $myname ?>" method="POST">
					<br><br><input type="hidden" name="mode" value="update">
					<input type="submit" value = "Update Pic/Box">
				<?php
						}
			?>

			<br><br><center><font size="1">Copyright &copy; 2002 <?= $author ?><br>All Rights Reserved.</center>
		</font>
	</body>
</html>

<?php

	//returns an array of all files with $ext extension in $dir in format:
	// $dir/*$ext
	// also parses subdirectories of $dir
	function getFiles($dir, $ext){
		$array = array();
		if($handle = opendir($dir)){
			while(false !== ($file = readdir($handle))){
				if($file != "." && $file != ".."){
					if(is_dir("$dir$file")){
						$array = array_merge($array, getFiles("$dir$file/", $ext));
					}
					elseif(substr($file, (-1 * strlen($ext))) == $ext)
						$array[sizeof($array)] = "$dir$file";
					}
			}
			closedir($handle);
		}
		else{
			echo "ERROR: Couldn't open $textdir";
		}
		return $array;
	}
	
?>
