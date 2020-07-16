if(0 && ! HTMLArea.is_ie)
{
  // Creates the toolbar and appends it to the _htmlarea
  HTMLArea.prototype._createToolbar = function () {
    var editor = this;	// to access this in nested functions

    var toolbar = document.createElement("div");
    this._toolbar = toolbar;
    toolbar.className = "toolbar";
    toolbar.unselectable = "1";
    var line = null;
    var tb_objects = new Object();
    this._toolbarObjects = tb_objects;

    // creates a new line in the toolbar
    function newLine() {
      line = newGroup(true);// document.createElement("div");
      // toolbar.appendChild(line);
    }; // END of function: newLine

    function newGroup(line_break) {
      var tb_group = document.createElement('div');
      tb_group.style.cssFloat = 'left';

      if(line_break == true) {
        tb_group.style.clear = 'left';
        // HTMLArea._addClass(tb_group, 'clear_left');
      }

      toolbar.appendChild(tb_group);
      return tb_group;
    }

    // init first line
    newLine();

    // updates the state of a toolbar element.  This function is member of
    // a toolbar element object (unnamed objects created by createButton or
    // createSelect functions below).
    function setButtonStatus(id, newval) {
      var oldval = this[id];
      var el = this.element;
      if (oldval != newval) {
        switch (id) {
            case "enabled":
          if (newval) {
            HTMLArea._removeClass(el, "buttonDisabled");
            el.disabled = false;
          } else {
            HTMLArea._addClass(el, "buttonDisabled");
            el.disabled = true;
          }
          break;
            case "active":
          if (newval) {
            HTMLArea._addClass(el, "buttonPressed");
          } else {
            HTMLArea._removeClass(el, "buttonPressed");
          }
          break;
        }
        this[id] = newval;
      }
    }; // END of function: setButtonStatus

    // this function will handle creation of combo boxes.  Receives as
    // parameter the name of a button as defined in the toolBar config.
    // This function is called from createButton, above, if the given "txt"
    // doesn't match a button.
    function createSelect(txt) {
      var options = null;
      var el = null;
      var cmd = null;
      var customSelects = editor.config.customSelects;
      var context = null;
      switch (txt) {
          case "fontsize":
          case "fontname":
          case "formatblock":
        // the following line retrieves the correct
        // configuration option because the variable name
        // inside the Config object is named the same as the
        // button/select in the toolbar.  For instance, if txt
        // == "formatblock" we retrieve config.formatblock (or
        // a different way to write it in JS is
        // config["formatblock"].
        options = editor.config[txt];
        cmd = txt;
        break;
          default:
        // try to fetch it from the list of registered selects
        cmd = txt;
        var dropdown = customSelects[cmd];
        if (typeof dropdown != "undefined") {
          options = dropdown.options;
          context = dropdown.context;
        } else {
          alert("ERROR [createSelect]:\nCan't find the requested dropdown definition");
        }
        break;
      }
      if (options) {
        el = document.createElement("select");
        var obj = {
          name	: txt, // field name
          element : el,	// the UI element (SELECT)
          enabled : true, // is it enabled?
          text	: false, // enabled in text mode?
          cmd	: cmd, // command ID
          state	: setButtonStatus, // for changing state
          context : context
        };
        tb_objects[txt] = obj;
        for (var i in options) {
          var op = document.createElement("option");
          op.appendChild(document.createTextNode(i));
          op.value = options[i];
          el.appendChild(op);
        }
        HTMLArea._addEvent(el, "change", function () {
          editor._comboSelected(el, txt);
        });
      }
      return el;
    }; // END of function: createSelect

    // appends a new button to toolbar
    function createButton(txt) {
      // the element that will be created
      var el = null;
      var btn = null;
      switch (txt) {
          case "separator":
        el = document.createElement("div");
        el.className = "separator";
        break;
          case "space":
        el = document.createElement("div");
        el.className = "space";
        break;
          case "linebreak":
        newLine();
        return false;
          case "textindicator":
        el = document.createElement("div");
        el.appendChild(document.createTextNode("A"));
        el.className = "indicator";
        el.title = HTMLArea.I18N.tooltips.textindicator;
        var obj = {
          name	: txt, // the button name (i.e. 'bold')
          element : el, // the UI element (DIV)
          enabled : true, // is it enabled?
          active	: false, // is it pressed?
          text	: false, // enabled in text mode?
          cmd	: "textindicator", // the command ID
          state	: setButtonStatus // for changing state
        };
        tb_objects[txt] = obj;
        break;
          default:
        btn = editor.config.btnList[txt];
      }
      if (!el && btn) {
        el = document.createElement("div");
        el.title = btn[0];
        el.className = "button";
        // let's just pretend we have a button object, and
        // assign all the needed information to it.
        var obj = {
          name	: txt, // the button name (i.e. 'bold')
          element : el, // the UI element (DIV)
          enabled : true, // is it enabled?
          active	: false, // is it pressed?
          text	: btn[2], // enabled in text mode?
          cmd	: btn[3], // the command ID
          state	: setButtonStatus, // for changing state
          context : btn[4] || null // enabled in a certain context?
        };
        tb_objects[txt] = obj;
        // handlers to emulate nice flat toolbar buttons
        HTMLArea._addEvent(el, "mouseover", function () {
          if (obj.enabled) {
            HTMLArea._addClass(el, "buttonHover");
          }
        });
        HTMLArea._addEvent(el, "mouseout", function () {
          if (obj.enabled) with (HTMLArea) {
            _removeClass(el, "buttonHover");
            _removeClass(el, "buttonActive");
            (obj.active) && _addClass(el, "buttonPressed");
          }
        });
        HTMLArea._addEvent(el, "mousedown", function (ev) {
          if (obj.enabled) with (HTMLArea) {
            _addClass(el, "buttonActive");
            _removeClass(el, "buttonPressed");
            _stopEvent(is_ie ? window.event : ev);
          }
        });
        // when clicked, do the following:
        HTMLArea._addEvent(el, "click", function (ev) {
          if (obj.enabled) with (HTMLArea) {
            _removeClass(el, "buttonActive");
            _removeClass(el, "buttonHover");
            obj.cmd(editor, obj.name, obj);
            _stopEvent(is_ie ? window.event : ev);
          }
        });
        var img = document.createElement("img");
        img.src = btn[1];
        img.style.width = "18px";
        img.style.height = "18px";
        el.appendChild(img);
      } else if (!el) {
        el = createSelect(txt);
      }
      if (el) {
        el.style.cssFloat = 'left';
        line.appendChild(el);
        // var tb_cell = document.createElement("td");
        // tb_row.appendChild(tb_cell);
        // b_cell.appendChild(el);
      } else {
        alert("FIXME: Unknown toolbar item: " + txt);
      }
      return el;
    };

    var first = true;
    for (var i in this.config.toolbar) {
      if (!first) {
        // createButton("linebreak");
        newGroup();
      } else {
        first = false;
      }
      var group = this.config.toolbar[i];
      for (var j in group) {
        var code = group[j];
        if (/^([IT])\[(.*?)\]/.test(code)) {
          // special case, create text label
          var l7ed = RegExp.$1 == "I"; // localized?
          var label = RegExp.$2;
          if (l7ed) {
            label = HTMLArea.I18N.custom[label];
          }
          var labele = document.createElement("div");
          line.appendChild(labele);
          labele.className = "label";
          labele.innerHTML = label;
        } else {
          createButton(code);
        }
      }
    }

    var breaka = document.createElement('div');
    breaka.innerHTML = "&nbsp;"
    breaka.style.clear = 'both';
    toolbar.appendChild(breaka);
    this._htmlArea.appendChild(toolbar);
  };
}


