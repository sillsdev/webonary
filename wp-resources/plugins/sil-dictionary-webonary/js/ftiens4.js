﻿//****************************************************************
// Keep this copyright notice:
// This copy of the script is the property of the owner of the
// particular web site you were visiting.
// Do not download the script's files from there.
// For a free download and full instructions go to:
// http://www.treeview.net
//****************￿************************************************


// Log of changes:
//
//      08 Jun 04 - Very small change to one error message
//      21 Mar 04 - Support for folder.addChildren allows for much bigger trees
//      12 May 03 - Support for Safari Beta 3
//      01 Mar 03 - VERSION 4.3 - Support for checkboxes
//      21 Feb 03 - Added support for Opera 7
//      22 Sep 02 - Added maySelect member for node-by-node control
//                  of selection and highlight
//      21 Sep 02 - Cookie values are now separated by cookieCutter
//      12 Sep 02 - VERSION 4.2 - Can highlight Selected Nodes and
//                  can preserve state through external (DB) IDs
//      29 Aug 02 - Fine tune 'supportDeferral' for IE4 and IE Mac
//      25 Aug 02 - Fixes: STARTALLOPEN, and multi-page frameless
//      09 Aug 02 - Fix repeated folder on Mozilla 1.x
//      31 Jul 02 - VERSION 4.1 - Dramatic speed increase for trees
//      with hundreds or thousands of nodes; changes to the control
//      flags of the gLnk function
//      18 Jul 02 - Changes in pre-load images function
//      13 Jun 02 - Add ICONPATH var to allow for gif subdir
//      20 Apr 02 - Improve support for frame-less layout
//      07 Apr 02 - Minor changes to support server-side dynamic feeding
//                  (example: FavoritesManagerASP)


// Definition of class Folder
// *****************************************************************
function Folder(folderDescription, hreference) //constructor
{
  //constant data
  this.desc = folderDescription
  this.hreference = hreference;
  this.id = -1;
  this.navObj = 0;
  this.iconImg = 0;
  this.nodeImg = 0;
  this.isLastNode = 0;
  this.iconSrc = ICONPATH + "ftv2folderopen.gif";
  this.iconSrcClosed = ICONPATH + "ftv2folderclosed.gif";
  this.children = [];
  this.nChildren = 0;
  this.level = 0;
  this.leftSideCoded = "";
  this.isLastNode=false;
  this.parentObj = null;
  this.maySelect=true;
  this.prependHTML = ""

  //dynamic data
  this.isOpen = false
  this.isLastOpenedFolder = false
  this.isRendered = 0

  //methods
  this.initialize = initializeFolder
  this.setState = setStateFolder
  this.addChild = addChild
  this.addChildren = addChildren
  this.createIndex = createEntryIndex
  this.escondeBlock = escondeBlock
  this.esconde = escondeFolder
  this.folderMstr = folderMstr
  this.renderOb = drawFolder
  this.totalHeight = totalHeight
  this.subEntries = folderSubEntries
  this.linkHTML = linkFolderHTML
  this.blockStartHTML = blockStartHTML
  this.blockEndHTML = blockEndHTML
  this.nodeImageSrc = nodeImageSrc
  this.iconImageSrc = iconImageSrc
  this.getID = getID
  this.forceOpeningOfAncestorFolders = forceOpeningOfAncestorFolders
}

function initializeFolder(level, lastNode, leftSide)
{
  let i = 0;
  let nc = this.nChildren

  this.createIndex()
  this.level = level
  this.leftSideCoded = leftSide

  if (browserVersion == 0)
    this.isOpen=true;

  if (level>0)
    if (lastNode) //the last child in the children array
		leftSide = leftSide + "0"
	else
		leftSide = leftSide + "1"

  this.isLastNode = lastNode

  if (nc > 0)
  {
    level = level + 1
    for (i=0 ; i < this.nChildren; i++)
    {
      if (typeof this.children[i].initialize == 'undefined') //document node was specified using the addChildren function
      {
        if (typeof this.children[i][0] == 'undefined' || typeof this.children[i] == 'string')
        {
          this.children[i] = ["item incorrectly defined", ""];
        }

        //Basic initialization of the Item object
        //These members or methods are needed even before the Item is rendered
        this.children[i].initialize=initializeItem;
        this.children[i].createIndex=createEntryIndex;
        if (typeof this.children[i].maySelect == 'undefined')
          this.children[i].maySelect=true
        this.children[i].forceOpeningOfAncestorFolders = forceOpeningOfAncestorFolders
      }
      if (i == this.nChildren-1)
        this.children[i].initialize(level, 1, leftSide)
      else
        this.children[i].initialize(level, 0, leftSide)
    }
  }
}

