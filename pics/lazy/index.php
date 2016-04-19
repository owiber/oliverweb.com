<!--

DIRECTORYLISTER v0.6a

©2003-2004 Gabe Dunne
gdunne@quilime.com

You can use this without license, but I'd
love to see it in use. It has many
upgrades to be made. Download the
source here:

http://quilime.com/?p=opensourcePHP

-->
<?
/*
	VERSION HISTORY
	
	Updates to version 0.6
	-----------------------------------------------------------------------------------------
	Minor security fixes
	Ability to display full local file system
	Recognize files with more than one . in them. (test.test.test.test.txt, for example) (Kev@virtualkev.com)
	Ability to remove excess path from the displayed path. e.g /var/www/html/mystuff can now be displayed as just /mystuff (Kev@virtualkev.com)	
	
	Added an Icon Mod available for download at: http://quilime.com/?p=opensourcePHP
	-----------------------------------------------------------------------------------------
	
	Updates to version 0.5
	-----------------------------------------------------------------------------------------
 	Global Exceptions (Exclude any file or dir named something specific)
	Unique Exceptions (Exclude one single file/dir)
	Exception Classes (Exclude certain file formats)
	-----------------------------------------------------------------------------------------

	Updates that probably won't happen, but may be available in the future as a mod.
	-----------------------------------------------------------------------------------------
	File Uploading
	Password-protected directories
	Statistics .log file (download counts, directory views, list of IP's)
	-----------------------------------------------------------------------------------------

	© 2003, 2004 gdunne @ http://quilime.com
	
*/


// for adding mods, make sure the 'config' line below is at line 50.
// config ////////////////////////
								//
								//
                            	// Define exception files and folders.
								//
								//	Examples:
    							//	"./somedir/index.php"	    // unique exception (hides the single file: ./somedir/index.php )
    							//	"./some/other/folder/" 		// unique exception (hides the single folder: ./some/other/folder/ )
    							//	".htaccess"    				// global exception (hides all .htaccess's)
    							//	"somedir/" 					// global exception (hides all somedir/'s)
								//  
$exceptions = array(			//
    ".htaccess",
	".htpasswd",
	".".$_SERVER['PHP_SELF'],	// hiding this file
	"/"							// hide / for good measure
    );
								// define a specific file extension that you always want invisible
								//
								// Examples:
								// "php"	// excludes all *.php files
								// "desc"	// excludes all *.desc files
								//
								//
$exceptionClasses = array(
	//"php",
	//"phtml"
	);

$calcExceptions = 	FALSE; 		// Calculate file/folder exceptions into the total size? (FALSE is default)
$rowNumbers = 		TRUE;		// Show row numbers?	(TRUE is default)						

$displayFullRoot = 	FALSE;		// Display full local file system path in title? (FALSE is default)
$rootdir='/kunden/homepages/37/d87307754/htdocs/oliverweb.com';			// use $rootdir to hide the local file path so that that displayed path is a dir name instead of ./
								// $rootdir is ignored if $displayFullRoot is set to TRUE.
								//
								// Example (unix)
								// Files are stored in /var/www/html/mystuff, this script operates from /mystuff as ./
								// the page displays /mystuff instead of ./ when $rootdir="/var/www/html";
								//
								// Example (win)
								// Files are stored in X:\Apache2\htdocs, this script operates from /htdocs as ./
								// the page displays /htdocs instead of ./ when $rootdir="X:\Apache2";
//////////////////////////////////

// path error handling //
$root = "./"; // script root dir reference
$path = (isset($_GET['path'])) ? $_GET['path'] : $root; // get path from 'get'

// security modding
if ($path == '/') $path = $root; // hijack the / to stop real root access, need to hijack everything else somehow.
if(!is_dir(".".str_replace('.','',$path))) $path = $root; // hijack everything else that isnt below the cwd.

// if someone tries to go up a parent directory (..), refer to root instead
$dirPieces = explode("/", $path); 
for ($i=0; $i<sizeof($dirPieces); $i++) { if ($dirPieces[$i] == ".." || $dirPieces[$i] == "...") { $path = $root; break; } }

if (substr($path, -1) != "/") $path .= "/"; // if path does not have a trailing slash, add one
$path = preg_replace("/\/\//", "/", $path); // if path has two slashes in succession, eliminate one


// directory recursion //

// Open the folder

