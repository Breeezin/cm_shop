/**
 * Unless somebody already has, make a little function to debug things
 */
if(typeof dump == 'undefined')
{
  function dump(o) {
    var s = '';
    for (var prop in o) {
      s += prop + ' = ' + o[prop] + '\n';
    }

    x = window.open("", "debugger");
    x.document.write('<pre>' + s + '</pre>');
  }
}

/**
 * Unless somebody already has, add a trim() to String objects
 *  never understand why Javascript doesn't have this built in..
 */
if(typeof String.prototype.trim == 'undefined')
{
  String.prototype.trim = function()
  {
    return this.replace(/^\s+/,'').replace(/\s+$/,'');
  }
}

/**
 * Add an empty css_style to Config object's prototype
 *  the format is { '.className' : 'Description' }
 */
HTMLArea.Config.prototype.css_style = { };

/**
 * Returns the selected element, if any.  That is,
 * the element that you have last selected in the "path"
 * at the bottom of the editor, or a "control" (eg image)
 *
 * @returns null | element
 */
HTMLArea.prototype._activeElement = function(sel)
{
  if(this._selectionEmpty(sel)) return null;

  if(HTMLArea.is_ie)
  {
    if(sel.type.toLowerCase() == "control")
    {
      return sel.createRange().item(0);
    }
    else
    {
      // If it's not a control, then we need to see if
      // the selection is the _entire_ text of a parent node
      // (this happens when a node is clicked in the tree)
      var range = sel.createRange();
      var parElem = range.parentElement();
      if(parElem)
      {
        if (this._doc.body.isTextRange) {
        var prnt_range = this._doc.body.createTextRange();
        if (prnt_range != null) {
        var s = '';
		for (var prop in parElem) {
			s += prop + ' = ' + parElem[prop] + '\n';
		}
		//document.getElementById('dbg').innerHTML = '<pre>' + s + '</pre>';
		//alert(s);

        	prnt_range.moveToElementText(parElem);
        	if(prnt_range && prnt_range.isEqual(range))
        	{
          	return range.parentElement();
        	}
        }
        }
        
      }
      return null;
    }
  }
  else
  {
    // For Mozilla we just see if the selection is not collapsed (something is selected)
    // and that the anchor (start of selection) is an element.  This might not be totally
    // correct, we possibly should do a simlar check to IE?
    if(sel != null) {
    	if(!sel.isCollapsed){
      		if(sel.anchorNode.nodeType == 1){
        		return sel.anchorNode;
      		}
      	}
    }

    return null;
  }
}

HTMLArea.prototype._getAncestorsClassNames = function(sel)
{
  // Scan upwards to find a block level element that we can change or apply to
  var prnt = this._activeElement(sel);
  if(prnt == null)
  {
    prnt = (HTMLArea.is_ie ? this._createRange(sel).parentElement() : this._createRange(sel).commonAncestorContainer);
  }

  var classNames = [ ];
  while(prnt)
  {
    if(prnt.nodeType == 1)
    {
      var classes = prnt.className.trim().split(' ');
      for(var x = 0; x < classes.length; x++)
      {
        classNames[classNames.length] = classes[x];
      }

      if(prnt.tagName.toLowerCase() == 'body') break;
      if(prnt.tagName.toLowerCase() == 'table'  ) break;
    }
      prnt = prnt.parentNode;
  }

  return classNames;
}







HTMLArea.prototype._selectionEmpty = function(sel)
{
  if(HTMLArea.is_ie)
  {
  	if (sel)
    	return this._createRange(sel).htmlText == '';
    else 
    	return null;
  }
  else
  {
  	if (sel)
    	return sel.isCollapsed;
    else 
    	return null;
  }
}

