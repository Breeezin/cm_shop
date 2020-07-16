/* 

MTree v0.2 by Matt Currie
----------------------------
mattcurrie188athotmaildotcom
http://mattserve.bounceme.net

History
-------
2003-01-27 - Ability to set double click or single click handler
           - Fix for mozilla mac
2003-01-11 - Initial version of MTree.

License
-------
Anyone is free to use this code however they want, as long as no comments are removed 
from the source code and I'm notified of any commerical use. I reserve the right to
modify this license at any time.

*/

var MT_SKIP_ROOT_NODE = true;
var MT_UNOPENED_CHILDREN = true;

var MTreeRoot = null;

function MTreeDefaultOnClick(id) {
	alert('No onClick handler set.');
	
}

function MTreeDefaultOnDoubleClick(id) {
	//alert('No onDoubleClick handler set.');
}

function MTreeDefaultOnAddChildren(id) {
	alert('No onAddChildren handler set.');
}

function MTreeSelectNode(id) {
	if (MTreeRoot.selectedNodeID) {
		lastSelected = document.getElementById('MTreeNodeLink'+MTreeRoot.selectedNodeID);
		if (lastSelected) lastSelected.className = 'MTreeItem';
	}
	MTreeRoot.selectedNodeID = id;
	newSelected = document.getElementById('MTreeNodeLink'+id);
	if (newSelected) newSelected.className = 'MTreeItemSelected';
}

function MTreeNodeOpenClose(id,force) {
	var theDiv = document.getElementById('MTreeChildNodesOf'+id);

	var theImg = document.getElementById('MTreeOpenCloseImage'+id);
	var theNode = MTreeRoot.nodes[id];
	var branchType;

	if (theNode == theNode.parentNode.lastChild) {
		branchType = 'L';
	} else {
		branchType = 'T';
	}
	
	if (theNode.expanded && !force) {
		// Collapse the display
		theDiv.style.display = 'none';
		theImg.src = MTreeRoot.relativePathToMTree+'Images/'+branchType+'plus.png';
		theNode.expanded = false;
		MTreeRoot.expandedNodes[id] = 0;
	} else {
		// See if this node has been opened before 
		if (!theNode.previouslyOpened) {
			/*	Call the callback function which should add the new nodes the callback function should create and 
				add new child nodes and then call 'updateChildNodesHTML' on the node with the given id. The addChild 
				(or newChild) methods will set the specified node to being previously opened so this call to onAddChildren 
				will only occur once per node.	
			*/
			MTreeRoot.onAddChildren(id);
		} else {
			// Expand the display
			theNode.expanded = true;
			theDiv.style.display = 'block';
			theImg.src = MTreeRoot.relativePathToMTree+'Images/'+branchType+'minus.png';
			MTreeRoot.expandedNodes[id] = 1;
		}


		
	}
}

/* The MTreeNode class */
function MTreeNode(type,display,id,value,onClickAction,onDoubleClickAction, hiddenChildren) {
	this.type = type;
	this.display = display;
	this.id = id;	
	if (onClickAction.length) {
		this.displaylink = true;
	} else {
		this.displaylink = false;	
	}
	this.value = value;
	this.firstChild = null;
	this.nextNode = null;
	this.parentNode = null;
	this.lastChild = null;
	this.hasChildren = hiddenChildren;
	this.previouslyOpened = false;
	this.expanded = false;
	this.onClick = onClickAction;
	this.onDoubleClick = onDoubleClickAction;
	if (MTreeRoot == null) {
		MTreeRoot = this;
		//this.onClick = MTreeDefaultOnClick; 
		//this.onDoubleClick = MTreeDefaultOnDoubleClick;
		this.onAddChildren = MTreeDefaultOnAddChildren;
		this.wantContainerDiv = true;			// Put a <DIV CLASS="MTreeContainer"></DIV> around the returned root tree
		this.expandProvidedChildren = true;		// Expand provided children
		this.relativePathToMTree = '';			// Required for path to images
		this.relativePathToIcons = '';			// Required for path to icon images
												// (trailing forwardslash require for both)
		this.selectedNodeID = null;
		this.nodes = new Array();
		this.expandedNodes = new Array();
	}
		
	MTreeRoot.nodes[id] = this;
}

