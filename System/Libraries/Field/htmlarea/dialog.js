// htmlArea v3.0 - Copyright (c) 2002, 2003 interactivetools.com, inc.
// This copyright notice MUST stay intact for use (see license.txt).
//
// Portions (c) dynarch.com, 2003
//
// A free WYSIWYG editor replacement for <textarea> fields.
// For full source code and docs, visit http://www.interactivetools.com/
//
// Version 3.0 developed by Mihai Bazon.
//   http://dynarch.com/mishoo
//
// $Id: dialog.js,v 1.3 2003/10/28 19:57:31 mishoo Exp $

// Though "Dialog" looks like an object, it isn't really an object.  Instead
// it's just namespace for protecting global symbols.

function Dialog(url, action, init) {
	if (typeof init == "undefined") {
		init = window;	// pass this window object by default
	}
	Dialog._geckoOpenModal(url, action, init);
};

Dialog._parentEvent = function(ev) {
	if (Dialog._modal && !Dialog._modal.closed) {
		Dialog._modal.focus();
		HTMLArea._stopEvent(ev);
	}
};

// should be a function, the return handler of the currently opened dialog.
Dialog._return = null;

// constant, the currently opened dialog
Dialog._modal = null;

// the dialog will read it's args from this variable
Dialog._arguments = null;

// Generic popup_opener
function popup_open(url,name,title,width,height) {
	window_manager = null;
	if (parent.popup_windows_supported) window_manager = parent;
	if (parent.parent.popup_windows_supported) window_manager = parent.parent;
	if (parent.parent.parent.popup_windows_supported) window_manager = parent.parent.parent;
	
	if (window_manager) {
		result = window_manager.popup_window_open(window, url, name, title, width, height, true);
	} else {
		result = window.open(url, name,"toolbar=no,menubar=no,personalbar=no,width="+width+",height="+height+",scrollbars=no,resizable=yes");
	}

	return result;
}

Dialog._geckoOpenModal = function(url, action, init) {
		
	var dlg = popup_open(url,"hadialog","",10,10);

/*	var dlg = parent.parent.popup_window_open(window, url, "hadialog",
			      "toolbar=no,menubar=no,personalbar=no,width=10,height=10," +
			      "scrollbars=no,resizable=yes",500,500);*/
/*	var dlg = window.open(url, "hadialog",
			      "toolbar=no,menubar=no,personalbar=no,width=10,height=10," +
			      "scrollbars=no,resizable=yes");*/
	Dialog._modal = dlg;
	Dialog._arguments = init;

	// capture some window's events
	function capwin(w) {
		HTMLArea._addEvent(w, "click", Dialog._parentEvent);
		HTMLArea._addEvent(w, "mousedown", Dialog._parentEvent);
		HTMLArea._addEvent(w, "focus", Dialog._parentEvent);
	};
	// release the captured events
	function relwin(w) {
		HTMLArea._removeEvent(w, "click", Dialog._parentEvent);
		HTMLArea._removeEvent(w, "mousedown", Dialog._parentEvent);
		HTMLArea._removeEvent(w, "focus", Dialog._parentEvent);
	};
	capwin(window);
	
	// capture other frames
	for (var i = 0; i < window.frames.length; capwin(window.frames[i++]));
	
	// make up a function to be called when the Dialog ends.
	Dialog._return = function (val) {
		//alert("dialog class: val : "+ val + " action: " + action);
		if (val && action) {
			action(val);
		}
		relwin(window);
		// capture other frames
		for (var i = 0; i < window.frames.length; relwin(window.frames[i++]));
		Dialog._modal = null;
	};
};
