<?
## ImageTool can either be "gd" or "im" (ImageMagick) 
## ImageMagick does better quality scaling but may be 
## slower on some machines.  Take your pick
$version		= "2.0pre1";
$ImageTool		= "gd";
$convert		= "convert";
$pix_base		= "albums";
$dyn_base		= "generated";
## Define $sitehome if you wish to have a link to you main site included in the navigation
$sitehome		= "http://www.oliverweb.com";
## EXIF Support requires PHP4 with exif support compiled
$show_exif		= 0;
$items_per_page	= 20;
$display_cols	= 4;
$default_size	= 800;
$thumb_size		= 90; 
$thumb_border	= 2;
$viewsizes		= array(512,640,800,1024,1280,"Original");
?>