$dir_handle = @opendir($path);
if(!$dir_handle) { $dir_handle = @opendir($root); $path=$root; } // if folder isn't there, go back to root

// get file extension
function GetExt($ext) {
	$ext = strtolower($ext);
	switch($ext) {
	case "avi";  $type = "Video File"; break;
	case "bat";  $type = "Batch File"; break;
	case "css";  $type = "Cascading Style Sheet"; break;
	case "exe";  $type = "Executable"; break;
	case "fla";  $type = "Flash File"; break;
	case "swf";  $type = "SWF Flash File"; break;
	case "gif";  $type = "GIF Image"; break;
	case "dir";  $type = "Director File"; break;
	case "html"; $type = "HTML File"; break;
	case "htm";  $type = "HTM File"; break;
	case "inc";	 $type = "Include File"; break;
	case "jpg";  $type = "JPEG Image"; break;
	case "mov";  $type = "Quicktime File"; break;
	case "mp3";  $type = "MP3 File"; break;
	case "mp4";  $type = "MPEG 4"; break;
	case "msg";  $type = "Email Message"; break;
	case "pdf";  $type = "PDF Acrobat File"; break;
	case "psd";  $type = "Photoshop File"; break;
	case "php";  $type = "PHP File"; break;
	case "ppt";  $type = "PowerPoint Presentation"; break;
	case "txt";  $type = "Text Document"; break;
	case "wma";  $type = "Windows Media Audio"; break;
	case "xls";  $type = "Excel File"; break;
	case "zip";  $type = "Zip File"; break;
	case "ttf";  $type = "True Type Font"; break;
	case "sql";  $type = "SQL File"; break;
	case "rpm";  $type = "RedHat Package Manager File"; break;
	case "doc";  $type = "Word Document"; break;
	case "png";  $type = "PNG Image"; break;
	case "jpeg"; $type = "JPEG Image"; break;
	case "psp";  $type = "Paint Shop Pro Image"; break;
	case "obd";  $type = "Microsoft Binder"; break;
	case "nrg";  $type = "Nero Project"; break;
	case "iso";  $type = "ISO Image File"; break;
	case "bmp";  $type = "Windows Bit Map"; break;
	case "ini";  $type = "INI File"; break;
	case "inf";  $type = "INF File"; break;
	case "hlp";  $type = "Help File"; break;
	case "reg";  $type = "Registry File"; break;
	case "log";  $type = "Log File"; break;
	case "chm";  $type = "Compiled html"; break;
	default: $type = "Unknown Type";
	}
	return $type;
}

// format size
function formatSize($size) {
	$kb = 1024;        // Kilobyte
	$mb = 1024 * $kb;  // Megabyte
	$gb = 1024 * $mb;  // Gigabyte
	$tb = 1024 * $gb;  // Terabyte
	if($size==0) { 			return "(empty)"; }
	if($size < $kb) { 		return $size." Bytes"; }
	else if($size < $mb) { 	return round($size/$kb,2)." kb"; }
	else if($size < $gb) { 	return round($size/$mb,2)." mb"; }
	else if($size < $tb) { 	return round($size/$gb,2)." gb"; }
	else { 					return round($size/$tb,2)." tb"; }
}

// get amount of files, dirs, and formatted size
function getSize($dir) {
	global $exceptions, $exceptionClasses, $calcExceptions;

	$files = 0;
	$dirs = 0;
	$memory = 0;
	$hidden = 0;
	if ($dir_handle = @opendir($dir)) {
	   while ($file = readdir($dir_handle)) {
		   if($file != "." && $file != "..") {
		    $dir .= "/";
			$dir = preg_replace("/\/\//", "/", $dir); // error check: makes sure there are no double slashes
			if (is_dir($dir.$file)) { $dir.$file .= "/"; } // add a slash to directories



			   if(@is_dir($dir.$file)) {
				   if (!$calcExceptions) { if (in_array($dir, $exceptions) || in_array($file, $exceptions) || in_array($dir.$file, $exceptions)) continue; } // skip if exception
				   $sizeBuild = getSize($dir.$file);
				   $files +=  $sizeBuild[0];
				   $dirs +=  $sizeBuild[1];
				   $memory +=  $sizeBuild[2];
				   $dirs++;
			   } else {

				     if (!$calcExceptions) { $fileExt = ereg_replace("^.+\\.([^.]+)$", "\\1", $file); if (in_array($file, $exceptions)  || in_array($fileExt, $exceptionClasses) || in_array($dir.$file, $exceptions)) continue; } // skip if exception

				   $memory += @filesize($dir.$file);
				   $files++;
			   }
		   }
	   }
	closedir($dir_handle);
	}
	$size[0] = $files;
	$size[1] = $dirs;
	$size[2] = $memory;
	$size[3] = $hidden;
	return $size;
}


