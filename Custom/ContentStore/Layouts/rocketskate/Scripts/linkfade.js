
// Link fader for IE, not Opera pretending to be IE
if (navigator.appName.indexOf("Microsoft") != -1 && navigator.userAgent.indexOf("Opera") == -1 && navigator.appVersion.indexOf("Mac") == -1) {
	function mixColor() {
		if (el.tagName != "A") return;

		// Recombine color with white based on ratio
		var r = 255 - ((255-r1) * ratio);
		var g = 255 - ((255-g1) * ratio);
		var b = 255 - ((255-b1) * ratio);

		el.style.color = (r << 16 | g << 8 | b);

		if (ratio < 1 && ratio > 0) {
			window.setTimeout(mixColor, 30);
			ratio += 0.1;
		}
	}

	document.onmouseover = function() {
		el = window.event.srcElement;
		if (el.tagName == "A") {
			// Get anchor element's color
			r1 = g1 = b1 = 0x33;
			if(el.className != "") { 
				return;
			} else if (document.styleSheets.length > 0) {
				/*var css = document.styleSheets[0].rules;
				for (var i in css) {
					if (css[i].selectorText == ".A_hover") {
						// Split color
						r1 = Number("0x" + col.substr(1, 2));
						g1 = Number("0x" + col.substr(3, 2));
						b1 = Number("0x" + col.substr(5, 2));
						break;
					}
				}*/
			}
			ratio = 0.1;
			mixColor();
		}
	};

	document.onmouseout = function() {
		if (el.className != "") return;
		if (el.tagName == "A") {
			if (el.parentNode.tagName != "H1") {
				window.clearTimeout();
				ratio = 0;
				el.style.color = '';
			}
		}
	};
}