HTMLArea.prototype._ancestorsWithClasses = function(sel, tag, classes)
{
  var ancestors = [ ];
  var prnt = this._activeElement(sel);
  if(prnt == null)
  {
    try
    {
      prnt = (HTMLArea.is_ie ? this._createRange(sel).parentElement() : this._createRange(sel).commonAncestorContainer);
    }
    catch(e)
    {
      return ancestors;
    }
  }
  var search_classes = classes.trim().split(' ');

  while(prnt)
  {
    if(prnt.nodeType == 1)
    {
      if(tag == null || prnt.tagName.toLowerCase() == tag)
      {
        var classes = prnt.className.trim().split(' ');
        var found_all = true;
        for(var i = 0; i < search_classes.length; i++)
        {
          var found_class = false;
          for(var x = 0; x < classes.length; x++)
          {
            if(search_classes[i] == classes[x])
            {
              found_class = true;
              break;
            }
          }

          if(!found_class)
          {
            found_all = false;
            break;
          }
        }

        if(found_all) ancestors[ancestors.length] = prnt;
      }
      if(prnt.tagName.toLowerCase() == 'body')    break;
      if(prnt.tagName.toLowerCase() == 'table'  ) break;
    }
    prnt = prnt.parentNode;
  }

  return ancestors;
}

/**
 * Fill the stylist panel with styles that may be applied to the current selection.  Styles
 * are supplied in the css_style property of the HTMLArea.Config object, which is in the format
 * { '.className' : 'Description' }
 * classes that are defined on a specific tag (eg 'a.email_link') are only shown in the panel
 *    when an element of that type is selected.
 * classes that are defined with selectors/psuedoclasses (eg 'a.email_link:hover') are never
 *    shown (if you have an 'a.email_link' without the pseudoclass it will be shown of course)
 * multiple classes (eg 'a.email_link.staff_member') are shown as a single class, and applied
 *    to the element as multiple classes (class="email_link staff_member")
 * you may click a class name in the stylist panel to add it, and click again to remove it
 * you may add multiple classes to any element
 * spans will be added where no single _and_entire_ element is selected
 */
