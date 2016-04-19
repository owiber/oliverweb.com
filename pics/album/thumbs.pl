#!/usr/bin/perl

#$dirname = shift();
#if ($dirname eq '') {
	#$dirname = ".";
#}
#print "Dirname = $dirname\n";
open(CONFIG,"config.inc");

while (<CONFIG>) {
	if (/\$thumb_size.*= "?(.*?)"*;$/) {
		#print "Thumb Size = $1\n";
		$thumbsize = $1;
	}
	if (/\$thumb_border.*= "?(.*?)"*;$/) {
		#print "Border Size = $1\n";
		$border = $1;
	}
	if (/\$convert.*= "?(.*?)"*;$/) {
		#print "Convert Prog = $1\n";
		$convert = $1;
	}
	if (/\$pix_base.*= "?(.*?)"*;$/) {
		#print "Pix Base = $1\n";
		$pixbase = $1;
	}
	if (/\$dyn_base.*= "?(.*?)"*;$/) {
		#print "Dyn Base = $1\n";
		$dynbase = $1;
	}
}
close(CONFIG);

open(FLIST,"find \"$pixbase\" -iname \"*.jpg\" | grep -iv scaled|");

while (<FLIST>) {
	chomp;
	$sourcefile = $_;
	print "$sourcefile...";
	$sourcefile =~ /$pixbase\/(.*\/)(.*)\.jpg/i;
	$destfile = $dynbase . "/" . $1 . $2 . "__scaled_$thumbsize.jpg";
	@paths = split("/",$1);

	for ($i = 0; $i < @paths ; $i++) {
		$currentpath = $dynbase;
		for ($j = 0; $j <= $i; $j++) {
			$currentpath .= "/";
			$currentpath .= $paths[$j];
		}
		#print "Testing $currentpath...";
		if (-d $currentpath) {
			#print "Exists\n";
		} else {
			#print "baaaa\n";
			mkdir($currentpath);
		}
	}

	#print("$convert -antialias -quality 80 -sample $thumbsize -bordercolor black -border 2 \"$sourcefile\" \"$destfile\"");
	if (! -e $destfile) {
		system("$convert -antialias -quality 80 -sample $thumbsize -bordercolor black -border $border \"$sourcefile\" \"$destfile\"");
	}
	print " done\n";
}