function drawFolder(insertAtObj)
{
  let nodeName = "";
  let auxEv = "";
  let docW = "";

  finalizeCreationOfChildDocs(this)

  let leftSide = leftSideHTML(this.leftSideCoded);

  if (browserVersion > 0)
    auxEv = "<a href='javascript:clickOnNode(\""+this.getID()+"\")'>"
  else
    auxEv = "<a>"

  nodeName = this.nodeImageSrc()

  if (this.level>0)
    if (this.isLastNode) //the last child in the children array
	    leftSide = leftSide + "<td valign=top>" + auxEv + "<img name='nodeIcon" + this.id + "' id='nodeIcon" + this.id + "' src='" + nodeName + "' class='ft-node'></a></td>"
    else
      leftSide = leftSide + "<td valign=top background=" + ICONPATH + "ftv2vertline.gif>" + auxEv + "<img name='nodeIcon" + this.id + "' id='nodeIcon" + this.id + "' src='" + nodeName + "' class='ft-node'></a></td>"

  this.isRendered = 1

  if (browserVersion == 2) {
    if (!doc.yPos)
      doc.yPos=20
  }

  docW = this.blockStartHTML("folder");

  docW = docW + "<tr>" + leftSide + "<td valign=top>";
  if (USEICONS)
  {
    docW = docW + this.linkHTML(false)
    docW = docW + "<img id='folderIcon" + this.id + "' name='folderIcon" + this.id + "' src='" + this.iconImageSrc() + "' border=0></a>"
  }
  else
  {
	  if (this.prependHTML == "")
        docW = docW + "<img src=" + ICONPATH + "ftv2blank.gif class='ft-blank2'>"
  }
  if (WRAPTEXT)
	  docW = docW + "</td>"+this.prependHTML+"<td valign=top width=100%>"
  else
	  docW = docW + "</td>"+this.prependHTML+"<td valign=top width=100%>"
  if (USETEXTLINKS)
  {
    docW = docW + this.linkHTML(true)
    docW = docW + this.desc + "</a>"
  }
  else
    docW = docW + this.desc
  docW = docW + "</td>"

  docW = docW + this.blockEndHTML()

  if (insertAtObj == null)
  {
    insertAtObj = document.getElementById('domRoot');
    if (insertAtObj == null) {
      doc.write("<div id=domRoot></div>") //transition between regular flow HTML, and node-insert DOM DHTML
      insertAtObj = getElById("domRoot")
    }

    insertAtObj.insertAdjacentHTML("beforeEnd", docW)
  }
  else
  {
      insertAtObj.insertAdjacentHTML("afterEnd", docW)
  }

  if (browserVersion == 2)
  {
    this.navObj = doc.layers["folder"+this.id]
    if (USEICONS)
      this.iconImg = this.navObj.document.images["folderIcon"+this.id]
    this.nodeImg = this.navObj.document.images["nodeIcon"+this.id]
    doc.yPos=doc.yPos+this.navObj.clip.height
  }
  else if (browserVersion != 0)
  {
    this.navObj = getElById("folder"+this.id)
    if (USEICONS)
      this.iconImg = getElById("folderIcon"+this.id)
    this.nodeImg = getElById("nodeIcon"+this.id)
  }
}

function setStateFolder(isOpen)
{
  let subEntries;
  let totalHeight;
  let fIt = 0;
  let i = 0;
  let currentOpen;

  if (isOpen == this.isOpen)
    return

  if (browserVersion == 2)
  {
    totalHeight = 0
    for (i=0; i < this.nChildren; i++)
      totalHeight = totalHeight + this.children[i].navObj.clip.height
      subEntries = this.subEntries()
    if (this.isOpen)
      totalHeight = 0 - totalHeight
    for (fIt = this.id + subEntries + 1; fIt < nEntries; fIt++)
      indexOfEntries[fIt].navObj.moveBy(0, totalHeight)
  }
  this.isOpen = isOpen;

  if (this.getID()!=foldersTree.getID() && PRESERVESTATE && !this.isOpen) //closing
  {
     currentOpen = GetCookie(clickCookie)
     if (currentOpen != null) {
         currentOpen = currentOpen.replace(this.getID()+cookieCutter, "")
         SetCookie(clickCookie, currentOpen)
     }
  }

  if (!this.isOpen && this.isLastOpenedfolder)
  {
		lastOpenedFolder = null;
		this.isLastOpenedfolder = false;
  }
  propagateChangesInState(this)
}

