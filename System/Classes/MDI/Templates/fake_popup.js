// These wrapper functions should work whether we're using fake popups or just standard ones
function popup_is_fake() {
	if (parent.popup_windows_supported) {
		return true;	
	} else {
		return false;	
	}
}

function popup_opener() {
	if (popup_is_fake()) { 
		return parent.popup_opener;
	} else {
		return window.opener;
	}
}

function popup_close() {
	if (popup_is_fake()) { 
		parent.popup_window_close();		
	} else {
		window.close();
	}
}

function popup_resize_to(width,height) {
	if (popup_is_fake()) {
		parent.popup_window_resize(width,height);
	} else {
		window.resizeTo(width,height);	
	}
}

function popup_set_title(title) {
	if (popup_is_fake()) {
		parent.popup_window_settitle(title);
	} else {
		document.title = title;
	}
}