HTMLArea.prototype._fillStylist = function()
{
  if(!this._stylist) return false;
  this._stylist.innerHTML = '<div class="toolbar" style="padding-left:3px">Styles</div>'; // FIXME : l18n

  var may_apply = true;
  var sel       = this._getSelection();

  // What is applied
  // var applied = this._getAncestorsClassNames(this._getSelection());

  // Get an active element
  var active_elem = this._activeElement(sel);

  for(var x in this.config.css_style)
  {
    var tag   = null;
    var className = x.trim();
    var applicable = true;
    var apply_to   = active_elem;

    if(applicable && /[^a-zA-Z0-9_.-]/.test(className))
    {
      applicable = false; // Only basic classes are accepted, no selectors, etc.. presumed
                          // that if you have a.foo:visited you'll also have a.foo
      // alert('complex');
    }

    if(className.indexOf('.') < 0)
    {
      // No class name, just redefines a tag
      applicable = false;
    }

    if(applicable && (className.indexOf('.') > 0))
    {
      // requires specific html tag
      tag = className.substring(0, className.indexOf('.')).toLowerCase();
      className = className.substring(className.indexOf('.'), className.length);

      // To apply we must have an ancestor tag that is the right type
      if(active_elem != null && active_elem.tagName.toLowerCase() == tag)
      {
        applicable = true;
        apply_to = active_elem;
      }
      else
      {
        if(this._getFirstAncestor(this._getSelection(), [tag]) != null)
        {
          applicable = true;
          apply_to = this._getFirstAncestor(this._getSelection(), [tag]);
        }
        else
        {
          // alert (this._getFirstAncestor(this._getSelection(), tag));
          // If we don't have an ancestor, but it's a div/span/p/hx stle, we can make one
          if(( tag == 'div' || tag == 'span' || tag == 'p'
              || (tag.substr(0,1) == 'h' && tag.length == 2)))
          {
            if(!this._selectionEmpty(this._getSelection()))
            {
              applicable = true;
              apply_to = 'new';
            }
            else
            {
              // See if we can get a paragraph or header that can be converted
              apply_to = this._getFirstAncestor(sel, ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7']);
              if(apply_to != null)
              {
                applicable = true;
              }
            }
          }
          else
          {
            applicable = false;
          }
        }
      }
    }

    if(applicable)
    {
      // Remove the first .
      className = className.substring(className.indexOf('.'), className.length);

      // Replace any futher ones with spaces (for multiple class definitions)
      className = className.replace('.', ' ');

      if(apply_to == null)
      {
      	theSel = this._getSelection();
      	
        if(theSel && this._selectionEmpty(theSel))
        {
          // Get the previous element and apply to that
          apply_to = this._getFirstAncestor(this._getSelection(), null);
        }
        else
        {
          apply_to = 'new';
          tag      = 'span';
        }
/*
        if(this._selectionEmpty(this._getSelection()))
        {
          // Get the previous element and apply to that
          apply_to = this._getFirstAncestor(this._getSelection(), null);
        }
        else
        {
          apply_to = 'new';
          tag      = 'span';
        }
*/        
              }
    }

    var applied    = (this._ancestorsWithClasses(sel, tag, className).length > 0 ? true : false);
    var applied_to = this._ancestorsWithClasses(sel, tag, className);

    if(applicable)
    {
      var anch = document.createElement('a');
      anch._stylist_className = className.trim();
      anch._stylist_applied   = applied;
      anch._stylist_appliedTo = applied_to;
      anch._stylist_applyTo = apply_to;
      anch._stylist_applyTag = tag;

      anch.innerHTML = this.config.css_style[x];
      anch.href = 'javascript:void(0)';
      var editor = this;
      anch.onclick = function()
      {
        if(this._stylist_applied == true)
        {
          editor._stylistRemoveClasses(this._stylist_className, this._stylist_appliedTo);
        }
        else
        {
          editor._stylistAddClasses(this._stylist_applyTo, this._stylist_applyTag, this._stylist_className);
        }
        return false;
      }

      anch.style.display = 'block';
      anch.style.paddingLeft = '3px';
      anch.style.paddingTop = '1px';
      anch.style.paddingBottom = '1px';
      anch.style.textDecoration = 'none';
      anch.style.color = 'black';


      if(applied)
      {
        anch.style.background = 'Highlight';
        anch.style.color = 'HighlightText';
      }

      this._stylist.appendChild(anch);
    }
  }
}

/**
 * Remove the given classes (space seperated list)
 */
HTMLArea.prototype._stylistRemoveClasses = function(classes, from)
  {
    for(var x = 0; x < from.length; x++)
    {
      this._removeClasses(from[x], classes);
    }
    this.focusEditor();
    this.updateToolbar();
  };


HTMLArea.arrayContainsArray = function(a1, a2)
{
  var all_found = true;
  for(var x = 0; x < a2.length; x++)
  {
    var found = false;
    for(var i = 0; i < a1.length; i++)
    {
      if(a1[i] == a2[x])
      {
        found = true;
        break;
      }
    }
    if(!found)
    {
      all_found = false;
      break;
    }
  }
  return all_found;
}

HTMLArea.arrayFilter = function(a1, filterfn)
{
  var new_a = [ ];
  for(var x = 0; x < a1.length; x++)
  {
    if(filterfn(a1[x]))
      new_a[new_a.length] = a1[x];
  }

  return new_a;
}

HTMLArea.prototype._removeClasses = function(el, classes)
{
  if(el != null)
  {
    var thiers = el.className.trim().split(' ');
    var new_thiers = [ ];
    var ours   = classes.split(' ');
    for(var x = 0; x < thiers.length; x++)
    {
      var exists = false;
      for(var i = 0; exists == false && i < ours.length; i++)
      {
        if(ours[i] == thiers[x])
        {
          exists = true;
        }
      }
      if(exists == false)
      {
        new_thiers[new_thiers.length] = thiers[x];
      }
    }

    if(new_thiers.length == 0 && el._stylist_usedToBe && el._stylist_usedToBe.length > 0 && el._stylist_usedToBe[el._stylist_usedToBe.length - 1].className != null)
    {
      // Revert back to what we were IF the classes are identical
      var last_el = el._stylist_usedToBe[el._stylist_usedToBe.length - 1];
      var last_classes = HTMLArea.arrayFilter(last_el.className.trim().split(' '), function(c) { if (c == null || c.trim() == '') { return false;} return true; });

      if(
        (new_thiers.length == 0)
        ||
        (
        HTMLArea.arrayContainsArray(new_thiers, last_classes)
        && HTMLArea.arrayContainsArray(last_classes, new_thiers)
        )
      )
      {
        el = this.switchElementTag(el, last_el.tagName);
        new_thiers = last_classes;
      }
      else
      {
        // We can't rely on the remembered tags any more
        el._stylist_usedToBe = [ ];
      }
    }

    if(     new_thiers.length > 0
        ||  el.tagName.toLowerCase() != 'span'
        || (el.id && el.id != '')
      )
    {
      el.className = new_thiers.join(' ').trim();
    }
    else
    {
      // Must be a span with no classes and no id, so we can splice it out
      var prnt = el.parentNode;
      var childs = el.childNodes;
      for(var x = 0; x < childs.length; x++)
      {
        prnt.insertBefore(childs[x], el);
      }
      prnt.removeChild(el);
    }
  }
}

HTMLArea.prototype._addClasses = function(el, classes)
  {
    if(el != null)
    {
      var thiers = el.className.trim().split(' ');
      var ours   = classes.split(' ');
      for(var x = 0; x < ours.length; x++)
      {
        var exists = false;
        for(var i = 0; exists == false && i < thiers.length; i++)
        {
          if(thiers[i] == ours[x])
          {
            exists = true;
          }
        }
        if(exists == false)
        {
          thiers[thiers.length] = ours[x];
        }
      }
      el.className = thiers.join(' ').trim();
    }
  }

/**
 * Add the given classes (space seperated list) to the currently selected element
 * (will add a span if none selected)
 */
HTMLArea.prototype._stylistAddClasses = function(el, tag, classes)
  {
    if(el == 'new')
    {
      this.insertHTML('<' + tag + ' class="' + classes + '">' + this.getSelectedHTML() + '</' + tag + '>');
    }
    else
    {
      if(tag != null && el.tagName.toLowerCase() != tag)
      {
        // Have to change the tag!
        var new_el = this.switchElementTag(el, tag);

        if(typeof el._stylist_usedToBe != 'undefined')
        {
          new_el._stylist_usedToBe = el._stylist_usedToBe;
          new_el._stylist_usedToBe[new_el._stylist_usedToBe.length] = {'tagName' : el.tagName, 'className' : el.getAttribute('class')};
        }
        else
        {
          new_el._stylist_usedToBe = [{'tagName' : el.tagName, 'className' : el.getAttribute('class')}];
        }

        this._addClasses(new_el, classes);
      }
      else
      {
        this._addClasses(el, classes);
      }
    }
    this.focusEditor();
    this.updateToolbar();
  };

HTMLArea.prototype.switchElementTag = function(el, tag)
{
  var prnt = el.parentNode;
  var new_el = this._doc.createElement(tag);

  if(HTMLArea.is_ie || el.hasAttribute('id'))    new_el.setAttribute('id', el.getAttribute('id'));
  if(HTMLArea.is_ie || el.hasAttribute('style')) new_el.setAttribute('style', el.getAttribute('style'));

  var childs = el.childNodes;
  for(var x = 0; x < childs.length; x++)
  {
    new_el.appendChild(childs[x].cloneNode(true));
  }

  prnt.insertBefore(new_el, el);
  new_el._stylist_usedToBe = [el.tagName];
  prnt.removeChild(el);
  this.selectNodeContents(new_el);
  return new_el;
}
