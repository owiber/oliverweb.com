<?
include("config.inc");
include("functions.inc");

$version = "2.0.2";

print "<HTML>\n";
print "<HEAD>\n";
if ($mode == "album" or $mode == "view") {
	print "<title>PHPix: $album</title>\n";
} else {
	print "<title>PHPix: List of Albums</title>\n";
}
print "</HEAD>\n";
?>
<?php require("../../ssi/top.shtml"); ?>
<?
print "<a name=albumtop>";
print "<div align=center>\n";
print "<TABLE><TR><TD><TABLE width=100%>";
print "<tr>";
print "<td colspan=$display_cols>";

preg_match("/(.*\/).*$/",$SCRIPT_FILENAME,$matches);

$realbase = $matches[1] . $pix_base;
$dynbase = $matches[1] . $dyn_base;

$imgbase = dirname($SCRIPT_NAME);
($imgbase == '/') and $imgbase = '';

if ($dispsize == '') $dispsize = $default_size;
if ($start == '') $start = 0;
if (preg_match("/\.\./",$pic) or preg_match("/\.\./",$album)) {
	print "Please refrain from trying to access unauthorized files in this manner.<br>";
	print "Have a nice day.";
} else if ($mode == 'home' or $mode == '') {
	print "<h2>Albums:</h2>\n";
	$handle=opendir($pix_base);
	while (($file = readdir($handle)) != '') {
		if ($file != "." && $file != "..") { 
			$alblist[] = $file;
		}
	}
	is_array($alblist) and sort($alblist);
	for ($i = 0 ; $i < sizeof($alblist) ; $i++) {
		print "<A HREF=?mode=album&album=" . urlencode($alblist[$i]) . "&dispsize=$dispsize#albumtop>";
		print "<b>$alblist[$i]</b>\n";
		print "</a><br>\n";
	}
	closedir($handle); 

## Album View (Thumbnails)
} else if ($mode == 'album') {
	NavBar("album",$album,$start);
	print "</td></tr>\n";
	set_time_limit(0);
	$handle=opendir("$pix_base/$album");
	$colcounter = 0;
	
	while (($file = readdir($handle)) != '') {
		if (preg_match("/.*\.jpg/i",$file) 
			and !preg_match("/__scaled_/",$file) 
			) 
			{ 
			$filelist[] = $file;
		}
	}
	rewinddir($handle);
	while (($file = readdir($handle)) != '') {
		if (is_dir($realbase . "/$album/" . $file)
				and $file != "."
				and $file != ".."
				)
			{ 
				$dirlist[] = $file;
			}
	}
	is_array($dirlist) and sort($dirlist);
	is_array($filelist) and sort($filelist);
	$totalfiles = sizeof($filelist);
	print "<tr >";
	for ($i = 0 ; $i < sizeof($dirlist) ; $i++) {
		$dirname = $dirlist[$i];
		if ($colcounter == $display_cols) {
			print "</tr><tr >\n";
			$colcounter = 0;
		}
		print "<td align=center><A HREF=?mode=album&album=".urlencode("$album/$dirname") . "#albumtop>";
		print "<b>$dirname</b>\n";
		print "</a><br><IMG width=$thumb_size height=1 src=\"" . $imgbase . "/blank.gif\"></td>\n";
		$colcounter++;
	}

	for ($x = $start ; $x < $start+$items_per_page and $filelist[$x] ; $x++) {
		$file = $filelist[$x];
		if ($colcounter == $display_cols) {
			print "</tr><tr >\n";
			$colcounter = 0;
		}
		if (is_dir($realbase . "/$album/" . $file)) {
			print "<td><A HREF=?mode=album&album=$album/$file#albumtop>";
			print "<b>$file</b>\n";
			print "</a></td>\n";
			$colcounter++;
		} else if (preg_match("/.*\.jpg/i",$file)) {
			$sourcefile = "$realbase/$album/$file";
			preg_match("/(.*)\.jpg/i",$file,$parts);
			$thumbpic = "$parts[1]__scaled_$thumb_size.jpg";
			$thumbfile = "$dynbase/$album/$thumbpic";
			checkDir("$dynbase/$album");
			#if (!file_exists("$dynbase/$album")) {
				#mkdir("$dynbase/$album",0755);
			#}
			#if (!file_exists("$thumbfile")) {
				createImage($thumb_size,$sourcefile,$thumbfile,$thumb_border);
				chmod ("$thumbfile", 0666);
				#chown ("$thumbfile", "oliverts");
			#}
			$thumbsize = GetImageSize($thumbfile);
			$imgsrc = preg_replace('/ /',"%20","/$dyn_base/$album/$thumbpic");
			print "<td align=center><A HREF=?mode=view&album=".urlencode($album)."&pic=".urlencode($file)."&dispsize=$dispsize&start=$start#albumtop>";
			print "<IMG hspace=3 vspace=3 $thumbsize[3] BORDER=0 SRC=\"" . $imgbase . "$imgsrc\"><br><font size=2>$file</font>\n";
			print "</a></td>\n";
			$colcounter++;
		}
	}
	for ($i = 0 ; $i < ($display_cols - $colcounter) ; $i++) {
		print "<td><IMG width=$thumb_size height=1 src=\"" . $imgbase . "/blank.gif\"></td>\n";
	}
	print "</tr>\n";
	print "<tr ><td colspan=$display_cols><table border=0 width=100%><tr><td>";
	print "<A HREF=?mode=album&album=" . urlencode($album) . "&start=0#albumtop>Start</a> | ";
	if ($start > 0) {
		print "<A HREF=?mode=album&album=" . urlencode($album) . "&start=";
		if ($start-$items_per_page < 0) {
			print "0";
		} else {
			print ($start-$items_per_page);
		}
		print "#albumtop>Prev $items_per_page</a> | ";
	} else {
		print "Prev $items_per_page | ";
	}
	if ($totalfiles > $x) {
		print "<A HREF=?mode=album&album=" . urlencode($album) . "&start=$x#albumtop>Next $items_per_page</a> | ";
	} else {
		print "Next $items_per_page | ";
	}
	print "</td><td align=right>";
	if ($totalfiles > 0) {
		print "Images " . ($start+1) . " to $x of $totalfiles<br>\n";
	} else {
		print "&nbsp;";
	}
	print "</td></tr></table></td></tr>";
} else if ($mode == 'view') {
	$handle=opendir("$pix_base/$album");
	while (($file = readdir($handle)) != '') {
		if (preg_match("/.*\.jpg/i",$file) 
			and !preg_match("/__scaled_/",$file) 
			) 
			{ 
			$filelist[] = $file;
		}
	}
	sort($filelist);
	$picindex = -1;
	for($i=0 ; $i<sizeof($filelist) ; $i++) {
		if ($filelist[$i] == $pic) {
			$picindex = $i;
		}
	}
	$start = 0;
	for($i = 0 ; $i <= $picindex ; $i++) {
		if (($i) % $items_per_page == 0) {
			$start = $i;
		}
	}

	NavBar("view","$album/$pic",$start);
	print "</td></tr>\n";
	clearstatcache();
	$sourcefile = "$realbase/$album/$pic";
	$dlinksize = $dispsize;
	$srcsize = GetImageSize("$sourcefile");
	if ($dispsize >= $srcsize[0]) {
		$dispsize = "Original";
	}
	if ($dispsize == 'Original') {
		$viewfile = $sourcefile;
		$viewpic = $pic;
		$imgsrc = preg_replace('/ /',"%20","/$pix_base/$album/$viewpic");
	} else {
		preg_match("/(.*)\.jpg/i",$pic,$parts);
		$viewpic = "$parts[1]__scaled_$dispsize.jpg";
		$viewfile = "$dynbase/$album/$viewpic";
		$imgsrc = preg_replace('/ /',"%20","/$dyn_base/$album/$viewpic");
	}
	#if (!file_exists($viewfile)) {
		createImage($dispsize,$sourcefile,$viewfile);
	#}
	$viewsize = GetImageSize("$viewfile");
	print "<tr ><td colspan=$display_cols>";
	print "<IMG hspace=4 vspace=4 $viewsize[3] SRC=\"" . $imgbase . "$imgsrc\">";
	print "</td></tr>";
	
	print "<tr ><td colspan=$display_cols><table border=0 width=100%><tr><td valign=top>\n";
	if ($filelist[($picindex-1)] != '') {
		$prevfile = $filelist[($picindex-1)];
		$sourcefile = "$realbase/$album/$prevfile";
		preg_match("/(.*)\.jpg/i",$prevfile,$parts);
			checkDir("$dynbase/$album");
		#if (!file_exists("$dynbase/$album")) {
				#mkdir("$dynbase/$album",0755);
		#}
		$thumbpic = "$parts[1]__scaled_$thumb_size.jpg";
		$thumbfile = "$dynbase/$album/$thumbpic";
		#if (!file_exists("$thumbfile")) {
			createImage($thumb_size,$sourcefile,$thumbfile,$thumb_border);
		#}
		$thumbsize = GetImageSize($thumbfile);
		$imgsrc = preg_replace('/ /',"%20","/$dyn_base/$album/$thumbpic");
		print "<A HREF=?mode=view&album=".urlencode($album)."&pic=".urlencode($prevfile)."&dispsize=$dlinksize&start=$start#albumtop>";
		print "&lt;-Prev<br><IMG align=center hspace=2 vspace=2 $thumbsize[3] BORDER=0 SRC=\"" . $imgbase . "$imgsrc\"></a>";
	} else {
		print "<td><IMG width=$thumb_size height=1 src=\"" . $imgbase . "/blank.gif\"></td>\n";
	}
	print "</td>\n";
	print "<td valign=top>";
	#print "<tr ><td colspan=$display_cols>\n";
	print "<FORM METHOD=GET>\n";
	print "<SELECT onChange='submit();' name=dispsize>\n";
	while (list($foo,$dsize) = each($viewsizes)) {
		if ($dsize < $srcsize[0] or $dsize == 'Original') {
			print "<OPTION VALUE=$dsize";
			$dsize == $dispsize and print " SELECTED";
			if ($dsize == "Original") {
				print ">Original\n";
			} else {
				print ">$dsize";
				print "x";
				print ceil($dsize * $srcsize[1] / $srcsize[0]);
				print "\n";
			}
		}
	}
	print "</select><input type=hidden name=mode value=view>\n";
	print "<input type=hidden name=album value=\"$album\">\n";
	print "<input type=hidden name=pic value=\"$pic\">\n";
	print "<input type=submit value=\"View\">";
	print "<br>(Original Photo Size = $srcsize[0]x$srcsize[1])</FORM>\n";
	#print "</td></tr>\n";
	print "</td>";
############
	print "<td valign=top align=right>\n";
	if ($filelist[($picindex+1)] != '') {
		$nextfile = $filelist[($picindex+1)];
		$sourcefile = "$realbase/$album/$nextfile";
		preg_match("/(.*)\.jpg/i",$nextfile,$parts);
			checkDir("$dynbase/$album");
		#if (!file_exists("$dynbase/$album")) {
				#mkdir("$dynbase/$album",0755);
		#}
		$thumbpic = "$parts[1]__scaled_$thumb_size.jpg";
		$thumbfile = "$dynbase/$album/$thumbpic";
		#if (!file_exists("$thumbfile")) {
			createImage($thumb_size,$sourcefile,$thumbfile,$thumb_border);
		#}
		$thumbsize = GetImageSize($thumbfile);
		$imgsrc = preg_replace('/ /',"%20","/$dyn_base/$album/$thumbpic");
		print "<A HREF=?mode=view&album=".urlencode($album)."&pic=".urlencode($nextfile)."&dispsize=$dlinksize&start=$start#albumtop>";
		print "Next -&gt;<br>";
		print "<IMG align=center hspace=2 vspace=2 $thumbsize[3] BORDER=0 SRC=\"" . $imgbase . "$imgsrc\"></a>";
	} else {
		print "<td><IMG width=$thumb_size height=1 src=\"" . $imgbase . "/blank.gif\"></td>\n";
	}
	
	$totalfiles = sizeof($filelist);
	#print "<A HREF=convert.phtml?effect=charcoal&file=" . urlencode($viewfile) . ">Charcoal</a>\n";
	#print " | <A HREF=convert.phtml?effect=paint&file=" . urlencode($viewfile) . ">Paint</a>\n";
	#print " | <A HREF=convert.phtml?effect=shade&file=" . urlencode($viewfile) . ">Shade</a>\n";
	#print "<br>\n";
	print "</td></tr></table></td></tr>\n";
	#if ($show_exif) {
		#$exif = read_exif_data($sourcefile);
		#while(list($k,$v)=each($exif)) {
			#echo "$k: $v<br>\n";
		#}
	#}

}
print "</table></td></tr></table>\n";
print "<font size=-1>Generated by <A TARGET=_blank HREF=http://phpix.org/>PHPix $version</a></font>";
print "</div>\n";
require("../../ssi/bottom.shtml");

?>
