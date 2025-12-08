var FaceCounter = function(parent,link,type,num1,num2) {
	this.parent = parent;
	this.link = link;
	if (this.link) {
		this.parent.addEventListener("click",this.clickHandler.bind(this));
		this.parent.style.cursor = "pointer";
	}
	this.renderNumber(num1);
	var img;
	switch (type) {
		case "gems":
			img = this.renderSymbol("images/counter_gem.gif");
			img.style.margin = 0;
			img.style.paddingTop = 8;
			img = this.renderSymbol("images/counter_plus.png");
			img.style.margin = 0;
			this.renderNumber(num2);
			img = this.renderSymbol("images/counter_percent.png");
			break;
		case "estate":
			img = this.renderSymbol("images/counter_estate.gif");
			img.style.margin = 0;
			img.style.paddingTop = 6;
			break;
	}
}

clickHandler = function() {
	window.open(this.link,"_blank");
}

renderNumber = function(value) {
	var str = value.toString();
	var i,len = str.length;
	for (i = 0; i < len; i++) {
		this.renderSymbol("images/counter"+str[i]+".png");
	}	
}

renderSymbol = function(name) {
	var img = document.createElement("img");
	img.src = name;
	this.parent.appendChild(img);
	return img;
}