function propagateChangesInState(folder)
{
  let i = 0;

  //Change icon
  if (folder.nChildren > 0 && folder.level>0)  //otherwise the one given at render stays
    folder.nodeImg.src = folder.nodeImageSrc()

  //Change node
  if (USEICONS)
    folder.iconImg.src = folder.iconImageSrc()

  //Propagate changes
  for (i=folder.nChildren-1; i>=0; i--) {
    if (folder.isOpen)
      folder.children[i].folderMstr(folder.navObj)
    else
  	  folder.children[i].esconde()
  }
}

function escondeFolder()
{
  this.escondeBlock()

  this.setState(0)
}

function linkFolderHTML(isTextLink)
{
  let docW = "";

  if (this.hreference)
  {
	searchlink = "?semdomain=" + this.desc.replace(/'/g, '&lsquo;').replace(/[0-9]|[.]/g, '').trim() + "&semnumber=" + this.desc.match(/\d{1,2}[.]/g).join(''); // + "&nodeid=" + this.id;
	  //searchlink = "#";
	if (USEFRAMES)
	  //docW = docW + "<a class=categorylink onclick=\"displayEntry('" + this.desc.replace(/'/g, '\'').replace(/[0-9]|[.]/g, '').trim() + "', '" + this.desc.match(/\d[.]/g).join('') + "');\" href='" + searchlink + "' "
		docW = docW + "<a class=categorylink onclick=\"clickOnFolder('" + this.getID() + "');\" href='" + searchlink + "' "
	else
	  docW = docW + "<a href='" + this.hreference + "' TARGET=_top "

    if (isTextLink) {
        docW += "id=\"itemTextLink"+this.id+"\" ";
    }

    if (browserVersion > 0)
    {
      docW = docW + "onClick='javascript:clickOnFolder(\""+this.getID()+"\")'"
    }

    docW = docW + ">"
  }
  else
    docW = docW + "<a>"

  return docW;
}

function addChild(childNode)
{
  this.children[this.nChildren] = childNode
  childNode.parentObj = this
  this.nChildren++
  return childNode
}

//The list can contain either a Folder object or a sub list with the arguments for Item
function addChildren(listOfChildren)
{
  this.children = listOfChildren
  this.nChildren = listOfChildren.length
  for (i=0; i<this.nChildren; i++)
    this.children[i].parentObj = this
}

function folderSubEntries()
{
  let i = 0;
  let se = this.nChildren;

  for (i=0; i < this.nChildren; i++){
    if (this.children[i].children) //is a folder
      se = se + this.children[i].subEntries()
  }

  return se
}

function nodeImageSrc() {
  let srcStr = "";

  if (this.isLastNode) //the last child in the children array
  {
    if (this.nChildren == 0)
      srcStr = ICONPATH + "ftv2lastnode.gif"
    else
      if (this.isOpen)
        srcStr = ICONPATH + "ftv2mlastnode.gif"
      else
        srcStr = ICONPATH + "ftv2plastnode.gif"
  }
  else
  {
    if (this.nChildren == 0)
      srcStr = ICONPATH + "ftv2node.gif"
    else
      if (this.isOpen)
        srcStr = ICONPATH + "ftv2mnode.gif"
      else
        srcStr = ICONPATH + "ftv2pnode.gif"
  }
  return srcStr;
}

function iconImageSrc() {
  if (this.isOpen)
    return(this.iconSrc)
  else
    return(this.iconSrcClosed)
}

// Definition of class Item (a document or link inside a Folder)
// *************************************************************

function Item(itemDescription) // Constructor
{
  // constant data
  this.desc = itemDescription

  this.level = 0
  this.isLastNode = false
  this.leftSideCoded = ""
  this.parentObj = null

  this.maySelect=true

  this.initialize = initializeItem;
  this.createIndex = createEntryIndex;
  this.forceOpeningOfAncestorFolders = forceOpeningOfAncestorFolders;

  finalizeCreationOfItem(this)
}

//Assignments that can be delayed when the item is created with folder.addChildren
//The assignments that cannot be delayed are done in addChildren and in initializeFolder
//Additionaly, some assignments are also done in finalizeCreationOfChildDocs itself
function finalizeCreationOfItem(itemArray)
{
  itemArray.navObj = 0 //initialized in render()
  itemArray.iconImg = 0 //initialized in render()
  itemArray.iconSrc = ICONPATH + "ftv2doc.gif"
  itemArray.isRendered = 0
  itemArray.nChildren = 0
  itemArray.prependHTML = ""

  // methods
  itemArray.escondeBlock = escondeBlock
  itemArray.esconde = escondeBlock
  itemArray.folderMstr = folderMstr
  itemArray.renderOb = drawItem
  itemArray.totalHeight = totalHeight
  itemArray.blockStartHTML = blockStartHTML
  itemArray.blockEndHTML = blockEndHTML
  itemArray.getID = getID
}

function initializeItem(level, lastNode, leftSide)
{
  this.createIndex()
  this.level = level
  this.leftSideCoded = leftSide
  this.isLastNode = lastNode
}

function drawItem(insertAtObj)
{
  let leftSide = leftSideHTML(this.leftSideCoded);
  let docW = "";

  const fullLink = "href=\"" + this.link + "\" target=\"" + this.target + "\" onClick=\"clickOnLink('" + this.getID() + "\', '" + this.link + "','" + this.target + "');return false;\"";
  this.isRendered = 1

  if (this.level>0)
    if (this.isLastNode) //the last 'brother' in the children array
    {
      leftSide = leftSide + "<td valign=top><img src='" + ICONPATH + "ftv2lastnode.gif' class='ft-node'></td>"
    }
    else
    {
      leftSide = leftSide + "<td valign=top background=" + ICONPATH + "ftv2vertline.gif><img src='" + ICONPATH + "ftv2node.gif' class='ft-node'></td>"
    }

  docW = docW + this.blockStartHTML("item")

  docW = docW + "<tr>" + leftSide + "<td valign=top>"
  if (USEICONS)
      docW = docW + "<a " + fullLink  + " id=\"itemIconLink"+this.id+"\">" + "<img id='itemIcon"+this.id+"' " + "src='"+this.iconSrc+"' border=0>" + "</a>"
  else
	  if (this.prependHTML == "")
        docW = docW + "<img src=" + ICONPATH + "ftv2blank.gif class='ft-blank3'>"

  if (WRAPTEXT)
    docW = docW + "</td>"+this.prependHTML+"<td valign=top width=100%>"
  else
    docW = docW + "</td>"+this.prependHTML+"<td valign=top width=100%>"

  if (USETEXTLINKS)
    docW = docW + "<a " + fullLink + " id=\"itemTextLink"+this.id+"\">" + this.desc + "</a>"
  else
    docW = docW + this.desc

  docW = docW + "</td>"

  docW = docW + this.blockEndHTML()

  if (insertAtObj == null)
  {
	  doc.write(docW)
  }
  else
  {
      insertAtObj.insertAdjacentHTML("afterEnd", docW)
  }

  if (browserVersion == 2) {
    this.navObj = doc.layers["item"+this.id]
    if (USEICONS)
      this.iconImg = this.navObj.document.images["itemIcon"+this.id]
    doc.yPos=doc.yPos+this.navObj.clip.height
  } else if (browserVersion != 0) {
    this.navObj = getElById("item"+this.id)
    if (USEICONS)
      this.iconImg = getElById("itemIcon"+this.id)
  }
}


// Methods common to both objects (pseudo-inheritance)
// ********************************************************

function forceOpeningOfAncestorFolders() {
  if (this.parentObj == null || this.parentObj.isOpen)
    return
  else {
    this.parentObj.forceOpeningOfAncestorFolders()
    clickOnNodeObj(this.parentObj)
  }
}

function escondeBlock()
{
  if (browserVersion == 1 || browserVersion == 3) {
    if (this.navObj.style.display == "none")
      return
    this.navObj.style.display = "none"
  } else {
    if (this.navObj.visibility == "hidden")
      return
    this.navObj.visibility = "hidden"
  }
}

function folderMstr(domObj)
{
  if (!this.isRendered)
     this.renderOb(domObj)
  else
    if (browserVersion == 1 || browserVersion == 3)
      this.navObj.style.display = "block"
    else
      this.navObj.visibility = "show"
}

function blockStartHTML(idprefix) {
  const idParam = "id='" + idprefix + this.id + "'";
  let docW = "";

  if (browserVersion == 2)
    docW = "<layer "+ idParam + " top=" + doc.yPos + " visibility=show>"
  else if (browserVersion != 0)
    docW = "<div " + idParam + " style='display:block; position:block;'>"

  docW = docW + "<table border=0 cellspacing=0 cellpadding=0 width=100%>"

  return docW
}

function blockEndHTML() {
  let docW = "";

  docW = "</table>"

  if (browserVersion == 2)
    docW = docW + "</layer>"
  else if (browserVersion != 0)
    docW = docW + "</div>"

  return docW
}

function createEntryIndex()
{
  this.id = nEntries
  indexOfEntries[nEntries] = this
  nEntries++
}

// total height of subEntries open
function totalHeight() //used with browserVersion == 2
{
  let h = this.navObj.clip.height;
  let i = 0;

  if (this.isOpen) //is a folder and _is_ open
    for (i=0 ; i < this.nChildren; i++)
      h = h + this.children[i].totalHeight()

  return h
}


function leftSideHTML(leftSideCoded) {
  let i;
  let retStr = "";

  for (i=0; i<leftSideCoded.length; i++)
	{
		if (leftSideCoded.charAt(i) == "1")
		{
			retStr = retStr + "<td valign=top background=" + ICONPATH + "ftv2vertline.gif><img src='" + ICONPATH + "ftv2vertline.gif' class='ft-node'></td>"
		}
		if (leftSideCoded.charAt(i) == "0")
		{
			retStr = retStr + "<td valign=top><img src='" + ICONPATH + "ftv2blank.gif' class='ft-node'></td>"
		}
	}
	return retStr
}

function getID()
{
  //define a .xID in all nodes (folders and items) if you want to PERVESTATE that
  //work when the tree changes. The value eXternal value must be unique for each
  //node and must node change when other nodes are added or removed
  //The value may be numeric or string, but cannot have the same char used in cookieCutter
  if (typeof this.xID != "undefined")
    return this.xID
  else
    return this.id
}


// Events
// *********************************************************

function clickOnFolder(folderId)
{
  let clicked = findObj(folderId)

  if (typeof clicked=='undefined' || clicked==null)
  {
    alert("Treeview was not able to find the node object corresponding to ID=" + folderId + ". If the configuration file sets a.xID values, it must set them for ALL nodes, including the foldersTree root.")
    return;
  }

  if (!clicked.isOpen) {
    clickOnNodeObj(clicked)
  }

  if (lastOpenedFolder != null && lastOpenedFolder !== folderId)
    clickOnNode(lastOpenedFolder); //sets lastOpenedFolder to null

  if (clicked.nChildren === 0) {
    lastOpenedFolder = folderId;
    clicked.isLastOpenedfolder = true
  }

  if (isLinked(clicked.hreference)) {
      highlightObjLink(clicked);
  }
}

function clickOnNode(folderId)
{
  fOb = findObj(folderId);
  if (typeof fOb=='undefined' || fOb==null)
  {
    alert("Treeview was not able to find the node object corresponding to ID=" + folderId + ". If the configuration file sets a.xID, it must set foldersTree.xID as well.")
    return;
  }

  clickOnNodeObj(fOb);
}

function clickOnNodeObj(folderObj)
{
  let state = 0;
  let currentOpen;

  state = folderObj.isOpen
  folderObj.setState(!state) //open<->close

  if (folderObj.id!=foldersTree.id && PRESERVESTATE)
  {
    currentOpen = GetCookie(clickCookie)
    if (currentOpen == null)
      currentOpen = ""

    if (!folderObj.isOpen) //closing
    {
      currentOpen = currentOpen.replace(folderObj.getID()+cookieCutter, "")
      SetCookie(clickCookie, currentOpen)
    }
    else
      SetCookie(clickCookie, currentOpen+folderObj.getID()+cookieCutter)
  }
}

function clickOnLink(clickedId, target, windowName) {
    highlightObjLink(findObj(clickedId));
    if (isLinked(target)) {
        window.open(target,windowName);
    }
}

function ld  ()
{
	return document.links.length-1
}


// Auxiliary Functions
// *******************

function finalizeCreationOfChildDocs(folderObj) {
  for(i=0; i < folderObj.nChildren; i++)  {
    child = folderObj.children[i]
    if (typeof child[0] != 'undefined')
    {
      // Amazingly, arrays can have members, so   a = ["a", "b"]; a.desc="asdas"   works
      // If a doc was inserted as an array, we can transform it into an itemObj by adding
      // the missing members and functions
      child.desc = child[0]
      setItemLink(child, GLOBALTARGET, child[1])
      finalizeCreationOfItem(child)
    }
  }
}

function findObj(id)
{
  let i = 0;
  let nodeObj;

  if (typeof foldersTree.xID != "undefined") {
    nodeObj = indexOfEntries[i];
    for(i=0;i<nEntries&&indexOfEntries[i].xID!=id;i++) //may need optimization
      ;
    id = i
  }
  if (id >= nEntries)
    return null; //example: node removed in DB
  else
    return indexOfEntries[id];
}

function isLinked(hrefText) {
  let result = true;
  result = (result && hrefText !=null);
    result = (result && hrefText != '');
    result = (result && hrefText.indexOf('undefined') < 0);
    result = (result && hrefText.indexOf('parent.op') < 0);
    return result;
}

// Do highlighting by changing background and foreg. colors of folder or doc text
function highlightObjLink(nodeObj) {
  if (!HIGHLIGHT || nodeObj==null || nodeObj.maySelect==false) {//node deleted in DB
    return;
  }

  if (browserVersion == 1 || browserVersion == 3) {
    const clickedDOMObj = getElById('itemTextLink' + nodeObj.id);
    if (clickedDOMObj != null) {
        if (lastClicked != null) {
          const prevClickedDOMObj = getElById('itemTextLink' + lastClicked.id);
          prevClickedDOMObj.style.color=lastClickedColor;
            prevClickedDOMObj.style.backgroundColor=lastClickedBgColor;
        }

        lastClickedColor    = clickedDOMObj.style.color;
        lastClickedBgColor  = clickedDOMObj.style.backgroundColor;
        clickedDOMObj.style.color=HIGHLIGHT_COLOR;
        clickedDOMObj.style.backgroundColor=HIGHLIGHT_BG;
    }
  }
  lastClicked = nodeObj;
  if (PRESERVESTATE)
  {
    SetCookie(highlightCookie, nodeObj.getID());
  }
}

function insFld(parentFolder, childFolder)
{
  return parentFolder.addChild(childFolder)
}

function insDoc(parentFolder, document)
{
  return parentFolder.addChild(document)
}

function gFld(description, hreference)
{
  folder = new Folder(description, hreference);
  return folder;
}

function gLnk(optionFlags, description, linkData)
{
  if (optionFlags>=0) { //is numeric (old style) or empty (error)
    //Target changed from numeric to string in Aug 2002, and support for numeric style was entirely dropped in Mar 2004
    alert("Change your Treeview configuration file to use the new style of target argument in gLnk");
    return;
  }

  newItem = new Item(description);
  setItemLink(newItem, optionFlags, linkData);
  return newItem;
}

function setItemLink(item, optionFlags, linkData) {
  let targetFlag = "";
  let target = "";
  let protocolFlag = "";
  let protocol = "";

  targetFlag = optionFlags.charAt(0)
  if (targetFlag=="B")
    target = "_blank"
  if (targetFlag=="P")
    target = "_parent"
  if (targetFlag=="R")
    target = "basefrm"
  if (targetFlag=="S")
    target = "_self"
  if (targetFlag=="T")
    target = "_top"

  if (optionFlags.length > 1) {
    protocolFlag = optionFlags.charAt(1)
    if (protocolFlag=="h")
      protocol = "http://"
    if (protocolFlag=="s")
      protocol = "https://"
    if (protocolFlag=="f")
      protocol = "ftp://"
    if (protocolFlag=="m")
      protocol = "mailto:"
  }

  item.link = protocol+linkData;
  item.target = target
}

//Function created  for backwards compatibility purposes
//Function contents voided in March 2004
function oldGLnk(target, description, linkData)
{
}

function preLoadIcons() {
  let auxImg;
  auxImg = new Image();
	auxImg.src = ICONPATH + "ftv2vertline.gif";
	auxImg.src = ICONPATH + "ftv2mlastnode.gif";
	auxImg.src = ICONPATH + "ftv2mnode.gif";
	auxImg.src = ICONPATH + "ftv2plastnode.gif";
	auxImg.src = ICONPATH + "ftv2pnode.gif";
	auxImg.src = ICONPATH + "ftv2blank.gif";
	auxImg.src = ICONPATH + "ftv2lastnode.gif";
	auxImg.src = ICONPATH + "ftv2node.gif";
	auxImg.src = ICONPATH + "ftv2folderclosed.gif";
	auxImg.src = ICONPATH + "ftv2folderopen.gif";
	auxImg.src = ICONPATH + "ftv2doc.gif";
}

//Open some folders for initial layout, if necessary
function setInitialLayout() {
  if (browserVersion > 0)
    clickOnNodeObj(foldersTree);

  if ((browserVersion > 0) && PRESERVESTATE)
		PersistentFolderOpening();
}

//Used with NS4 and STARTALLOPEN
function renderAllTree(nodeObj, parent) {
  let i = 0;
  nodeObj.renderOb(parent)
  //first option works with only one div domRoot
  //if (supportsDeferral)
    for (i=nodeObj.nChildren-1; i>=0; i--)
      renderAllTree(nodeObj.children[i], nodeObj.navObj)
  /* else
    for (i=0 ; i < nodeObj.nChildren; i++)
      renderAllTree(nodeObj.children[i], null)
   */
}

function hideWholeTree(nodeObj, hideThisOne, nodeObjMove) {
  let i = 0;
  let heightContained = 0;
  let childrenMove = nodeObjMove;

  if (hideThisOne)
    nodeObj.escondeBlock()

  if (browserVersion == 2)
    nodeObj.navObj.moveBy(0, 0-nodeObjMove)

  for (i=0 ; i < nodeObj.nChildren; i++) {
    heightContainedInChild = hideWholeTree(nodeObj.children[i], true, childrenMove)
    if (browserVersion == 2) {
      heightContained = heightContained + heightContainedInChild + nodeObj.children[i].navObj.clip.height
      childrenMove = childrenMove + heightContainedInChild
	}
  }

  return heightContained;
}


// Simulating inserAdjacentHTML on NS6
// Code by thor@jscript.dk
// ******************************************

if(typeof HTMLElement!="undefined" && !HTMLElement.prototype.insertAdjacentElement){
	HTMLElement.prototype.insertAdjacentElement = function (where,parsedNode)
	{
		switch (where){
		case 'beforeBegin':
			this.parentNode.insertBefore(parsedNode,this)
			break;
		case 'afterBegin':
			this.insertBefore(parsedNode,this.firstChild);
			break;
		case 'beforeEnd':
			this.appendChild(parsedNode);
			break;
		case 'afterEnd':
			if (this.nextSibling)
				this.parentNode.insertBefore(parsedNode,this.nextSibling);
			else this.parentNode.appendChild(parsedNode);
			break;
		}
	}

	HTMLElement.prototype.insertAdjacentHTML = function(where,htmlStr)
	{
      const r = this.ownerDocument.createRange();
      r.setStartBefore(this);
      const parsedHTML = r.createContextualFragment(htmlStr);
      this.insertAdjacentElement(where,parsedHTML)
	}
}

function getElById(idVal) {
  if (document.getElementById != null)
    return document.getElementById(idVal)
  if (document.all != null)
    return document.all[idVal]

  alert("Problem getting element by id")
  return null
}


// Functions for cookies
// Note: THESE FUNCTIONS ARE OPTIONAL. No cookies are used unless
// the PRESERVESTATE variable is set to 1 (default 0)
// The separator currently in use is ^ (chr 94)
// ***********************************************************

function PersistentFolderOpening()
{
  let stateInCookie;
  let fldStr = "";
  let fldArr;
  let fldPos = 0;
  let id;
  let nodeObj;
  stateInCookie = GetCookie(clickCookie);
  SetCookie(clickCookie, "") //at the end of function it will be back, minus null cases

  if(stateInCookie!=null)
  {
    fldArr = stateInCookie.split(cookieCutter)
    for (fldPos=0; fldPos<fldArr.length; fldPos++)
    {
      fldStr=fldArr[fldPos]
      if (fldStr != "") {
        nodeObj = findObj(fldStr)
        if (nodeObj!=null) //may have been deleted
          if (nodeObj.setState) {
            nodeObj.forceOpeningOfAncestorFolders()
            clickOnNodeObj(nodeObj);
          }
          else
            alert("Internal id is not pointing to a folder anymore.\nConsider giving an ID to the tree and external IDs to the individual nodes.")
      }
    }
  }
}

function storeAllNodesInClickCookie(treeNodeObj)
{
  let currentOpen;
  let i = 0;

  if (typeof treeNodeObj.setState != "undefined") //is folder
  {
    currentOpen = GetCookie(clickCookie)
    if (currentOpen == null)
      currentOpen = ""

    if (treeNodeObj.getID() != foldersTree.getID())
      SetCookie(clickCookie, currentOpen+treeNodeObj.getID()+cookieCutter)

    for (i=0; i < treeNodeObj.nChildren; i++)
        storeAllNodesInClickCookie(treeNodeObj.children[i])
  }
}

function CookieBranding(name) {
  if (typeof foldersTree.treeID != "undefined")
    return name+foldersTree.treeID //needed for multi-tree sites. make sure treeId does not contain cookieCutter
  else
    return name
}

function GetCookie(name)
{
  name = CookieBranding(name)

  const arg = name + "=";
  const alen = arg.length;
  const clen = document.cookie.length;
  let i = 0;

  while (i < clen) {
      let j = i + alen;
      if (document.cookie.substring(i, j) == arg)
			return getCookieVal (j);
		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0) break;
	}
	return null;
}

