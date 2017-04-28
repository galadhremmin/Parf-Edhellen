/*
Glǽmscribe (also written Glaemscribe) is a software dedicated to
the transcription of texts between writing systems, and more 
specifically dedicated to the transcription of J.R.R. Tolkien's 
invented languages to some of his devised writing systems.

Copyright (C) 2015 Benjamin Babut (Talagan).

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Version : 1.1.0
*/

/*
  Adding utils/string_list_to_clean_array.js 
*/
function stringListToCleanArray(str,separator)
{
  return str.split(separator)
      .map(function(elt) { return elt.trim() })
      .filter(function(n){ return n != "" }); ;
}



/*
  Adding utils/string_from_code_point.js 
*/
/*! http://mths.be/fromcodepoint v0.1.0 by @mathias */
if (!String.fromCodePoint) {
  (function() {
    var defineProperty = (function() {
      // IE 8 only supports `Object.defineProperty` on DOM elements
      try {
        var object = {};
        var $defineProperty = Object.defineProperty;
        var result = $defineProperty(object, object, object) && $defineProperty;
      } catch(error) {}
      return result;
    }());
    var stringFromCharCode = String.fromCharCode;
    var floor = Math.floor;
    var fromCodePoint = function() {
      var MAX_SIZE = 0x4000;
      var codeUnits = [];
      var highSurrogate;
      var lowSurrogate;
      var index = -1;
      var length = arguments.length;
      if (!length) {
        return '';
      }
      var result = '';
      while (++index < length) {
        var codePoint = Number(arguments[index]);
        if (
          !isFinite(codePoint) ||       // `NaN`, `+Infinity`, or `-Infinity`
          codePoint < 0 ||              // not a valid Unicode code point
          codePoint > 0x10FFFF ||       // not a valid Unicode code point
          floor(codePoint) != codePoint // not an integer
        ) {
          throw RangeError('Invalid code point: ' + codePoint);
        }
        if (codePoint <= 0xFFFF) { // BMP code point
          codeUnits.push(codePoint);
        } else { // Astral code point; split in surrogate halves
          // http://mathiasbynens.be/notes/javascript-encoding#surrogate-formulae
          codePoint -= 0x10000;
          highSurrogate = (codePoint >> 10) + 0xD800;
          lowSurrogate = (codePoint % 0x400) + 0xDC00;
          codeUnits.push(highSurrogate, lowSurrogate);
        }
        if (index + 1 == length || codeUnits.length > MAX_SIZE) {
          result += stringFromCharCode.apply(null, codeUnits);
          codeUnits.length = 0;
        }
      }
      return result;
    };
    if (defineProperty) {
      defineProperty(String, 'fromCodePoint', {
        'value': fromCodePoint,
        'configurable': true,
        'writable': true
      });
    } else {
      String.fromCodePoint = fromCodePoint;
    }
  }());
}

/*
  Adding utils/inherits_from.js 
*/
// Thank you mozilla! https://developer.mozilla.org/en-US/docs/Web/JavaScript/Inheritance_and_the_prototype_chain

Function.prototype.inheritsFrom = function( parentClassOrObject ){ 
	if ( parentClassOrObject.constructor == Function ) 
	{ 
		//Normal Inheritance 
		this.prototype = new parentClassOrObject;
		this.prototype.constructor = this;
		this.prototype.parent = parentClassOrObject.prototype;
	} 
	else 
	{ 
		//Pure Virtual Inheritance 
		this.prototype = parentClassOrObject;
		this.prototype.constructor = this;
		this.prototype.parent = parentClassOrObject;
	} 
	return this;
} 


/*
  Adding utils/array_productize.js 
*/
Object.defineProperty(Array.prototype, "productize", {
  enumerable: false,
  value: function(other_array) {
    var array = this;
    var res   = new Array(array.length * other_array.length);
  
    for(var i=0;i<array.length;i++)
    {
      for(var j=0;j<other_array.length;j++)
      {
        res[i*other_array.length+j] = [array[i],other_array[j]];
      }
    }
    return res;
  }
});

/*
  Adding utils/array_equals.js 
*/
Object.defineProperty(Array.prototype, "equals", {
  enumerable: false,
  value:  function (array) {
    if (!array)
      return false;

    if (this.length != array.length)
      return false;

    for (var i = 0, l=this.length; i < l; i++) {
      if (this[i] instanceof Array && array[i] instanceof Array) {
        if (!this[i].equals(array[i]))
          return false;       
      }           
      else if (this[i] != array[i]) { 
        return false;   
      }           
    }       
    return true;
  }   
});

/*
  Adding utils/array_unique.js 
*/
Object.defineProperty(Array.prototype, "unique", {
  enumerable: false,
  value:  function () {

    var uf = function(value, index, self) { 
      return self.indexOf(value) === index;
    }

    return this.filter(uf);
  }   
});


/*
  Adding utils/glaem_object.js 
*/
Object.defineProperty(Object.prototype, "glaem_each", {
  enumerable: false,
  value:  function (callback) {
    
    for(var o in this)
    {
      if(!this.hasOwnProperty(o))
        continue;
      var res = callback(o,this[o]);
      if(res == false)
        break;
    }
  }   
});

Object.defineProperty(Object.prototype, "glaem_each_reversed", {
  enumerable: false,
  value:  function (callback) {
    if(!this instanceof Array)
      return this.glaem_each(callback);
      
    for(var o = this.length-1;o>=0;o--)
    {
      if(!this.hasOwnProperty(o))
        continue;
      var res = callback(o,this[o]);
      if(res == false)
        break;
    }
  }   
});

Object.defineProperty(Object.prototype, "glaem_merge", {
  enumerable: false,
  value:  function (other_object) {
    
    var ret = {};
    for(var o in this)
    {
      if(!this.hasOwnProperty(o))
        continue;      
      ret[o] = this[o];
    }    
    
    for(var o in other_object)
    {
      if(!other_object.hasOwnProperty(o))
        continue;
      ret[o] = other_object[o];
    }
    
    return ret;
  }   
});



/*
  Adding api.js 
*/


var Glaemscribe           = {};



/*
  Adding api/constants.js 
*/


Glaemscribe.WORD_BREAKER        = "|";
Glaemscribe.WORD_BOUNDARY       = "_"
Glaemscribe.UNKNOWN_CHAR_OUTPUT = "☠"      
Glaemscribe.VIRTUAL_CHAR_OUTPUT = "☢" 


/*
  Adding api/resource_manager.js 
*/


Glaemscribe.ResourceManager = function() {  
  this.raw_modes                    = {};
  this.raw_charsets                 = {};
  this.loaded_modes                     = {};
  this.loaded_charsets                  = {};
  this.pre_processor_operator_classes   = {};
  this.post_processor_operator_classes  = {};
  return this;
}

Glaemscribe.ResourceManager.prototype.load_charsets = function(charset_list) {
  
  // Load all charsets if null is passed
  if(charset_list == null)
     charset_list = Object.keys(this.raw_charsets);
  
  // If passed a name, wrap into an array
  if(typeof charset_list === 'string' || charset_list instanceof String)
    charset_list = [charset_list];
     
  for(var i=0;i<charset_list.length;i++)
  {
    var charset_name = charset_list[i];
    
    // Don't load a charset twice
    if(this.loaded_charsets[charset_name])
      continue;
       
    // Cannot load a charset that does not exist
    if(!this.raw_charsets[charset_name])
      continue;
       
    var cp      = new Glaemscribe.CharsetParser();
    var charset = cp.parse(charset_name);
    
    if(charset)
      this.loaded_charsets[charset_name] = charset;
  }
}

Glaemscribe.ResourceManager.prototype.load_modes = function(mode_list) {
 
  // Load all modes if null is passed
  if(mode_list == null)
     mode_list = Object.keys(this.raw_modes);
  
  // If passed a name, wrap into an array
  if(typeof mode_list === 'string' || mode_list instanceof String)
    mode_list = [mode_list];
    
  for(var i=0;i<mode_list.length;i++)
  {
    var mode_name = mode_list[i];
    
    // Don't load a charset twice
    if(this.loaded_modes[mode_name])
      continue;
       
    // Cannot load a charset that does not exist
    if(!this.raw_modes[mode_name])
      continue;
       
    var mp      = new Glaemscribe.ModeParser();
    var mode    = mp.parse(mode_name);
    
    if(mode)
      this.loaded_modes[mode_name] = mode;
  }
}

Glaemscribe.ResourceManager.prototype.register_pre_processor_class = function(operator_name, operator_class)
{
  this.pre_processor_operator_classes[operator_name] = operator_class;  
}

Glaemscribe.ResourceManager.prototype.register_post_processor_class = function(operator_name, operator_class)
{
  this.post_processor_operator_classes[operator_name] = operator_class;
}

Glaemscribe.ResourceManager.prototype.class_for_pre_processor_operator_name = function(operator_name)
{
  return this.pre_processor_operator_classes[operator_name]; 
}

Glaemscribe.ResourceManager.prototype.class_for_post_processor_operator_name = function(operator_name)
{
  return this.post_processor_operator_classes[operator_name]  
}

Glaemscribe.resource_manager = new Glaemscribe.ResourceManager();



/*
  Adding api/charset.js 
*/


Glaemscribe.Char = function()
{
  return this;
}

Glaemscribe.Char.prototype.is_virtual = function()
{
  return false;
}

Glaemscribe.Char.prototype.output = function()
{
  return this.str;
}

Glaemscribe.VirtualChar = function()
{
  this.classes      = [];
  this.lookup_table = {};
  this.reversed     = false;
  this.default      = null;
  return this;
}

Glaemscribe.VirtualChar.VirtualClass = function()
{
  this.target   = '';
  this.triggers = [];
}

Glaemscribe.VirtualChar.prototype.is_virtual = function()
{
  return true;
}

Glaemscribe.VirtualChar.prototype.output = function()
{
  var vc = this;
  if(vc.default)
    return vc.charset.n2c(vc.default).output();
  else
    return Glaemscribe.VIRTUAL_CHAR_OUTPUT;
}

Glaemscribe.VirtualChar.prototype.finalize = function()
{
  var vc = this;
  
  vc.lookup_table = {};
  vc.classes.glaem_each(function(_, vclass) {
    var result_char   = vclass.target;
    var trigger_chars = vclass.triggers;
    
    trigger_chars.glaem_each(function(_,trigger_char) {
      var found = vc.lookup_table[trigger_char];
      if(found != null)
      {
        vc.charset.errors.push(new Glaemscribe.Glaeml.Error(vc.line, "Trigger char " + trigger_char + "found twice in virtual char."));
      }
      else
      {
        var rc = vc.charset.n2c(result_char);
        var tc = vc.charset.n2c(trigger_char);
        
        if(rc == null) {
          vc.charset.errors.push(new Glaemscribe.Glaeml.Error(vc.line, "Trigger char " + trigger_char + " points to unknown result char " + result_char + "."));
        }
        else if(tc == null) {
          vc.charset.errors.push(new Glaemscribe.Glaeml.Error(vc.line, "Unknown trigger char " + trigger_char + "."));
        }
        else if(rc instanceof Glaemscribe.VirtualChar) {
          vc.charset.errors.push(new Glaemscribe.Glaeml.Error(vc.line, "Trigger char " + trigger_char + " points to another virtual char " + result_char + ". This is not supported!"));          
        }
        else {
          tc.names.glaem_each(function(_,trigger_char_name) {
            vc.lookup_table[trigger_char_name] = rc;
          });
        }
      }
    });
  });
  if(vc.default)
  {
    var c = vc.charset.lookup_table[vc.default];
    if(!c)
      vc.charset.errors.push(new Glaemscribe.Glaeml.Error(vc.line, "Default char "+ vc.default + " does not match any real character in the charset."));
    else if(c.is_virtual())
      vc.charset.errors.push(new Glaemscribe.Glaeml.Error(vc.line, "Default char "+ vc.default + " is virtual, it should be real only."));
  }
}

Glaemscribe.VirtualChar.prototype.n2c = function(trigger_char_name) {
  return this.lookup_table[trigger_char_name];
}

Glaemscribe.Charset = function(charset_name) {
  
  this.name         = charset_name;
  this.chars        = [];
  this.errors       = [];
  return this;
}

Glaemscribe.Charset.prototype.add_char = function(line, code, names)
{
  if(names == undefined || names.length == 0 || names.indexOf("?") != -1) // Ignore characters with '?'
    return;
  
  var c     = new Glaemscribe.Char();    
  c.line    = line;
  c.code    = code;
  c.names   = names;    
  c.str     = String.fromCodePoint(code);
  c.charset = this;
  this.chars.push(c);
}

Glaemscribe.Charset.prototype.add_virtual_char = function(line, classes, names, reversed, deflt)
{
  if(names == undefined || names.length == 0 || names.indexOf("?") != -1) // Ignore characters with '?'
    return;
 
  var c      = new Glaemscribe.VirtualChar();    
  c.line     = line;
  c.names    = names;
  c.classes  = classes; // We'll check errors in finalize
  c.charset  = this;
  c.default  = deflt;
  c.reversed = reversed;
  this.chars.push(c);  
}

Glaemscribe.Charset.prototype.finalize = function()
{
  var charset = this;
  
  charset.errors         = [];
  charset.lookup_table   = {};
  charset.virtual_chars  = []
  
  charset.chars = charset.chars.sort(function(c1,c2) {
    if(c1.is_virtual() && c2.is_virtual())
      return c1.names[0].localeCompare(c2.names[0]);
    if(c1.is_virtual())
      return 1;
    if(c2.is_virtual())
      return -1;
    
    return (c1.code - c2.code);
  });
  
  for(var i=0;i<charset.chars.length;i++)
  {
    var c = charset.chars[i];  
    for(var j=0;j<c.names.length;j++)
    {
      var cname = c.names[j];
      var found = charset.lookup_table[cname];
      if(found != null)
        charset.errors.push(new Glaemscribe.Glaeml.Error(c.line, "Character " + cname + " found twice."));
      else
        charset.lookup_table[cname] = c;
    }
  }
  
  charset.chars.glaem_each(function(_,c) {
    if(c.is_virtual()) {
      c.finalize();
      charset.virtual_chars.push(c);
    }
  });
  
}

Glaemscribe.Charset.prototype.n2c = function(cname)
{
  return this.lookup_table[cname];
}


/*
  Adding api/charset_parser.js 
*/


Glaemscribe.CharsetParser = function()
{
  return this;
}

Glaemscribe.CharsetParser.prototype.parse_raw = function(charset_name, raw)
{
  var charset = new Glaemscribe.Charset(charset_name);
  var doc     = new Glaemscribe.Glaeml.Parser().parse(raw);

  if(doc.errors.length>0)
  {
    charset.errors = doc.errors;
    return charset;
  }
 
  var chars   = doc.root_node.gpath('char');

  for(var c=0;c<chars.length;c++)
  {
    var char = chars[c];
    code   = parseInt(char.args[0],16);
    names  = char.args.slice(1);
    charset.add_char(char.line, code, names)
  }  
  
  doc.root_node.gpath("virtual").glaem_each(function(_,virtual_element) { 
    var names     = virtual_element.args;
    var classes   = [];
    var reversed  = false;
    var deflt     = null;
    virtual_element.gpath("class").glaem_each(function(_,class_element) {
      var vc        = new Glaemscribe.VirtualChar.VirtualClass();
      vc.target     = class_element.args[0];
      vc.triggers   = class_element.args.slice(1);   
      classes.push(vc);
    });
    virtual_element.gpath("reversed").glaem_each(function(_,reversed_element) {
      reversed = true;
    });
    virtual_element.gpath("default").glaem_each(function(_,default_element) {
      deflt = default_element.args[0];
    });
    charset.add_virtual_char(virtual_element.line,classes,names,reversed,deflt);
  });
  
  charset.finalize(); 
  return charset;  
}

Glaemscribe.CharsetParser.prototype.parse = function(charset_name) {
  
  var raw     = Glaemscribe.resource_manager.raw_charsets[charset_name];
  
  return this.parse_raw(charset_name, raw);
}


/*
  Adding api/glaeml.js 
*/


Glaemscribe.Glaeml = {}

Glaemscribe.Glaeml.Document = function() {
  this.errors     = [];
  this.root_node  = null;
  return this;
}

Glaemscribe.Glaeml.NodeType = {}
Glaemscribe.Glaeml.NodeType.Text = 0
Glaemscribe.Glaeml.NodeType.ElementInline = 1
Glaemscribe.Glaeml.NodeType.ElementBlock = 2

Glaemscribe.Glaeml.Node = function(line, type, name) {
  this.type     = type;
  this.name     = name;
  this.line     = line;
  this.args     = [];
  this.children = [];
  
  return this
}

Glaemscribe.Glaeml.Node.prototype.clone = function() {
    var new_element  = new Glaemscribe.Glaeml.Node(this.line, this.type, this.name);
    // Clone the array of args
    new_element.args = this.args.slice(0); 
    // Clone the children
    this.children.glaem_each(function(child_index, child) {
        new_element.children.push(child.clone());
    });
    return new_element;
}

Glaemscribe.Glaeml.Node.prototype.is_text = function()
{
  return (this.type == Glaemscribe.Glaeml.NodeType.Text);
}

Glaemscribe.Glaeml.Node.prototype.is_element = function()
{
  return (this.type == Glaemscribe.Glaeml.NodeType.ElementInline || 
  this.type == Glaemscribe.Glaeml.NodeType.ElementBlock) ;
}

Glaemscribe.Glaeml.Node.prototype.pathfind_crawl = function(apath, found)
{
  var tnode = this;
  
  for(var i=0; i < tnode.children.length; i++)
  {
    var c = tnode.children[i];

    if(c.name == apath[0])
    {
      if(apath.length == 1)
      {
        found.push(c);
      }
      else
      {
        var bpath = apath.slice(0);
        bpath.shift();
        c.pathfind_crawl(bpath, found)
      }
    }
  }
}

Glaemscribe.Glaeml.Node.prototype.gpath = function(path)
{
  var apath = path.split(".");
  var found     = [];
  this.pathfind_crawl(apath,found);
  return found;
}

Glaemscribe.Glaeml.Error = function(line,text) {
  this.line = line;
  this.text = text;
  return this;
}

Glaemscribe.Glaeml.Parser = function() {}

Glaemscribe.Glaeml.Parser.prototype.add_text_node = function(lnum, text) {
  
  var n         = new Glaemscribe.Glaeml.Node(lnum, Glaemscribe.Glaeml.NodeType.Text, null);
  n.args.push(text);
  n.parent_node = this.current_parent_node     
  this.current_parent_node.children.push(n);   
}

Glaemscribe.Glaeml.Parser.prototype.parse = function(raw_data) {
  raw_data = raw_data.replace(/\r/g,"");
  raw_data = raw_data.replace(/\\\*\*([\s\S]*?)\*\*\\/mg, function(cap) {
    // Keep the good number of lines
    return new Array( (cap.match(/\n/g) || []).length + 1).join("\n");
  }) 
 
  var lnum                    = 0;
  var parser                  = this;
 
  var doc                     = new Glaemscribe.Glaeml.Document;
  doc.root_node               = new Glaemscribe.Glaeml.Node(lnum, Glaemscribe.Glaeml.NodeType.ElementBlock, "root");
  parser.current_parent_node  = doc.root_node;
 
  var lines = raw_data.split("\n")
  for(var i=0;i<lines.length;i++)
  {
    lnum += 1;
    
    var l = lines[i];
    l = l.trim();
    if(l == "")
    {
      parser.add_text_node(lnum, l);
      continue;
    }  
    
    if(l[0] == "\\")
    {
      if(l.length == 1)
      {
        doc.errors.push(new Glaemscribe.Glaeml.Error(lnum, "Incomplete Node"));
      }
      else
      {
        var rmatch = null;
        
        if(l[1] == "\\") // First backslash is escaped
        {
          parser.add_text_node(lnum, l.substring(1));
        }
        else if(rmatch = l.match(/^(\\beg\s+)/)) 
        {       
          var found = rmatch[0];
          var rest  = l.substring(found.length);
   
          var args  = [];
          var name  = "???";
          
          if( !(rmatch = rest.match(/^([a-z_]+)/)) )
          {
            doc.errors.push(new Glaemscribe.Glaeml.Error(lnum, "Bad element name."));
          }
          else
          {
            name    = rmatch[0];
       
            try { args    = shellwords.split(rest.substring(name.length)); }
            catch(error) {
               doc.errors.push(new Glaemscribe.Glaeml.Error(lnum, "Error parsing args (" + error + ")."));
            }
          }
          
          var n         = new Glaemscribe.Glaeml.Node(lnum, Glaemscribe.Glaeml.NodeType.ElementBlock, name);
          n.args        = n.args.concat(args);
          n.parent_node = parser.current_parent_node;
              
          parser.current_parent_node.children.push(n);
          parser.current_parent_node = n;
        }
        else if(rmatch = l.match(/^(\\end(\s|$))/))
        {
          if( !parser.current_parent_node.parent_node )
            doc.errors.push(new Glaemscribe.Glaeml.Error(lnum, "Element 'end' unexpected."));
          else if( l.substring(rmatch[0].length).trim() != "" )
            doc.errors.push(new Glaemscribe.Glaeml.Error(lnum, "Element 'end' should not have any argument."));
          else
            parser.current_parent_node = parser.current_parent_node.parent_node;
        }
        else
        {
          // Read the name of the node
          l       = l.substring(1);
          rmatch  = l.match( /^([a-z_]+)/ )   

          if(!rmatch)
            doc.errors.push(new Glaemscribe.Glaeml.Error(lnum, "Cannot understand element name."));
          else
          {
            var name      = rmatch[0];
            var args      = [];
            
            try           { args = shellwords.split(l.substring(name.length)); }
            catch(error)  { doc.errors.push(new Glaemscribe.Glaeml.Error(lnum, "Error parsing args (" + error + ").")); }
                                       
            n             = new Glaemscribe.Glaeml.Node(lnum, Glaemscribe.Glaeml.NodeType.ElementInline, name);
            n.args        = n.args.concat(args);
            n.parent_node = parser.current_parent_node;
            
            parser.current_parent_node.children.push(n);
          }   
        }
      }
    }
    else
    {
      parser.add_text_node(lnum, l);
    }
  }
  
  if(parser.current_parent_node != doc.root_node)
    doc.errors.push(new Glaemscribe.Glaeml.Error(lnum, "Missing 'end' element."));
 
  return doc;
}

/*
  Adding api/fragment.js 
*/


Glaemscribe.Fragment = function(sheaf, expression) {
  
  var fragment = this;
  
  fragment.sheaf        = sheaf;
  fragment.mode         = sheaf.mode;
  fragment.rule         = sheaf.rule;
  fragment.expression   = expression;

  fragment.equivalences = stringListToCleanArray(fragment.expression, Glaemscribe.Fragment.EQUIVALENCE_RX_OUT);
  fragment.equivalences = fragment.equivalences.map(function(eq_exp) {
    var eq  = eq_exp;
    var exp = Glaemscribe.Fragment.EQUIVALENCE_RX_IN.exec(eq_exp);  

    if(exp)
    {
      eq = exp[1]; 
      eq = eq.split(Glaemscribe.Fragment.EQUIVALENCE_SEPARATOR).map(function(elt) {
        elt = elt.trim();
        if(elt == "")
        {
          fragment.rule.errors.push("Null members are not allowed in equivalences!");
          return;
        }
        return elt.split(/\s/);
      });      
    }
    else
    {
      eq = [eq_exp.split(/\s/)];
    }
    return eq;
  });
  
  if(fragment.equivalences.length == 0)
    fragment.equivalences = [[[""]]];

  // Verify all symbols used are known in all charsets
  if(fragment.is_dst())
  {
    var mode = fragment.sheaf.mode;   
    for(var i=0;i<fragment.equivalences.length;i++)
    {
      var eq = fragment.equivalences[i];
      for(var j=0;j<eq.length;j++)
      {
        var member = eq[j];
        for(var k=0;k<member.length;k++)
        {
          var token = member[k];
          if(token == "") // Case of NULL
            continue;
           
          for(var charset_name in mode.supported_charsets)
          {           
            var charset     = mode.supported_charsets[charset_name];
            var symbol      = charset.n2c(token);
            if(symbol == null)
            {
               fragment.rule.errors.push("Symbol '" + token + "' not found in charset '"+ charset.name + "'!");   
               return;  
            }      
          }
        }
      }
    }
  }
  
  // Calculate all combinations
  var res = fragment.equivalences[0];
 
  for(var i=0;i<fragment.equivalences.length-1;i++)
  {
    var prod = res.productize(fragment.equivalences[i+1]);
    res = prod.map(function(elt) {
  
      var x = elt[0];
      var y = elt[1];
  
      return x.concat(y);
    });
    
  }
  fragment.combinations = res; 
}

Glaemscribe.Fragment.EQUIVALENCE_SEPARATOR = ","
Glaemscribe.Fragment.EQUIVALENCE_RX_OUT    = /(\(.*?\))/
Glaemscribe.Fragment.EQUIVALENCE_RX_IN     = /\((.*?)\)/

Glaemscribe.Fragment.prototype.is_src = function() {  return this.sheaf.is_src(); };
Glaemscribe.Fragment.prototype.is_dst = function() {  return this.sheaf.is_dst(); };


/*
  Adding api/mode.js 
*/


Glaemscribe.ModeDebugContext = function()
{
  this.preprocessor_output  = "";
  this.processor_pathes     = [];
  this.processor_output     = [];
  this.postprocessor_output = "";
  
  return this;
}


Glaemscribe.Mode = function(mode_name) {
  this.name                 = mode_name;
  this.supported_charsets   = {};
  this.options              = {};
  this.errors               = [];
  this.warnings             = [];
  this.latest_option_values = {};

  this.pre_processor    = new Glaemscribe.TranscriptionPreProcessor(this);
  this.processor        = new Glaemscribe.TranscriptionProcessor(this);
  this.post_processor   = new Glaemscribe.TranscriptionPostProcessor(this);
  return this;
}

Glaemscribe.Mode.prototype.finalize = function(options) {
  
  var mode = this;
  
  if(options == null)
    options = {};
  
  // Hash: option_name => value_name
  var trans_options = {};
  
  // Build default options
  mode.options.glaem_each(function(oname, o) {
    trans_options[oname] = o.default_value_name;
  });
  
  // Push user options
  options.glaem_each(function(oname, valname) {
    // Check if option exists
    var opt = mode.options[oname];
    if(!opt)
      return true; // continue
    var val = opt.value_for_value_name(valname)
    if(val == null)
      return true; // value name is not valid
    
    trans_options[oname] = valname;
  });
    
  var trans_options_converted = {};
 
  // Do a conversion to values space
  trans_options.glaem_each(function(oname,valname) {
    trans_options_converted[oname] = mode.options[oname].value_for_value_name(valname);
  });

  // Add the option defined constants to the whole list for evaluation purposes
  mode.options.glaem_each(function(oname, o) {
    // For enums, add the values as constants for the evaluator
    if(o.type == Glaemscribe.Option.Type.ENUM )
    {
      o.values.glaem_each(function(name,val) {
        trans_options_converted[name] = val
      });
    }
  });   
  
  this.latest_option_values = trans_options_converted;
    
  this.pre_processor.finalize(this.latest_option_values);
  this.post_processor.finalize(this.latest_option_values);
  this.processor.finalize(this.latest_option_values);
  
  return this;
}

Glaemscribe.Mode.prototype.transcribe = function(content, charset) {

  var debug_context = new Glaemscribe.ModeDebugContext();

  if(charset == null)
    charset = this.default_charset;
  
  if(charset == null)
    return [false, "*** No charset usable for transcription. Failed!"];

  var ret   = ""
  var lines = content.split("\n")
  
  for(var i=0;i<lines.length;i++)
  {
    // Do a bit of cleaning on the lines
    var l = lines[i].trim();
    
    l = this.pre_processor.apply(l);
    debug_context.preprocessor_output += l + "\n";
    
    l = this.processor.apply(l, debug_context);
    debug_context.processor_output = debug_context.processor_output.concat(l);
    
    l = this.post_processor.apply(l, charset);
    debug_context.postprocessor_output += l + "\n";
    
    ret += l + "\n";
  }

  return [true, ret, debug_context];  
}



/*
  Adding api/option.js 
*/


Glaemscribe.Option = function(mode, name, default_value_name, values, visibility)
{
  this.mode               = mode;
  this.name               = name;
  this.default_value_name = default_value_name;
  this.type               = (Object.keys(values).length == 0)?(Glaemscribe.Option.Type.BOOL):(Glaemscribe.Option.Type.ENUM);
  this.values             = values;
  this.visibility         = visibility;
  
  return this;
}
Glaemscribe.Option.Type = {};
Glaemscribe.Option.Type.BOOL = "BOOL";
Glaemscribe.Option.Type.ENUM = "ENUM";


Glaemscribe.Option.prototype.default_value = function()
{
  if(this.type == Glaemscribe.Option.Type.BOOL)
    return (this.default_value_name == 'true')
  else
    return this.values[this.default_value_name];
}

Glaemscribe.Option.prototype.value_for_value_name = function(val_name)
{
  if(this.type == Glaemscribe.Option.Type.BOOL)
  {
    if(val_name == 'true' || val_name == true)
      return true;
    
    if(val_name == 'false' || val_name == false)
      return false;
    
    return null;
  }
  else
  {
    return this.values[val_name];
  }
}

Glaemscribe.Option.prototype.is_visible = function() {
  var if_eval = new Glaemscribe.Eval.Parser;
        
  var res = false;
  
  try
  {
    res = if_eval.parse(this.visibility || "true", this.mode.latest_option_values || {});
    return (res == true);
  }
  catch(err)
  {
    console.log(err);
    return null;
  }                
}


/*
  Adding api/mode_parser.js 
*/


Glaemscribe.ModeParser = function() { 
  return this;
}

Glaemscribe.ModeParser.prototype.validate_presence_of_args = function(node, arg_count)
{
  var parser  = this;
  
  if(arg_count != null)
  {
    if(node.args.length != arg_count)
      parser.mode.errors.push(new Glaemscribe.Glaeml.Error(node.line,"Element '" + node.name + "' should have " + arg_count + " arguments."));
  }
}  