HTMLArea.prototype._getSelectedAnchor = function(sel, range)
  {
		if(HTMLArea.is_ie) {
			// IE makes it easy to get at a "selected" image
			if(sel.type == 'Control' && range.item(0).tagName.toLowerCase() == 'a') {
				return range.item(0);
			}
			return null;
		} else {
			// Gecko makes it a bit harder, but it seems to always have the startContainer to be
			// the deepest ancestor ELEMENT (not text) of the selected image
			if(range.startContainer.nodeType == 1) { // 1 == ELEMENT
				if(range.startContainer.childNodes[range.startOffset].tagName &&
					range.startContainer.childNodes[range.startOffset].tagName.toLowerCase() == 'a')
				{
					return range.startContainer.childNodes[range.startOffset];
				}
			}
			return null;
		}
  }

HTMLArea.prototype._createLink2 = HTMLArea.prototype._createLink;

HTMLArea.prototype._createLink = function(link)
  {
    if(typeof this._gogo_createLink == 'undefined')
    {
      // this.execCommand("createlink", true);
      this._createLink2(link);
    }
    else
    {
      var sel = this._getSelection();
      var range = this._createRange(sel);
      this._gogo_createLink(this._getSelectedAnchor(sel, range));
    }
  }


if(HTMLArea.gogoImageAvailable) {

	// Called when the user clicks on "InsertImage" button
	HTMLArea.prototype._getSelectedImage = function(sel, range) {

		function dump(o) {
			var s = '';
			for (var prop in o) {
				s += prop + ' = ' + o[prop] + '\n';
			}
			document.getElementById('dbg').innerHTML = '<pre>' + s + '</pre>';
			//alert(s);

		}

		if(HTMLArea.is_ie) {
			// IE makes it easy to get at a "selected" image
			if(sel.type == 'Control' && range.item(0).tagName.toLowerCase() == 'img') {
				return range.item(0);
			}
			return null;
		} else {
			// Gecko makes it a bit harder, but it seems to always have the startContainer to be
			// the deepest ancestor ELEMENT (not text) of the selected image
			if(range.startContainer.nodeType == 1) { // 1 == ELEMENT
				if(range.startContainer.childNodes[range.startOffset].tagName &&
					range.startContainer.childNodes[range.startOffset].tagName.toLowerCase() == 'img')
				{
					return range.startContainer.childNodes[range.startOffset];
				}
			}
			return null;
		}
	}

	HTMLArea.prototype._insertImage = function() {
		var sel = this._getSelection();
		var range = this._createRange(sel);

		if(this._getSelectedImage(sel, range) != null) {
			// Edit an existing image
			this.openGogoImageDialog(this._getSelectedImage(sel, range));
		} else {
			var editor = this;
      this.openGogoImageDialog(null,
				function(return_img) {
          // alert('Returnig');
					if (!return_img)
          {	// user must have pressed Cancel
            // alert("Cancel");
            return false;
					}

          if(HTMLArea.is_gecko) editor.focusEditor();
          // alert(return_img.src.replace(/about:blank/, ''));
					editor._doc.execCommand("insertimage", false, return_img.src.replace(/about:blank/, ''));

					//editor._execCommand("insertimage", false, return_img.src);

					//var sel = editor._getSelection();
					//var range = editor._createRange(sel);
					var img = null;
					if (HTMLArea.is_ie) {
						img = range.parentElement();
						// wonder if this works...
						if (img.tagName.toLowerCase() != "img") {
							img = img.previousSibling;
						}
					} else {
						img = editor._getSelectedImage(sel, range);
					}
          if(HTMLArea.is_ie) img.src = img.src.replace(/https?:\/\/null[^\/]*\//, location.href.replace(/(https?:\/\/[^\/]*\/).*/, '$1'));
          // alert(img.src);
					// TODO : Fix this, Mozilla isn't setting image
					if(img)
					{
						img.alt = return_img.alt;
						img.style.width = return_img.width + 'px';
						img.style.height = return_img.height + 'px';
            if(return_img.align)
            {
              img.align = return_img.align;
            }
          }

				}
			);
		}
	};
}