$i=0; // var used to increment $files array
$j=0; // var used to increment $dirs array
while ($file = readdir($dir_handle)) { // Loop through everything in the selected $path
	if($file != "." && $file != "..") {

	if (is_dir($path.$file)) { $path.$file .= "/"; } // add a slash to directories

	$fileExt = ereg_replace("^.+\\.([^.]+)$", "\\1", $file);

		if(in_array($fileExt, $exceptionClasses) || in_array($path.$file, $exceptions) || in_array($file, $exceptions) || in_array($path, $exceptions)) continue;

			if (is_dir($path.$file)) { // if file is a directory, add to the $dirs array, increment the counter, and continue
				$file = preg_replace("/\//", "", $file);
				$dirs[$j] = $file;
				$j++;
				continue;
			}

			$files[$i] = $file; // if file, add to the $files array and increment the counter
			$i++;
		}
}

closedir($dir_handle); // close directory

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<? echo $_SERVER['PHP_SELF']; ?>

<html>
<head>

<style type="text/css" media="screen" title="default">

/* text styling */
* { font-family: Arial, Helvetica, sans-serif; font-size: 11px; }

body { margin: 30px; }

/* container */
#main { margin: 20px 0px 20px 0px; }

h1 { font-family: Georgia, "Times New Roman", Times, serif; font-size: 18px; letter-spacing: 3px;
font-style:italic; margin: 0px 0px 20px 0px; display:inline; font-weight:normal; }

h2 { font-weight:normal; font-size: 11px; margin-left: 15px; display:inline; }