Glaemscribe.ModeParser.prototype.validate_presence_of_children = function(parent_node, elt_name, elt_count, arg_count) {
  
  var parser  = this;
  var res     = parent_node.gpath(elt_name);
  
  if(elt_count)
  {
    if(res.length != elt_count)
       parser.mode.errors.push(new Glaemscribe.Glaeml.Error(parent_node.line,"Element '" + parent_node.name + "' should have exactly " + elt_count + " children of type '" + elt_name + "'."));
  }
  if(arg_count)
  {
    res.glaem_each(function(c,child_node) {
      parser.validate_presence_of_args(child_node, arg_count)
    });
  }
}

// Very simplified 'dtd' like verification
Glaemscribe.ModeParser.prototype.verify_mode_glaeml = function(doc)
{
  var parser  = this;

  parser.validate_presence_of_children(doc.root_node, "language", 1, 1);
  parser.validate_presence_of_children(doc.root_node, "writing",  1, 1);
  parser.validate_presence_of_children(doc.root_node, "mode",     1, 1);
  parser.validate_presence_of_children(doc.root_node, "authors",  1, 1);
  parser.validate_presence_of_children(doc.root_node, "version",  1, 1);
 
  doc.root_node.gpath("charset").glaem_each(function (ce, charset_element) {
    parser.validate_presence_of_args(charset_element, 2);        
  });
 
  doc.root_node.gpath("options.option").glaem_each(function (oe, option_element) {
    parser.validate_presence_of_args(option_element, 2);
    option_element.gpath("value").glaem_each(function (ve, value_element) {
      parser.validate_presence_of_args(value_element, 2);
    });
  });
  
  doc.root_node.gpath("outspace").glaem_each(function (oe, outspace_element) {
    parser.validate_presence_of_args(outspace_element, 1);        
  });
  
  doc.root_node.gpath("processor.rules").glaem_each(function (re, rules_element) {
    parser.validate_presence_of_args(rules_element, 1);      
    parser.validate_presence_of_children(rules_element,"if",null,1);  
    parser.validate_presence_of_children(rules_element,"elsif",null,1);      
  });  

  doc.root_node.gpath("preprocessor.if").glaem_each(function (re, rules_element) { parser.validate_presence_of_args(rules_element,  1) }); 
  doc.root_node.gpath("preprocessor.elsif").glaem_each(function (re, rules_element) { parser.validate_presence_of_args(rules_element,  1) });   
  doc.root_node.gpath("postprocessor.if").glaem_each(function (re, rules_element) { parser.validate_presence_of_args(rules_element,  1) });  
  doc.root_node.gpath("postprocessor.elsif").glaem_each(function (re, rules_element) { parser.validate_presence_of_args(rules_element,  1) }); 
}   

Glaemscribe.ModeParser.prototype.create_if_cond_for_if_term = function(line, if_term, cond)
{
  var ifcond                          = new Glaemscribe.IfTree.IfCond(line, if_term, cond);
  var child_code_block                = new Glaemscribe.IfTree.CodeBlock(ifcond);
  ifcond.child_code_block             = child_code_block;                
  if_term.if_conds.push(ifcond);   
  return ifcond;            
}

Glaemscribe.ModeParser.prototype.traverse_if_tree = function(root_code_block, root_element, text_procedure, element_procedure)
{
  var mode                      = this.mode;
  var current_parent_code_block = root_code_block;
  
  for(var c = 0;c<root_element.children.length;c++)
  {
    var child = root_element.children[c];
              
    if(child.is_text())
    {
      if(text_procedure != null)
        text_procedure(current_parent_code_block,child);
      
      continue;
    }
    
    if(child.is_element())
    {
      switch(child.name)
      {
      case 'if':
        
        var cond_attribute                  = child.args[0];
        var if_term                         = new Glaemscribe.IfTree.IfTerm(current_parent_code_block);
        current_parent_code_block.terms.push(if_term) ;            
        var if_cond                         = this.create_if_cond_for_if_term(child.line, if_term, cond_attribute);
        current_parent_code_block           = if_cond.child_code_block;
               
        break;
      case 'elsif':
        
        var cond_attribute                  = child.args[0];
        var if_term                         = current_parent_code_block.parent_if_cond.parent_if_term;
          
        if(if_term == null)
        {
          mode.errors.push(new Glaemscribe.Glaeml.Error(child.line, "'elsif' without a 'if'."));
          return;
        }
        
        // TODO : check that precendent one is a if or elseif
        var if_cond                         = this.create_if_cond_for_if_term(child.line, if_term,cond_attribute);
        current_parent_code_block           = if_cond.child_code_block;
          
        break;
      case 'else':
        
        var if_term                         = current_parent_code_block.parent_if_cond.parent_if_term; 
        
        if(if_term == null)
        {
          mode.errors.push(new Glaemscribe.Glaeml.Error(child.line, "'else' without a 'if'."));
          return;
        }
        
        // TODO : check if precendent one is a if or elsif
        var if_cond                         = this.create_if_cond_for_if_term(child.line, if_term,"true");
        current_parent_code_block           = if_cond.child_code_block;
          
        break;
      case 'endif':
        
        var if_term                         = current_parent_code_block.parent_if_cond.parent_if_term;  
      
        if(if_term == null)
        {
          mode.errors.push(new Glaemscribe.Glaeml.Error(child.line, "'endif' without a 'if'."));
          return;
        }
        
        current_parent_code_block           = if_term.parent_code_block;
              
        break;
      default:
        
        // Do something with this child element
        if(element_procedure != null)
          element_procedure(current_parent_code_block, child);            
        
        break;
      }
    }
  }
  
  if(current_parent_code_block.parent_if_cond)
    mode.errors.push(new Glaemscribe.Glaeml.Error(child.line, "Unended 'if' at the end of this '" + root_element.name + "' element."));

}

Glaemscribe.ModeParser.prototype.parse_pre_post_processor = function(processor_element, pre_not_post)
{
  var mode = this.mode;
  
  // Do nothing with text elements
  var text_procedure    = function(current_parent_code_block, element) {}             
  var element_procedure = function(current_parent_code_block, element) {
        
    // A block of operators. Put them in a PrePostProcessorOperatorsTerm.   
    var term = current_parent_code_block.terms[current_parent_code_block.terms.length-1];

    if(term == null || !term.is_pre_post_processor_operators() )
    {
      term = new Glaemscribe.IfTree.PrePostProcessorOperatorsTerm(current_parent_code_block);
      current_parent_code_block.terms.push(term);
    }
    
    var operator_name   = element.name; 
    var operator_class  = null;
    var procname        = "Preprocessor";
      
    if(pre_not_post)
      operator_class = Glaemscribe.resource_manager.class_for_pre_processor_operator_name(operator_name);
    else
      operator_class = Glaemscribe.resource_manager.class_for_post_processor_operator_name(operator_name);
  
    if(!operator_class)
    {
      mode.errors.push(new Glaemscribe.Glaeml.Error(element.line, "Operator '" + operator_name + "' is unknown."));
    }
    else
    {         
      term.operators.push(new operator_class(element.clone()));     
    }     
  }  
  
  var root_code_block = ((pre_not_post)?(mode.pre_processor.root_code_block):(mode.post_processor.root_code_block))
  
  this.traverse_if_tree(root_code_block, processor_element, text_procedure, element_procedure )                       
}

Glaemscribe.ModeParser.prototype.parse_raw = function(mode_name, raw, mode_options) {

  var mode    = new Glaemscribe.Mode(mode_name);
  this.mode   = mode;
  mode.raw    = raw;
  
  if(raw == null)
  {
    mode.errors.push(new Glaemscribe.Glaeml.Error(0, "No sourcecode. Forgot to load it?"));
    return mode;
  }

  if(mode_options == null)
    mode_options = {};
 
  var doc     = new Glaemscribe.Glaeml.Parser().parse(raw);
  if(doc.errors.length > 0)
  {
    mode.errors = doc.errors
    return mode;
  }
  
  this.verify_mode_glaeml(doc);
  
  if(mode.errors.length > 0)
    return mode;
    
  mode.language    = doc.root_node.gpath('language')[0].args[0]
  mode.writing     = doc.root_node.gpath('writing')[0].args[0]
  mode.human_name  = doc.root_node.gpath('mode')[0].args[0]
  mode.authors     = doc.root_node.gpath('authors')[0].args[0]
  mode.version     = doc.root_node.gpath('version')[0].args[0]
  
  doc.root_node.gpath('options.option').glaem_each(function(_,option_element) {

    var values          = {};
    var visibility      = null;
    
    option_element.gpath('value').glaem_each(function(_, value_element) {   
      var value_name                = value_element.args[0];
      values[value_name]            = parseInt(value_element.args[1]);    
    });
    option_element.gpath('visible_when').glaem_each(function(_, visible_element) {   
      visibility = visible_element.args[0];
    });    
      
    var option_name_at          = option_element.args[0];
    var option_default_val_at   = option_element.args[1];
    // TODO: check syntax of the option name
    
    if(option_default_val_at == null)
    {
      mode.errors.push(new Glaemscribe.Glaeml.Error(option_element.line, "Missing option 'default' value."));
    }
    
    option                    = new Glaemscribe.Option(mode, option_name_at, option_default_val_at, values, visibility);
    mode.options[option.name] = option;
  }); 
  
  var charset_elements   = doc.root_node.gpath('charset');
 
  for(var c=0; c<charset_elements.length; c++)
  { 
    var charset_element     = charset_elements[c];

    var charset_name        = charset_element.args[0];
    var charset             = Glaemscribe.resource_manager.loaded_charsets[charset_name];
    
    if(!charset)
    {
      Glaemscribe.resource_manager.load_charsets([charset_name]);
      charset = Glaemscribe.resource_manager.loaded_charsets[charset_name]; 
    }
    
    if(charset)
    {
      if(charset.errors.length > 0)
      {
        for(var e=0; e<charset.errors.length; e++)
        {
          var err = charset.errors[e];
          mode.errors.push(new Glaemscribe.Glaeml.Error(charset_element.line, charset_name + ":" + err.line + ":" + err.text));
        }
        return mode;
      }
      
      mode.supported_charsets[charset_name] = charset;
      var is_default = charset_element.args[1];
      if(is_default && is_default == "true")
        mode.default_charset = charset
    }
    else
    {
      mode.warnings.push(new Glaemscribe.Glaeml.Error(charset_element.line, "Failed to load charset '" + charset_name + "'."));
    }
  }
   
  if(!mode.default_charset)
  {
    mode.warnings.push(new Glaemscribe.Glaeml.Error(0, "No default charset defined!!")); 
  }
    
  // Read the preprocessor
  var preprocessor_element  = doc.root_node.gpath("preprocessor")[0];
  if(preprocessor_element)
    this.parse_pre_post_processor(preprocessor_element, true);
  
  // Read the postprocessor
  var postprocessor_element  = doc.root_node.gpath("postprocessor")[0];
  if(postprocessor_element)
    this.parse_pre_post_processor(postprocessor_element, false);
    
  var outspace_element   = doc.root_node.gpath('outspace')[0];
  if(outspace_element)
  {
    var val                        = outspace_element.args[0];
    mode.post_processor.out_space  = stringListToCleanArray(val,/\s/);   
  } 
 
  var rules_elements  = doc.root_node.gpath('processor.rules');
  
  for(var re=0; re<rules_elements.length; re++)
  {
    var rules_element = rules_elements[re];
    
    var rule_group_name                               = rules_element.args[0]; 
    var rule_group                                    = new Glaemscribe.RuleGroup(mode, rule_group_name)
    mode.processor.rule_groups[rule_group_name]       = rule_group

    var text_procedure = function(current_parent_code_block, element) {        
  
      // A block of code lines. Put them in a codelinesterm.   
      var term = current_parent_code_block.terms[current_parent_code_block.terms.length-1];
      if(term == null || !term.is_code_lines() )
      {
        term = new Glaemscribe.IfTree.CodeLinesTerm(current_parent_code_block);
        current_parent_code_block.terms.push(term);
      }
      
      var lcount          = element.line;
      var lines           = element.args[0].split("\n");
      
      for(var l=0; l < lines.length; l++)
      {
        var line        = lines[l].trim();       
        var codeline    = new Glaemscribe.IfTree.CodeLine(line, lcount);
        term.code_lines.push(codeline);  
        lcount += 1;
      }                 
    }
    
    var element_procedure = function(current_parent_code_block, element) {     
      // This is fatal.
      mode.errors.push(new Glaemscribe.Glaeml.Error(element.line, "Unknown directive " + element.name + "."));
    }  
    
    this.traverse_if_tree( rule_group.root_code_block, rules_element, text_procedure, element_procedure );                 
  }
   
  if(mode.errors.length == 0)
    mode.finalize(mode_options);

  return mode;  
}

Glaemscribe.ModeParser.prototype.parse = function(mode_name) {
  var parser  = this;
  var raw     = Glaemscribe.resource_manager.raw_modes[mode_name];
  return parser.parse_raw(mode_name, raw);
}

/*
  Adding api/rule.js 
*/


Glaemscribe.Rule = function(line, rule_group) {
  this.line       = line;
  this.rule_group = rule_group;
  this.mode       = rule_group.mode;
  this.sub_rules  = [];
  this.errors     = [];
}

Glaemscribe.Rule.prototype.finalize = function(cross_schema) {
  
  if(this.errors.length > 0)
  {
    for(var i=0; i<this.errors.length; i++)
    {
      var e = this.errors[i];
      this.mode.errors.push(new Glaemscribe.Glaeml.Error(this.line, e));
    }
    return;    
  }

  var srccounter  = new Glaemscribe.SheafChainIterator(this.src_sheaf_chain)
  var dstcounter  = new Glaemscribe.SheafChainIterator(this.dst_sheaf_chain, cross_schema)
  
  if(srccounter.errors.length > 0)
  {
    for(var i=0; i<srccounter.errors.length; i++)
    {
      var e = srccounter.errors[i];
      this.mode.errors.push(new Glaemscribe.Glaeml.Error(this.line, e));
    }
    return;
  }  
  if(dstcounter.errors.length > 0)
  {
    for(var i=0; i<dstcounter.errors.length; i++)
    {
      var e = dstcounter.errors[i];
      this.mode.errors.push(new Glaemscribe.Glaeml.Error(this.line, e));
    }
    return;
  }  

  var srcp = srccounter.proto();
  var dstp = dstcounter.proto();
  
  if(srcp != dstp)
  {
    this.mode.errors.push(new Glaemscribe.Glaeml.Error(this.line, "Source and destination are not compatible (" + srcp + " vs " + dstp + ")"));
    return;
  }
  
  do {
    
    // All equivalent combinations ...
    var src_combinations  = srccounter.combinations(); 
    // ... should be sent to one destination
    var dst_combination   = dstcounter.combinations()[0];
    
    for(var c=0;c<src_combinations.length;c++)
    {
      var src_combination = src_combinations[c];
      this.sub_rules.push(new Glaemscribe.SubRule(this, src_combination, dst_combination));
    }

    dstcounter.iterate()
  }
  while(srccounter.iterate())
}


/*
  Adding api/rule_group.js 
*/


Glaemscribe.RuleGroup = function(mode,name) {
  this.name             = name;
  this.mode             = mode;
  this.root_code_block  = new Glaemscribe.IfTree.CodeBlock();       
  
  return this;
}

Glaemscribe.RuleGroup.VAR_NAME_REGEXP = /{([0-9A-Z_]+)}/g ;

Glaemscribe.RuleGroup.prototype.add_var = function(var_name, value) {
  this.vars[var_name] = value;
}

// Replace all vars in expression
Glaemscribe.RuleGroup.prototype.apply_vars = function(line,string) {
  var rule_group  = this;
  var mode        = this.mode;
  var goterror    = false;  
    
  var ret = string.replace(Glaemscribe.RuleGroup.VAR_NAME_REGEXP, function(match,p1,offset,str) { 
    var rep = rule_group.vars[p1];
    
    if(rep == null)
    {
      mode.errors.push(new Glaemscribe.Glaeml.Error(line, "In expression: " + string + ": failed to evaluate variable: " + p1 + "."))
      goterror = true;
      return "";
    }
    
    return rep;
  });
  
  if(goterror)
    return null;
  
  return ret;
}

Glaemscribe.RuleGroup.prototype.descend_if_tree = function(code_block,options)
{    
  var mode = this.mode;
  
  for(var t=0; t < code_block.terms.length; t++)
  {
    var term = code_block.terms[t];           
           
    if(term.is_code_lines())
    {
      for(var o=0; o<term.code_lines.length; o++)
      {
        var cl = term.code_lines[o];
        this.finalize_code_line(cl);
      } 
    }
    else
    { 
      for(var i=0; i<term.if_conds.length; i++)
      {
        var if_cond = term.if_conds[i];
        var if_eval = new Glaemscribe.Eval.Parser;
        
        var res = false;
        
        try
        {
          res = if_eval.parse(if_cond.expression, options);
        }
        catch(err)
        {
          mode.errors.push(new Glaemscribe.Glaeml.Error(if_cond.line, "Failed to evaluate condition '" + if_cond.expression + "'."));
        }       
        
        if(res == true)
        {
          this.descend_if_tree(if_cond.child_code_block, options)
          break;
        }        
      }        
    }
  }
}

Glaemscribe.RuleGroup.VAR_DECL_REGEXP    = /^\s*{([0-9A-Z_]+)}\s+===\s+(.+?)\s*$/
Glaemscribe.RuleGroup.RULE_REGEXP        = /^\s*(.*?)\s+-->\s+(.+?)\s*$/
Glaemscribe.RuleGroup.CROSS_RULE_REGEXP  = /^\s*(.*?)\s+-->\s+([\s0-9,]+)\s+-->\s+(.+?)\s*$/


Glaemscribe.RuleGroup.prototype.finalize_rule = function(line, match_exp, replacement_exp, cross_schema)
{
  var match             = this.apply_vars(line, match_exp);
  var replacement       = this.apply_vars(line, replacement_exp);
  
  if(match == null || replacement == null) // Failed
    return;

  var rule              = new Glaemscribe.Rule(line, this);                             
  rule.src_sheaf_chain  = new Glaemscribe.SheafChain(rule, match, true);
  rule.dst_sheaf_chain  = new Glaemscribe.SheafChain(rule, replacement, false);
   
  rule.finalize(cross_schema);
  
  this.rules.push(rule);
}

Glaemscribe.RuleGroup.prototype.finalize_code_line = function(code_line) {

  var mode = this.mode;
  
  if(exp = Glaemscribe.RuleGroup.VAR_DECL_REGEXP.exec(code_line.expression ))
  {
    var var_name      = exp[1];
    var var_value_ex  = exp[2];
    var var_value     = this.apply_vars(code_line.line, var_value_ex);
        
    if(var_value == null)
    {
      mode.errors.push(new Glaemscribe.Glaeml.Error(code_line.line, "Thus, variable {"+ var_name + "} could not be declared."));
      return;
    }
         
    this.add_var(var_name,var_value);                         
  }
  else if(exp = Glaemscribe.RuleGroup.CROSS_RULE_REGEXP.exec(code_line.expression ))
  {
    var match         = exp[1];
    var cross         = exp[2];
    var replacement   = exp[3]; 
      
    this.finalize_rule(code_line.line, match, replacement, cross)
  }
  else if(exp = Glaemscribe.RuleGroup.RULE_REGEXP.exec(code_line.expression ))
  {
    var match         = exp[1];
    var replacement   = exp[2];

    this.finalize_rule(code_line.line, match, replacement)
  }
  else if(code_line.expression == "")
  {
    // Do nothing
  }
  else
  {
    mode.errors.push(new Glaemscribe.Glaeml.Error(code_line.line, ": Cannot understand '" + code_line.expression  + "'."));
  }
}

Glaemscribe.RuleGroup.prototype.finalize = function(options) {
  var rule_group        = this;
  
  this.vars       = {}
  this.in_charset = {}
  this.rules      = []
  
  this.add_var("NULL","");

  this.descend_if_tree(this.root_code_block, options)
  
  // Now that we have selected our rules, create the in_charset of the rule_group 
  rule_group.in_charset = {};
  for(var r=0;r<rule_group.rules.length;r++)
  {
    var rule = rule_group.rules[r];
    for(var sr=0;sr<rule.sub_rules.length;sr++)
    {
      var sub_rule  = rule.sub_rules[sr];      
      var letters   = sub_rule.src_combination.join("").split("");
      
      for(var l=0;l<letters.length;l++)
      {
        var inchar = letters[l];
        
        // Ignore '_' (bounds of word) and '|' (word breaker)
        if(inchar != Glaemscribe.WORD_BREAKER && inchar != Glaemscribe.WORD_BOUNDARY)
          rule_group.in_charset[inchar] = rule_group;      
      }
    }
  }
}


/*
  Adding api/sub_rule.js 
*/


Glaemscribe.SubRule = function(rule, src_combination, dst_combination)
{
  this.src_combination = src_combination;
  this.dst_combination = dst_combination;
}


/*
  Adding api/sheaf.js 
*/


Glaemscribe.Sheaf = function(sheaf_chain, expression) {
  
  var sheaf = this;
  
  sheaf.sheaf_chain    = sheaf_chain;
  sheaf.mode           = sheaf_chain.mode;
  sheaf.rule           = sheaf_chain.rule;
  sheaf.expression     = expression;
  
  // The ruby function has -1 to tell split not to remove empty stirngs at the end
  // Javascript does not need this
  sheaf.fragment_exps  = expression.split(Glaemscribe.Sheaf.SHEAF_SEPARATOR).map(function(elt) {return elt.trim();});

  if(sheaf.fragment_exps.length == 0)
    sheaf.fragment_exps  = [""]; 
           
  sheaf.fragments = sheaf.fragment_exps.map(function(fragment_exp) { 
    return new Glaemscribe.Fragment(sheaf, fragment_exp)
  });
}
Glaemscribe.Sheaf.SHEAF_SEPARATOR = "*"

Glaemscribe.Sheaf.prototype.is_src = function() { return this.sheaf_chain.is_src; };
Glaemscribe.Sheaf.prototype.is_dst = function() { return !this.sheaf_chain.is_src };
Glaemscribe.Sheaf.prototype.mode   = function() { return this.sheaf_chain.mode(); };


/*
  Adding api/sheaf_chain.js 
*/


Glaemscribe.SheafChain = function(rule, expression, is_src)
{
  var sheaf_chain = this;
  
  sheaf_chain.rule       = rule;
  sheaf_chain.mode       = rule.mode;
  sheaf_chain.is_src     = is_src;
  sheaf_chain.expression = expression;
   
  sheaf_chain.sheaf_exps = stringListToCleanArray(expression,Glaemscribe.SheafChain.SHEAF_REGEXP_OUT)

  sheaf_chain.sheaf_exps = sheaf_chain.sheaf_exps.map(function(sheaf_exp) { 
    var exp     =  Glaemscribe.SheafChain.SHEAF_REGEXP_IN.exec(sheaf_exp);
    
    if(exp)
      sheaf_exp   = exp[1];
    
    return sheaf_exp.trim();
  });

  sheaf_chain.sheaves    = sheaf_chain.sheaf_exps.map(function(sheaf_exp) { return new Glaemscribe.Sheaf(sheaf_chain, sheaf_exp) });
  
  if(sheaf_chain.sheaves.length == 0)
    sheaf_chain.sheaves    = [new Glaemscribe.Sheaf(sheaf_chain,"")]
    
  return sheaf_chain;    
}

Glaemscribe.SheafChain.SHEAF_REGEXP_IN    = /\[(.*?)\]/;
Glaemscribe.SheafChain.SHEAF_REGEXP_OUT   = /(\[.*?\])/;

Glaemscribe.SheafChain.prototype.mode = function() { return this.rule.mode() };

/*
  Adding api/sheaf_chain_iterator.js 
*/


Glaemscribe.SheafChainIterator = function (sheaf_chain, cross_schema)
{
  var sci = this;
  
  sci.sheaf_chain = sheaf_chain;
  sci.sizes       = sheaf_chain.sheaves.map(function(sheaf) {  return sheaf.fragments.length });
   
  sci.iterators   = sci.sizes.map(function(elt) { return 0;});
  
  sci.errors      = [];

  var identity_cross_array  = []
  var sheaf_count           = sheaf_chain.sheaves.length;
  
  // Construct the identity array
  for(var i=0;i<sheaf_count;i++)
    identity_cross_array.push(i+1);
  
  // Construct the cross array
  var cross_array = null;
  if(cross_schema != null)
  {
    cross_array     = cross_schema.split(",").map(function(i) { return parseInt(i) });
    var ca_count    = cross_array.length;
    
    if(ca_count != sheaf_count)
      sci.errors.push(sheaf_count + " sheaves found in right predicate, but " + ca_count + " elements in cross rule.");  
    
    var sorted = cross_array.slice(0); // clone
    if(!identity_cross_array.equals(sorted.sort()))
      sci.errors.push("Cross rule should contain each element of "+ identity_cross_array + " once and only once.");
  }
  else
  {
    cross_array = identity_cross_array;    
  }  
  
  this.cross_array = cross_array;
}

Glaemscribe.SheafChainIterator.prototype.proto = function()
{
  var sci   = this;
  
  var res   = sci.sizes.slice(0); // clone
  var res2  = sci.sizes.slice(0); // clone
  
  for(var i=0;i<res.length;i++)
    res2[i] = res[sci.cross_array[i]-1];
  
  // Remove all sheaves of size 1 (which are constant)
  res = res2.filter(function(elt) {return elt != 1})
  
  // Create a prototype string
  res = res.join("x");
  
  if(res == "")
    res = "1";
  
  return res;
}

Glaemscribe.SheafChainIterator.prototype.combinations = function()
{
  var sci = this;
  var resolved = [];
  
  for(var i=0;i<sci.iterators.length;i++)
  {
    var counter   = sci.iterators[i];
    var sheaf     = sci.sheaf_chain.sheaves[i];
    
    var fragment  = sheaf.fragments[counter];
    resolved.push(fragment.combinations); 
  }
    
  var res = resolved[0]; 
  for(var i=0;i<resolved.length-1;i++)
  {
    var prod  = res.productize(resolved[i+1]);
    res       = prod.map(function(elt) {
      var e1 = elt[0];
      var e2 = elt[1];
      return e1.concat(e2);
    }); 
  }
  return res;
}

Glaemscribe.SheafChainIterator.prototype.iterate = function()
{
  var sci = this;
  var pos = 0
  
  while(pos < sci.sizes.length)
  {
    var realpos = sci.cross_array[pos]-1;
    sci.iterators[realpos] += 1;
    if(sci.iterators[realpos] >= sci.sizes[realpos])
    {
      sci.iterators[realpos] = 0;
      pos += 1;
    }
    else
      return true;
  }
  
  // Wrapped!
  return false  
}


/*
  Adding api/if_tree.js 
*/


Glaemscribe.IfTree = {}

/* ================ */

Glaemscribe.IfTree.IfCond = function(line, parent_if_term, expression)
{
  this.line = line;
  this.parent_if_term = parent_if_term;
  this.expression = expression;
  return this;
}

/* ================ */

Glaemscribe.IfTree.Term = function(parent_code_block)
{
  this.parent_code_block = parent_code_block;
  return this;
}
Glaemscribe.IfTree.Term.prototype.is_code_lines = function()
{
  return false;
}
Glaemscribe.IfTree.Term.prototype.is_pre_post_processor_operators = function()
{
  return false;
}
Glaemscribe.IfTree.Term.prototype.name = function()
{
  return "TERM"
}
Glaemscribe.IfTree.Term.prototype.dump = function(level)
{
  var str = "";
  for(var i=0;i<level;i++)
    str += " ";
  str += "|-" + this.name(); 
  console.log(str);
}

/* ================ */

Glaemscribe.IfTree.IfTerm = function(parent_code_block)
{
  Glaemscribe.IfTree.Term.call(this,parent_code_block); //super
  this.if_conds = [];
  return this;
}
Glaemscribe.IfTree.IfTerm.inheritsFrom( Glaemscribe.IfTree.Term );  

Glaemscribe.IfTree.IfTerm.prototype.name = function()
{
  return "IF_TERM";
}
Glaemscribe.IfTree.IfTerm.prototype.dump = function(level)
{
  this.parent.dump.call(this,level);
  
}

/* ================ */

Glaemscribe.IfTree.CodeLine = function(expression, line)
{
  this.expression = expression;
  this.line       = line;
  return this;
}

/* ================ */

Glaemscribe.IfTree.PrePostProcessorOperatorsTerm = function(parent_code_block)
{
  Glaemscribe.IfTree.Term.call(this,parent_code_block); //super
  this.operators = []
  return this;
}
Glaemscribe.IfTree.PrePostProcessorOperatorsTerm.inheritsFrom( Glaemscribe.IfTree.Term );  

Glaemscribe.IfTree.PrePostProcessorOperatorsTerm.prototype.name = function()
{
  return "OP_TERM";
}
Glaemscribe.IfTree.PrePostProcessorOperatorsTerm.prototype.is_pre_post_processor_operators = function()
{
  return true;
}

/* ================ */

Glaemscribe.IfTree.CodeLinesTerm = function(parent_code_block)
{
  Glaemscribe.IfTree.Term.call(this,parent_code_block); //super
  this.code_lines = []
  return this;
}
Glaemscribe.IfTree.CodeLinesTerm.inheritsFrom( Glaemscribe.IfTree.Term );  

Glaemscribe.IfTree.CodeLinesTerm.prototype.name = function()
{
  return "CL_TERM";
}
Glaemscribe.IfTree.CodeLinesTerm.prototype.is_code_lines = function()
{
  return true;
}
      
/* ================ */

Glaemscribe.IfTree.CodeBlock = function(parent_if_cond)
{
  this.parent_if_cond = parent_if_cond;
  this.terms          = [];
  return this;
}

Glaemscribe.IfTree.CodeBlock.prototype.dump = function(level)
{
  var str = "";
  for(var i=0;i<level;i++)
    str += " ";
  str += "|-BLOCK"; 
  console.log(str);
  
  for(var t=0;t<this.terms.length; t++)
    this.terms[t].dump(level+1);
}



/*
  Adding api/eval.js 
*/


Glaemscribe.Eval = {}
Glaemscribe.Eval.Token = function(name, expression)
{
  this.name       = name;
  this.expression = expression;
}
Glaemscribe.Eval.Token.prototype.is_regexp = function()
{
  return (this.expression instanceof RegExp);
}
Glaemscribe.Eval.Token.prototype.clone = function(value)
{
  var t = new Glaemscribe.Eval.Token(this.name, this.expression);
  t.value = value;
  return t;
}