MTreeNode.prototype.flagPreviouslyOpened = function() {
	this.previouslyOpened = true;
}

MTreeNode.prototype.updateChildNodesHTML = function() {
	document.getElementById('MTreeChildNodesOf'+this.id).innerHTML = this.render(MT_SKIP_ROOT_NODE);
}

MTreeNode.prototype.getLinkHTML = function() {
	var output = '';
	var className = 'MTreeItem';
	
	var re = new RegExp ('You do not have permission to administer');		
	
	if (!MTreeRoot.nodes[this.id].displaylink) {			
		output = "<SPAN CLASS='MTreeDisabledItem'>" + this.display + "</span>";
	} else {
		output += '<A ID="MTreeNodeLink'+this.id+'" CLASS="MTreeItem"';
		if (MTreeRoot.selectedNodeID == this.id) {
			output += 'Selected';
		}
		output += '" HREF="Javascript:MTreeSelectNode('+this.id+'); '+MTreeRoot.nodes[this.id].onClick+'" ONDBLCLICK="'+MTreeRoot.nodes[this.id].onDoubleClick+'">'+this.display+'</A>';		
	}		
	return output;
}

MTreeNode.prototype.render = function(skipThis) {
	var image;
	var current;
	var output = '';
	var isRootNode = (this.parentNode == null);
	
	if  (isRootNode && MTreeRoot.wantContainerDiv) output += '<DIV CLASS="MTreeContainer">';

	if (!skipThis) { 
		// Draw the lines or blanks of the parent nodes
		current = this.parentNode;
		var images = '';
		while (current != null) {
			if (current.parentNode != null) {
				if (current.parentNode.lastChild != current) {
					// If we're not the last node, then draw a line going down
					// do the other nodes
					image = 'I';
				} else {
					// If we are the last node, then we dont need a line
					image = 'blank';
				}
				images = '<IMG SRC="'+MTreeRoot.relativePathToMTree+'Images/'+image+'.png" WIDTH="19" HEIGHT="16">'+images;
			}
			current = current.parentNode;
		}
		output += images;
		
		// Display the tree branching for this node
		if (!isRootNode) {
			if (this.nextNode == null) {
				image = 'L';
			} else {
				image = 'T';
			}
			if (this.hasChildren) {
				if ((this.firstChild == null) || (!this.expanded)) {
					// 1) If we "hasChildren" but no Nodes defined, then it's node with progressively
					// loading children, so display a plus to allow us to expand it.
					// ---
					// 2) If we "hasChildren" and don't want to expand provided children, then display a 
					// plus to allow us to expand it 
					image += 'plus';
				} else {
					image += 'minus';
				}
				output += '<A HREF="Javascript:MTreeSelectNode('+this.id+'); MTreeNodeOpenClose('+this.id+');"><IMG ID="MTreeOpenCloseImage'+this.id+'" BORDER="0" SRC="'+MTreeRoot.relativePathToMTree+'Images/'+image+'.png" WIDTH="19" HEIGHT="16"></A>';
			} else {
				output += '<IMG SRC="'+MTreeRoot.relativePathToMTree+'Images/'+image+'.gif" WIDTH="19" HEIGHT="16">';
			}
		}
		
		// Display the node description
		output += '<IMG SRC="'+MTreeRoot.relativePathToIcons+this.type+'.gif" WIDTH="16" HEIGHT="16">';
		output += '  <SPAN ID="MTreeNodeDisplay'+this.id+'">'+this.getLinkHTML()+'</SPAN>';
		output += '<BR>';
		output += '<SPAN CLASS="MTreeContainer" ID="MTreeChildNodesOf'+this.id+'"';
		if (!isRootNode && !this.expanded) {
			output += ' STYLE="display:none;"';
		}
		output += '>';
	}

	// Render any child nodes
	current = this.firstChild;
	while (current != null) {
		output += current.render(false);
		current = current.nextNode;
	}
	
	if (!skipThis) output += '</SPAN>';	

	if  (isRootNode && MTreeRoot.wantContainerDiv) output += '</DIV>';

	return output;
}