/* fields */
.filenumber { width:10px; padding: 0px 10px 0px 10px; border-left:1px solid #ddd; color:#777; text-align:center; }
.filename { }
.filesize { text-align:right; padding:0px 20px 0px 10px; }
.filetype { width:160px; }
.modified { width:100px; border-right:1px solid #ddd; padding: 0px 10px 0px 0px; }

/* file links */
a { color: #ee0000; text-decoration: none; }
a:hover { color: #440000; text-decoration: underline; }

/* folder link */
.folder a { font-weight:bold; }

/* parent folder link*/
.parentFolderRow a { color:#000; }

/* file table */
table { width:100%; }

/* rows */
.border { border-top: 1px solid #ddd; }
.folderRow { background-color:#eeeeee; }
.rowColor1 { background-color: #f9f9f9; }
.rowColor2 { }

/* row hover effect */
.rowHover { background-color: #e6e6e6; cursor: pointer; }

.monospace { font-family: "Lucida Console", Courier, Mono; font-size: 10px; }

</style>

</head>
<body>



<div id="main">
<?  if ($displayFullRoot) { $displaypath=getcwd(); } // if displayFullRoot is true, display... the full root
	else {
		if (realpath($rootdir))  { // if $rootdir exists, remove it from the full local path and just show the current dir
			if("$path" == './') $displaypath = str_replace($rootdir,'',getcwd()); // if we're in the root, just show the root dir
			else $displaypath=str_replace($rootdir,'',realpath($path)); // else show the subfolders
		} else {
			$displaypath = $path; // if $rootdir doesn't exist, show the literal path variable instead of anything fancy
		}
	}
?>

	<h1><?=$displaypath?></h1>
	<? $total = getSize($path); // total size of stuff ?><h2>(<?=$total[1]?> folders, <?=$total[0]?> files)</h2>

	<table border="0" cellspacing="0" cellpadding="0" width="100%">

    <!--time right now-->
	<tr>
		<!--empty columns-->
		<td colspan="4">&nbsp;</td>
		<!--current date/time-->
		<td style="color:red;height:20px;border-right:0px;" class="modified" title="Current Date, Time"><?=date("m/d/y, h:ia");?></td>
    </tr>

	<?
	//if in subdirectory, print a button row to the parent directory
	if ($path != $root) {
	?>

	<tr class="folderRow parentFolderRow" onmouseover="className='parentFolderRow folderRow rowHover'" onmouseout="className='folderRow parentFolderRow'" onclick="javascript:window.location='?path=<?=dirname($path)?>/'" title="Parent Folder">
		<!--file number-->
		<td class="filenumber monospace border">&laquo;</td>
		<!-- file name -->
		<td class="folder border"><a href="?path=<?=dirname($path)?>/">Parent Folder</a></td>
		<!-- file size -->
		<td class="filesize border">&nbsp;</td>
		<!-- file type -->
		<td class="filetype border">&nbsp;</td>
		<!-- file last modified -->
		<td class="modified border">&nbsp;</td>
	</tr>

	<?

	} //end if

	if (isset($dirs)) {

	    sort($dirs);

	    // echo directories
	    for ($i=0; $i < sizeof($dirs); $i++) {

		// total size of stuff
		$total = getSize($path.$dirs[$i]);

		?>

	    <tr class="folderRow" onmouseover="className='row rowHover'" onmouseout="className='folderRow'" onclick="javascript:window.location='?path=<?=$path.$dirs[$i]?>/'" title="Folder: <?=$dirs[$i]?>, <?=formatSize($total[2]);?>">
	        <!--file number-->
	        <td class="filenumber monospace border">&raquo;</td>
	        <!-- file name -->
	        <td class="folder border"><a href="?path=<?=$path.$dirs[$i]?>/"><?=$dirs[$i]?></a> (<?=$total[1]?> folders, <?=$total[0]?> files)</td>
	        <!-- file size -->
	        <td class="filesize border"><? echo formatSize($total[2]); ?></td>
	        <!-- file type -->
	        <td class="filetype border"><strong>File Folder</strong></td>
	        <!-- file last modified -->
	        <td class="modified border"><?=date("m/d/y, h:ia", filemtime($path.$dirs[$i]))?></td>
	    </tr>

	    <?

         } // end for
    } // end if

    if (isset($files)) {

		sort($files);

	    // echo files
	    for ($i=0; $i < sizeof($files); $i++){

	    // alternate color for table each time through the loop
	        if ($i%2) $alt = "1";
	        else $alt = "2";
	    ?>

	    <tr class="row rowColor<?=$alt?>" onmouseover="className='row rowHover'" onmouseout="className='row rowColor<?=$alt?>'" onclick="javascript:window.location='<?=$path.$files[$i]?>'" title="File: <?=$files[$i]?>, <?=number_format((filesize(trim($path.$files[$i]))/1024), 2)?> kb">
	        <!--file number-->
	        <td class="filenumber monospace border"><span <?=($rowNumbers?"":"style=\"visibility:hidden;\"")?>><? if (($i+1) < 10) { echo "0"; } echo $i+1; ?></span></td>
	        <!-- file name -->
	        <td class="filename border"><a href="<?=$path.$files[$i]?>"><?=$files[$i]?></a></td>
	        <!-- file size -->
	        <td class="filesize border"><? echo formatSize(filesize(trim($path.$files[$i]))); ?></td>
	        <!-- file type -->
	        <td class="filetype border"><?  $fileExt = explode('.', $files[$i]); echo GetExt(end($fileExt)); ?></td>
	        <!-- file last modified -->
	        <td class="modified border"><?=date("m/d/y, h:ia", filemtime($path.$files[$i]))?></td>
	    </tr>

	    <?
	     } // end for loop
	// end if
	}

	// if there are files or directories, put out a total. If not, then put an empty table
    if (isset($files) || isset($dirs)) {
    ?>

    <!--total-->
	<tr>
		<!--empty columns-->
		<td class="border" colspan="2">&nbsp;</td>
		<!--total size-->
		<td style="color:red;height:20px;border-right:0px;" title="Total" class="filesize border"><? $total = getSize($path); if (formatSize($total[2]) != "(empty)") {echo formatSize($total[2]);} //echo formatSize($totalSize); ?></td>
		<!--empty column-->
		<td class="border" colspan="2">&nbsp;</td>
    </tr>

    <?
	} else {
    ?>

    <!--bottom border-->
    <tr>
        <td class="border" colspan="5">&nbsp;</td>
    </tr>

	<?
	} // end else
	?>
    </table>

</div><!-- // end #main -->

</body>
</html>
