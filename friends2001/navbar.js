function MakeArray()
        {
        this.length = MakeArray.arguments.length
        for (var i = 0; i < this.length; i++)
        this[i+1] = MakeArray.arguments[i]
        }

var siteopt = new MakeArray(
			"Jump to...",
			"OliverWEB",
			"Oliver",
			"Olivia",
			"Minix",
			"Sarah",
			"Nancy",
			"Christine",
			"Kenny",
			"Catherine",
			"Jennifer",
			"Brook"
			);

var url = new MakeArray("",
			"http://www.oliverweb.com/main/",
			"oliver.shtml",
			"olivia.shtml",
			"minix.shtml",
			"sarah.shtml",
			"nancy.shtml",
			"christine.shtml",
			"kenny.shtml",
			"catherine.shtml",
			"jennifer.shtml",
			"brook.shtml"
     		);

function jumpPage(form) {
        i = form.SelectMenu.selectedIndex;
        if (i == 0) return;
        window.location.href = url[i+1];
}