Glaemscribe.Eval.Lexer = function(exp) {
  this.exp            = exp;
  this.token_chain    = [];
  this.retain_last    = false
}
Glaemscribe.Eval.Lexer.prototype.uneat = function()
{
  this.retain_last = true;
}
Glaemscribe.Eval.Lexer.prototype.EXP_TOKENS = [
  new Glaemscribe.Eval.Token("bool_or",      "||"),
  new Glaemscribe.Eval.Token("bool_and",     "&&"),
  new Glaemscribe.Eval.Token("cond_inf_eq",  "<="),
  new Glaemscribe.Eval.Token("cond_inf",     "<"),
  new Glaemscribe.Eval.Token("cond_sup_eq",  ">="),
  new Glaemscribe.Eval.Token("cond_sup",     ">"),
  new Glaemscribe.Eval.Token("cond_eq",      "=="),
  new Glaemscribe.Eval.Token("cond_not_eq",  "!="),
  new Glaemscribe.Eval.Token("add_plus",     "+"),
  new Glaemscribe.Eval.Token("add_minus",    "-"),
  new Glaemscribe.Eval.Token("mult_times",   "*"),
  new Glaemscribe.Eval.Token("mult_div",     "/"),
  new Glaemscribe.Eval.Token("mult_modulo",  "%"),
  new Glaemscribe.Eval.Token("prim_not",     "!"),
  new Glaemscribe.Eval.Token("prim_lparen",  "("),
  new Glaemscribe.Eval.Token("prim_rparen",  ")"),
  new Glaemscribe.Eval.Token("prim_string",  /^'[^']*'/),
  new Glaemscribe.Eval.Token("prim_string",  /^"[^"]*"/),
  new Glaemscribe.Eval.Token("prim_const",   /^[a-zA-Z0-9_.]+/)
];   
Glaemscribe.Eval.Lexer.prototype.TOKEN_END = new Glaemscribe.Eval.Token("prim_end","");

Glaemscribe.Eval.Lexer.prototype.advance = function()
{
  this.exp = this.exp.trim();
    
  if(this.retain_last == true) 
  {
    this.retain_last = false
    return this.token_chain[this.token_chain.length-1];
  }
  
  if(this.exp == Glaemscribe.Eval.Lexer.prototype.TOKEN_END.expression)
  {
    var t = Glaemscribe.Eval.Lexer.prototype.TOKEN_END.clone("");
    this.token_chain.push(t);
    return t;
  }
  
  for(var t=0;t<Glaemscribe.Eval.Lexer.prototype.EXP_TOKENS.length;t++)
  {
    var token = Glaemscribe.Eval.Lexer.prototype.EXP_TOKENS[t];
    if(token.is_regexp())
    {
      var match = this.exp.match(token.expression);
      if(match)
      {
        var found = match[0];
        this.exp  = this.exp.substring(found.length);
        var t     = token.clone(found);
        this.token_chain.push(t);
        return t;
      }
    }
    else
    {
      if(this.exp.indexOf(token.expression) == 0)
      {
        this.exp = this.exp.substring(token.expression.length);
        var t    = token.clone(token.expression);
        this.token_chain.push(t);
        return t;        
      }
    }
  }
  
  throw "UnknownToken";    
}

Glaemscribe.Eval.Parser = function() {}
Glaemscribe.Eval.Parser.prototype.parse = function(exp, vars)
{  
  this.lexer  = new Glaemscribe.Eval.Lexer(exp);
  this.vars   = vars;
  return this.parse_top_level();
}

Glaemscribe.Eval.Parser.prototype.parse_top_level = function()
{
  return this.explore_bool();
}

Glaemscribe.Eval.Parser.prototype.explore_bool = function()
{
  var v     = this.explore_compare();
  var stop  = false
  while(!stop)
  {
    switch(this.lexer.advance().name)
    {
    case 'bool_or':
      if(v == true)
        this.explore_bool();
      else
        v = this.explore_compare();
      break;
    case 'bool_and':
      if(!v == true)
        this.explore_bool(); 
      else
        v = this.explore_compare();
      break;
    default:
      stop = true;
    }
  }      
  this.lexer.uneat(); // Keep the unused token for the higher level
  return v;
}

Glaemscribe.Eval.Parser.prototype.explore_compare = function()
{
  var v = this.explore_add();
  var stop = false;
  while(!stop)
  {
    switch(this.lexer.advance().name)
    {
      case 'cond_inf_eq': v = (v <= this.explore_add() ); break;
      case 'cond_inf':    v = (v <  this.explore_add() ); break;
      case 'cond_sup_eq': v = (v >= this.explore_add() ); break;
      case 'cond_sup':    v = (v >  this.explore_add() ); break;
      case 'cond_eq':     v = (v == this.explore_add() ); break;
      case 'cond_not_eq': v = (v != this.explore_add() ); break;
      default: stop = true; break;
    }
  }
  this.lexer.uneat();
  return v;
}



Glaemscribe.Eval.Parser.prototype.explore_add = function()
{
  var v = this.explore_mult();
  var stop = false;
  while(!stop) {
    switch(this.lexer.advance().name)
    {
      case 'add_plus':  v += this.explore_mult(); break;
      case 'add_minus': v -= this.explore_mult(); break;
      default: stop = true; break;
    }
  }
  this.lexer.uneat(); // Keep the unused token for the higher level
  return v;
}

Glaemscribe.Eval.Parser.prototype.explore_mult = function()
{
  var v = this.explore_primary();
  var stop = false;
  while(!stop) {
    switch(this.lexer.advance().name)
    {
      case 'mult_times':    v *= this.explore_primary(); break;
      case 'mult_div':      v /= this.explore_primary(); break;
      case 'mult_modulo':   v %= this.explore_primary(); break;
      default: stop = true; break;
    }
  }
  this.lexer.uneat(); // Keep the unused token for the higher level
  return v;
}


Glaemscribe.Eval.Parser.prototype.explore_primary = function()
{
  var token = this.lexer.advance();
  var v     = null;
  switch(token.name)
  {
    case 'prim_const':  v = this.cast_constant(token.value); break;
    case 'add_minus':   v = -this.explore_primary(); break; // Allow the use of - as primary token for negative numbers
    case 'prim_not':    v = !this.explore_primary(); break; // Allow the use of ! for booleans
    case 'prim_lparen':   
    
      v               = this.parse_top_level();
      var rtoken      = this.lexer.advance(); 
    
      if(rtoken.name != 'prim_rparen') 
        throw "Missing right parenthesis."; 
    
      break;
    default:
      throw "Cannot understand: " + token.value + ".";
      break;
  }
  return v;
}

Glaemscribe.Eval.Parser.prototype.constant_is_float = function(cst)
{
  if(isNaN(cst))
    return false;
  
  return  Number(cst) % 1 !== 0;  
}

Glaemscribe.Eval.Parser.prototype.constant_is_int = function(cst)
{
  if(isNaN(cst))
    return false;
  
  return Number(cst) % 1 === 0;
}

Glaemscribe.Eval.Parser.prototype.constant_is_string = function(cst)
{
  if(cst.length < 2)
    return false;
  
  var f = cst[0]
  var l = cst[cst.length-1]
  
  return ( f == l && (l == "'" || l == '"') );
}