MTreeNode.prototype.lastNodeInLevel = function() {
	current = this;
	while (current.nextNode != null) {
		current = current.nextNode;
	}
	return current;
	/*
	// rewritten to avoid stack overflow
	if (this.nextNode == null) {
		return this;
	} else {
		return this.nextNode.lastNodeInLevel();
	}*/
}

MTreeNode.prototype.newChild = function(s1,s2,s3,s4,s5,s6,s7) {
	var temp = new MTreeNode(s1,s2,s3,s4,s5,s6,s7);
	this.addChild(temp);
	return temp;
}

MTreeNode.prototype.addChild = function(newNode) {
	// Set some stuff for the parent node
	this.previouslyOpened = true;
	this.hasChildren = true;
	if (MTreeRoot.expandProvidedChildren) {
		this.expanded = true;
		MTreeRoot.expandedNodes[this.id] = 1;
	} else {
		this.expanded = false;
	}
	MTreeRoot.expandedNodes[newNode.id] = 0;
	this.lastChild = newNode;
	
	// Set the parent for the child node
	newNode.parentNode = this;
	
	// Insert the node
	if (this.firstChild == null) {
		this.firstChild = newNode;		
	} else {
		insertPoint = this.firstChild.lastNodeInLevel();
		insertPoint.nextNode = newNode;
	}
}

MTreeNode.prototype.moveUp = function() {
	theNode = this;
	theParent = theNode.parentNode;
	theLastNode = null;
	theCurrentNode = theParent.firstChild;
	while (!(theCurrentNode == null)) {
		if (theCurrentNode.nextNode == theNode) {
			if (theLastNode) {
				theLastNode.nextNode = theNode;
			} else {
				theParent.firstChild = theNode;
			}
			theCurrentNode.nextNode = theNode.nextNode;
			theNode.nextNode = theCurrentNode;			
			if (theCurrentNode.nextNode == null) theParent.lastChild = theCurrentNode;
			theParent.updateChildNodesHTML();				
			break;
		} else {
			theLastNode = theCurrentNode;
			theCurrentNode = theCurrentNode.nextNode;
		}
	}
}
	
MTreeNode.prototype.moveDown = function() {
	theNode = this;
	theParent = theNode.parentNode;
	theLastNode = null;
	theCurrentNode = theParent.firstChild;
	while (!(theCurrentNode.nextNode == null)) {
		if (theCurrentNode == theNode) {
			theNextNode = theCurrentNode.nextNode;
		
			if (theLastNode) {
				theLastNode.nextNode = theNextNode;
			} else {
				theParent.firstChild = theNextNode;
			}

			theNode.nextNode = theNextNode.nextNode;
			theNextNode.nextNode = theNode;
			if (theNode.nextNode == null) theParent.lastChild = theNode;

			theParent.updateChildNodesHTML();				
			break;
		} else {
			theLastNode = theCurrentNode;
			theCurrentNode = theCurrentNode.nextNode;
		}
	}
}

MTreeNode.prototype.getNodeById = function(id) {
	return MTreeRoot.nodes[id];
}

MTreeNode.prototype.getExpandedNodesList = function() {
	var List = '';
	for (var i=0; i<MTreeRoot.expandedNodes.length; i++) {
		if (MTreeRoot.expandedNodes[i]) {
			List = List+i+',';
		}
	}
	return List;	

}