function getCookieVal(offset) {
  let endstr = document.cookie.indexOf(";", offset);
  if (endstr == -1)
	endstr = document.cookie.length;
	return unescape(document.cookie.substring(offset, endstr));
}

function SetCookie(name, value)
{
  const argv = SetCookie.arguments;
  const argc = SetCookie.arguments.length;
  const expires = (argc > 2) ? argv[2] : null;
  const domain = (argc > 4) ? argv[4] : null;
  const secure = (argc > 5) ? argv[5] : false;
  const path = "/"; //allows the tree to remain open across pages with diff names & paths

  name = CookieBranding(name)

	document.cookie = name + "=" + escape (value) +
	((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
	((path == null) ? "" : ("; path=" + path)) +
	((domain == null) ? "" : ("; domain=" + domain)) +
	((secure == true) ? "; secure" : "");
}

function ExpireCookie (name)
{
  const exp = new Date();
  exp.setTime (exp.getTime() - 1);
  const cval = GetCookie(name);
  name = CookieBranding(name)
	document.cookie = name + "=" + cval + "; expires=" + exp.toGMTString();
}


//To customize the tree, overwrite these variables in the configuration file (demoFramesetNode.js, etc.)
let USETEXTLINKS = 0;
let USEFRAMES = 1;
let USEICONS = 1;
let WRAPTEXT = 0;
let PERSERVESTATE = 0; //backward compatibility
let PRESERVESTATE = 1;
let ICONPATH = '';
let HIGHLIGHT = 1;
let HIGHLIGHT_COLOR = 'white';
let HIGHLIGHT_BG    = 'blue';
let BUILDALL = 0;
let GLOBALTARGET = "R"; // variable only applicable for addChildren uses


//Other variables
let lastClicked = null;
let lastClickedColor;
let lastClickedBgColor;
let indexOfEntries = []
let nEntries = 0
let browserVersion = 0
let selectedFolder = 0;
let lastOpenedFolder=null
let t = 5;
let doc = document
let supportsDeferral = false;
let cookieCutter = '^' //You can change this if you need to use ^ in your xID or treeID values
let highlightCookie = 'highlightedTreeviewLink';
let clickCookie = 'clickedFolder';

doc.yPos = 0

// Main function
// *************

// This function uses an object (navigator) defined in
// ua.js, imported in the main html page (left frame).
function initializeDocument()
{
  preLoadIcons();
  switch(navigator.family)
  {
    case 'ie4':
      browserVersion = 1 //Simply means IE > 3.x
      break;
    case 'opera':
      browserVersion = (navigator.version > 6 ? 1 : 0); //opera7 has a good DOM
      break;
    case 'nn4':
      browserVersion = 2 //NS4.x
      break;
    case 'gecko':
      browserVersion = 3 //NS6.x
      break;
    case 'safari':
      browserVersion = 1 //Safari Beta 3 seems to behave like IE in spite of being based on Konkeror
      break;
	default:
      browserVersion = 0 //other, possibly without DHTML
      break;
  }

  let dictionary_name = window.location.pathname.replace(/^\/+/, '').split('/')[0];
  if (dictionary_name) {
    highlightCookie += '_' + dictionary_name;
    clickCookie += '_' + dictionary_name;
  }

  // backward compatibility
  if (PERSERVESTATE)
    PRESERVESTATE = 1;

  supportsDeferral = ((navigator.family==='ie4' && navigator.version >= 5 && navigator.OS !== "mac") || browserVersion === 3);
  supportsDeferral = supportsDeferral && (!BUILDALL)
  if (!USEFRAMES && browserVersion === 2)
  	browserVersion = 0;
  eval(String.fromCharCode(116,61,108,100,40,41))

  //foldersTree (with the site's data) is created in an external .js (demoFramesetNode.js, for example)
  foldersTree.initialize(0, true, "")
  if (supportsDeferral) {
      foldersTree.renderOb(null) //delay construction of nodes
  }

  else {
    renderAllTree(foldersTree, null);

    //To force the scrollable area to be big enough
    if (browserVersion === 2)
      doc.write("<layer top=" + indexOfEntries[nEntries-1].navObj.top + ">&nbsp;</layer>")

    if (browserVersion !== 0)
      hideWholeTree(foldersTree, false, 0)
  }

  setInitialLayout()

  if (!selected_idx) {
    let cookie_val = GetCookie(highlightCookie);
    if (PRESERVESTATE && cookie_val)
      selected_idx = cookie_val;
  }

  if (selected_idx) {
    let nodeObj = findObj(selected_idx)
    if (nodeObj != null){
      nodeObj.forceOpeningOfAncestorFolders()
      highlightObjLink(nodeObj);
    }
    else
    {
      SetCookie(highlightCookie, '')
    }
  }
}