Glaemscribe.Eval.Parser.prototype.cast_constant = function(cst)
{
  var match = null;
  
  if(this.constant_is_int(cst))
    return parseInt(cst);
  else if(this.constant_is_float(cst))
    return parseFloat(cst);
  else if(match = cst.match(/^\'(.*)\'$/))
    return match[0];
  else if(match = cst.match(/^\"(.*)\"$/))
    return match[0];
  else if(cst == 'true')
    return true;
  else if(cst == 'false')
    return false;
  else if(cst == 'nil')
    return null;
  else if(this.vars[cst] != null)
    return this.vars[cst];
  else
    throw "Cannot understand constant '" + cst + "'.";          
}



/*
  Adding api/transcription_tree_node.js 
*/


Glaemscribe.TranscriptionTreeNode = function(character,replacement,path) {
  var tree_node         = this;
  tree_node.character   = character;
  tree_node.replacement = replacement;
  tree_node.path        = path;
  tree_node.siblings    = {}
}

Glaemscribe.TranscriptionTreeNode.prototype.is_effective = function() {
  return this.replacement != null;
}

Glaemscribe.TranscriptionTreeNode.prototype.add_subpath = function(source, rep, path) {
  if(source == null || source == "")
    return;
  
  var tree_node     = this;
  var cc            = source[0];
  var sibling       = tree_node.siblings[cc];
  var path_to_here  = (path || "") + cc;
  
  if(sibling == null)
    sibling = new Glaemscribe.TranscriptionTreeNode(cc,null,path_to_here);
    
  tree_node.siblings[cc] = sibling;
  
  if(source.length == 1)
    sibling.replacement = rep;
  else
    sibling.add_subpath(source.substring(1),rep,path_to_here);
}

Glaemscribe.TranscriptionTreeNode.prototype.transcribe = function(string, chain) {
  
  if(chain == null)
    chain = [];
  
  chain.push(this);

  if(string != "")
  {
    var cc = string[0];
    var sibling = this.siblings[cc];
    
    if(sibling)
      return sibling.transcribe(string.substring(1), chain);
  }
  
  // We are at the end of the chain
  while(chain.length > 1) {
    var last_node = chain.pop();
    if(last_node.is_effective())
      return [last_node.replacement, chain.length] 
  }
  
  // Only the root node is in the chain, we could not find anything; return the "unknown char"
  return [["*UNKNOWN"], 1]; 
}


/*
  Adding api/transcription_pre_post_processor.js 
*/


// ====================== //
//      OPERATORS         //
// ====================== //

Glaemscribe.PrePostProcessorOperator = function(glaeml_element)
{
  this.glaeml_element = glaeml_element;
  return this;
}
Glaemscribe.PrePostProcessorOperator.prototype.apply = function(l)
{
  throw "Pure virtual method, should be overloaded.";
}
Glaemscribe.PrePostProcessorOperator.prototype.eval_arg = function(arg, trans_options) {
  if(arg == null)
    return null;
  
  var rmatch = null;
  if( rmatch = arg.match(/^\\eval\s/) )
  {
    to_eval = arg.substring( rmatch[0].length ); 
    return new Glaemscribe.Eval.Parser().parse(to_eval, trans_options);   
  }
  return arg;
}
Glaemscribe.PrePostProcessorOperator.prototype.finalize_glaeml_element = function(ge, trans_options) {
  var op = this;
  
  for(var i=0;i<ge.args.length;i++)
    ge.args[i] = op.eval_arg(ge.args[i], trans_options);

  ge.children.glaem_each(function(idx, child) {
    op.finalize_glaeml_element(child, trans_options);
  });
  return ge;
}
Glaemscribe.PrePostProcessorOperator.prototype.finalize = function(trans_options) {
  var op = this;
  
  // Deep copy the glaeml_element so we can safely eval the inner args
  op.finalized_glaeml_element = op.finalize_glaeml_element(op.glaeml_element.clone(), trans_options);
}

// Inherit from PrePostProcessorOperator
Glaemscribe.PreProcessorOperator = function(raw_args)  
{
  Glaemscribe.PrePostProcessorOperator.call(this,raw_args);
  return this;
} 
Glaemscribe.PreProcessorOperator.inheritsFrom( Glaemscribe.PrePostProcessorOperator );  

// Inherit from PrePostProcessorOperator
Glaemscribe.PostProcessorOperator = function(raw_args)  
{
  Glaemscribe.PrePostProcessorOperator.call(this,raw_args);
  return this;
} 
Glaemscribe.PostProcessorOperator.inheritsFrom( Glaemscribe.PrePostProcessorOperator );  


// =========================== //
//      PRE/POST PROCESSORS    //
// =========================== //

Glaemscribe.TranscriptionPrePostProcessor = function(mode)
{
  this.mode             = mode;
  this.root_code_block  = new Glaemscribe.IfTree.CodeBlock(); 
  return this;
}

Glaemscribe.TranscriptionPrePostProcessor.prototype.finalize = function(options)
{
  this.operators = []
  this.descend_if_tree(this.root_code_block, options);
  
  this.operators.glaem_each(function(op_num, op) {
    op.finalize(options);
  });
}

Glaemscribe.TranscriptionPrePostProcessor.prototype.descend_if_tree = function(code_block, options)
{
  for(var t=0; t < code_block.terms.length; t++)
  {
    var term = code_block.terms[t];
           
    if(term.is_pre_post_processor_operators())
    {
      for(var o=0; o<term.operators.length; o++)
      {
        var operator = term.operators[o];
        this.operators.push(operator);
      } 
    }
    else
    { 
      for(var i=0; i < term.if_conds.length; i++)
      {
        var if_cond = term.if_conds[i];
        var if_eval = new Glaemscribe.Eval.Parser();
        
        // TODO: CONTEXT VARS!!
        if(if_eval.parse(if_cond.expression, options) == true)
        {
          this.descend_if_tree(if_cond.child_code_block, options)
          break; // Don't try other conditions! 
        }
      }        
    }
  }
}

// PREPROCESSOR
// Inherit from TranscriptionPrePostProcessor; a bit more verbose than in ruby ...
Glaemscribe.TranscriptionPreProcessor = function(mode)  
{
  Glaemscribe.TranscriptionPrePostProcessor.call(this,mode);
  return this;
} 
Glaemscribe.TranscriptionPreProcessor.inheritsFrom( Glaemscribe.TranscriptionPrePostProcessor ); 

Glaemscribe.TranscriptionPreProcessor.prototype.apply = function(l)
{
  var ret = l
  
  for(var i=0;i<this.operators.length;i++)
  {
    var operator  = this.operators[i];
    ret       = operator.apply(ret);
  }
  
  return ret;
}   

// POSTPROCESSOR
// Inherit from TranscriptionPrePostProcessor; a bit more verbose than in ruby ...
Glaemscribe.TranscriptionPostProcessor = function(mode)  
{
  Glaemscribe.TranscriptionPrePostProcessor.call(this,mode);
  return this;
} 
Glaemscribe.TranscriptionPostProcessor.inheritsFrom( Glaemscribe.TranscriptionPrePostProcessor ); 

Glaemscribe.TranscriptionPostProcessor.prototype.apply = function(tokens, out_charset)
{
  var out_space_str     = " ";
  if(this.out_space != null)
  {
    out_space_str       = this.out_space.map(function(token) { return out_charset.n2c(token).output() }).join("");
  }
  
  for(var i=0;i<this.operators.length;i++)
  {
    var operator  = this.operators[i];
    tokens        = operator.apply(tokens, out_charset);
  }
  
  // Convert output
  var ret = "";
  for(var t=0;t<tokens.length;t++)
  {
    var token = tokens[t];
    switch(token)
    {
    case "":
      break;
    case "*UNKNOWN":
      ret += Glaemscribe.UNKNOWN_CHAR_OUTPUT;
      break;
    case "*SPACE":
      ret += out_space_str;
      break;
    case "*LF":
      ret += "\n";
    default:
      var c = out_charset.n2c(token);
      if(!c)
        ret += Glaemscribe.UNKNOWN_CHAR_OUTPUT; // Should not happen
      else
        ret += c.output();
    }    
  }
 
  return ret;
}   

 
 

/*
  Adding api/transcription_processor.js 
*/


Glaemscribe.TranscriptionProcessor = function(mode)
{
  this.mode         = mode;
  this.rule_groups  = {};
  
  return this;
}

Glaemscribe.TranscriptionProcessor.prototype.finalize = function(options) {
  
  var processor = this;
  var mode = this.mode;
    
  processor.transcription_tree = new Glaemscribe.TranscriptionTreeNode(null,null,"");
  processor.transcription_tree.add_subpath(Glaemscribe.WORD_BOUNDARY, [""]);
  processor.transcription_tree.add_subpath(Glaemscribe.WORD_BREAKER,  [""]);
  
  this.rule_groups.glaem_each(function(gname,rg) {
    rg.finalize(options);
  });
  
  // Build the input charsets
  processor.in_charset = {}
  
  this.rule_groups.glaem_each(function(gname, rg) {
    rg.in_charset.glaem_each(function(char, group) {
      
      var group_for_char  = processor.in_charset[char];
           
      if(group_for_char != null)
        mode.errors.push(new Glaemscribe.Glaeml.Error(0, "Group " + gname + " uses input character " + char + " which is also used by group " + group_for_char.name + ". Input charsets should not intersect between groups.")); 
      else
        processor.in_charset[char] = group;
      
    })
  });
  
  this.rule_groups.glaem_each(function(gname, rg) {
    for(var r=0;r<rg.rules.length;r++)
    {
      var rule = rg.rules[r];
      
      for(var sr=0;sr<rule.sub_rules.length;sr++)
      {  
        var sub_rule = rule.sub_rules[sr];
        processor.add_subrule(sub_rule);    
      }  
    }
  });
     
}

Glaemscribe.TranscriptionProcessor.prototype.add_subrule = function(sub_rule) {
  var path = sub_rule.src_combination.join("");
  this.transcription_tree.add_subpath(path, sub_rule.dst_combination)
}


Glaemscribe.TranscriptionProcessor.prototype.apply = function(l, debug_context) {
      
  var ret               = [];
  var current_group     = null;
  var accumulated_word  = "";
  
  var chars             = l.split("");
  for(var i=0;i<chars.length;i++)
  {
    var c = chars[i];
    switch(c)
    {
      case " ":
      case "\t":
        ret = ret.concat(this.transcribe_word(accumulated_word, debug_context));
        ret = ret.concat("*SPACE");
            
        accumulated_word = "";
        break;
      case "\r":
        // ignore
        break;
      case "\n":
        ret = ret.concat(this.transcribe_word(accumulated_word, debug_context));
        ret = ret.concat("*LF");
        
        accumulated_word = ""
        break;
      default:
        var c_group = this.in_charset[c];
        if(c_group == current_group)
          accumulated_word += c;
        else
        {
          ret = ret.concat(this.transcribe_word(accumulated_word, debug_context));
          current_group    = c_group;
          accumulated_word = c;
        }
        break;
    }
    
  }
  // End of stirng
  ret = ret.concat(this.transcribe_word(accumulated_word, debug_context));
  return ret;
}

Glaemscribe.TranscriptionProcessor.prototype.transcribe_word = function(word, debug_context) {
  
  var processor = this;
    
  var res = [];
  var word = Glaemscribe.WORD_BOUNDARY + word + Glaemscribe.WORD_BOUNDARY;

  while(word.length != 0)
  {    
    // Explore tree
    var ttret = this.transcription_tree.transcribe(word);   
    
    // r is the replacement, len its length
    var tokens    = ttret[0];
    var len       = ttret[1];   
    var eaten     = word.substring(0,len);
    
    word          = word.substring(len); // eat len characters
    res           = res.concat(tokens);
    
    debug_context.processor_pathes.push([eaten, tokens, tokens]);
  }
  
  return res;
}
      

/*
  Adding api/pre_processor/downcase.js 
*/


Glaemscribe.DowncasePreProcessorOperator = function(args)  
{
  Glaemscribe.PreProcessorOperator.call(this,args); //super
  return this;
} 
Glaemscribe.DowncasePreProcessorOperator.inheritsFrom( Glaemscribe.PreProcessorOperator );  

Glaemscribe.DowncasePreProcessorOperator.prototype.apply = function(str)
{
  return str.toLowerCase();
}  

Glaemscribe.resource_manager.register_pre_processor_class("downcase", Glaemscribe.DowncasePreProcessorOperator);    


/*
  Adding api/pre_processor/rxsubstitute.js 
*/


// Inherit from PrePostProcessorOperator
Glaemscribe.RxSubstitutePreProcessorOperator = function(glaeml_element)  
{
  Glaemscribe.PreProcessorOperator.call(this, glaeml_element); //super
  return this;
} 
Glaemscribe.RxSubstitutePreProcessorOperator.inheritsFrom( Glaemscribe.PreProcessorOperator );  

Glaemscribe.RxSubstitutePreProcessorOperator.prototype.finalize = function(trans_options) {
  
  Glaemscribe.PreProcessorOperator.prototype.finalize.call(this, trans_options); // super
  
  // Ruby uses \1, \2, etc for captured expressions. Convert to javascript. 
  this.finalized_glaeml_element.args[1] = this.finalized_glaeml_element.args[1].replace(/(\\\d)/g,function(cap) { return "$" + cap.replace("\\","")});  
}

Glaemscribe.RxSubstitutePreProcessorOperator.prototype.apply = function(str)
{
  var what  = new RegExp(this.finalized_glaeml_element.args[0],"g");
  var to    = this.finalized_glaeml_element.args[1];

  return str.replace(what,to);
}  

Glaemscribe.resource_manager.register_pre_processor_class("rxsubstitute", Glaemscribe.RxSubstitutePreProcessorOperator);    


/*
  Adding api/pre_processor/substitute.js 
*/


// Inherit from PrePostProcessorOperator
Glaemscribe.SubstitutePreProcessorOperator = function(args)  
{
  Glaemscribe.PreProcessorOperator.call(this,args); //super
  return this;
} 
Glaemscribe.SubstitutePreProcessorOperator.inheritsFrom( Glaemscribe.PreProcessorOperator );  

Glaemscribe.SubstitutePreProcessorOperator.prototype.apply = function(str)
{
  var what  = new RegExp(this.finalized_glaeml_element.args[0],"g");
  var to    = this.finalized_glaeml_element.args[1];

  return str.replace(what,to);
}  

Glaemscribe.resource_manager.register_pre_processor_class("substitute", Glaemscribe.SubstitutePreProcessorOperator);    


/*
  Adding api/pre_processor/up_down_tehta_split.js 
*/


// Inherit from PrePostProcessorOperator
Glaemscribe.UpDownTehtaSplitPreProcessorOperator = function(args)  
{
  Glaemscribe.PreProcessorOperator.call(this,args); //super 
  return this;
} 
Glaemscribe.UpDownTehtaSplitPreProcessorOperator.inheritsFrom( Glaemscribe.PreProcessorOperator );  

Glaemscribe.UpDownTehtaSplitPreProcessorOperator.prototype.finalize = function(trans_options) {
  Glaemscribe.PreProcessorOperator.prototype.finalize.call(this, trans_options); // super
   
  var op    = this;
  var args  = op.finalized_glaeml_element.args; 
  
  var vowel_list      = args[0];
  var consonant_list  = args[1];
      
  vowel_list          = vowel_list.split(/,/).map(function(s) {return s.trim(); });
  consonant_list      = consonant_list.split(/,/).map(function(s) {return s.trim(); });
     
  this.vowel_map          = {}; // Recognize vowel tokens
  this.consonant_map      = {};// Recognize consonant tokens
  this.splitter_tree      = new Glaemscribe.TranscriptionTreeNode(null,null,""); // Recognize tokens
  this.word_split_map     = {};
  // The word split map will help to recognize words
  // The splitter tree will help to split words into tokens
  
  for(var vi=0;vi<vowel_list.length;vi++)
  {
    var v = vowel_list[vi];
    this.splitter_tree.add_subpath(v, v); 
    this.vowel_map[v] = v;
  }
  for(var ci=0;ci<consonant_list.length;ci++)
  {
    var c = consonant_list[ci];
    this.splitter_tree.add_subpath(c, c); 
    this.consonant_map[c] = c;
  }

  var all_letters = vowel_list.concat(consonant_list).join("").split("").sort().unique();

  for(var li=0;li<all_letters.length;li++)
  {
    var l = all_letters[li];
    this.word_split_map[l] = l;
  }    
   
}

Glaemscribe.UpDownTehtaSplitPreProcessorOperator.prototype.type_of_token = function(token)
{
  if(this.vowel_map[token] != null)          return "V";
  if(this.consonant_map[token] != null)      return "C";
  return "X";
}

Glaemscribe.UpDownTehtaSplitPreProcessorOperator.prototype.apply_to_word = function(w)
{
  var res = [];
   
  if(w.trim() == "")
    res.push(w);
  else
  {
    while(w.length != 0)
    {
      var ret = this.splitter_tree.transcribe(w)
      var r   = ret[0];
      var len = ret[1];   
      
      if(r instanceof Array && r.equals([Glaemscribe.UNKNOWN_CHAR_OUTPUT]))
        res.push(w[0]); 
      else
        res.push(r); 
    
      w = w.substring(len);
    }
  }
    
    
  var res_modified = [];

  // We replace the pattern CVC by CvVC where v is a phantom vowel.
  // This makes the pattern CVC not possible.
  var i = 0
  while(i < res.length - 2)
  {
    var r0 = res[i];
    var r1 = res[i+1];
    var r2 = res[i+2];;
    var t0 = this.type_of_token(r0);
    var t1 = this.type_of_token(r1);
    var t2 = this.type_of_token(r2);
   
    if(t0 == "C" && t1 == "V" && t2 == "C")
    {
      res_modified.push(res[i]);
      res_modified.push("@");
      res_modified.push(res[i+1]); 
      i += 2;
    }
    else
    {   
      res_modified.push(res[i]);
      i += 1;
    }
  }

  // Add the remaining stuff
  while(i < res.length)
  {
    res_modified.push(res[i]);
    i += 1
  }
    
  return res_modified.join("")       
}

Glaemscribe.UpDownTehtaSplitPreProcessorOperator.prototype.apply = function(content)
{
  var accumulated_word = ""  
  var ret = ""
        
  var letters = content.split("");
  for(var li=0;li<letters.length;li++)
  {
    var letter = letters[li];
    if(this.word_split_map[letter] != null)
      accumulated_word += letter;
    else
    {
      ret += this.apply_to_word(accumulated_word);
      ret += letter;
      accumulated_word = "";
    }        
  }
  ret += this.apply_to_word(accumulated_word) 
  
  return ret;         
}  

Glaemscribe.resource_manager.register_pre_processor_class("up_down_tehta_split", Glaemscribe.UpDownTehtaSplitPreProcessorOperator);    



/*
  Adding api/pre_processor/elvish_numbers.js 
*/


Glaemscribe.ElvishNumbersPreProcessorOperator = function(args)  {  Glaemscribe.PreProcessorOperator.call(this,args); return this; } 
Glaemscribe.ElvishNumbersPreProcessorOperator.inheritsFrom( Glaemscribe.PreProcessorOperator );  
Glaemscribe.ElvishNumbersPreProcessorOperator.prototype.apply = function(str)
{
  var op      = this;
  
  var base    = op.finalized_glaeml_element.args[0];
  base        = (base != null)?(parseInt(base)):(12);
  
  var reverse = op.finalized_glaeml_element.args[1]
  reverse     = (reverse != null)?(reverse == true || reverse == "true"):(true) 
  
  return str.replace(/\d+/g,function(match) {
    var inbase  = parseInt(match).toString(base);
    inbase      = inbase.toUpperCase(); // Beware, we want letters in upper case!
    
    var ret = '';
    if(reverse)
    {
      for(var i=inbase.length-1;i>=0;i--)
        ret += inbase[i];
    }
    else
    {
      ret = inbase;
    }
    
    return ret;
  });

}  

Glaemscribe.resource_manager.register_pre_processor_class("elvish_numbers", Glaemscribe.ElvishNumbersPreProcessorOperator);    


/*
  Adding api/post_processor/reverse.js 
*/


Glaemscribe.ReversePostProcessorOperator = function(args)  
{
  Glaemscribe.PostProcessorOperator.call(this,args); //super
  return this;
} 
Glaemscribe.ReversePostProcessorOperator.inheritsFrom( Glaemscribe.PostProcessorOperator );  

Glaemscribe.ReversePostProcessorOperator.prototype.apply = function(tokens, charset)
{
  return tokens.reverse();
}  

Glaemscribe.resource_manager.register_post_processor_class("reverse", Glaemscribe.ReversePostProcessorOperator);    


/*
  Adding api/post_processor/resolve_virtuals.js 
*/



Glaemscribe.ResolveVirtualsPostProcessorOperator = function(args)  
{
  Glaemscribe.PostProcessorOperator.call(this,args); //super
  return this;
} 
Glaemscribe.ResolveVirtualsPostProcessorOperator.inheritsFrom( Glaemscribe.PostProcessorOperator );  


Glaemscribe.ResolveVirtualsPostProcessorOperator.prototype.finalize = function(trans_options)
{
  Glaemscribe.PostProcessorOperator.prototype.finalize.call(this, trans_options); // super
  this.last_triggers = {}; // Allocate here to optimize
}  

Glaemscribe.ResolveVirtualsPostProcessorOperator.prototype.reset_trigger_states = function(charset) {
  var op = this;
  charset.virtual_chars.glaem_each(function(idx,vc) {
    vc.object_reference                   = idx; // We cannot objects as references in hashes in js. Attribute a reference.
    op.last_triggers[vc.object_reference] = null; // Clear the state
  });
}

Glaemscribe.ResolveVirtualsPostProcessorOperator.prototype.apply_loop = function(charset, tokens, new_tokens, reversed, token, idx) {
  var op = this;
  if(token == '*SPACE') {
    op.reset_trigger_states(charset);
    return; // continue
  }
  var c = charset.n2c(token);

  if(c == null)
    return;
  
  if(c.is_virtual() && (reversed == c.reversed)) {
    
    // Try to replace
    var last_trigger = op.last_triggers[c.object_reference];
    if(last_trigger != null) {
      new_tokens[idx] = last_trigger.names[0]; // Take the first name of the non-virtual replacement.
    };
  }
  else {
    // Update states of virtual classes
    charset.virtual_chars.glaem_each(function(_,vc) {
      var rc = vc.n2c(token);
      if(rc != null)
        op.last_triggers[vc.object_reference] = rc;
    });
  }  
}


Glaemscribe.ResolveVirtualsPostProcessorOperator.prototype.apply = function(tokens, charset) {   
  var op = this;
  
  // Clone the array so that we can perform diacritics and ligatures without interfering
  var new_tokens = tokens.slice(0);
  
  op.reset_trigger_states(charset);
  tokens.glaem_each(function(idx,token) {
    op.apply_loop(charset,tokens,new_tokens,false,token,idx);
  });
  
  op.reset_trigger_states(charset);
  tokens.glaem_each_reversed(function(idx,token) {
    op.apply_loop(charset,tokens,new_tokens,true,token,idx);    
  });
  return new_tokens;
}  

Glaemscribe.resource_manager.register_post_processor_class("resolve_virtuals", Glaemscribe.ResolveVirtualsPostProcessorOperator);    



/*
  Adding extern/shellwords.js 
*/
/*

Copyright (C) 2011 by Jimmy Cuadra

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

(function(f){if(typeof exports==="object"&&typeof module!=="undefined"){module.exports=f()}else if(typeof define==="function"&&define.amd){define([],f)}else{var g;if(typeof window!=="undefined"){g=window}else if(typeof global!=="undefined"){g=global}else if(typeof self!=="undefined"){g=self}else{g=this}g.shellwords = f()}})(function(){var define,module,exports;return (function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
// Generated by CoffeeScript 1.3.3
(function() {
  var scan;

  scan = function(string, pattern, callback) {
    var match, result;
    result = "";
    while (string.length > 0) {
      match = string.match(pattern);
      if (match) {
        result += string.slice(0, match.index);
        result += callback(match);
        string = string.slice(match.index + match[0].length);
      } else {
        result += string;
        string = "";
      }
    }
    return result;
  };

  exports.split = function(line) {
    var field, words;
    if (line == null) {
      line = "";
    }
    words = [];
    field = "";
    scan(line, /\s*(?:([^\s\\\'\"]+)|'((?:[^\'\\]|\\.)*)'|"((?:[^\"\\]|\\.)*)"|(\\.?)|(\S))(\s|$)?/, function(match) {
      var dq, escape, garbage, raw, seperator, sq, word;
      raw = match[0], word = match[1], sq = match[2], dq = match[3], escape = match[4], garbage = match[5], seperator = match[6];
      if (garbage != null) {
        throw new Error("Unmatched quote");
      }
      field += word || (sq || dq || escape).replace(/\\(?=.)/, "");
      if (seperator != null) {
        words.push(field);
        return field = "";
      }
    });
    if (field) {
      words.push(field);
    }
    return words;
  };

  exports.escape = function(str) {
    if (str == null) {
      str = "";
    }
    if (str == null) {
      return "''";
    }
    return str.replace(/([^A-Za-z0-9_\-.,:\/@\n])/g, "\\$1").replace(/\n/g, "'\n'");
  };

}).call(this);

},{}]},{},[1])(1)
});

Glaemscribe.resource_manager.raw_charsets["tengwar_ds_parmaite"] = "\\**\n\nGlǽmscribe (also written Glaemscribe) is a software dedicated to\nthe transcription of texts between writing systems, and more \nspecifically dedicated to the transcription of J.R.R. Tolkien\'s \ninvented languages to some of his devised writing systems.\n\nCopyright (C) 2015 Benjamin Babut (Talagan).\n\nThis program is free software: you can redistribute it and/or modify\nit under the terms of the GNU Affero General Public License as published by\nthe Free Software Foundation, either version 3 of the License, or\nany later version.\n\nThis program is distributed in the hope that it will be useful,\nbut WITHOUT ANY WARRANTY; without even the implied warranty of\nMERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\nGNU Affero General Public License for more details.\n\nYou should have received a copy of the GNU Affero General Public License\nalong with this program.  If not, see <http://www.gnu.org/licenses/>.\n\n**\\\n\n\\** Charset for Dan Smith layout based fonts **\\ \n\n\\version 0.0.2\n\n\\**   **\\ \\char 20 SPACE              \n\\** ! **\\ \\char 21 TW_EXT_11            \n\\** \" **\\ \\char 22 DASH_INF_L            \n\\** # **\\ \\char 23 A_TEHTA_XL          \n\\** $ **\\ \\char 24 E_TEHTA_XL   \n\\** % **\\ \\char 25 I_TEHTA_XL     \n\\** & **\\ \\char 26 U_TEHTA_XL        \n\\** \' **\\ \\char 27 DASH_INF_S            \n\\** ( **\\ \\char 28 ?                \n\\** ) **\\ \\char 29 TILD_XSUP_L           \n\\** * **\\ \\char 2a ?                \n\\** + **\\ \\char 2b SHOOK_RIGHT_L          \n\\** , **\\ \\char 2c TW_84 ESSE_NUQUERNA       \n\\** - **\\ \\char 2d PUNCT_DDOT            \n\\** . **\\ \\char 2e TW_94 URE            \n\\** / **\\ \\char 2f TILD_XINF_S           \n\\** 0 **\\ \\char 30 TILD_XSUP_S           \n\\** 1 **\\ \\char 31 TW_11 TINCO           \n\\** 2 **\\ \\char 32 TW_21 ANDO           \n\\** 3 **\\ \\char 33 TW_31 SULE THULE        \n\\** 4 **\\ \\char 34 TW_41 ANTO           \n\\** 5 **\\ \\char 35 TW_51 NUMEN           \n\\** 6 **\\ \\char 36 TW_61 ORE            \n\\** 7 **\\ \\char 37 TW_71 ROMEN           \n\\** 8 **\\ \\char 38 TW_81 SILME           \n\\** 9 **\\ \\char 39 TW_91 HYARMEN          \n\\** : **\\ \\char 3a TILD_INF_L            \n\\** ; **\\ \\char 3b TILD_INF_S            \n\\** < **\\ \\char 3c ?  \\** Does not look compliant between DS and Annatar **\\\n\\** = **\\ \\char 3d PUNCT_DOT            \n\\** > **\\ \\char 3e ?                \n\\** ? **\\ \\char 3f TILD_XINF_L           \n\\** @ **\\ \\char 40 TW_EXT_21 ANDO_EXT ANTO_EXT            \n\\** A **\\ \\char 41 TW_EXT_13 CALMA_EXT AHA_EXT            \n\\** B **\\ \\char 42 I_TEHTA_XS     \n\\** C **\\ \\char 43 A_TEHTA_XS          \n\\** D **\\ \\char 44 A_TEHTA_S           \n\\** E **\\ \\char 45 A_TEHTA_L           \n\\** F **\\ \\char 46 E_TEHTA_S    \n\\** G **\\ \\char 47 I_TEHTA_S           \n\\** H **\\ \\char 48 O_TEHTA_S           \n\\** I **\\ \\char 49 SILME_NUQUERNA_ALT \\** Used for y in s. beleriand **\\                \n\\** J **\\ \\char 4a U_TEHTA_S         \n\\** K **\\ \\char 4b ?                \n\\** L **\\ \\char 4c LAMBE_MARK_DOT                \n\\** M **\\ \\char 4d U_TEHTA_XS        \n\\** N **\\ \\char 4e O_TEHTA_XS          \n\\** O **\\ \\char 4f ?                \n\\** P **\\ \\char 50 TILD_SUP_L            \n\\** Q **\\ \\char 51 TW_EXT_12 PARMA_EXT FORMEN_EXT            \n\\** R **\\ \\char 52 E_TEHTA_L          \n\\** S **\\ \\char 53 TW_EXT_23 ANGA_EXT ANCA_EXT            \n\\** T **\\ \\char 54 I_TEHTA_L           \n\\** U **\\ \\char 55 U_TEHTA_L         \n\\** V **\\ \\char 56 E_TEHTA_XS         \n\\** W **\\ \\char 57 TW_EXT_22 UMBAR_EXT AMPA_EXT            \n\\** X **\\ \\char 58 TW_EXT_24 UNQUE_EXT UNGWE_EXT            \n\\** Y **\\ \\char 59 O_TEHTA_L           \n\\** Z **\\ \\char 5a TW_EXT_14 QUESSE_EXT HWESTA_EXT            \n\\** [ **\\ \\char 5b DASH_SUP_S            \n\\** \\ **\\ \\char 5c TILD_L              \n\\** ] **\\ \\char 5d OSSE               \n\\** ^ **\\ \\char 5e O_TEHTA_XL          \n\\** _ **\\ \\char 5f SHOOOK_RIGHT_S          \n\\** ` **\\ \\char 60 TELCO              \n\\** a **\\ \\char 61 TW_13 CALMA          \n\\** b **\\ \\char 62 TW_54 NWALME          \n\\** c **\\ \\char 63 TW_34 HWESTA          \n\\** d **\\ \\char 64 TW_33 AHA           \n\\** e **\\ \\char 65 TW_32 FORMEN          \n\\** f **\\ \\char 66 TW_43 ANCA           \n\\** g **\\ \\char 67 TW_53 NOLDO          \n\\** h **\\ \\char 68 TW_63 ANNA           \n\\** i **\\ \\char 69 TW_82 SILME_NUQUERNA      \n\\** j **\\ \\char 6a TW_73 LAMBE          \n\\** k **\\ \\char 6b TW_83 ESSE           \n\\** l **\\ \\char 6c TW_93 YANTA          \n\\** m **\\ \\char 6d TW_74 ALDA           \n\\** n **\\ \\char 6e TW_64 VILYA          \n\\** o **\\ \\char 6f TW_92 HWESTA_SINDARINWA    \n\\** p **\\ \\char 70 TILD_SUP_S           \n\\** q **\\ \\char 71 TW_12 PARMA          \n\\** r **\\ \\char 72 TW_42 AMPA           \n\\** s **\\ \\char 73 TW_23 ANGA           \n\\** t **\\ \\char 74 TW_52 MALTA          \n\\** u **\\ \\char 75 TW_72 ARDA           \n\\** v **\\ \\char 76 TW_44 UNQUE          \n\\** w **\\ \\char 77 TW_22 UMBAR          \n\\** x **\\ \\char 78 TW_24 UNGWE          \n\\** y **\\ \\char 79 TW_62 VALA           \n\\** z **\\ \\char 7a TW_14 QUESSE          \n\\** { **\\ \\char 7b DASH_SUP_L           \n\\** | **\\ \\char 7c SHOOK_LEFT_L          \n\\** } **\\ \\char 7d SHOOK_LEFT_S          \n\\** ~ **\\ \\char 7e ARA               \n\\** ¡ **\\ \\char a1 ?                \n\\** ¢ **\\ \\char a2 ?                \n\\** £ **\\ \\char a3 SHOOK_BEAUTIFUL                \n\\** ¥ **\\ \\char a5 ?                \n\\** ¦ **\\ \\char a6 HWESTA_TINCO                \n\\** § **\\ \\char a7 AHA_TINCO                \n\\** ¨ **\\ \\char a8 TH_SUB_CIRC_S          \n\\** © **\\ \\char a9 TH_SUB_CIRC_XS          \n\\** ª **\\ \\char aa A_TEHTA_INV_XL        \n\\** « **\\ \\char ab DQUOT_OPEN            \n\\** ¬ **\\ \\char ac PUNCT_DTILD RING_MARK_L RING_MARK_R              \n\\** ­ **\\ \\char ad A_TEHTA_INV_L         \n\\** ® **\\ \\char ae ?                \n\\** ¯ **\\ \\char af A_TEHTA_INV_S         \n\\** ° **\\ \\char b0 LAMBE_MARK_TILD                 \n\\** ± **\\ \\char b1 SQUOT_OPEN            \n\\** ² **\\ \\char b2 SQUOT_CLOSE           \n\\** ³ **\\ \\char b3 ?                \n\\** ´ **\\ \\char b4 LAMBE_MARK_DDOT         \n\\** µ **\\ \\char b5 A_TEHTA_INV_XS        \n\\** · **\\ \\char b7 ?                \n\\** ¸ **\\ \\char b8 LAMBE_MARK_DASH         \n\\** ¹ **\\ \\char b9 ?                \n\\** º **\\ \\char ba ?                \n\\** » **\\ \\char bb DQUOT_CLOSE           \n\\** ¼ **\\ \\char bc ?                \n\\** ½ **\\ \\char bd HALLA              \n\\** ¾ **\\ \\char be ?                \n\\** ¿ **\\ \\char bf ?                \n\\** À **\\ \\char c0 PUNCT_INTERR           \n\\** Á **\\ \\char c1 PUNCT_EXCLAM           \n\\** Â **\\ \\char c2 PUNCT_TILD            \n\\** Ã **\\ \\char c3 ?                \n\\** Ä **\\ \\char c4 ?                \n\\** Å **\\ \\char c5 ?                \n\\** Æ **\\ \\char c6 ?                \n\\** Ç **\\ \\char c7 ?                \n\\** È **\\ \\char c8 THINF_DOT_XL           \n\\** É **\\ \\char c9 THINF_DOT_L           \n\\** Ê **\\ \\char ca THINF_DOT_S           \n\\** Ë **\\ \\char cb THINF_DOT_XS           \n\\** Ì **\\ \\char cc THINF_DDOT_XL          \n\\** Í **\\ \\char cd THINF_DDOT_L           \n\\** Î **\\ \\char ce THINF_DDOT_S           \n\\** Ï **\\ \\char cf THINF_DDOT_XS          \n\\** Ð **\\ \\char d0 THINF_TDOT_XL          \n\\** Ñ **\\ \\char d1 THINF_TDOT_L           \n\\** Ò **\\ \\char d2 THINF_TDOT_S           \n\\** Ó **\\ \\char d3 THINF_TDOT_XS          \n\\** Ô **\\ \\char d4 THSUP_DDOT_XL Y_TEHTA_XL I_TEHTA_DOUBLE_XL         \n\\** Õ **\\ \\char d5 THSUP_DDOT_L Y_TEHTA_L I_TEHTA_DOUBLE_L       \n\\** Ö **\\ \\char d6 THSUP_DDOT_S Y_TEHTA_S I_TEHTA_DOUBLE_S          \n\\** × **\\ \\char d7 THSUP_DDOT_XS Y_TEHTA_XS I_TEHTA_DOUBLE_XS         \n\\** Ø **\\ \\char d8 THSUP_TICK_XL          \n\\** Ù **\\ \\char d9 THSUP_TICK_L           \n\\** Ú **\\ \\char da THSUP_TICK_S           \n\\** Û **\\ \\char db THSUP_TICK_XS          \n\\** Ü **\\ \\char dc THSUP_TICK_INV_XL A_TEHTA_CIRCUM_XL       \n\\** Ý **\\ \\char dd THSUP_TICK_INV_L A_TEHTA_CIRCUM_L        \n\\** Þ **\\ \\char de THSUP_TICK_INV_S A_TEHTA_CIRCUM_S         \n\\** ß **\\ \\char df THSUP_TICK_INV_XS A_TEHTA_CIRCUM_XS       \n\\** à **\\ \\char e0 THSUP_LAMBDA_XL         \n\\** á **\\ \\char e1 THSUP_LAMBDA_L          \n\\** â **\\ \\char e2 THSUP_LAMBDA_S          \n\\** ã **\\ \\char e3 THSUP_LAMBDA_XS         \n\\** ä **\\ \\char e4 THINF_CURL_XL          \n\\** å **\\ \\char e5 THINF_CURL_L           \n\\** æ **\\ \\char e6 THINF_CURL_S           \n\\** ç **\\ \\char e7 THINF_CURL_XS          \n\\** è **\\ \\char e8 SEV_TEHTA_XL           \n\\** é **\\ \\char e9 SEV_TEHTA_L           \n\\** ê **\\ \\char ea SEV_TEHTA_S           \n\\** ë **\\ \\char eb SEV_TEHTA_XS           \n\\** ì **\\ \\char ec ?                                 \n\\** í **\\ \\char ed ?                                 \n\\** î **\\ \\char ee ?                                 \n\\** ï **\\ \\char ef ?                                 \n\\** ð **\\ \\char f0 NUM_0                               \n\\** ñ **\\ \\char f1 NUM_1                               \n\\** ò **\\ \\char f2 NUM_2                               \n\\** ó **\\ \\char f3 NUM_3                               \n\\** ô **\\ \\char f4 NUM_4                               \n\\** õ **\\ \\char f5 NUM_5                               \n\\** ö **\\ \\char f6 NUM_6                               \n\\** ÷ **\\ \\char f7 NUM_7                               \n\\** ø **\\ \\char f8 NUM_8                               \n\\** ù **\\ \\char f9 NUM_9                               \n\\** ú **\\ \\char fa NUM_10                               \n\\** û **\\ \\char fb NUM_11                               \n\\** ü **\\ \\char fc THINF_STROKE_XL                                 \n\\** ý **\\ \\char fd THINF_STROKE_L                                 \n\\** þ **\\ \\char fe THINF_STROKE_S                                 \n\\** ÿ **\\ \\char ff THINF_STROKE_XS                                 \n\\** Œ **\\ \\char 152 PUNCT_PAREN_L                           \n\\** œ **\\ \\char 153 PUNCT_PAREN_R                           \n\\** Š **\\ \\char 160 THINF_ACCENT_L                                 \n\\** š **\\ \\char 161 MALTA_W_HOOK                                 \n\\** Ÿ **\\ \\char 178 THINF_ACCENT_XS                                 \n\\** ƒ **\\ \\char 192 THINF_DSTROKE_XL                                 \n\\** ˆ **\\ \\char 2c6 ?                                 \n\\** ˜ **\\ \\char 2dc TH_SUB_CIRC_XL                                 \n\\** – **\\ \\char 2013 ANCA_CLOSED                                \n\\** — **\\ \\char 2014 OLD_ENGLISH_AND                                \n\\** ‘ **\\ \\char 2018 THINF_CURL_INV_XL                                \n\\** ’ **\\ \\char 2019 THINF_CURL_INV_L                                \n\\** ‚ **\\ \\char 201a LAMBE_MARK_DSTROKE                                \n\\** “ **\\ \\char 201c THINF_CURL_INV_S                                \n\\** ” **\\ \\char 201d THINF_CURL_INV_XS                                \n\\** „ **\\ \\char 201e THINF_DSTROKE_L                                \n\\** † **\\ \\char 2020 THINF_DSTROKE_XS                                \n\\** ‡ **\\ \\char 2021 ?                                \n\\** • **\\ \\char 2022 HARP_SHAPED                                \n\\** … **\\ \\char 2026 THINF_DSTROKE_S                                \n\\** ‰ **\\ \\char 2030 THINF_ACCENT_XL                                \n\\** ‹ **\\ \\char 2039 THINF_ACCENT_S                                \n\\** › **\\ \\char 203a BOOKMARK_SIGN                                \n\\** ™ **\\ \\char 2122 TH_SUB_CIRC_L                                \n\\** 〠 **\\ \\char 3020 ?                                \n\\** 〡 **\\ \\char 3021 ?                                \n\\** 〣 **\\ \\char 3023 A_TEHTA_DOUBLE_XL                                \n\\** 〤 **\\ \\char 3024 E_TEHTA_DOUBLE_XL                                \n\\** 〦 **\\ \\char 3026 U_TEHTA_DOUBLE_XL                                \n\\** **\\ \\char 302e ?                                \n\\** 〰 **\\ \\char 3030 ?                                \n\\** 〱 **\\ \\char 3031 ?                                \n\\** 〲 **\\ \\char 3032 ?                                \n\\** 〳 **\\ \\char 3033 ?                                \n\\** 〴 **\\ \\char 3034 ?                                \n\\** 〵 **\\ \\char 3035 ?                                \n\\** 〶 **\\ \\char 3036 ?                                \n\\** 〷 **\\ \\char 3037 ?                                \n\\** 〸 **\\ \\char 3038 ?                                \n\\** 〹 **\\ \\char 3039 ?                                \n\\** 〼 **\\ \\char 303c ?                                \n\\** ぀ **\\ \\char 3040 ?                                \n\\** ぃ **\\ \\char 3043 A_TEHTA_DOUBLE_XS                                \n\\** い **\\ \\char 3044 A_TEHTA_DOUBLE_S                                \n\\** ぅ **\\ \\char 3045 A_TEHTA_DOUBLE_L                                \n\\** う **\\ \\char 3046 E_TEHTA_DOUBLE_XS                                \n\\** え **\\ \\char 3048 O_TEHTA_DOUBLE_S                                \n\\** お **\\ \\char 304a U_TEHTA_DOUBLE_S                                \n\\** き **\\ \\char 304d U_TEHTA_DOUBLE_XS                                \n\\** ぎ **\\ \\char 304e O_TEHTA_DOUBLE_XS                                \n\\** け **\\ \\char 3051 ?                                \n\\** げ **\\ \\char 3052 E_TEHTA_DOUBLE_L                                \n\\** さ **\\ \\char 3055 U_TEHTA_DOUBLE_L                                \n\\** ざ **\\ \\char 3056 E_TEHTA_DOUBLE_S                                \n\\** し **\\ \\char 3057 ?                                \n\\** す **\\ \\char 3059 O_TEHTA_DOUBLE_L                                \n\\** ぞ **\\ \\char 305e O_TEHTA_DOUBLE_XL                                \n\\** ぢ **\\ \\char 3062 ?                                \n\\** づ **\\ \\char 3065 ?                                \n\\** と **\\ \\char 3068 ?                                \n\\** な **\\ \\char 306a LAMBE_LIG                                \n\\** の **\\ \\char 306e ?                                \n\\** ぱ **\\ \\char 3071 ?                                \n\\** ひ **\\ \\char 3072 ?                                \n\\** ぴ **\\ \\char 3074 ?                                \n\\** ふ **\\ \\char 3075 ?                                \n\\** ぷ **\\ \\char 3077 ?                                \n\\** へ **\\ \\char 3078 ?                                \n\\** べ **\\ \\char 3079 ?                                \n\\** ぺ **\\ \\char 307a ?                                \n\\** ア **\\ \\char 30a2 ?                                \n\\** カ **\\ \\char 30ab ?                                \n\\** ギ **\\ \\char 30ae ?                                \n\\** セ **\\ \\char 30bb ?                                \n\\** タ **\\ \\char 30bf ?                                \n\\** ツ **\\ \\char 30c4 ?                                \n\\** テ **\\ \\char 30c6 ?                                \n\\** デ **\\ \\char 30c7 ?                                \n\\** ヘ **\\ \\char 30d8 ?                                \n\\** ベ **\\ \\char 30d9 ?                                \n\\** ペ **\\ \\char 30da ?                                \n\\** ホ **\\ \\char 30db ?                                \n\\** ム **\\ \\char 30e0 ?                                \n\\** メ **\\ \\char 30e1 ?                                \n\\** モ **\\ \\char 30e2 ?                                \n\\** ャ **\\ \\char 30e3 ?                                \n\\** ヨ **\\ \\char 30e8 ?                                \n\\** ラ **\\ \\char 30e9 ?                                \n\\** リ **\\ \\char 30ea ?                                \n\\** ル **\\ \\char 30eb ? \n\\** **\\ \\char 3152 PUNCT_PAREN_L_ALT                                \n\\** **\\ \\char 3153 PUNCT_PAREN_R_ALT                                                               \n\\** ㅠ **\\ \\char 3160 ?                                \n\\** ㅡ **\\ \\char 3161 ?                                \n\\** ㅸ **\\ \\char 3178 ?                                \n\\** ㆒ **\\ \\char 3192 ?                                \n\\** 倚 **\\ \\char 501a ?                                \n\\** 倞 **\\ \\char 501e ?                                \n\\** 倠 **\\ \\char 5020 ?                                \n\\** 倢 **\\ \\char 5022 ?                                \n\\** 倦 **\\ \\char 5026 ?                                \n\\** 倰 **\\ \\char 5030 ?                                \n\\** 倹 **\\ \\char 5039 ?\n\n\n\\** The following virtual chars are used to handle tehtar (& the like) multiple version chosing **\\\n\\** It could be avoided with modern fonts with gsub/gpos tables for ligatures and diacritics **\\\n\\** placement **\\\n\\beg virtual A_TEHTA\n  \\class A_TEHTA_XS TELCO ARA HYARMEN\n  \\class A_TEHTA_S TINCO PARMA CALMA QUESSE SULE FORMEN TW_EXT_11 TW_EXT_12 ROMEN ARDA URE OSSE HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class A_TEHTA_L AHA HWESTA ORE VALA ANNA VILYA TW_EXT_13 TW_EXT_14 SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE_NUQUERNA YANTA SHOOK_BEAUTIFUL\n  \\class A_TEHTA_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual A_TEHTA_CIRCUM\n  \\class A_TEHTA_CIRCUM_XS TELCO ARA HYARMEN\n  \\class A_TEHTA_CIRCUM_S TINCO PARMA CALMA QUESSE SULE FORMEN ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 ROMEN ARDA SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE_NUQUERNA YANTA URE OSSE HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class A_TEHTA_CIRCUM_L AHA HWESTA TW_EXT_13 TW_EXT_14 SHOOK_BEAUTIFUL\n  \\class A_TEHTA_CIRCUM_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual E_TEHTA\n  \\class E_TEHTA_XS TELCO ARA HYARMEN\n  \\class E_TEHTA_S TINCO PARMA CALMA QUESSE SULE FORMEN ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 ROMEN ARDA URE OSSE HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class E_TEHTA_L AHA HWESTA TW_EXT_13 TW_EXT_14 SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE_NUQUERNA YANTA SHOOK_BEAUTIFUL\n  \\class E_TEHTA_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual I_TEHTA\n  \\class I_TEHTA_XS TELCO ARA HYARMEN\n  \\class I_TEHTA_S TINCO PARMA CALMA QUESSE SULE FORMEN ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 ROMEN ARDA SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE_NUQUERNA YANTA URE OSSE HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class I_TEHTA_L AHA HWESTA TW_EXT_13 TW_EXT_14 SHOOK_BEAUTIFUL\n  \\class I_TEHTA_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual O_TEHTA\n  \\class O_TEHTA_XS TELCO ARA HYARMEN\n  \\class O_TEHTA_S TINCO PARMA CALMA QUESSE SULE FORMEN ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 ROMEN ARDA SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE_NUQUERNA YANTA URE OSSE HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class O_TEHTA_L AHA HWESTA TW_EXT_13 TW_EXT_14 SHOOK_BEAUTIFUL\n  \\class O_TEHTA_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual U_TEHTA\n  \\class U_TEHTA_XS TELCO ARA HYARMEN\n  \\class U_TEHTA_S TINCO PARMA CALMA QUESSE SULE FORMEN ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 ROMEN ARDA SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE_NUQUERNA YANTA URE OSSE HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class U_TEHTA_L AHA HWESTA TW_EXT_13 TW_EXT_14 SHOOK_BEAUTIFUL\n  \\class U_TEHTA_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual SEV_TEHTA\n  \\class SEV_TEHTA_XS TELCO ARA SULE FORMEN TW_EXT_11 TW_EXT_12 HYARMEN HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class SEV_TEHTA_S TINCO PARMA CALMA QUESSE ORE VALA ANNA VILYA ROMEN ARDA SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE_NUQUERNA YANTA URE OSSE\n  \\class SEV_TEHTA_L AHA HWESTA TW_EXT_13 TW_EXT_14 SHOOK_BEAUTIFUL\n  \\class SEV_TEHTA_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual A_TEHTA_DOUBLE\n  \\class A_TEHTA_DOUBLE_XS TELCO ARA HYARMEN\n  \\class A_TEHTA_DOUBLE_S SULE FORMEN TW_EXT_11 TW_EXT_12 OSSE HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class A_TEHTA_DOUBLE_L TINCO PARMA CALMA QUESSE ORE VALA ANNA VILYA ROMEN ARDA SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE_NUQUERNA YANTA URE SHOOK_BEAUTIFUL\n  \\class A_TEHTA_DOUBLE_XL ANDO UMBAR ANGA UNGWE AHA HWESTA ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_13 TW_EXT_14 TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual E_TEHTA_DOUBLE\n  \\class E_TEHTA_DOUBLE_XS TELCO ARA HYARMEN\n  \\class E_TEHTA_DOUBLE_S TW_EXT_11\n  \\class E_TEHTA_DOUBLE_L TINCO PARMA CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_12 TW_EXT_13 TW_EXT_14 ROMEN ARDA SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE_NUQUERNA YANTA URE OSSE SHOOK_BEAUTIFUL HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class E_TEHTA_DOUBLE_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual I_TEHTA_DOUBLE Y_TEHTA\n  \\class I_TEHTA_DOUBLE_XS TELCO ARA SULE FORMEN TW_EXT_11 TW_EXT_12 HYARMEN HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class I_TEHTA_DOUBLE_S TINCO PARMA CALMA QUESSE ORE VALA ANNA VILYA ESSE_NUQUERNA YANTA URE OSSE SHOOK_BEAUTIFUL\n  \\class I_TEHTA_DOUBLE_L AHA HWESTA TW_EXT_13 TW_EXT_14 ROMEN ARDA SILME_NUQUERNA SILME_NUQUERNA_ALT\n  \\class I_TEHTA_DOUBLE_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual O_TEHTA_DOUBLE\n  \\class O_TEHTA_DOUBLE_XS TELCO ARA HYARMEN\n  \\class O_TEHTA_DOUBLE_S TINCO PARMA CALMA QUESSE SULE FORMEN ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 ROMEN ARDA SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE_NUQUERNA YANTA URE OSSE SHOOK_BEAUTIFUL HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class O_TEHTA_DOUBLE_L AHA HWESTA TW_EXT_13 TW_EXT_14\n  \\class O_TEHTA_DOUBLE_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual U_TEHTA_DOUBLE\n  \\class U_TEHTA_DOUBLE_XS TELCO ARA HYARMEN\n  \\class U_TEHTA_DOUBLE_S TINCO PARMA CALMA QUESSE SULE FORMEN ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 ROMEN ARDA SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE_NUQUERNA YANTA URE OSSE SHOOK_BEAUTIFUL HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class U_TEHTA_DOUBLE_L AHA HWESTA TW_EXT_13 TW_EXT_14\n  \\class U_TEHTA_DOUBLE_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 LAMBE ALDA SILME ESSE HWESTA_SINDARINWA ANCA_CLOSED\n\\end\n\n\\beg virtual A_TEHTA_INF\n  \\class THINF_TDOT_XS TELCO ARA ROMEN ARDA SILME_NUQUERNA HARP_SHAPED\n  \\class THINF_TDOT_S TINCO PARMA TW_EXT_11 TW_EXT_12 SILME URE OSSE SHOOK_BEAUTIFUL AHA_TINCO HWESTA_TINCO\n  \\class THINF_TDOT_L CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_13 TW_EXT_14 LAMBE ALDA SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN HWESTA_SINDARINWA YANTA\n  \\class THINF_TDOT_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n\\end\n\n\\beg virtual E_TEHTA_INF\n  \\class THINF_ACCENT_XS TELCO ARA ROMEN ARDA SILME_NUQUERNA HARP_SHAPED\n  \\class THINF_ACCENT_S TINCO PARMA TW_EXT_11 TW_EXT_12 SILME URE OSSE SHOOK_BEAUTIFUL AHA_TINCO HWESTA_TINCO\n  \\class THINF_ACCENT_L CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_13 TW_EXT_14 LAMBE ALDA SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN HWESTA_SINDARINWA YANTA\n  \\class THINF_ACCENT_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n\\end\n\n\\beg virtual CIRC_TEHTA_INF\n  \\class TH_SUB_CIRC_XS TELCO ARA ROMEN ARDA SILME_NUQUERNA HARP_SHAPED\n  \\class TH_SUB_CIRC_S TINCO PARMA TW_EXT_11 TW_EXT_12 SILME URE OSSE SHOOK_BEAUTIFUL AHA_TINCO HWESTA_TINCO\n  \\class TH_SUB_CIRC_L CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_13 TW_EXT_14 LAMBE ALDA SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN HWESTA_SINDARINWA YANTA\n  \\class TH_SUB_CIRC_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n\\end\n\n\\beg virtual SEV_TEHTA_INF THINF_STROKE\n  \\class THINF_STROKE_XS TELCO ARA ROMEN ARDA SILME_NUQUERNA HARP_SHAPED\n  \\class THINF_STROKE_S TINCO PARMA TW_EXT_11 TW_EXT_12 SILME URE OSSE SHOOK_BEAUTIFUL AHA_TINCO HWESTA_TINCO\n  \\class THINF_STROKE_L CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_13 TW_EXT_14 LAMBE ALDA SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN HWESTA_SINDARINWA YANTA\n  \\class THINF_STROKE_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n\\end\n\n\\beg virtual O_TEHTA_INF\n  \\class THINF_CURL_XS TELCO ARA ROMEN ARDA SILME_NUQUERNA HARP_SHAPED\n  \\class THINF_CURL_S TINCO PARMA TW_EXT_11 TW_EXT_12 SILME URE OSSE SHOOK_BEAUTIFUL AHA_TINCO HWESTA_TINCO\n  \\class THINF_CURL_L CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_13 TW_EXT_14 LAMBE ALDA SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN HWESTA_SINDARINWA YANTA\n  \\class THINF_CURL_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n\\end\n\n\\beg virtual U_TEHTA_INF\n  \\class THINF_CURL_INV_XS TELCO ARA ROMEN ARDA SILME_NUQUERNA HARP_SHAPED\n  \\class THINF_CURL_INV_S TINCO PARMA TW_EXT_11 TW_EXT_12 SILME URE OSSE SHOOK_BEAUTIFUL AHA_TINCO HWESTA_TINCO\n  \\class THINF_CURL_INV_L CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_13 TW_EXT_14 LAMBE ALDA SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN HWESTA_SINDARINWA YANTA\n  \\class THINF_CURL_INV_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n\\end\n\n\\beg virtual Y_TEHTA_INF PALATAL_SIGN \n  \\class THINF_DDOT_XS TELCO ARA ROMEN ARDA SILME_NUQUERNA HARP_SHAPED\n  \\class THINF_DDOT_S TINCO PARMA TW_EXT_11 TW_EXT_12 SILME URE OSSE SHOOK_BEAUTIFUL AHA_TINCO HWESTA_TINCO\n  \\class THINF_DDOT_L CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_13 TW_EXT_14 SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN HWESTA_SINDARINWA YANTA\n  \\class THINF_DDOT_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n  \\class LAMBE_MARK_DDOT LAMBE ALDA\n\\end\n\n\\beg virtual E_TEHTA_DOUBLE_INF GEMINATE_DOUBLE\n  \\class THINF_DSTROKE_XS TELCO ARA ROMEN ARDA SILME_NUQUERNA HARP_SHAPED\n  \\class THINF_DSTROKE_S TINCO PARMA TW_EXT_11 TW_EXT_12 SILME URE OSSE SHOOK_BEAUTIFUL AHA_TINCO HWESTA_TINCO\n  \\class THINF_DSTROKE_L CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_13 TW_EXT_14 SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN HWESTA_SINDARINWA YANTA\n  \\class THINF_DSTROKE_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n  \\class LAMBE_MARK_DSTROKE LAMBE ALDA\n\\end\n\n\\beg virtual I_TEHTA_INF NO_VOWEL_DOT UNUTIXE\n  \\class THINF_DOT_XS TELCO ARA ROMEN ARDA SILME_NUQUERNA HARP_SHAPED\n  \\class THINF_DOT_S TINCO PARMA TW_EXT_11 TW_EXT_12 SILME URE OSSE SHOOK_BEAUTIFUL AHA_TINCO HWESTA_TINCO\n  \\class THINF_DOT_L CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_13 TW_EXT_14 SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN HWESTA_SINDARINWA YANTA\n  \\class THINF_DOT_XL ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n  \\class LAMBE_MARK_DOT LAMBE ALDA\n\\end\n\n\\beg virtual GEMINATE_SIGN\n  \\class DASH_INF_S TELCO ARA TINCO PARMA CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 TW_EXT_13 TW_EXT_14 ROMEN ARDA SILME SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN HWESTA_SINDARINWA YANTA URE OSSE SHOOK_BEAUTIFUL HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class DASH_INF_L ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n  \\class LAMBE_MARK_DASH LAMBE ALDA\n\\end\n\n\\beg virtual GEMINATE_SIGN_TILD\n  \\class TILD_INF_S TELCO ARA TINCO PARMA CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 TW_EXT_13 TW_EXT_14 ROMEN ARDA SILME SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN HWESTA_SINDARINWA YANTA URE OSSE SHOOK_BEAUTIFUL HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class TILD_INF_L ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n  \\class LAMBE_MARK_TILD LAMBE ALDA\n\\end\n\n\\beg virtual NASALIZE_SIGN\n  \\class DASH_SUP_S TELCO ARA TINCO PARMA CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 TW_EXT_13 TW_EXT_14 ROMEN ARDA LAMBE ALDA SILME SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE HYARMEN YANTA URE OSSE SHOOK_BEAUTIFUL HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class DASH_SUP_L ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n\\end\n\n\\beg virtual NASALIZE_SIGN_TILD\n  \\class TILD_SUP_S TELCO ARA TINCO PARMA CALMA QUESSE SULE FORMEN AHA HWESTA ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 TW_EXT_13 TW_EXT_14 ROMEN ARDA LAMBE ALDA SILME SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE HYARMEN YANTA URE OSSE SHOOK_BEAUTIFUL HARP_SHAPED AHA_TINCO HWESTA_TINCO\n  \\class TILD_SUP_L ANDO UMBAR ANGA UNGWE ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME TW_EXT_21 TW_EXT_22 TW_EXT_23 TW_EXT_24 ANCA_CLOSED\n\\end\n\n\\beg virtual ALVEOLAR_SIGN\n  \\class SHOOK_LEFT_L TELCO ARA CALMA QUESSE ANGA UNGWE TW_EXT_13 TW_EXT_14 TW_EXT_23 TW_EXT_24 HWESTA_SINDARINWA\n  \\class SHOOK_RIGHT_L TINCO PARMA ANDO UMBAR SULE FORMEN AHA HWESTA ANTO AMPA ANCA UNQUE NUMEN MALTA NOLDO NWALME ORE VALA ANNA VILYA TW_EXT_11 TW_EXT_12 TW_EXT_21 TW_EXT_22 ROMEN ARDA LAMBE ALDA SILME SILME_NUQUERNA SILME_NUQUERNA_ALT ESSE ESSE_NUQUERNA HYARMEN YANTA URE OSSE SHOOK_BEAUTIFUL ANCA_CLOSED HARP_SHAPED AHA_TINCO HWESTA_TINCO\n\\end\n"
Glaemscribe.resource_manager.raw_modes["adunaic"] = "\\**\n\nGlǽmscribe (also written Glaemscribe) is a software dedicated to\nthe transcription of texts between writing systems, and more \nspecifically dedicated to the transcription of J.R.R. Tolkien\'s \ninvented languages to some of his devised writing systems.\n\nCopyright (C) 2015 Benjamin Babut (Talagan).\n\nThis program is free software: you can redistribute it and/or modify\nit under the terms of the GNU Affero General Public License as published by\nthe Free Software Foundation, either version 3 of the License, or\nany later version.\n\nThis program is distributed in the hope that it will be useful,\nbut WITHOUT ANY WARRANTY; without even the implied warranty of\nMERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\nGNU Affero General Public License for more details.\n\nYou should have received a copy of the GNU Affero General Public License\nalong with this program.  If not, see <http://www.gnu.org/licenses/>.\n\n**\\\n\n\\beg changelog\n \\entry \"0.0.2\" \"Added option for o/u tehtar loop orientation\"\n \\entry \"0.0.3\" \"Normalizing to virtual chars\" \n \\entry \"0.0.4\" \"Added charset support for Annatar\"\n \\entry \"0.0.5\" \"Added support for the FreeMonoTengwar font\"\n \\entry \"0.0.6\" \"Added wave/bar option for consonant modifications sign\"\n \\entry \"0.1.0\" \"Added support for the Tengwar Elfica font\"\n\\end\n\n\\**  Adunaic mode for glaemscribe (MAY BE INCOMPLETE) **\\\n\\language Adûnaic\n\\writing  Tengwar\n\\mode     Glaemscrafu\n\\version  \"0.1.0\"\n\\authors  \"Talagan (Benjamin Babut), based on J.R.R Tolkien\"\n\n\\charset  tengwar_ds_sindarin true\n\\charset  tengwar_ds_parmaite false\n\\charset  tengwar_ds_eldamar  false\n\\charset  tengwar_ds_annatar  false\n\\charset  tengwar_ds_elfica   false\n\\charset  tengwar_freemono    false\n\n\\beg      options\n  \\beg option reverse_o_u_tehtar O_UP_U_DOWN\n    \\value O_UP_U_DOWN 1\n    \\value U_UP_O_DOWN 2\n  \\end\n\n  \\beg option consonant_modification_style CONSONANT_MODIFICATION_STYLE_BAR\n    \\value CONSONANT_MODIFICATION_STYLE_WAVE 0\n    \\value CONSONANT_MODIFICATION_STYLE_BAR 1\n  \\end\n\n  \\option reverse_numbers true\n  \\beg option numbers_base BASE_12\n    \\value    BASE_10 10\n    \\value    BASE_12 12\n  \\end\n\n\\end\n\n\\beg      preprocessor\n  \\** Work exclusively downcase **\\\n  \\downcase\n  \n  \\** Simplify trema vowels **\\\n  \\substitute \"ä\" \"a\"\n  \\substitute \"ë\" \"e\"\n  \\substitute \"ï\" \"i\"\n  \\substitute \"ö\" \"o\"\n  \\substitute \"ü\" \"u\"\n  \\substitute \"ÿ\" \"y\"\n  \n  \\** Dis-ambiguate long vowels **\\\n  \\rxsubstitute \"(ā|â|aa)\" \"á\"\n  \\rxsubstitute \"(ē|ê|ee)\" \"é\"\n  \\rxsubstitute \"(ī|î|ii)\" \"í\"\n  \\rxsubstitute \"(ō|ô|oo)\" \"ó\"\n  \\rxsubstitute \"(ū|û|uu)\" \"ú\"\n  \\rxsubstitute \"(ȳ|ŷ|yy)\" \"ý\"\n  \n  \\** Preprocess numbers **\\\n  \\elvish_numbers \"\\\\eval numbers_base\" \"\\\\eval reverse_numbers\"\n\\end\n\n\\beg      processor\n\n  \\beg    rules litteral  \n  \n    \\if \"consonant_modification_style == CONSONANT_MODIFICATION_STYLE_WAVE\"\n      {GEMINATE} === GEMINATE_SIGN_TILD\n    \\else\n      {GEMINATE} === GEMINATE_SIGN\n    \\endif\n  \n    {A}   === a\n    {AA}  === á\n    {E}   === e\n    {EE}  === é\n    {I}   === i\n    {II}  === í\n    {O}   === o\n    {OO}  === ó\n    {U}   === u\n    {UU}  === ú\n\n    \\** Short diphthongs **\\\n    {AI}  === {A}{I}\n    {AU}  === {A}{U}\n\n    \\** LONG diphthongs **\\      \n    {AAI} === {AA}{I} \\** âi **\\\n    {AAU} === {AA}{U} \\** âu **\\\n    {EEI} === {EE}{I} \\** êi **\\\n    {EEU} === {EE}{U} \\** êu **\\\n    {OOI} === {OO}{I} \\** ôi **\\\n    {OOU} === {OO}{U} \\** ôu **\\\n        \n    \\if \"reverse_o_u_tehtar == U_UP_O_DOWN\"\n      {O_LOOP} === O_TEHTA\n      {U_LOOP} === U_TEHTA\n    \\else\n      {O_LOOP} === U_TEHTA\n      {U_LOOP} === O_TEHTA\n    \\endif\n        \n    {SDIPHTHONGS}  === {AI}           * {AU}\n    {SDIPHTHENGS}  === YANTA A_TEHTA  * URE A_TEHTA \n                   \n    {LDIPHTHONGS}  === {AAI}               * {AAU}              * {EEI}              * {EEU}            * {OOI}               * {OOU}\n    {LDIPHTHENGS}  === ARA A_TEHTA YANTA   * ARA A_TEHTA URE    * ARA E_TEHTA YANTA  * ARA E_TEHTA URE  * ARA {O_LOOP} YANTA  * ARA {O_LOOP} URE\n                                                                                                                 \n    {VOWELS}      === {A}          * {E}          * {I}          * {O}          * {U}    \n    {_TEHTAR_}    === A_TEHTA      * E_TEHTA      *  I_TEHTA     * {O_LOOP}     * {U_LOOP}\n                   \n    {LVOWELS}     === {AA}         * {EE}         * {II}         * {OO}         * {UU}\n    {LVOWTNG}     === ARA A_TEHTA  * ARA E_TEHTA  * ARA I_TEHTA  * ARA {O_LOOP} * ARA {U_LOOP} \n\n    \\** Let\' put all vowels/diphthongs in the same basket **\\\n    {V_D}         === [ {VOWELS}    * {LVOWELS} * {SDIPHTHONGS} * {LDIPHTHONGS} ]        \n    \\** And their images... **\\            \n    {_V_D_}       === [ {_TEHTAR_}  * {LVOWTNG} * {SDIPHTHENGS} * {LDIPHTHENGS} ]\n  \n    [{VOWELS}]       --> TELCO [{_TEHTAR_}]   \\** Replace isolated short vowels **\\\n    [{LVOWELS}]      --> [{LVOWTNG}]    \\** Replace long vowels **\\\n    [{SDIPHTHONGS}]  --> [{SDIPHTHENGS}]  \\** Replace short diphthongs **\\\n    [{LDIPHTHONGS}]  --> [{LDIPHTHENGS}]  \\** Replace long diphthongs **\\\n\n    \\** ================ **\\\n    \\**    CONSONANTS    **\\\n    \\** ================ **\\     \n    {K}   === (c,k)\n    {V}   === (v,w)\n\n    {L1_S}         === {K}     * p     * t     * {K}{K}            * pp                * tt\n    {L1_T}         === QUESSE  * PARMA * TINCO * CALMA {GEMINATE}  * PARMA {GEMINATE}  * TINCO  {GEMINATE}\n    \n    [{L1_S}]       -->  [ {L1_T} ]\n    [{L1_S}]{V_D}  -->  [ {L1_T} ]{_V_D_} \n\n    {L2_S}         === d    * b     * g     * dd              * bb                * gg\n    {L2_T}         === ANDO * UMBAR * UNGWE * ANDO {GEMINATE} * UMBAR {GEMINATE}  * UNGWE {GEMINATE}\n    [{L2_S}]       --> [{L2_T}] \n    [{L2_S}]{V_D}  --> [{L2_T}]{_V_D_} \n\n    \\** Alignment of tehta is not the same in the font **\\\n    \\** So we need to split the third line unfortunately **\\\n    {L3_1_S}          === th    * ph      * (t,th)th          * (p,ph)ph            * (t,th)ph    * (k,kh)ph      * (p,ph)th    * (k,kh)th\n    {L3_1_T}          === SULE  * FORMEN  * SULE {GEMINATE}   * FORMEN {GEMINATE}   * SULE FORMEN * HWESTA FORMEN * FORMEN SULE * HWESTA SULE\n   \n    {L3_2_S}          === sh    * kh      * (k,kh)kh          * (p,ph)kh            * (t,th)kh\n    {L3_2_T}          === AHA   * HWESTA  * HWESTA {GEMINATE} * FORMEN HWESTA       * SULE HWESTA\n   \n    [{L3_1_S}]        --> [{L3_1_T}] \n    [{L3_1_S}]{V_D}   --> [{L3_1_T}]{_V_D_} \n    [{L3_2_S}]        --> [{L3_2_T}] \n    [{L3_2_S}]{V_D}   --> [{L3_2_T}]{_V_D_} \n\n    {L4_S}            === nd    * mb    * ng\n    {L4_T}            === ANTO  * AMPA  * UNQUE\n    [{L4_S}]          --> [{L4_T}] \n    [{L4_S}]{V_D}     --> [{L4_T}]{_V_D_} \n\n    {L5_S}            === n     * m     * nn                 * mm\n    {L5_T}            === NUMEN * MALTA * NUMEN {GEMINATE}   * MALTA {GEMINATE}\n    [{L5_S}]          --> [{L5_T}] \n    [{L5_S}]{V_D}     --> [{L5_T}]{_V_D_} \n\n    {L6_S}            === {V}   * y     * rr                 * {V}{V}             * yy\n    {L6_T}            === VALA  * ANNA  * ROMEN {GEMINATE}   * VALA {GEMINATE}    * ANNA {GEMINATE}\n    [r * {L6_S}]      --> [ ORE   * {L6_T}] \n    [r * {L6_S}]{V_D} --> [ ROMEN * {L6_T}]{_V_D_} \n\n    \\** This one is not useful (redundant with higher) **\\\n    \\** Keep it for clarity of mind **\\\n    r_                --> ORE\n\n    s{V_D}            --> SILME_NUQUERNA {_V_D_}  \\** Before a vowel goes down **\\\n    s                 --> SILME                   \\** Any other pos, up **\\\n    z{V_D}            --> ESSE_NUQUERNA {_V_D_}   \\** Before a vowel goes down **\\\n    z                 --> ESSE                    \\** Any other pos, up **\\\n\n    h{V_D}            --> HYARMEN {_V_D_}\n    h                 --> HYARMEN\n    hh{V_D}           --> HYARMEN {GEMINATE} {_V_D_}\n    hh                --> HYARMEN {GEMINATE}\n                      \n    l{V_D}            --> LAMBE {_V_D_}\n    l                 --> LAMBE\n                      \n    ll{V_D}           --> LAMBE {GEMINATE} {_V_D_}\n    ll                --> LAMBE {GEMINATE}\n  \n  \\end\n  \n  \\beg rules punctutation\n    . --> PUNCT_DDOT\n    .. --> PUNCT_DOT PUNCT_DDOT PUNCT_DOT\n    …  --> PUNCT_TILD\n    ... --> PUNCT_TILD\n    .... --> PUNCT_TILD\n    ..... --> PUNCT_TILD\n    ...... --> PUNCT_TILD\n    ....... --> PUNCT_TILD\n\n    , --> PUNCT_DOT\n    : --> PUNCT_DOT\n    ; --> PUNCT_DOT\n    ! --> PUNCT_EXCLAM\n    ? --> PUNCT_INTERR\n    · --> PUNCT_DOT\n\n    \\** Apostrophe **\\\n\n    \' --> {NULL}\n    ’ --> {NULL}\n\n    \\** Quotes **\\\n\n    “ --> DQUOT_OPEN\n    ” --> DQUOT_CLOSE\n    « --> DQUOT_OPEN \n    » --> DQUOT_CLOSE \n    \n    - --> PUNCT_DOT    \n    – --> PUNCT_TILD  \n    — --> PUNCT_DTILD\n \n    [ --> PUNCT_PAREN_L\n    ] --> PUNCT_PAREN_R\n    ( --> PUNCT_PAREN_L\n    ) --> PUNCT_PAREN_R\n    { --> PUNCT_PAREN_L\n    } --> PUNCT_PAREN_R\n    < --> PUNCT_PAREN_L\n    > --> PUNCT_PAREN_R  \n\n    \\** Not universal between fonts ... **\\\n    $ --> BOOKMARK_SIGN\n    ≤ --> RING_MARK_L \\** Ring inscription left beautiful stuff **\\\n    ≥ --> RING_MARK_R \\** Ring inscription right beautiful stuff **\\\n  \\end\n\n  \\beg rules numbers\n    0 --> NUM_0\n    1 --> NUM_1\n    2 --> NUM_2\n    3 --> NUM_3\n    4 --> NUM_4\n    5 --> NUM_5\n    6 --> NUM_6\n    7 --> NUM_7\n    8 --> NUM_8\n    9 --> NUM_9\n    A --> NUM_10\n    B --> NUM_11   \n  \\end\n  \n\\end\n\n\\beg postprocessor\n  \\resolve_virtuals\n\\end\n"
Glaemscribe.resource_manager.raw_modes["blackspeech"] = "\\**\n\nGlǽmscribe (also written Glaemscribe) is a software dedicated to\nthe transcription of texts between writing systems, and more \nspecifically dedicated to the transcription of J.R.R. Tolkien\'s \ninvented languages to some of his devised writing systems.\n\nCopyright (C) 2015 Benjamin Babut (Talagan).\n\nThis program is free software: you can redistribute it and/or modify\nit under the terms of the GNU Affero General Public License as published by\nthe Free Software Foundation, either version 3 of the License, or\nany later version.\n\nThis program is distributed in the hope that it will be useful,\nbut WITHOUT ANY WARRANTY; without even the implied warranty of\nMERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\nGNU Affero General Public License for more details.\n\nYou should have received a copy of the GNU Affero General Public License\nalong with this program.  If not, see <http://www.gnu.org/licenses/>.\n\n**\\\n\n\\** BlackSpeech ring mode for glaemscribe (MAY BE INCOMPLETE) **\\\n\n\\beg changelog\n  \\entry 0.0.1 \"First version\"\n  \\entry 0.0.2 \"Ported to virtual chars.\"\n  \\entry 0.0.3 \"Merging with blackspeech Annatar\".\n  \\entry 0.0.4 \"Adding double tehtar handling.\"\n  \\entry 0.0.5 \"Fixing ORE/ROMEN, refactoring.\"\n  \\entry 0.0.6 \"Added support for the FreeMonoTengwar font\"\n  \\entry \"0.1.0\" \"Added support for the Tengwar Elfica font\"\n\\end\n\n\\language \"Black Speech\"\n\\writing  \"Tengwar\"\n\\mode     \"General Use\"\n\\version  \"0.1.0\"\n\\authors  \"J.R.R. Tolkien, impl. Talagan (Benjamin Babut)\"\n \n\\charset  tengwar_ds_sindarin false\n\\charset  tengwar_ds_parmaite false\n\\charset  tengwar_ds_eldamar  false\n\\charset  tengwar_ds_annatar  true\n\\charset  tengwar_ds_elfica   false\n\\charset  tengwar_freemono    false\n\n\\beg      options\n\n  \\beg option reverse_o_u_tehtar O_UP_U_DOWN\n    \\value O_UP_U_DOWN 1\n    \\value U_UP_O_DOWN 2\n  \\end\n\n  \\beg option long_vowels_format LONG_VOWELS_USE_DOUBLE_TEHTAR\n    \\value LONG_VOWELS_USE_LONG_CARRIER 1\n    \\value LONG_VOWELS_USE_DOUBLE_TEHTAR 2\n  \\end  \n  \n  \\beg option double_tehta_e true\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \\beg option double_tehta_i true\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \\beg option double_tehta_o true\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \\beg option double_tehta_u true\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \n  \\beg option consonant_modification_style CONSONANT_MODIFICATION_STYLE_WAVE\n    \\value CONSONANT_MODIFICATION_STYLE_WAVE 0\n    \\value CONSONANT_MODIFICATION_STYLE_BAR 1\n  \\end\n  \n  \\option reverse_numbers true\n  \\beg option numbers_base BASE_12\n    \\value    BASE_10 10\n    \\value    BASE_12 12\n  \\end\n\\end\n\n\\beg      preprocessor\n  \\** Work exclusively downcase **\\\n  \\downcase\n  \n  \\** Simplify trema vowels **\\\n  \\substitute \"ä\" \"a\"\n  \\substitute \"ë\" \"e\"\n  \\substitute \"ï\" \"i\"\n  \\substitute \"ö\" \"o\"\n  \\substitute \"ü\" \"u\"\n  \\substitute \"ÿ\" \"y\"\n\n  \\** Dis-ambiguate long vowels **\\\n  \\rxsubstitute \"(ā|â|aa)\" \"á\"\n  \\rxsubstitute \"(ē|ê|ee)\" \"é\"\n  \\rxsubstitute \"(ī|î|ii)\" \"í\"\n  \\rxsubstitute \"(ō|ô|oo)\" \"ó\"\n  \\rxsubstitute \"(ū|û|uu)\" \"ú\"\n\n  \\** For ORE/ROMEN **\\\n  \\rxsubstitute \"r(a|e|i|o|u|á|é|í|ó|ú)\" \"R\\\\1\"\n\n  \\** Preprocess numbers **\\\n  \\elvish_numbers \"\\\\eval numbers_base\" \"\\\\eval reverse_numbers\"\n\\end\n\n\\beg      processor\n\n  \\beg    rules litteral\n     \n    {K}                 === (c,k)\n    \n    \\if \"consonant_modification_style == CONSONANT_MODIFICATION_STYLE_WAVE\"\n      {GEMINATE} === GEMINATE_SIGN_TILD\n      {NASAL}    === NASALIZE_SIGN_TILD\n    \\else\n      {GEMINATE} === GEMINATE_SIGN\n      {NASAL}    === NASALIZE_SIGN\n    \\endif\n    \n    {VOWELS}            === a               *  e              * i              * o              *  u\n    {LVOWELS}           === á               *  é              * í              * ó              *  ú\n    \n    \\if \"reverse_o_u_tehtar == U_UP_O_DOWN\"\n      {O_LOOP}        === O_TEHTA\n      {O_LOOP_DOUBLE} === O_TEHTA_DOUBLE\n      {U_LOOP}        === U_TEHTA\n      {U_LOOP_DOUBLE} === U_TEHTA_DOUBLE\n    \\else\n      {O_LOOP}        === U_TEHTA\n      {O_LOOP_DOUBLE} === U_TEHTA_DOUBLE\n      {U_LOOP}        === O_TEHTA\n      {U_LOOP_DOUBLE} === O_TEHTA_DOUBLE\n    \\endif   \n       \n    {_TEHTAR_}          === A_TEHTA         * E_TEHTA         * I_TEHTA       *  {O_LOOP}  * {U_LOOP}      \n     \n    {DIPHTHONGS}        === ai              * au              * oi          \n    {_DIPHTHONGS_}      === YANTA A_TEHTA   * URE A_TEHTA     * YANTA {O_LOOP}  \n    {WDIPHTHONGS}       === * {DIPHTHONGS}\n    {_WDIPHTHONGS_}     === * {_DIPHTHONGS_}\n      \n		{WLONG}     === {NULL} \\** long vowels that can be used as tehtar **\\\n    {_WLONG_}   === {NULL} \\** tehtar of long vowels that can be used as tehtar **\\\n		\n		{_LONG_A_}      === ARA A_TEHTA\n		{_LONG_E_}      === ARA E_TEHTA	\n		{_LONG_I_}      === ARA I_TEHTA\n		{_LONG_O_}      === ARA {O_LOOP}	\n		{_LONG_U_}      === ARA {U_LOOP}\n		{_LONE_LONG_A_} === {_LONG_A_}\n		{_LONE_LONG_E_} === {_LONG_E_}\n		{_LONE_LONG_I_} === {_LONG_I_}\n		{_LONE_LONG_O_} === {_LONG_O_}\n		{_LONE_LONG_U_} === {_LONG_U_}\n    \n		\\if \"long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\"\n	    \\if double_tehta_e\n		    {_LONG_E_}       === E_TEHTA_DOUBLE\n		    {_LONE_LONG_E_}  === TELCO {_LONG_E_}\n				{WLONG}          === {WLONG}   * é\n        {_WLONG_}        === {_WLONG_} * {_LONG_E_}\n			\\endif\n		  \\if double_tehta_i\n		    {_LONG_I_}       === I_TEHTA_DOUBLE\n		    {_LONE_LONG_I_}  === TELCO {_LONG_I_}\n				{WLONG}          === {WLONG}   * í             \n        {_WLONG_}        === {_WLONG_} * {_LONG_I_}\n		  \\endif\n		  \\if double_tehta_o\n		    {_LONG_O_}       === {O_LOOP_DOUBLE}\n		    {_LONE_LONG_O_}  === TELCO {_LONG_O_}\n				{WLONG}          === {WLONG}   * ó             \n        {_WLONG_}        === {_WLONG_} * {_LONG_O_}\n		  \\endif\n		  \\if double_tehta_u\n		    {_LONG_U_}       === {U_LOOP_DOUBLE}\n		    {_LONE_LONG_U_}  === TELCO {_LONG_U_}\n				{WLONG}          === {WLONG}   * ú            \n        {_WLONG_}        === {_WLONG_} * {_LONG_U_}\n		  \\endif\n    \\endif  \n			\n		{_LTEHTAR_}     === {_LONG_A_} * {_LONG_E_} * {_LONG_I_} * {_LONG_O_} * {_LONG_U_}\n         \n    {V_D}           === [ {VOWELS} {WLONG}  ]\n    {V_D_WN}        === [ {VOWELS} {WLONG} * {NULL} ]\n\n    {_V_D_}         === [ {_TEHTAR_} {_WLONG_} ]\n    {_V_D_WN_}      === [ {_TEHTAR_} {_WLONG_} * {NULL} ]\n		\n		\\** LONE SHORT VOWELS **\\\n    [{VOWELS}]    --> TELCO [{_TEHTAR_}]  \\** Replace isolated short vowels **\\\n    \n		\\** LONE LONG VOWELS **\\	\n		[{LVOWELS}]   --> [{_LONE_LONG_A_} * {_LONE_LONG_E_} * {_LONE_LONG_I_} * {_LONE_LONG_O_} * {_LONE_LONG_U_}]\n    \n    [{DIPHTHONGS}] -->   [{_DIPHTHONGS_}]     \\**  Replace diphthongs **\\\n    \n    \\** ========================= **\\\n    \n    {V_D_WN}p     --> PARMA {_V_D_WN_}\n    {V_D_WN}t     --> TINCO {_V_D_WN_}\n    {V_D_WN}{K}   --> QUESSE {_V_D_WN_}\n  \n    {V_D_WN} b  --> UMBAR {_V_D_WN_}\n    {V_D_WN} d  --> ANDO {_V_D_WN_}\n    {V_D_WN} f  --> FORMEN_EXT {_V_D_WN_} \\** Beware. **\\ \n    {V_D_WN} g  --> UNGWE {_V_D_WN_}\n    {V_D_WN} gh --> UNGWE_EXT {_V_D_WN_}\n    {V_D_WN} h  --> HYARMEN {_V_D_WN_}\n\n    \\** ======================== **\\\n\n    {K}h          --> HWESTA\n    {V_D}{K}h     --> HWESTA_EXT {_V_D_} \\** Take care. **\\  \n\n    \\** ======================== **\\\n\n    {V_D_WN} l  --> LAMBE {_V_D_WN_} \n\n    \\** ======================== **\\\n\n    {V_D_WN} m  --> MALTA {_V_D_WN_}\n    {V_D_WN} mb --> UMBAR {NASAL} {_V_D_WN_}\n    {V_D_WN} mp --> PARMA {NASAL} {_V_D_WN_}\n\n    \\** ======================== **\\\n    \n    {V_D_WN}n   --> NUMEN {_V_D_WN_} \n    {V_D_WN}n{K}  --> QUESSE {NASAL} {_V_D_WN_} \n\n    \\** ======================== **\\\n    \n    \\** ROMEN / ORE handling probably not accurate **\\\n    {V_D_WN}r   --> ORE   {_V_D_WN_}\n    {V_D_WN}R   --> ROMEN {_V_D_WN_}\n    \n    \\** ======================== **\\\n\n    s             --> SILME\n    {V_D} s       --> SILME_NUQUERNA {_V_D_}\n    z             --> ESSE    \n    {V_D} z       --> ESSE_NUQUERNA {_V_D_}\n\n    \\** ======================== **\\\n\n    sh            --> AHA             \n    {V_D} sh      --> AHA_EXT {_V_D_} \\** BEWARE. **\\\n    \n\n    th            --> SULE\n    \n    y             --> ANNA\n\n  \\end\n  \n  \\beg rules punctuation\n    . --> PUNCT_DDOT\n    .. --> PUNCT_DOT PUNCT_DDOT PUNCT_DOT\n    …  --> PUNCT_TILD\n    ... --> PUNCT_TILD\n    .... --> PUNCT_TILD\n    ..... --> PUNCT_TILD\n    ...... --> PUNCT_TILD\n    ....... --> PUNCT_TILD\n\n    , --> PUNCT_DOT\n    : --> PUNCT_DOT\n    ; --> PUNCT_DOT\n    ! --> PUNCT_EXCLAM\n    ? --> PUNCT_INTERR\n    · --> PUNCT_DOT\n\n    \\** Apostrophe **\\\n\n    \' --> {NULL}\n    ’ --> {NULL}\n\n    \\** Quotes **\\\n\n    “ --> DQUOT_OPEN\n    ” --> DQUOT_CLOSE\n    « --> DQUOT_OPEN \n    » --> DQUOT_CLOSE \n    \n    - --> {NULL}     \n    – --> PUNCT_TILD  \n    — --> PUNCT_TILD\n    \n    [ --> PUNCT_PAREN_L\n    ] --> PUNCT_PAREN_R\n    ( --> PUNCT_PAREN_L\n    ) --> PUNCT_PAREN_R\n    { --> PUNCT_PAREN_L\n    } --> PUNCT_PAREN_R\n    < --> PUNCT_PAREN_L\n    > --> PUNCT_PAREN_R\n\n    \\** Not universal between fonts ... **\\\n    $ --> BOOKMARK_SIGN\n    ≤ --> RING_MARK_L \\** Ring inscription left beautiful stuff **\\\n    ≥ --> RING_MARK_R \\** Ring inscription right beautiful stuff **\\\n\n  \\end\n  \n  \\beg rules numbers\n    0 --> NUM_0\n    1 --> NUM_1\n    2 --> NUM_2\n    3 --> NUM_3\n    4 --> NUM_4\n    5 --> NUM_5\n    6 --> NUM_6\n    7 --> NUM_7\n    8 --> NUM_8\n    9 --> NUM_9\n    A --> NUM_10\n    B --> NUM_11      \n  \\end\n  \n\\end\n\n\\beg postprocessor\n  \\resolve_virtuals\n\\end\n"
Glaemscribe.resource_manager.raw_modes["quenya"] = "\\**\n\nGlǽmscribe (also written Glaemscribe) is a software dedicated to\nthe transcription of texts between writing systems, and more\nspecifically dedicated to the transcription of J.R.R. Tolkien\'s\ninvented languages to some of his devised writing systems.\n\nCopyright (C) 2015 Benjamin Babut (Talagan).\n\nThis program is free software: you can redistribute it and/or modify\nit under the terms of the GNU Affero General Public License as published by\nthe Free Software Foundation, either version 3 of the License, or\nany later version.\n\nThis program is distributed in the hope that it will be useful,\nbut WITHOUT ANY WARRANTY; without even the implied warranty of\nMERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\nGNU Affero General Public License for more details.\n\nYou should have received a copy of the GNU Affero General Public License\nalong with this program.  If not, see <http://www.gnu.org/licenses/>.\n\n**\\\n\n\\beg changelog\n  \\entry \"0.0.2\" \"added χ for the word χarina, correcting ts/ps sequences to work better with eldamar\"\n  \\entry \"0.0.3\" \"added o/u curl option\"\n  \\entry \"0.0.4\" \"added voiced plosives corner cases treatment and option to chose method\"\n  \\entry \"0.0.5\" \"fixing h+long vowel medially\"\n  \\entry \"0.0.6\" \"adding option for alveolarized consonants  st (t+t), pt (p+t), ht (c+t)\"\n  \\entry \"0.0.7\" \"Fixing rb/lb, to be treated as r+mb and l+mb\"\n  \\entry \"0.0.8\" \"Correcting double dot version for ry (aesthetics)\"\n  \\entry \"0.0.9\" \"Adding \'implicit a\' option.\"\n  \\entry \"0.1.0\" \"Simplified diacritic use by using new post-processor directive\"\n  \\entry \"0.1.1\" \"Added default option for voiced plosives : use mb, nd, ng, ngw\"\n  \\entry \"0.1.2\" \"Added a tehta shape selection\"\n  \\entry \"0.1.3\" \"Fixing ks, ps, ts. Fixing dot under ore, romen in implicit a mode.\"\n  \\entry \"0.1.4\" \"Conforming to the new csub format. Cleaning with new csub classes.\"\n  \\entry \"0.1.5\" \"csub removed. Now using virtual chars defined in charsets.\"\n  \\entry \"0.1.6\" \"Removing unutixe under óre for coherency in implicit a submode.\"\n  \\entry \"0.9.0\" \"Adding double tehtar support\"\n  \\entry \"0.9.1\" \"Added support for the FreeMonoTengwar font\"\n  \\entry \"0.9.2\" \"Added support for the Tengwar Elfica font\"\n\\end\n\n\\**\n  TODO : Option for dot or not in \'a implicit\' option before long vowels ?\n  TODO : bb, dd etc ? (for noobs)\n**\\\n\n\\language \"Quenya\"\n\\writing  \"Tengwar\"\n\\mode     \"Classical\"\n\\version  \"0.9.2\"\n\\authors  \"J.R.R. Tolkien, impl. Talagan (Benjamin Babut)\"\n\n\\charset  tengwar_ds_sindarin true\n\\charset  tengwar_ds_parmaite false\n\\charset  tengwar_ds_eldamar  false\n\\charset  tengwar_ds_annatar  false\n\\charset  tengwar_ds_elfica   false\n\\charset  tengwar_freemono    false\n\n\n\\beg      options\n  \\option implicit_a false\n  \n  \\beg option a_tetha_shape A_SHAPE_THREE_DOTS\n    \\value A_SHAPE_THREE_DOTS 1\n    \\value A_SHAPE_CIRCUMFLEX 2\n  \\end\n  \\beg option reverse_o_u_tehtar U_UP_O_DOWN\n    \\value O_UP_U_DOWN 1\n    \\value U_UP_O_DOWN 2\n  \\end\n  \\beg option long_vowels_format LONG_VOWELS_USE_LONG_CARRIER\n    \\value LONG_VOWELS_USE_LONG_CARRIER 1\n    \\value LONG_VOWELS_USE_DOUBLE_TEHTAR 2\n  \\end  \n  \n  \\** REMOVED BECAUSE UNATTESTED\n  \\beg option double_tehta_a false\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  **\\\n  \\beg option double_tehta_e false\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \\beg option double_tehta_i false\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \\beg option double_tehta_o true\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \\beg option double_tehta_u true\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \n  \\option split_diphthongs false\n  \\option always_use_romen_for_r false\n  \\beg option voiced_plosives_treatment VOICED_PLOSIVES_AS_NASALIZED\n    \\value VOICED_PLOSIVES_AS_NASALIZED 0\n    \\value VOICED_PLOSIVES_WITH_STROKE 1\n    \\value VOICED_PLOSIVES_WITH_XTD 2\n  \\end\n  \\beg option st_pt_ht ST_PT_HT_SEPARATED\n    \\value ST_PT_HT_SEPARATED 1\n    \\value ST_PT_HT_WITH_XTD 2\n  \\end\n  \n  \\beg option consonant_modification_style CONSONANT_MODIFICATION_STYLE_BAR\n    \\value CONSONANT_MODIFICATION_STYLE_WAVE 0\n    \\value CONSONANT_MODIFICATION_STYLE_BAR 1\n  \\end\n  \n  \\option reverse_numbers true\n  \\beg option numbers_base BASE_12\n    \\value    BASE_10 10\n    \\value    BASE_12 12\n  \\end\n\\end\n\n\\beg      preprocessor\n  \\** Work exclusively downcase **\\\n  \\downcase\n\n  \\** Simplify trema vowels **\\\n  \\substitute ä a\n  \\substitute ë e\n  \\substitute ï i\n  \\substitute ö o\n  \\substitute ü u\n  \\substitute ÿ y\n\n  \\** Dis-ambiguate long vowels **\\\n  \\rxsubstitute \"(ā|â|aa)\" \"á\"\n  \\rxsubstitute \"(ē|ê|ee)\" \"é\"\n  \\rxsubstitute \"(ī|î|ii)\" \"í\"\n  \\rxsubstitute \"(ō|ô|oo)\" \"ó\"\n  \\rxsubstitute \"(ū|û|uu)\" \"ú\"\n  \\rxsubstitute \"(ȳ|ŷ|yy)\" \"ý\"\n\n  \\** Dis-ambiguate qu **\\\n  \\substitute   \"qu\" \"q\" \n\n  \\elvish_numbers \"\\\\eval numbers_base\" \"\\\\eval reverse_numbers\"\n\\end\n\n\n\\beg processor\n\n  \\beg    rules litteral\n    \n    {K}                 ===  (c,k)\n    {SS}                ===  (z,ss)\n    \n    \\if \"consonant_modification_style == CONSONANT_MODIFICATION_STYLE_WAVE\"\n      {GEMINATE} === GEMINATE_SIGN_TILD\n    \\else\n      {GEMINATE} === GEMINATE_SIGN\n    \\endif\n\n    {VOWELS}            === a               *  e              * i              * o              *  u\n    {LVOWELS}           === á               *  é              * í              * ó              *  ú\n\n    \\if \"reverse_o_u_tehtar == U_UP_O_DOWN\"\n      {O_LOOP}        === O_TEHTA\n      {O_LOOP_DOUBLE} === O_TEHTA_DOUBLE\n      {U_LOOP}        === U_TEHTA\n      {U_LOOP_DOUBLE} === U_TEHTA_DOUBLE\n    \\else\n      {O_LOOP}        === U_TEHTA\n      {O_LOOP_DOUBLE} === U_TEHTA_DOUBLE\n      {U_LOOP}        === O_TEHTA\n      {U_LOOP_DOUBLE} === O_TEHTA_DOUBLE\n    \\endif\n\n    \\if \"a_tetha_shape == A_SHAPE_THREE_DOTS\"\n      {A_SHAPE}         === A_TEHTA\n    \\else\n      {A_SHAPE}         === A_TEHTA_CIRCUM\n    \\endif\n\n    \\if implicit_a\n      {_A_}              === {NULL}\n      {_NVOWEL_}         === NO_VOWEL_DOT\n    \\else\n      {_A_}              === {A_SHAPE}\n      {_NVOWEL_}         === {NULL}\n    \\endif\n\n    {_TEHTAR_}          === {_A_}      *  E_TEHTA     *  I_TEHTA    * {O_LOOP}    *  {U_LOOP}\n\n    \\if split_diphthongs\n      {WDIPHTHONGS}     === {NULL}\n      {_WDIPHTHONGS_}   === {NULL}\n    \\else\n      {DIPHTHONGS}      === ai            * au            * eu            * iu             * oi               * ui\n      {_DIPHTHONGS_}    === YANTA {_A_}   * URE {_A_}     * URE E_TEHTA   * URE I_TEHTA    * YANTA {O_LOOP}   * YANTA {U_LOOP}\n      {WDIPHTHONGS}     === * {DIPHTHONGS}   \\** groovy! **\\\n      {_WDIPHTHONGS_}   === * {_DIPHTHONGS_} \\** same thing **\\\n    \\endif\n    \n		{_LONG_A_}      === ARA {A_SHAPE}\n		{_LONG_E_}      === ARA E_TEHTA	\n		{_LONG_I_}      === ARA I_TEHTA\n		{_LONG_O_}      === ARA {O_LOOP}	\n		{_LONG_U_}      === ARA {U_LOOP}\n		{_LONE_LONG_A_} === {_LONG_A_}\n		{_LONE_LONG_E_} === {_LONG_E_}\n		{_LONE_LONG_I_} === {_LONG_I_}\n		{_LONE_LONG_O_} === {_LONG_O_}\n		{_LONE_LONG_U_} === {_LONG_U_}\n    \n    {LTEHTAR}       === {NULL}\n    {_LTEHTAR_}     === {NULL} 				\n    	\n		\\if implicit_a\n     	{_LONG_A_}         === {A_SHAPE}        \\** Eat the long a **\\\n  		{_LONE_LONG_A_}    === TELCO {A_SHAPE}  \\** Eat the long a **\\\n      {LTEHTAR}          === {LTEHTAR}   * á\n      {_LTEHTAR_}        === {_LTEHTAR_} * {_LONG_A_}\n    \\endif\n 		\n		\\if \"long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\"\n    \\** REMOVED BECAUSE UNATTESTED\n	    \\if \"double_tehta_a && !implicit_a\"\n		    {_LONG_A_}       === A_TEHTA_DOUBLE\n			  {_LONE_LONG_A_}  === TELCO {_LONG_A_}\n        {LTEHTAR}        === {LTEHTAR}   * á\n        {_LTEHTAR_}      === {_LTEHTAR_} * {_LONG_A_}\n  	  \\endif		\n    **\\\n	    \\if double_tehta_e\n		    {_LONG_E_}       === E_TEHTA_DOUBLE\n		    {_LONE_LONG_E_}  === TELCO {_LONG_E_}\n        {LTEHTAR}        === {LTEHTAR}   * é\n        {_LTEHTAR_}      === {_LTEHTAR_} * {_LONG_E_}\n			\\endif\n		  \\if double_tehta_i\n		    {_LONG_I_}       === I_TEHTA_DOUBLE\n		    {_LONE_LONG_I_}  === TELCO {_LONG_I_}\n        {LTEHTAR}        === {LTEHTAR}   * í\n        {_LTEHTAR_}      === {_LTEHTAR_} * {_LONG_I_}\n		  \\endif\n		  \\if double_tehta_o\n		    {_LONG_O_}       === {O_LOOP_DOUBLE}\n		    {_LONE_LONG_O_}  === TELCO {_LONG_O_}\n        {LTEHTAR}        === {LTEHTAR}   * ó\n        {_LTEHTAR_}      === {_LTEHTAR_} * {_LONG_O_}\n		  \\endif\n		  \\if double_tehta_u\n		    {_LONG_U_}       === {U_LOOP_DOUBLE}\n		    {_LONE_LONG_U_}  === TELCO {_LONG_U_}\n        {LTEHTAR}        === {LTEHTAR}   * ú\n        {_LTEHTAR_}      === {_LTEHTAR_} * {_LONG_U_}\n		  \\endif\n    \\endif  \n       		\n    \\** images of long vowels, either tehtar or ara versions **\\\n    {_LVOWELS_}     === {_LONG_A_} * {_LONG_E_} * {_LONG_I_} * {_LONG_O_} * {_LONG_U_}      \n\n		{WLONG}         === * {LVOWELS} \n		{_WLONG_}       === * {_LVOWELS_}\n\n    {V_D}           === [ {VOWELS} {WLONG} {WDIPHTHONGS} ]\n    {V_D_WN}        === [ {VOWELS} {WLONG} {WDIPHTHONGS} * {NULL} ]\n\n    {_V_D_}         === [ {_TEHTAR_} {_WLONG_} {_WDIPHTHONGS_} ]\n    {_V_D_WN_}      === [ {_TEHTAR_} {_WLONG_} {_WDIPHTHONGS_} * {_NVOWEL_} ]\n		\n		\\** LONE SHORT VOWELS **\\\n    [{VOWELS}]    --> TELCO [{_TEHTAR_}]  \\** Replace isolated short vowels **\\\n    \n		\\** LONE LONG VOWELS **\\	\n		[{LVOWELS}]   --> [{_LONE_LONG_A_} * {_LONE_LONG_E_} * {_LONE_LONG_I_} * {_LONE_LONG_O_} * {_LONE_LONG_U_}]\n\n    \\if !split_diphthongs\n      [{DIPHTHONGS}]    -->   [{_DIPHTHONGS_}]     \\**  Replace diphthongs **\\\n    \\endif\n\n    \\** ===================== **\\\n    \\**     1ST LINE RULES    **\\\n    \\** ===================== **\\\n    {L1}          === t     * p       * {K}   * q\n    {_L1_}        === TINCO * PARMA   * CALMA * QUESSE\n\n    \\** GEMINATED **\\\n    {L1_1_GEMS}   === tt               * pp                 * {K}{K}\n    {_L1_1_GEMS_} === TINCO {GEMINATE} * PARMA {GEMINATE}   * CALMA {GEMINATE}\n\n    \\** NORMAL **\\\n    [ {L1} * {L1_1_GEMS} ] {V_D_WN} --> [ {_L1_} * {_L1_1_GEMS_} ] {_V_D_WN_}\n\n    \\** OTHERS **\\\n    ty{V_D_WN}          --> TINCO PALATAL_SIGN {_V_D_WN_}\n    py{V_D_WN}          --> PARMA PALATAL_SIGN {_V_D_WN_}\n\n    \\** For alveolarized consonants, we must put the alveolar_sign just after the tengwa\n        because else, FreeMonoTengwar will not handle well the ligature. Anyway, this is\n        more logical, but the tehta placement will not be perfect for older fonts **\\\n    ts{V_D_WN}          --> TINCO ALVEOLAR_SIGN {_V_D_WN_} \n    ps{V_D_WN}          --> PARMA ALVEOLAR_SIGN {_V_D_WN_} \n    {K}s{V_D_WN}        --> CALMA ALVEOLAR_SIGN {_V_D_WN_}   \n    x{V_D_WN}           --> CALMA ALVEOLAR_SIGN {_V_D_WN_}   \\** render ks for x **\\\n\n    \\** ===================== **\\\n    \\**     2ND LINE RULES    **\\\n    \\** ===================== **\\\n    {L2}          === nd      * mb        * ng      * ngw\n    {_L2_}        === ANDO    * UMBAR     * ANGA    * UNGWE\n\n    \\** STANDARD **\\\n    [{L2}]{V_D_WN}  --> [{_L2_}]{_V_D_WN_}\n\n    \\** Palatalized **\\\n    ndy{V_D_WN} --> ANDO PALATAL_SIGN {_V_D_WN_}\n\n    \\** Have some rules for d,b,g,gw although there are not theoritically possible, aldudénie e.g needs it **\\\n    {L2_UN}               === d       * b         * g       * gw\n\n    \\if \"voiced_plosives_treatment == VOICED_PLOSIVES_AS_NASALIZED\"\n      [{L2_UN}]{V_D_WN}   --> [{_L2_}] {_V_D_WN_}\n    \\elsif \"voiced_plosives_treatment == VOICED_PLOSIVES_WITH_STROKE\"\n      [{L2_UN}]{V_D_WN}   --> [{_L2_}] THINF_STROKE {_V_D_WN_}\n    \\else\n      {_L2_UN_}            === TW_EXT_21 * TW_EXT_22 * TW_EXT_23 * TW_EXT_24\n      [{L2_UN}]{V_D_WN}    --> [{_L2_UN_}] {_V_D_WN_}\n    \\endif\n\n    \\if \"st_pt_ht == ST_PT_HT_WITH_XTD\"\n      {L2_ALVEOLARIZED}     === st        * pt        * ht\n      {_L2_ALVEOLARIZED_}   === TW_EXT_11 * TW_EXT_12 * TW_EXT_13\n\n      [{L2_ALVEOLARIZED}]{V_D_WN}  --> [{_L2_ALVEOLARIZED_}] {_V_D_WN_}\n    \\endif\n\n    \\** ===================== **\\\n    \\**     3RD LINE RULES    **\\\n    \\** ===================== **\\\n    {L3}      === (th,þ) * f       * (h,χ)  * hw\n    {_L3_}    === SULE   * FORMEN  * AHA    * HWESTA\n\n    \\** NORMAL **\\\n    [{L3}]{V_D_WN}  --> [{_L3_}]{_V_D_WN_}\n\n    \\** OTHERS **\\\n    hy{V_D_WN}      --> HYARMEN PALATAL_SIGN {_V_D_WN_}\n\n    \\** Override h with vowels (descendent of hy) **\\\n    _h{V_D}         --> HYARMEN {_V_D_}\n    \\** Starting voiced h before long vowels **\\\n    _h[{LVOWELS}]   --> HYARMEN [{_LVOWELS_}]\n\n    (h,χ)           --> AHA\n\n    \\** ===================== **\\\n    \\**     4TH LINE RULES    **\\\n    \\** ===================== **\\\n    {L4}   === nt    * mp    * nc    * nq      \\** Not nqu, due to preprocessor **\\\n    {_L4_} === ANTO  * AMPA  * ANCA  * UNQUE\n\n    \\** NORMAL **\\\n    [{L4}]{V_D_WN}    --> [{_L4_}]{_V_D_WN_}\n    \\** OTHERS **\\\n    nty{V_D_WN}       --> ANTO PALATAL_SIGN {_V_D_WN_}\n\n    \\** ===================== **\\\n    \\**     5TH LINE RULES    **\\\n    \\** ===================== **\\\n    {L5}   === n     * m     * ñ     * ñw      * _nw\n    {_L5_} === NUMEN * MALTA * NOLDO * NWALME  * NWALME\n\n    [{L5}]{V_D_WN}  --> [{_L5_}]{_V_D_WN_}\n\n    ny{V_D_WN}          --> NUMEN PALATAL_SIGN {_V_D_WN_}\n    nn{V_D_WN}          --> NUMEN {GEMINATE}   {_V_D_WN_}\n    my{V_D_WN}          --> MALTA PALATAL_SIGN {_V_D_WN_}\n    mm{V_D_WN}          --> MALTA {GEMINATE}   {_V_D_WN_}\n\n    \\** ===================== **\\\n    \\**     6TH LINE RULES    **\\\n    \\** ===================== **\\\n\n    {_LONE_R_} === ORE \\** TODO: Add dot for full unutixe, don\'t add dot for lazy unutixe **\\\n    \\if always_use_romen_for_r\n      \\** Override lone r if option is on **\\\n      {_LONE_R_} === ROMEN {_NVOWEL_} \n    \\endif\n\n    {L6}        === r     * v     * y                   * w\n    {_L6_}      === ROMEN * VALA  * ANNA PALATAL_SIGN   * VILYA\n\n    [{L6}]{V_D_WN} --> [{_L6_}]{_V_D_WN_}\n\n    \\** Override rule r + null **\\\n    r                 --> {_LONE_R_}\n\n    rr{V_D_WN}        --> ROMEN {GEMINATE} {_V_D_WN_}\n    ry{V_D_WN}        --> ROMEN PALATAL_SIGN {_V_D_WN_}\n    rd{V_D_WN}        --> ARDA {_V_D_WN_}\n\n    \\** ===================== **\\\n    \\**     L  LINE RULES     **\\\n    \\** ===================== **\\\n    {LINE_L}          === l     * ld      * ll\n    {_LINE_L_}        === LAMBE * ALDA    * LAMBE {GEMINATE}\n\n    [{LINE_L}]{V_D_WN}    --> [{_LINE_L_}]{_V_D_WN_}\n\n    ly{V_D_WN}            --> LAMBE PALATAL_SIGN {_V_D_WN_}\n    hl{V_D_WN}            --> HALLA LAMBE {_V_D_WN_}\n    hr{V_D_WN}            --> HALLA ROMEN {_V_D_WN_}\n\n    \\** ===================== **\\\n    \\**   S/Z LINE RULES      **\\\n    \\** ===================== **\\\n    \n    \\** SILME is a bit tricky : the shape is not linked to voicing but to a tehta presence or not **\\\n    {L8}              === s               * {SS}\n    {_L8_TEHTAR_}     === SILME_NUQUERNA  * ESSE_NUQUERNA\n    {_L8_NO_TEHTAR_}  === SILME           * ESSE\n\n    [{L8}][{VOWELS}]   --> [{_L8_TEHTAR_}][{_TEHTAR_}]\n    [{L8}][{LTEHTAR}]  --> [{_L8_TEHTAR_}][{_LTEHTAR_}]\n    \n    {L8}               --> {_L8_NO_TEHTAR_}\n    {L8}[{DIPHTHONGS}] --> {_L8_NO_TEHTAR_}[{_DIPHTHONGS_}]\n    \n  \\end\n\n  \\beg    rules punctuation\n    . --> PUNCT_DDOT\n    .. --> PUNCT_DOT PUNCT_DDOT PUNCT_DOT\n    …  --> PUNCT_TILD\n    ... --> PUNCT_TILD\n    .... --> PUNCT_TILD\n    ..... --> PUNCT_TILD\n    ...... --> PUNCT_TILD\n    ....... --> PUNCT_TILD\n\n    , --> PUNCT_DOT\n    : --> PUNCT_DOT\n    ; --> PUNCT_DOT\n    ! --> PUNCT_EXCLAM\n    ? --> PUNCT_INTERR\n    · --> PUNCT_DOT\n\n    \\** Apostrophe **\\\n\n    \' --> {NULL}\n    ’ --> {NULL}\n\n    \\** Quotes **\\\n\n    “ --> DQUOT_OPEN\n    ” --> DQUOT_CLOSE\n    « --> DQUOT_OPEN\n    » --> DQUOT_CLOSE\n\n    - --> {NULL}\n    – --> PUNCT_TILD\n    — --> PUNCT_TILD\n\n    [ --> PUNCT_PAREN_L\n    ] --> PUNCT_PAREN_R\n    ( --> PUNCT_PAREN_L\n    ) --> PUNCT_PAREN_R\n    { --> PUNCT_PAREN_L\n    } --> PUNCT_PAREN_R\n    < --> PUNCT_PAREN_L\n    > --> PUNCT_PAREN_R\n\n    \\** Not universal between fonts ... **\\\n    $ --> BOOKMARK_SIGN\n    ≤ --> RING_MARK_L \\** Ring inscription left beautiful stuff **\\\n    ≥ --> RING_MARK_R \\** Ring inscription right beautiful stuff **\\\n\n  \\end\n\n  \\beg    rules  numbers\n    0 --> NUM_0\n    1 --> NUM_1\n    2 --> NUM_2\n    3 --> NUM_3\n    4 --> NUM_4\n    5 --> NUM_5\n    6 --> NUM_6\n    7 --> NUM_7\n    8 --> NUM_8\n    9 --> NUM_9\n    A --> NUM_10\n    B --> NUM_11\n  \\end\n\n\\end\n\n\\beg postprocessor\n  \\resolve_virtuals\n\\end\n\n"
Glaemscribe.resource_manager.raw_modes["sindarin-beleriand"] = "\\**\n\nGlǽmscribe (also written Glaemscribe) is a software dedicated to\nthe transcription of texts between writing systems, and more \nspecifically dedicated to the transcription of J.R.R. Tolkien\'s \ninvented languages to some of his devised writing systems.\n\nCopyright (C) 2015 Benjamin Babut (Talagan).\n\nThis program is free software: you can redistribute it and/or modify\nit under the terms of the GNU Affero General Public License as published by\nthe Free Software Foundation, either version 3 of the License, or\nany later version.\n\nThis program is distributed in the hope that it will be useful,\nbut WITHOUT ANY WARRANTY; without even the implied warranty of\nMERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\nGNU Affero General Public License for more details.\n\nYou should have received a copy of the GNU Affero General Public License\nalong with this program.  If not, see <http://www.gnu.org/licenses/>.\n\n**\\\n\n\\** Sindarin Beleriand mode for glaemscribe (MAY BE INCOMPLETE) **\\\n\\beg changelog\n  \\entry \"0.0.2\" \"Added lw\"\n  \\entry \"0.0.3\" \"Added thorn as equivalent for th\"\n  \\entry \"0.0.4\" \"Porting to virtual chars to simplify and beautify\"\n  \\entry \"0.0.5\" \"Added charset support for Annatar\"\n  \\entry \"0.0.6\" \"Added support for the FreeMonoTengwar font\"\n  \\entry \"0.1.0\" \"Added support for the Tengwar Elfica font\"\n\\end\n\n\\language \"Sindarin\"\n\\writing  \"Tengwar\"\n\\mode     \"Beleriand\"\n\\version  \"0.1.0\"\n\\authors  \"J.R.R. Tolkien, impl. Talagan (Benjamin Babut)\"\n\n\\charset  tengwar_ds_sindarin true\n\\charset  tengwar_ds_parmaite false\n\\charset  tengwar_ds_eldamar  false\n\\charset  tengwar_ds_annatar  false\n\\charset  tengwar_ds_elfica   false\n\\charset  tengwar_freemono    false\n\n\\beg      options\n\n  \\beg option consonant_modification_style CONSONANT_MODIFICATION_STYLE_BAR\n    \\value CONSONANT_MODIFICATION_STYLE_WAVE 0\n    \\value CONSONANT_MODIFICATION_STYLE_BAR 1\n  \\end\n\n  \\option reverse_numbers true\n  \\beg option numbers_base BASE_12\n    \\value    BASE_10 10\n    \\value    BASE_12 12\n  \\end\n\\end\n\n\\beg      preprocessor\n  \\** Work exclusively downcase **\\\n  \\downcase\n  \n  \\** Simplify trema vowels **\\\n  \\substitute ä a\n  \\substitute ë e\n  \\substitute ï i\n  \\substitute ö o\n  \\substitute ü u\n  \\substitute ÿ y\n\n  \\** We should do better for that one (todo) **\\\n  \\substitute œ e\n\n  \\** Dis-ambiguate long vowels **\\\n  \\rxsubstitute \"(ā|â|aa)\" \"á\"\n  \\rxsubstitute \"(ē|ê|ee)\" \"é\"\n  \\rxsubstitute \"(ī|î|ii)\" \"í\"\n  \\rxsubstitute \"(ō|ô|oo)\" \"ó\"\n  \\rxsubstitute \"(ū|û|uu)\" \"ú\"\n  \\rxsubstitute \"(ȳ|ŷ|yy)\" \"ý\"\n  \n  \\** Special case of starting \'i\' before vowels, replace i by j **\\     \n  \\rxsubstitute \"\\\\bi([aeouyáāâéēêíīîóōôúūûýȳŷ])\" \"j\\\\1\"\n  \n  \\** Preprocess numbers **\\\n  \\elvish_numbers \"\\\\eval numbers_base\" \"\\\\eval reverse_numbers\"\n\\end\n\n\\beg      processor\n\n  \\beg    rules litteral\n  \n    \\if \"consonant_modification_style == CONSONANT_MODIFICATION_STYLE_WAVE\"\n      {GEMINATE} === GEMINATE_SIGN_TILD\n      {NASAL}    === NASALIZE_SIGN_TILD\n    \\else\n      {GEMINATE} === GEMINATE_SIGN\n      {NASAL}    === NASALIZE_SIGN\n    \\endif\n  \n    {A}                 === a\n    {AA}                === á\n    {E}                 === e\n    {EE}                === é\n    {I}                 === i\n    {II}                === í\n    {O}                 === o\n    {OO}                === ó\n    {U}                 === u\n    {UU}                === ú\n    {Y}                 === y\n    {YY}                === ý\n    \n    {AE}                === {A}{E}\n    {AI}                === {A}{I}\n    {AU}                === {A}{U}\n    {AW}                === {A}w\n    {EI}                === {E}{I}\n    {UI}                === {U}{I}\n    {OE}                === {O}{E}\n             \n    {K}                 === (c,k)\n\n    \\** RULES **\\\n    {A}                 --> OSSE\n    {E}                 --> YANTA\n    {I}                 --> TELCO\n    {O}                 --> ANNA\n    {U}                 --> URE\n    \n    {Y}                 --> SILME_NUQUERNA_ALT\n\n    {AA}                --> OSSE  E_TEHTA\n    {EE}                --> YANTA E_TEHTA\n    {II}                --> TELCO E_TEHTA\n    {OO}                --> ANNA  E_TEHTA\n    {UU}                --> URE   E_TEHTA\n    {YY}                --> SILME_NUQUERNA_ALT E_TEHTA\n\n    {AE}                --> OSSE  YANTA  \\** Should chose between OSSE YANTA and OSSE THSUP_TICK_INV_L. Old tengscribe had second one, amanye tenceli has first one. **\\ \n    {AI}                --> OSSE  Y_TEHTA\n    {AU}                --> OSSE  SEV_TEHTA \n    {AW}                --> OSSE  SEV_TEHTA\n    {EI}                --> YANTA Y_TEHTA\n    {UI}                --> URE   Y_TEHTA\n    {OE}                --> ANNA  YANTA\n\n    \\** ======== **\\\n    \\** 1ST LINE **\\\n    \\** ======== **\\\n    {L1}         === t     * p      * {K}\n    {_L1_}       === TINCO * PARMA  * CALMA\n\n    [{L1}]       --> [{_L1_}]\n \n    nt   --> TINCO {NASAL}\n    mp   --> PARMA {NASAL}\n    n{K} --> CALMA {NASAL}\n\n    \\** ======== **\\\n    \\** 2ND LINE **\\\n    \\** ======== **\\\n    {L2}   === d     * b     * g \n    {_L2_} === ANDO  * UMBAR * ANGA \n\n    [{L2}] --> [{_L2_}]\n\n    mb   --> UMBAR  {NASAL}\n    nd   --> ANDO   {NASAL}\n\n    \\** ======== **\\\n    \\** 3RD LINE **\\\n    \\** ======== **\\\n    {L3}   === (þ,th) * (f,ph,ff) * ch\n    {_L3_} === SULE   * FORMEN    * AHA\n     \n    [{L3}] --> [{_L3_}]\n\n    nth   --> SULE    {NASAL}\n    mph   --> FORMEN  {NASAL}\n    nf    --> FORMEN  {NASAL}\n    nch   --> AHA     {NASAL}\n\n    \\** ======== **\\\n    \\** 4TH LINE **\\\n    \\** ======== **\\\n    {L4}    === (đ,ð,ðh,dh) * (v,bh,f_) \n    {_L4_}  === ANTO  * AMPA \n\n    [{L4}] --> [{_L4_}]\n\n    \\** ======== **\\\n    \\** 5TH LINE **\\\n    \\** ======== **\\\n    {L5}    === nn    * mm    * ng\n    {_L5_}  === NUMEN * MALTA * NOLDO \n\n    [{L5}] --> [{_L5_}]\n\n    \\** ======== **\\\n    \\** 6TH LINE **\\\n    \\** ======== **\\\n    {L6}    === n   * m     * w     * _mh \n    {_L6_}  === ORE * VALA  * VILYA * MALTA_W_HOOK  \n\n    [{L6}] --> [{_L6_}]\n\n    \\** ======== **\\\n    \\** R/L LINE **\\\n    \\** ======== **\\\n    {L_LINE}        === r     * _rh   * l     * _lh\n    {_L_LINE_}      === ROMEN * ARDA  * LAMBE  * ALDA \n\n    [{L_LINE}] --> [{_L_LINE_}]\n\n    \\** ======== **\\\n    \\** S/Z LINE **\\\n    \\** ======== **\\\n    {S_LINE}    === s\n    {_S_LINE_}  === SILME \n\n    [{S_LINE}] --> [{_S_LINE_}]\n\n    ns --> SILME_NUQUERNA {NASAL}\n\n    \\** ======== **\\\n    \\** OTHERS **\\\n    \\** ======== **\\\n\n    j --> ARA\n\n    h --> HYARMEN\n\n    hw   --> HWESTA_SINDARINWA\n\n    \\** labialized consonants **\\\n    dw   --> ANDO   SEV_TEHTA\n    gw   --> ANGA   SEV_TEHTA\n    lw   --> LAMBE  SEV_TEHTA\n    nw   --> ORE    SEV_TEHTA\n    rw   --> ROMEN  SEV_TEHTA \n\n  \\end\n  \n  \\beg    rules punctuation\n    . --> PUNCT_DDOT\n    .. --> PUNCT_DOT PUNCT_DDOT PUNCT_DOT\n    ... --> PUNCT_TILD\n    …   --> PUNCT_TILD\n    .... --> PUNCT_TILD\n    ..... --> PUNCT_TILD\n    ...... --> PUNCT_TILD\n    ....... --> PUNCT_TILD\n\n    , --> PUNCT_DOT\n    : --> PUNCT_DOT\n    ; --> PUNCT_DOT\n    ! --> PUNCT_EXCLAM\n    ? --> PUNCT_INTERR\n    · --> {NULL}\n\n    - --> {NULL}\n    – --> PUNCT_TILD  \n    — --> PUNCT_TILD\n\n    \\** Apostrophe **\\\n\n    \' --> {NULL}\n    ’ --> {NULL}\n\n    \\** Quotes **\\\n\n    “ --> DQUOT_OPEN\n    ” --> DQUOT_CLOSE\n    « --> DQUOT_OPEN \n    » --> DQUOT_CLOSE \n\n    [ --> PUNCT_PAREN_L\n    ] --> PUNCT_PAREN_R\n    ( --> PUNCT_PAREN_L\n    ) --> PUNCT_PAREN_R\n    { --> PUNCT_PAREN_L\n    } --> PUNCT_PAREN_R\n    < --> PUNCT_PAREN_L\n    > --> PUNCT_PAREN_R\n\n    \\** Not universal between fonts ... **\\\n    $ --> BOOKMARK_SIGN\n    ≤ --> RING_MARK_L \\** Ring inscription left beautiful stuff **\\\n    ≥ --> RING_MARK_R \\** Ring inscription right beautiful stuff **\\\n  \\end\n\n  \\beg    rules  numbers\n    0 --> NUM_0\n    1 --> NUM_1\n    2 --> NUM_2\n    3 --> NUM_3\n    4 --> NUM_4\n    5 --> NUM_5\n    6 --> NUM_6\n    7 --> NUM_7\n    8 --> NUM_8\n    9 --> NUM_9\n    A --> NUM_10\n    B --> NUM_11      \n  \\end\n  \n\\end\n\n\\beg postprocessor\n  \\resolve_virtuals\n\\end\n"
Glaemscribe.resource_manager.raw_modes["sindarin"] = "\\**\n\nGlǽmscribe (also written Glaemscribe) is a software dedicated to\nthe transcription of texts between writing systems, and more \nspecifically dedicated to the transcription of J.R.R. Tolkien\'s \ninvented languages to some of his devised writing systems.\n\nCopyright (C) 2015 Benjamin Babut (Talagan).\n\nThis program is free software: you can redistribute it and/or modify\nit under the terms of the GNU Affero General Public License as published by\nthe Free Software Foundation, either version 3 of the License, or\nany later version.\n\nThis program is distributed in the hope that it will be useful,\nbut WITHOUT ANY WARRANTY; without even the implied warranty of\nMERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\nGNU Affero General Public License for more details.\n\nYou should have received a copy of the GNU Affero General Public License\nalong with this program.  If not, see <http://www.gnu.org/licenses/>.\n\n**\\\n\n\\** Sindarin Classical mode for glaemscribe (MAY BE INCOMPLETE) **\\\n\n\\beg changelog\n  \\entry \"0.0.2\" \"Fixed some tehtar versions which did not look quite nice for ch, dh, v, mb. Reworked the problem of labialized consonnants (+w), adding an option for treating the u-curl + tehta combination.\"\n  \\entry \"0.0.3\" \"Extended the labialized consonnants option.\"\n  \\entry \"0.0.4\" \"Fixed nw (BUG : was using ORE from the beleriand mode), added lw\"\n  \\entry \"0.0.5\" \"Added thorn as equivalent for th\"\n  \\entry \"0.0.6\" \"Porting to virtual chars to simplify and beautify\"\n  \\entry \"0.0.7\" \"Added charset support for Annatar\"\n  \\entry \"0.0.8\" \"Added support for the FreeMonoTengwar font\" \n  \\entry \"0.1.0\" \"Added support for the Tengwar Elfica font\"\n\\end\n\n\n\\language \"Sindarin\"\n\\writing  \"Tengwar\"\n\\mode     \"General Use\"\n\\version  \"0.1.0\"\n\\authors  \"J.R.R. Tolkien, impl. Talagan (Benjamin Babut)\"\n\n\\charset  tengwar_ds_sindarin true\n\\charset  tengwar_ds_parmaite false\n\\charset  tengwar_ds_eldamar  false\n\\charset  tengwar_ds_annatar  false\n\\charset  tengwar_ds_elfica   false\n\\charset  tengwar_freemono    false\n\n\\beg      options\n\n  \\beg option reverse_o_u_tehtar U_UP_O_DOWN\n    \\value O_UP_U_DOWN 1\n    \\value U_UP_O_DOWN 2\n  \\end\n\n  \\beg option consonant_modification_style CONSONANT_MODIFICATION_STYLE_BAR\n    \\value CONSONANT_MODIFICATION_STYLE_WAVE 0\n    \\value CONSONANT_MODIFICATION_STYLE_BAR 1\n  \\end\n\n  \\beg option labialized_consonants_u_curl LABIALIZED_U_CURL_ALWAYS\n    \\value    LABIALIZED_U_CURL_NONE      1\n    \\value    LABIALIZED_U_CURL_NO_TEHTAR 2\n    \\value    LABIALIZED_U_CURL_ALWAYS    3\n  \\end\n\n  \\option reverse_numbers true\n  \\beg option numbers_base BASE_12\n    \\value    BASE_10 10\n    \\value    BASE_12 12\n  \\end\n\n\\end\n\n\\beg preprocessor\n  \\** Work exclusively downcase **\\\n  \\downcase\n  \n  \\** Simplify trema vowels **\\\n  \\substitute ä a\n  \\substitute ë e\n  \\substitute ï i\n  \\substitute ö o\n  \\substitute ü u\n  \\substitute ÿ y\n\n  \\** We should do better for that one (todo) **\\\n  \\substitute œ e\n  \n  \\** Dis-ambiguate long vowels **\\\n  \\rxsubstitute \"(ā|â|aa)\" \"á\"\n  \\rxsubstitute \"(ē|ê|ee)\" \"é\"\n  \\rxsubstitute \"(ī|î|ii)\" \"í\"\n  \\rxsubstitute \"(ō|ô|oo)\" \"ó\"\n  \\rxsubstitute \"(ū|û|uu)\" \"ú\"\n  \\rxsubstitute \"(ȳ|ŷ|yy)\" \"ý\"\n  \n  \\** Special case of starting \'i\' before vowels, replace i by j **\\     \n  \\rxsubstitute \"\\\\bi([aeouyáāâéēêíīîóōôúūûýȳŷ])\" \"j\\\\1\"\n  \n  \\** Preprocess numbers **\\\n  \\elvish_numbers \"\\\\eval numbers_base\" \"\\\\eval reverse_numbers\"\n\\end\n \n\\beg processor\n\n  \\beg rules litteral\n    \n    \\if \"consonant_modification_style == CONSONANT_MODIFICATION_STYLE_WAVE\"\n      {GEMINATE} === GEMINATE_SIGN_TILD\n      {NASAL}    === NASALIZE_SIGN_TILD\n    \\else\n      {GEMINATE} === GEMINATE_SIGN\n      {NASAL}    === NASALIZE_SIGN\n    \\endif\n    \n    \\if \"reverse_o_u_tehtar == U_UP_O_DOWN\"\n      {O_LOOP}        === O_TEHTA\n      {U_LOOP}        === U_TEHTA\n     \\else\n      {O_LOOP}        === U_TEHTA\n      {U_LOOP}        === O_TEHTA\n    \\endif\n    \n    \\** VOWELS **\\\n    {A}   === a\n    {AA}  === á\n    {E}   === e\n    {EE}  === é\n    {I}   === i\n    {II}  === í\n    {O}   === o\n    {OO}  === ó\n    {U}   === u\n    {UU}  === ú\n    {Y}   === y\n    {YY}  === ý\n\n    {AE}  === {A}{E}\n    {AI}  === {A}{I}\n    {AU}  === {A}{U}\n    {AW}  === {A}w\n    {EI}  === {E}{I}\n    {OE}  === {O}{E}\n    {UI}  === {U}{I}\n\n    \\** CONSONANTS **\\\n    {K}         === (c,k)\n\n    {VOWELS}    === {A}             * {E}             * {I}           * {O}         * {U}         * {Y} \n    {LVOWELS}   === {AA}            * {EE}            * {II}          * {OO}        * {UU}        * {YY}   \n\n    {TEHTAR}    === A_TEHTA         * E_TEHTA         * I_TEHTA       * {O_LOOP}     * {U_LOOP}     * Y_TEHTA \n  \n    {_LTEHTAR_} === ARA A_TEHTA     * ARA E_TEHTA     * ARA I_TEHTA   * ARA {O_LOOP} * ARA {U_LOOP} * ARA Y_TEHTA \n\n    {DIPHTHONGS}   === {AI}            * {AU}            * {AW}            * {EI}            * {UI}         * {AE}          * {OE}              \n    {_DIPHTHONGS_} === ANNA A_TEHTA    * VALA A_TEHTA    * VALA A_TEHTA    * ANNA E_TEHTA    * ANNA {U_LOOP} * YANTA A_TEHTA * YANTA {O_LOOP}     \n\n    \\** Consonants + Vowels, we will often need these ones **\\\n    {V_D}         === [ {VOWELS} ]\n    {V_D_WN}      === [ {VOWELS} * {NULL} ]\n\n    {_V_D_}       === [ {TEHTAR} ]\n    {_V_D_WN_}    === [ {TEHTAR} * {NULL} ]\n \n    \\** Vowel rules **\\  \n    [{VOWELS}]      -->   TELCO [{TEHTAR}]  \\** Replace isolated short vowels **\\\n    [{LVOWELS}]     -->   [{_LTEHTAR_}]   \\** Replace long vowels **\\\n    [{DIPHTHONGS}]  -->   [{_DIPHTHONGS_}]    \\** Replace diphthongs **\\\n   \n    \\** 1ST LINE **\\\n    {L1}           === t     * p * {K}\n    {_L1_}         === TINCO * PARMA * QUESSE\n \n    {V_D_WN}[{L1}] --> 2,1 --> [{_L1_}]{_V_D_WN_}\n  \n    {V_D_WN}nt   --> TINCO {NASAL} {_V_D_WN_}\n    {V_D_WN}mp   --> PARMA {NASAL} {_V_D_WN_}\n    {V_D_WN}n{K} --> CALMA {NASAL} {_V_D_WN_}\n\n    \\** 2ND LINE **\\\n    {L2}        === d     * b     * g     * ng                    \\** * g **\\\n    {_L2_}      === ANDO  * UMBAR * UNGWE * UNGWE {NASAL}      \\** * s **\\\n\n    {V_D_WN}[{L2}] --> 2,1 --> [{_L2_}]{_V_D_WN_}\n\n    {V_D_WN}mb   --> UMBAR  {NASAL} {_V_D_WN_}\n    {V_D_WN}nd   --> ANDO   {NASAL} {_V_D_WN_}\n\n    \\** 3RD LINE **\\\n    {L3}    === (þ,th) * (f,ph,ff) * ch \n    {_L3_}  === SULE   * FORMEN * HWESTA\n\n    {V_D_WN}[{L3}] --> 2,1 --> [{_L3_}]{_V_D_WN_} \n   \n    {V_D_WN}nth   --> SULE   {NASAL} {_V_D_WN_}\n    {V_D_WN}mph   --> FORMEN {NASAL} {_V_D_WN_}\n    {V_D_WN}nf    --> FORMEN {NASAL} {_V_D_WN_}\n    {V_D_WN}nch   --> HWESTA {NASAL} {_V_D_WN_}\n\n    \\** 4TH LINE **\\\n    {L4}        === (đ,ð,ðh,dh)   * (v,bh,f_) \\** Some noldorin variants here ... **\\\n    {_L4_}        === ANTO          * AMPA \n\n    {V_D_WN}[{L4}] --> 2,1 --> [{_L4_}]{_V_D_WN_}\n\n    \\** 5TH LINE **\\\n    {L5}        === n * m * _ng * _mh\n    {_L5_}      === NUMEN * MALTA * NWALME * MALTA_W_HOOK \n\n    {V_D_WN}[{L5}] --> 2,1 --> [{_L5_}]{_V_D_WN_}\n\n    {V_D_WN}nn        --> NUMEN {NASAL} {_V_D_WN_}\n    {V_D_WN}mm        --> MALTA {NASAL} {_V_D_WN_}\n\n    \\** 6TH LINE **\\\n\n    \\** 7TH LINE **\\\n    {L7}        === r_    * r     * l     * ll                    * w     \n    {_L7_}      === ORE   * ROMEN * LAMBE * LAMBE {GEMINATE} * VALA\n        \n    {V_D_WN}[{L7}] --> 2,1 --> [{_L7_}]{_V_D_WN_}\n    \n    _rh --> ARDA\n    _lh --> ALDA\n\n    \\** S/Z LINE **\\\n    {L8}    === s * y * ss\n    {_L8_}  === SILME_NUQUERNA * SILME_NUQUERNA_ALT * ESSE_NUQUERNA \n\n    {V_D_WN}[{L8}]  --> 2,1 --> [{_L8_}]{_V_D_WN_}\n\n    {V_D_WN}ns      --> SILME_NUQUERNA {NASAL} {_V_D_WN_}\n\n    s --> SILME\n\n    \\** OTHERS **\\\n    j --> YANTA\n\n    {V_D_WN}h    --> HYARMEN {_V_D_WN_}\n    {V_D_WN}hw   --> HWESTA_SINDARINWA {_V_D_WN_}\n\n    \\** \n        Ok here come the labialized consonants which are really tricky\n        The fonts generally do not handle well the u curl + tehtar, this should be one more argument for\n        adopting open type anchors with which we can stack diacritics (see the sarati modes).\n        For here, we cheat. Either we don\'t have any tehta on the tengwa, and it\'s easy.\n        Or, we put the two signs in their small versions, side by side.\n        We give an option not to use that trick, if the option is not set, we simply do not use\n        the u-curl at all when there\'s a tehta on the tengwa.\n    **\\\n    \n    \\if \"labialized_consonants_u_curl == LABIALIZED_U_CURL_NO_TEHTAR || labialized_consonants_u_curl == LABIALIZED_U_CURL_ALWAYS\"\n      dw   --> ANDO  SEV_TEHTA  \n      gw   --> UNGWE SEV_TEHTA  \n      lw   --> LAMBE SEV_TEHTA\n      nw   --> NUMEN SEV_TEHTA   \n      rw   --> ROMEN SEV_TEHTA   \n    \\endif\n\n    \\if \"labialized_consonants_u_curl == LABIALIZED_U_CURL_ALWAYS\"    \n      {V_D}dw   --> ANDO  SEV_TEHTA {_V_D_}\n      {V_D}gw   --> UNGWE SEV_TEHTA {_V_D_}\n      {V_D}lw   --> LAMBE SEV_TEHTA {_V_D_}   \n      {V_D}nw   --> NUMEN SEV_TEHTA {_V_D_}\n      {V_D}rw   --> ROMEN SEV_TEHTA {_V_D_}\n    \\endif\n  \\end\n  \n  \\beg rules punctuation\n    . --> PUNCT_DDOT\n    .. --> PUNCT_DOT PUNCT_DDOT PUNCT_DOT\n    ... --> PUNCT_TILD\n    …   --> PUNCT_TILD\n    .... --> PUNCT_TILD\n    ..... --> PUNCT_TILD\n    ...... --> PUNCT_TILD\n    ....... --> PUNCT_TILD\n    \n    , --> PUNCT_DOT\n    : --> PUNCT_DOT\n    ; --> PUNCT_DOT\n    ! --> PUNCT_EXCLAM\n    ? --> PUNCT_INTERR\n    · --> {NULL}\n\n    - --> {NULL} \n    – --> PUNCT_TILD  \n    — --> PUNCT_TILD\n\n    \\** Apostrophe **\\\n\n    \' --> {NULL}\n    ’ --> {NULL}\n\n    \\** Quotes **\\\n\n    “ --> DQUOT_OPEN\n    ” --> DQUOT_CLOSE\n    « --> DQUOT_OPEN \n    » --> DQUOT_CLOSE \n\n    [ --> PUNCT_PAREN_L\n    ] --> PUNCT_PAREN_R\n    ( --> PUNCT_PAREN_L\n    ) --> PUNCT_PAREN_R\n    { --> PUNCT_PAREN_L\n    } --> PUNCT_PAREN_R\n    < --> PUNCT_PAREN_L\n    > --> PUNCT_PAREN_R\n\n    \\** Not universal between fonts ... **\\\n    $ --> BOOKMARK_SIGN\n    ≤ --> RING_MARK_L \\** Ring inscription left beautiful stuff **\\\n    ≥ --> RING_MARK_R \\** Ring inscription right beautiful stuff **\\\n  \\end\n\n  \\beg rules numbers\n    0 --> NUM_0\n    1 --> NUM_1\n    2 --> NUM_2\n    3 --> NUM_3\n    4 --> NUM_4\n    5 --> NUM_5\n    6 --> NUM_6\n    7 --> NUM_7\n    8 --> NUM_8\n    9 --> NUM_9\n    A --> NUM_10\n    B --> NUM_11      \n  \\end\n\\end\n\n\\beg postprocessor\n  \\resolve_virtuals\n\\end\n"
Glaemscribe.resource_manager.raw_modes["telerin"] = "\\**\n\nGlǽmscribe (also written Glaemscribe) is a software dedicated to\nthe transcription of texts between writing systems, and more \nspecifically dedicated to the transcription of J.R.R. Tolkien\'s \ninvented languages to some of his devised writing systems.\n\nCopyright (C) 2015 Benjamin Babut (Talagan).\n\nThis program is free software: you can redistribute it and/or modify\nit under the terms of the GNU Affero General Public License as published by\nthe Free Software Foundation, either version 3 of the License, or\nany later version.\n\nThis program is distributed in the hope that it will be useful,\nbut WITHOUT ANY WARRANTY; without even the implied warranty of\nMERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\nGNU Affero General Public License for more details.\n\nYou should have received a copy of the GNU Affero General Public License\nalong with this program.  If not, see <http://www.gnu.org/licenses/>.\n\n**\\\n\n\\** Telerin mode for glaemscribe (MAY BE INCOMPLETE) - Derived from Quenya **\\\n\n\\beg changelog\n  \\entry \"0.0.2\" \"Correcting ts/ps sequences to work better with eldamar\"\n  \\entry \"0.0.3\" \"Porting to virtual chars\"\n  \\entry \"0.0.4\" \"Added charset support for Annatar\"\n  \\entry \"0.0.5\" \"Added support for the FreeMonoTengwar font\" \n  \\entry \"0.0.6\" \"Ported some options from the quenya mode\"\n  \\entry \"0.1.0\" \"Added support for the Tengwar Elfica font\"\n\\end\n\n\\language \"Telerin\"\n\\writing  \"Tengwar\"\n\\mode     \"Glaemscrafu\"\n\\version  \"0.1.0\"\n\\authors  \"Talagan (Benjamin Babut), based on J.R.R Tolkien\"\n\n\\charset  tengwar_ds_sindarin true\n\\charset  tengwar_ds_parmaite false\n\\charset  tengwar_ds_eldamar  false\n\\charset  tengwar_ds_annatar  false\n\\charset  tengwar_ds_elfica   false\n\\charset  tengwar_freemono    false\n\n\n\\beg      options\n\n  \\beg option reverse_o_u_tehtar U_UP_O_DOWN\n    \\value O_UP_U_DOWN 1\n    \\value U_UP_O_DOWN 2\n  \\end\n\n  \\beg option long_vowels_format LONG_VOWELS_USE_LONG_CARRIER\n    \\value LONG_VOWELS_USE_LONG_CARRIER 1\n    \\value LONG_VOWELS_USE_DOUBLE_TEHTAR 2\n  \\end  \n\n  \\beg option double_tehta_e false\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \\beg option double_tehta_i false\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \\beg option double_tehta_o true\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \\beg option double_tehta_u true\n    \\visible_when \'long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\'\n  \\end\n  \n  \\beg option consonant_modification_style CONSONANT_MODIFICATION_STYLE_BAR\n    \\value CONSONANT_MODIFICATION_STYLE_WAVE 0\n    \\value CONSONANT_MODIFICATION_STYLE_BAR 1\n  \\end\n\n  \\option reverse_numbers true\n  \\beg option numbers_base BASE_12\n    \\value    BASE_10 10\n    \\value    BASE_12 12\n  \\end\n\\end\n\n\\beg      preprocessor\n  \\** Work exclusively downcase **\\\n  \\downcase\n  \n  \\** Simplify trema vowels **\\\n  \\substitute ä a\n  \\substitute ë e\n  \\substitute ï i\n  \\substitute ö o\n  \\substitute ü u\n  \\substitute ÿ y\n  \n  \\** Dis-ambiguate long vowels **\\\n  \\rxsubstitute \"(ā|â|aa)\" \"á\"\n  \\rxsubstitute \"(ē|ê|ee)\" \"é\" \n  \\rxsubstitute \"(ī|î|ii)\" \"í\"\n  \\rxsubstitute \"(ō|ô|oo)\" \"ó\"\n  \\rxsubstitute \"(ū|û|uu)\" \"ú\"\n  \\rxsubstitute \"(ȳ|ŷ|yy)\" \"ý\"\n\n  \\substitute   \"qu\" \"q\" \\** Dis-ambiguate qu **\\\n  \n  \\elvish_numbers \"\\\\eval numbers_base\" \"\\\\eval reverse_numbers\"\n\\end\n  \n\\beg processor\n\n  \\beg rules litteral\n                       \n    {K}                 === (c,k)\n    {W}                 === (v,w)\n    {SS}                === (z,ss)\n    \n    \\if \"consonant_modification_style == CONSONANT_MODIFICATION_STYLE_WAVE\"\n      {GEMINATE} === GEMINATE_SIGN_TILD\n    \\else\n      {GEMINATE} === GEMINATE_SIGN\n    \\endif\n    \n    {VOWELS}            === a               *  e              * i              * o              *  u\n    {LVOWELS}           === á               *  é              * í              * ó              *  ú\n\n    \\if \"reverse_o_u_tehtar == U_UP_O_DOWN\"\n      {O_LOOP}        === O_TEHTA\n      {O_LOOP_DOUBLE} === O_TEHTA_DOUBLE\n      {U_LOOP}        === U_TEHTA\n      {U_LOOP_DOUBLE} === U_TEHTA_DOUBLE\n    \\else\n      {O_LOOP}        === U_TEHTA\n      {O_LOOP_DOUBLE} === U_TEHTA_DOUBLE\n      {U_LOOP}        === O_TEHTA\n      {U_LOOP_DOUBLE} === O_TEHTA_DOUBLE\n    \\endif\n\n    \\** Shape of the a, option removed from quenya, may be readded later **\\\n    {A_SHAPE}         === A_TEHTA\n\n    \\** Implicit a, option removed from quenya, may be readded later **\\\n    {_A_}              === {A_SHAPE}\n    {_NVOWEL_}         === {NULL}\n  \n    {_TEHTAR_}          === {_A_}      *  E_TEHTA     *  I_TEHTA    * {O_LOOP}    *  {U_LOOP}\n\n    \\** Split diphtongs option removed from quenya, may be readded later **\\\n    {DIPHTHONGS}      === ai            * au            * eu            * iu             * oi               * ui\n    {_DIPHTHONGS_}    === YANTA {_A_}   * URE {_A_}     * URE E_TEHTA   * URE I_TEHTA    * YANTA {O_LOOP}   * YANTA {U_LOOP}\n    {WDIPHTHONGS}     === * {DIPHTHONGS}   \\** groovy! **\\\n    {_WDIPHTHONGS_}   === * {_DIPHTHONGS_} \\** same thing **\\\n    \n		{_LONG_A_}      === ARA {A_SHAPE}\n		{_LONG_E_}      === ARA E_TEHTA	\n		{_LONG_I_}      === ARA I_TEHTA\n		{_LONG_O_}      === ARA {O_LOOP}	\n		{_LONG_U_}      === ARA {U_LOOP}\n		{_LONE_LONG_A_} === {_LONG_A_}\n		{_LONE_LONG_E_} === {_LONG_E_}\n		{_LONE_LONG_I_} === {_LONG_I_}\n		{_LONE_LONG_O_} === {_LONG_O_}\n		{_LONE_LONG_U_} === {_LONG_U_}\n    \n    {LTEHTAR}       === {NULL}\n    {_LTEHTAR_}     === {NULL} 				\n 		\n		\\if \"long_vowels_format == LONG_VOWELS_USE_DOUBLE_TEHTAR\"\n	    \\if double_tehta_e\n		    {_LONG_E_}       === E_TEHTA_DOUBLE\n		    {_LONE_LONG_E_}  === TELCO {_LONG_E_}\n        {LTEHTAR}        === {LTEHTAR}   * é\n        {_LTEHTAR_}      === {_LTEHTAR_} * {_LONG_E_}\n			\\endif\n		  \\if double_tehta_i\n		    {_LONG_I_}       === I_TEHTA_DOUBLE\n		    {_LONE_LONG_I_}  === TELCO {_LONG_I_}\n        {LTEHTAR}        === {LTEHTAR}   * í\n        {_LTEHTAR_}      === {_LTEHTAR_} * {_LONG_I_}\n		  \\endif\n		  \\if double_tehta_o\n		    {_LONG_O_}       === {O_LOOP_DOUBLE}\n		    {_LONE_LONG_O_}  === TELCO {_LONG_O_}\n        {LTEHTAR}        === {LTEHTAR}   * ó\n        {_LTEHTAR_}      === {_LTEHTAR_} * {_LONG_O_}\n		  \\endif\n		  \\if double_tehta_u\n		    {_LONG_U_}       === {U_LOOP_DOUBLE}\n		    {_LONE_LONG_U_}  === TELCO {_LONG_U_}\n        {LTEHTAR}        === {LTEHTAR}   * ú\n        {_LTEHTAR_}      === {_LTEHTAR_} * {_LONG_U_}\n		  \\endif\n    \\endif  \n       		\n    \\** images of long vowels, either tehtar or ara versions **\\\n    {_LVOWELS_}     === {_LONG_A_} * {_LONG_E_} * {_LONG_I_} * {_LONG_O_} * {_LONG_U_}      \n\n		{WLONG}         === * {LVOWELS} \n		{_WLONG_}       === * {_LVOWELS_}\n\n    {V_D}           === [ {VOWELS} {WLONG} {WDIPHTHONGS} ]\n    {V_D_WN}        === [ {VOWELS} {WLONG} {WDIPHTHONGS} * {NULL} ]\n\n    {_V_D_}         === [ {_TEHTAR_} {_WLONG_} {_WDIPHTHONGS_} ]\n    {_V_D_WN_}      === [ {_TEHTAR_} {_WLONG_} {_WDIPHTHONGS_} * {_NVOWEL_} ]\n		\n		\\** LONE SHORT VOWELS **\\\n    [{VOWELS}]    --> TELCO [{_TEHTAR_}]  \\** Replace isolated short vowels **\\\n    \n		\\** LONE LONG VOWELS **\\	\n		[{LVOWELS}]   --> [{_LONE_LONG_A_} * {_LONE_LONG_E_} * {_LONE_LONG_I_} * {_LONE_LONG_O_} * {_LONE_LONG_U_}]\n\n    [{DIPHTHONGS}]    -->   [{_DIPHTHONGS_}]     \\**  Replace diphthongs **\\\n    \n  \n    \\** TELERIN: changed v/w, removed all y rules **\\\n    \n    \\** ===================== **\\\n    \\** 1ST LINE RULES **\\\n    \\** ===================== **\\\n    {L1}          === {K}   * q      * t       * p \n    {_L1_}        === CALMA * QUESSE * TINCO   * PARMA\n    \n    {L1_GEMS}     === {K}{K}              * tt                     * pp\n    {_L1_GEMS_}   === CALMA {GEMINATE} * TINCO {GEMINATE}    * PARMA {GEMINATE}\n\n    \\** NORMAL **\\\n    [ {L1} ] {V_D_WN}         --> [ {_L1_} ] {_V_D_WN_}\n    [ {L1_GEMS} ] {V_D_WN}    --> [ {_L1_GEMS_} ] {_V_D_WN_}\n                            \n    ts{V_D_WN}          --> TINCO ALVEOLAR_SIGN {_V_D_WN_} \n    ps{V_D_WN}          --> PARMA ALVEOLAR_SIGN {_V_D_WN_}\n    {K}s{V_D_WN}        --> CALMA ALVEOLAR_SIGN {_V_D_WN_}   \n    x{V_D_WN}           --> CALMA ALVEOLAR_SIGN {_V_D_WN_}   \\** render ks for x **\\\n                            \n    \\** ===================== **\\\n    \\** 2ND LINE RULES **\\\n    \\** ===================== **\\\n    {L2}        === nd      * mb        * ng      *  ngw    * d      * b        * g\n    {_L2_}      === ANDO    * UMBAR     * ANGA    *  UNGWE  * ORE    * VALA     * ANNA\n    \n    \\** STANDARD **\\\n    [{L2}]{V_D_WN}  --> [{_L2_}]{_V_D_WN_}\n\n    \\** ===================== **\\\n    \\** 3RD LINE RULES **\\\n    \\** ===================== **\\\n    {L3}    === th     * f      * h      * hw\n    {_L3_}  === SULE   * FORMEN * AHA    * HWESTA\n\n    \\** NORMAL **\\\n    [{L3}]{V_D_WN}  --> [{_L3_}]{_V_D_WN_}\n      \n    \\** Override h with vowels (descendent) **\\\n    _h{V_D}         --> HYARMEN {_V_D_}\n    \\** Starting voiced h before long vowels **\\\n    _h[{LVOWELS}]   --> HYARMEN [{_LVOWELS_}]\n\n    (h,χ)           --> AHA\n\n    \\** ===================== **\\\n    \\** 4TH LINE RULES **\\\n    \\** ===================== **\\\n    {L4}    === nt    * mp    * nc    * nq      \\** Not nqu, due to preprocessor **\\\n    {_L4_}  === ANTO  * AMPA  * ANCA  * UNQUE\n \n    \\** NORMAL **\\\n    [{L4}]{V_D_WN}    --> [{_L4_}]{_V_D_WN_}\n\n    \\** ===================== **\\\n    \\** 5TH LINE RULES **\\\n    \\** ===================== **\\\n    {L5}    === n     * m     * ñ     * ñw      * _nw \n    {_L5_}  === NUMEN * MALTA * NOLDO * NWALME  * NWALME\n\n    [{L5}]{V_D_WN}  --> [{_L5_}]{_V_D_WN_}\n\n    nn{V_D_WN}          --> NUMEN {GEMINATE} {_V_D_WN_}\n    mm{V_D_WN}          --> MALTA {GEMINATE} {_V_D_WN_}\n\n    \\** ===================== **\\\n    \\** 6TH LINE RULES **\\\n    \\** ===================== **\\\n    {L6}        === r     * {W}  \n    {_L6_}      === ROMEN * VILYA \n\n    [{L6}]{V_D_WN} --> [{_L6_}]{_V_D_WN_}\n\n    rr{V_D_WN}        --> ROMEN {GEMINATE} {_V_D_WN_}\n    rd{V_D_WN}        --> ARDA {_V_D_WN_}\n\n    \\** ===================== **\\\n    \\** L   LINE RULES **\\\n    \\** ===================== **\\\n    {LINE_L}          === l     * ld      * ll\n    {_LINE_L_}        === LAMBE * ALDA    * LAMBE {GEMINATE}\n\n    [{LINE_L}]{V_D_WN}    --> [{_LINE_L_}]{_V_D_WN_}\n\n    hl{V_D_WN}            --> HALLA LAMBE {_V_D_WN_}\n    hr{V_D_WN}            --> HALLA ROMEN {_V_D_WN_}\n    \n    \\** ===================== **\\\n    \\** S/Z LINE RULES **\\\n    \\** ===================== **\\\n    {L8}              === s               * {SS}\n    {_L8_TEHTAR_}     === SILME_NUQUERNA  * ESSE_NUQUERNA\n    {_L8_NO_TEHTAR_}  === SILME           * ESSE\n\n    [{L8}][{VOWELS}]   --> [{_L8_TEHTAR_}][{_TEHTAR_}]\n    [{L8}][{LTEHTAR}]  --> [{_L8_TEHTAR_}][{_LTEHTAR_}]\n    \n    {L8}               --> {_L8_NO_TEHTAR_}\n    {L8}[{DIPHTHONGS}] --> {_L8_NO_TEHTAR_}[{_DIPHTHONGS_}]\n  \\end\n  \n  \\beg rules punctuation\n    . --> PUNCT_DDOT\n    .. --> PUNCT_DOT PUNCT_DDOT PUNCT_DOT\n    …  --> PUNCT_TILD\n    ... --> PUNCT_TILD\n    .... --> PUNCT_TILD\n    ..... --> PUNCT_TILD\n    ...... --> PUNCT_TILD\n    ....... --> PUNCT_TILD\n\n    , --> PUNCT_DOT\n    : --> PUNCT_DOT\n    ; --> PUNCT_DOT\n    ! --> PUNCT_EXCLAM\n    ? --> PUNCT_INTERR\n    · --> PUNCT_DOT\n\n    \\** Apostrophe **\\\n\n    \' --> {NULL}\n    ’ --> {NULL}\n\n    \\** Quotes **\\\n\n    “ --> DQUOT_OPEN\n    ” --> DQUOT_CLOSE\n    « --> DQUOT_OPEN \n    » --> DQUOT_CLOSE \n    \n    - --> {NULL}\n    – --> PUNCT_TILD  \n    — --> PUNCT_TILD\n\n    [ --> PUNCT_PAREN_L\n    ] --> PUNCT_PAREN_R\n    ( --> PUNCT_PAREN_L\n    ) --> PUNCT_PAREN_R\n    { --> PUNCT_PAREN_L\n    } --> PUNCT_PAREN_R\n    < --> PUNCT_PAREN_L\n    > --> PUNCT_PAREN_R  \n\n    \\** Not universal between fonts ... **\\\n    $ --> BOOKMARK_SIGN\n    ≤ --> RING_MARK_L \\** Ring inscription left beautiful stuff **\\\n    ≥ --> RING_MARK_R \\** Ring inscription right beautiful stuff **\\\n \n  \\end\n  \n  \\beg rules numbers\n    0 --> NUM_0\n    1 --> NUM_1\n    2 --> NUM_2\n    3 --> NUM_3\n    4 --> NUM_4\n    5 --> NUM_5\n    6 --> NUM_6\n    7 --> NUM_7\n    8 --> NUM_8\n    9 --> NUM_9\n    A --> NUM_10\n    B --> NUM_11      \n  \\end\n\\end\n\n\\beg postprocessor\n  \\resolve_virtuals\n\\end  \n"
Glaemscribe.resource_manager.raw_modes["westron"] = "\\**\n\nGlǽmscribe (also written Glaemscribe) is a software dedicated to\nthe transcription of texts between writing systems, and more \nspecifically dedicated to the transcription of J.R.R. Tolkien\'s \ninvented languages to some of his devised writing systems.\n\nCopyright (C) 2015 Benjamin Babut (Talagan).\n\nThis program is free software: you can redistribute it and/or modify\nit under the terms of the GNU Affero General Public License as published by\nthe Free Software Foundation, either version 3 of the License, or\nany later version.\n\nThis program is distributed in the hope that it will be useful,\nbut WITHOUT ANY WARRANTY; without even the implied warranty of\nMERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\nGNU Affero General Public License for more details.\n\nYou should have received a copy of the GNU Affero General Public License\nalong with this program.  If not, see <http://www.gnu.org/licenses/>.\n\n**\\\n\n\\beg changelog\n  \\entry \"0.0.2\" \"Correcting ts/ps sequences to work better with eldamar\"\n  \\entry \"0.0.3\" \"Porting to virtual chars\"\n  \\entry \"0.0.4\" \"Added charset support for Annatar\"\n  \\entry \"0.0.5\" \"Added support for the FreeMonoTengwar font\" \n  \\entry \"0.1.0\" \"Added support for the Tengwar Elfica font\"\n\\end\n\n\\**  Westron mode for glaemscribe (MAY BE INCOMPLETE) **\\\n\\language Westron\n\\writing  Tengwar\n\\mode     Glaemscrafu\n\\version  \"0.1.0\"\n\\authors  \"Talagan (Benjamin Babut), based on J.R.R. Tolkien\"\n\n\\charset  tengwar_ds_sindarin true\n\\charset  tengwar_ds_parmaite false\n\\charset  tengwar_ds_eldamar  false\n\\charset  tengwar_ds_annatar  false\n\\charset  tengwar_ds_elfica   false\n\\charset  tengwar_freemono    false\n\n\\beg      options\n\n  \\beg option reverse_o_u_tehtar U_UP_O_DOWN\n    \\value O_UP_U_DOWN 1\n    \\value U_UP_O_DOWN 2\n  \\end\n  \\beg option consonant_modification_style CONSONANT_MODIFICATION_STYLE_BAR\n    \\value CONSONANT_MODIFICATION_STYLE_WAVE 0\n    \\value CONSONANT_MODIFICATION_STYLE_BAR 1\n  \\end\n\n  \\option reverse_numbers true\n  \\beg option numbers_base BASE_12\n    \\value    BASE_10 10\n    \\value    BASE_12 12\n  \\end\n  \n\\end\n\n\\beg      preprocessor\n  \\** Work exclusively downcase **\\\n  \\downcase\n  \n  \\** Simplify trema vowels **\\\n  \\substitute \"ä\" \"a\"\n  \\substitute \"ë\" \"e\"\n  \\substitute \"ï\" \"i\"\n  \\substitute \"ö\" \"o\"\n  \\substitute \"ü\" \"u\"\n  \\substitute \"ÿ\" \"y\"\n  \n  \\** Dis-ambiguate long vowels **\\\n  \\rxsubstitute \"(ā|â|aa)\" \"á\"\n  \\rxsubstitute \"(ē|ê|ee)\" \"é\"\n  \\rxsubstitute \"(ī|î|ii)\" \"í\"\n  \\rxsubstitute \"(ō|ô|oo)\" \"ó\"\n  \\rxsubstitute \"(ū|û|uu)\" \"ú\"\n  \\rxsubstitute \"(ȳ|ŷ|yy)\" \"ý\"\n  \n  \\** Preprocess numbers **\\\n  \\elvish_numbers \"\\\\eval numbers_base\" \"\\\\eval reverse_numbers\"\n\\end\n\n\\beg      processor\n\n  \\beg    rules litteral  \n  \n    \\if \"consonant_modification_style == CONSONANT_MODIFICATION_STYLE_WAVE\"\n      {GEMINATE} === GEMINATE_SIGN_TILD\n      {NASAL}    === NASALIZE_SIGN_TILD\n    \\else\n      {GEMINATE} === GEMINATE_SIGN\n      {NASAL}    === NASALIZE_SIGN\n    \\endif\n    \n    \\if \"reverse_o_u_tehtar == U_UP_O_DOWN\"\n      {O_LOOP}        === O_TEHTA\n      {O_LOOP_DOUBLE} === O_TEHTA_DOUBLE\n      {U_LOOP}        === U_TEHTA\n      {U_LOOP_DOUBLE} === U_TEHTA_DOUBLE\n    \\else\n      {O_LOOP}        === U_TEHTA\n      {O_LOOP_DOUBLE} === U_TEHTA_DOUBLE\n      {U_LOOP}        === O_TEHTA\n      {U_LOOP_DOUBLE} === O_TEHTA_DOUBLE\n    \\endif\n  \n    {A}   === a\n    {AA}  === á\n    {E}   === e\n    {EE}  === é\n    {I}   === i\n    {II}  === í\n    {O}   === o\n    {OO}  === ó\n    {U}   === u\n    {UU}  === ú\n\n    \\** Short diphthongs **\\\n    {AI}  === {A}{I}\n    {AU}  === {A}{U}\n	  {EI}  === {E}{I}\n	  {EU}  === {E}{U}\n	  {OI}  === {O}{I}\n	  {OU}  === {O}{U}\n	  {UI}  === {U}{I}\n	  {IU}  === {I}{U}\n\n    \\** LONG diphthongs **\\      \n    {AAI} === {AA}{I} \\** âi **\\\n    {AAU} === {AA}{U} \\** âu **\\\n    {EEI} === {EE}{I} \\** êi **\\\n    {EEU} === {EE}{U} \\** êu **\\\n    {OOI} === {OO}{I} \\** ôi **\\\n    {OOU} === {OO}{U} \\** ôu **\\\n\n    {SDIPHTHONGS}  === {AI}           * {AU}          * {EI} 				    * {EU}        * {IU}        * {OI}          * {OU}        * {UI}\n    {_SDIPHTONGS_} === YANTA A_TEHTA  * URE A_TEHTA   * YANTA E_TEHTA	  * URE E_TEHTA * URE I_TEHTA * YANTA {O_LOOP} * URE {O_LOOP} * YANTA {U_LOOP}                   \n    \n    {LDIPHTHONGS}  === {AAI}              * {AAU}              * {EEI}              * {EEU}            * {OOI}              * {OOU}\n    {_LDIPHTONGS_} === ARA A_TEHTA YANTA  * ARA A_TEHTA URE    * ARA E_TEHTA YANTA  * ARA E_TEHTA URE  * ARA {O_LOOP} YANTA  * ARA {O_LOOP} URE\n                   \n    {VOWELS}      === {A}      * {E}      * {I}        * {O}        * {U}    \n    {TEHTAR}      === A_TEHTA  * E_TEHTA  * I_TEHTA    * {O_LOOP}    * {U_LOOP}\n                  \n    {LVOWELS}     === {AA}         * {EE}         * {II}         * {OO}         * {UU}\n    {LTETHAR}     === ARA A_TEHTA  * ARA E_TEHTA  * ARA I_TEHTA  * ARA {O_LOOP}  * ARA {U_LOOP} \n\n    \\** Let\' put all vowels/diphthongs in the same basket **\\\n    {V_D}         === [ {VOWELS}  * {LVOWELS} * {SDIPHTHONGS} * {LDIPHTHONGS} ]        \n    \\** And their images... **\\            \n    {_V_D_}       === [ {TEHTAR}  * {LTETHAR} * {_SDIPHTONGS_} * {_LDIPHTONGS_} ]\n \n    [{VOWELS}]      --> TELCO [{TEHTAR}]   \\** Replace isolated short vowels **\\\n    [{LVOWELS}]     --> [{LTETHAR}]    \\** Replace long vowels **\\\n    [{SDIPHTHONGS}]  --> [{_SDIPHTONGS_}]  \\** Replace short diphthongs **\\\n    [{LDIPHTHONGS}]  --> [{_LDIPHTONGS_}]  \\** Replace long diphthongs **\\\n\n    \\** ================ **\\\n    \\**    CONSONANTS    **\\\n    \\** ================ **\\     \n\n    {L1_S}         === t      * p     * ch		  * (c,k)       \n    {L1_T}         === TINCO  * PARMA * CALMA	  * QUESSE \n    \n    [{L1_S}]       -->  [ {L1_T} ]\n    [{L1_S}]{V_D}  -->  [ {L1_T} ]{_V_D_} \n	\n    {L1_G_S}         === tt			           * pp               * cch				        * (c,k)(c,k)             \n    {L1_G_T}         === TINCO {GEMINATE}  * PARMA {GEMINATE} * CALMA {GEMINATE}	* QUESSE {GEMINATE}  \n    \n    [{L1_G_S}]       -->  [ {L1_G_T} ]\n    [{L1_G_S}]{V_D}  -->  [ {L1_G_T} ]{_V_D_} \n	  \n    {L1_N_S}         === nt			        * mp              * nch				    * (n,ñ)(c,k)             \n    {L1_N_T}         === TINCO {NASAL}  * PARMA {NASAL}   * CALMA {NASAL} * QUESSE {NASAL}  \n    \n    [{L1_N_S}]       -->  [ {L1_N_T} ]\n    [{L1_N_S}]{V_D}  -->  [ {L1_N_T} ]{_V_D_} 	 \n\n    {L2_S}         === d    * b     * j	  	* g\n    {L2_T}         === ANDO * UMBAR * ANGA	* UNGWE\n		\n    [{L2_S}]       --> [{L2_T}] \n    [{L2_S}]{V_D}  --> [{L2_T}]{_V_D_} \n\n    {L2_G_S}         === dd              * bb               * jj			         * gg\n    {L2_G_T}         === ANDO {GEMINATE} * UMBAR {GEMINATE} * ANGA {GEMINATE}  * UNGWE {GEMINATE}\n		\n    [{L2_G_S}]       --> [{L2_G_T}] \n    [{L2_G_S}]{V_D}  --> [{L2_G_T}]{_V_D_} \n\n    {L2_N_S}         === nd           * mb            * nj			      * (n,ñ)g\n    {L2_N_T}         === ANDO {NASAL} * UMBAR {NASAL} * ANGA {NASAL}  * UNGWE {NASAL}\n		\n    [{L2_N_S}]       --> [{L2_N_T}] \n    [{L2_N_S}]{V_D}  --> [{L2_N_T}]{_V_D_} \n\n    \\** Alignment of tehta is not the same in the font **\\\n    \\** So we need to split the third line unfortunately **\\\n    {L3_1_S}          === (th,þ)    * (f,ph)      \n    {L3_1_T}          === SULE      * FORMEN  \n   \n    {L3_2_S}          === sh     * kh     \n    {L3_2_T}          === AHA    * HWESTA\n   \n    [{L3_1_S}]        --> [{L3_1_T}] \n    [{L3_1_S}]{V_D}   --> [{L3_1_T}]{_V_D_} \n    [{L3_2_S}]        --> [{L3_2_T}] \n    [{L3_2_S}]{V_D}   --> [{L3_2_T}]{_V_D_} \n		\n    {L3_1G_S}         === (thth,tth,þþ)    * (ff,phph,pph)\n    {L3_1G_T}         === SULE {GEMINATE}  * FORMEN {GEMINATE}\n   \n    {L3_2G_S}          === (shsh,ssh)      * (k,kh)kh\n    {L3_2G_T}          === AHA {GEMINATE}  * HWESTA {GEMINATE}\n   \n    [{L3_1G_S}]        --> [{L3_1G_T}] \n    [{L3_1G_S}]{V_D}   --> [{L3_1G_T}]{_V_D_} \n    [{L3_2G_S}]        --> [{L3_2G_T}] \n    [{L3_2G_S}]{V_D}   --> [{L3_2G_T}]{_V_D_} 		\n\n    {L3_1N_S}          === (nth,nþ)     * (nf,mf,mph)      \n    {L3_1N_T}          === SULE {NASAL} * FORMEN {NASAL}  \n   \n    {L3_2N_S}          === nsh         * (n,ñ)kh     \n    {L3_2N_T}          === AHA {NASAL} * HWESTA {NASAL}\n   \n    [{L3_1N_S}]        --> [{L3_1N_T}] \n    [{L3_1N_S}]{V_D}   --> [{L3_1N_T}]{_V_D_} \n    [{L3_2N_S}]        --> [{L3_2N_T}] \n    [{L3_2N_S}]{V_D}   --> [{L3_2N_T}]{_V_D_} \n\n    {L4_S}            === (dh,ð)    * v   	* zh	  * gh\n    {L4_T}            === ANTO      * AMPA  * ANCA	* UNQUE\n		\n    [{L4_S}]          --> [{L4_T}] \n    [{L4_S}]{V_D}     --> [{L4_T}]{_V_D_} \n\n    {L4_G_S}            === (dh,ð)(dh,ð)     * vv               * (zhzh,zzh)	     * (ghgh,ggh)\n    {L4_G_T}            === ANTO {GEMINATE}  * AMPA {GEMINATE}  * ANCA {GEMINATE}  * UNQUE {GEMINATE}\n		\n    [{L4_G_S}]          --> [{L4_G_T}] \n    [{L4_G_S}]{V_D}     --> [{L4_G_T}]{_V_D_} \n\n    {L4_N_S}            === n(dh,ð)       * (mv,nv)       * nzh	          * (n,ñ)gh\n    {L4_N_T}            === ANTO {NASAL}  * AMPA {NASAL}  * ANCA {NASAL}  * UNQUE {NASAL}\n		\n    [{L4_N_S}]          --> [{L4_N_T}] \n    [{L4_N_S}]{V_D}     --> [{L4_N_T}]{_V_D_} \n\n    {L5_S}            === n     * m     * ny     * ñ\n    {L5_T}            === NUMEN * MALTA * NOLDO  * NWALME\n		\n    [{L5_S}]          --> [{L5_T}] \n    [{L5_S}]{V_D}     --> [{L5_T}]{_V_D_} \n\n    {L5_G_S}            === nn      * mn      * (nyny,nny)   * ññ\n    {L5_G_T}            === NUMEN   * MALTA   * NOLDO        * NWALME\n		\n    [{L5_G_S}]          --> [{L5_G_T}] \n    [{L5_G_S}]{V_D}     --> [{L5_G_T}]{_V_D_} \n		\n    {L6_S}            === w  	  * y     * rr               * ww         	    * yy\n    {L6_T}            === VALA  * ANNA  * ROMEN {GEMINATE} * VALA {GEMINATE}  * ANNA {GEMINATE}\n    [r * {L6_S}]      --> [ ORE   * {L6_T}] \n    [r * {L6_S}]{V_D} --> [ ROMEN * {L6_T}]{_V_D_} \n\n    \\** This one is not useful (redundant with higher) **\\\n    \\** Keep it for clarity of mind **\\\n    r_                --> ORE\n\n    s{V_D}            --> SILME_NUQUERNA {_V_D_}  \\** Before a vowel goes down **\\\n    s                 --> SILME                   \\** Any other pos, up **\\\n    z{V_D}            --> ESSE_NUQUERNA {_V_D_}   \\** Before a vowel goes down **\\\n    z                 --> ESSE                    \\** Any other pos, up **\\\n		\n    ns{V_D}           --> SILME_NUQUERNA {NASAL} {_V_D_}\n    ns                --> SILME_NUQUERNA {NASAL}                   \n    nz{V_D}           --> ESSE_NUQUERNA {NASAL} {_V_D_}   \n    nz                --> ESSE_NUQUERNA {NASAL}                \n\n    ts                --> TINCO ALVEOLAR_SIGN\n    ps                --> PARMA ALVEOLAR_SIGN\n    (ks,cs,x)         --> QUESSE ALVEOLAR_SIGN\n\n    ts{V_D}           --> TINCO ALVEOLAR_SIGN {_V_D_}  \n    ps{V_D}           --> PARMA ALVEOLAR_SIGN {_V_D_}\n    (ks,cs,x){V_D}    --> QUESSE ALVEOLAR_SIGN {_V_D_}	\n\n    h{V_D}            --> HYARMEN {_V_D_}\n    h                 --> HYARMEN\n    hh{V_D}           --> HYARMEN {GEMINATE} {_V_D_}\n    hh                --> HYARMEN {GEMINATE}\n                      \n    l{V_D}            --> LAMBE {_V_D_}\n    l                 --> LAMBE\n                      \n    ll{V_D}           --> LAMBE {GEMINATE} {_V_D_}\n    ll                --> LAMBE {GEMINATE}\n		\n    (hl,lh){V_D}      --> ALDA {_V_D_}\n    (hl,lh)           --> ALDA		\n\n    (hr,rh){V_D}      --> ARDA {_V_D_}\n    (hr,rh)           --> ARDA	\n		\n  \\end\n  \n  \\beg rules punctutation\n    . --> PUNCT_DDOT\n    .. --> PUNCT_DOT PUNCT_DDOT PUNCT_DOT\n    …  --> PUNCT_TILD\n    ... --> PUNCT_TILD\n    .... --> PUNCT_TILD\n    ..... --> PUNCT_TILD\n    ...... --> PUNCT_TILD\n    ....... --> PUNCT_TILD\n\n    , --> PUNCT_DOT\n    : --> PUNCT_DOT\n    ; --> PUNCT_DOT\n    ! --> PUNCT_EXCLAM\n    ? --> PUNCT_INTERR\n    · --> PUNCT_DOT\n\n    \\** Apostrophe **\\\n\n    \' --> {NULL}\n    ’ --> {NULL}\n\n    \\** Quotes **\\\n\n    “ --> DQUOT_OPEN\n    ” --> DQUOT_CLOSE\n    « --> DQUOT_OPEN \n    » --> DQUOT_CLOSE \n    \n    - --> PUNCT_DOT    \n    – --> PUNCT_TILD  \n    — --> PUNCT_DTILD\n \n    [ --> PUNCT_PAREN_L\n    ] --> PUNCT_PAREN_R\n    ( --> PUNCT_PAREN_L\n    ) --> PUNCT_PAREN_R\n    { --> PUNCT_PAREN_L\n    } --> PUNCT_PAREN_R\n    < --> PUNCT_PAREN_L\n    > --> PUNCT_PAREN_R  \n\n    \\** Not universal between fonts ... **\\\n    $ --> BOOKMARK_SIGN\n    ≤ --> RING_MARK_L \\** Ring inscription left beautiful stuff **\\\n    ≥ --> RING_MARK_R \\** Ring inscription right beautiful stuff **\\\n  \\end\n\n  \\beg rules numbers\n    0 --> NUM_0\n    1 --> NUM_1\n    2 --> NUM_2\n    3 --> NUM_3\n    4 --> NUM_4\n    5 --> NUM_5\n    6 --> NUM_6\n    7 --> NUM_7\n    8 --> NUM_8\n    9 --> NUM_9\n    A --> NUM_10\n    B --> NUM_11   \n  \\end\n  \n\\end\n\n\\beg postprocessor\n  \\resolve_virtuals\n\\end"