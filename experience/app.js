// ------------------------------------------------------------
// UTILITIES
// Provides a common implementation for common functionality.
// ------------------------------------------------------------
var ENVCOM = {
  trim: function(str) {
    return str.replace(/^\s*|\s*$/g, '');
  },
  isArray: function(ref) {
    return Object.prototype.toString.call(ref) === "[object Array]";
  },
  getArguments: function(cmd) {
    var d = [];
    for (var i = 1; i < cmd.length; ++i) {
      if (/^[\\s]*$/.test(cmd[i])) {
        continue;
      }
      d.push(cmd[i]);
    }
    return d;
  }
}

// ------------------------------------------------------------
// DATA CONTAINER
// Maintains volatile state within the javascript document. 
// ------------------------------------------------------------
var ENVDATA = (function() {
  var _container = {};
  
  function set(key, value) {
    _container[key] = value;
  }
  
  function get(key) {
    return _container[key] === undefined ? null : _container[key];
  }
  
  function setStorageContext(context) {
    var container = null;
    
    switch (context) {
      case 'window':
        container = {};
        break;
      case 'localstorage':
        container = window.localStorage;
        break;
      case 'switch':
        return setStorageContext(getContext() === 'window' ? 'localstorage' : 'window');
      default:
        throw 'Invalid storage context';
    }
    
    for (var key in _container) {
      container[key] = _container[key];
    }
    
    _container = container;
    setContext(context);
    
    ENVINT.registerMessage('information', 'Persistence method "' + context + '".');
    
    return context;
  }
  
  function setContext(context) {
    window.localStorage['storageContext'] = context;
  }
  
  function getContext() {
    if (!window.localStorage['storageContext']) {
      return 'window';
    }
    
    return window.localStorage['storageContext'];
  }
  
  // invoke on page load
  $(function() {
    setStorageContext(getContext());
  });
  
  return {
    set: set,
    get: get,
    setStorage: setStorageContext 
  };
})();

// ------------------------------------------------------------
// INTERNAL FUNCTIONALITY
// Maintains configuration properties and interaction that 
// should not be sent to and is not supported by the server. 
// ------------------------------------------------------------
var ENVINT = (function() {
  var _process = null;
  var _previousCommands = [];
  
  // an object containing all client side functions
  var _commands = {
    about: function(args) {
      return this['help'](['about']);
    },
    help: function(args) {
      if (args.length < 1 || /[^a-z0-9]/i.test(args[0])) {
        registerMessage('information', 'Usage: /help command\nUse commands to list all available commands.');
      } else {
        var cmd = ENVCOM.trim(args[0]).toLowerCase(); 
        
        $.get('experience/help/' + cmd + '.txt', function(data) {
          registerMessage('information', cmd.toUpperCase() + '\n' + data);
        }).error(function() {
          registerMessage('error', 'Unrecognised help topic "' + cmd + '".');
        });
      }
      
      return true;
    },
    nick: function(args) {
      var nick = args.length < 1 ? null : ENVCOM.trim(args.join(' '));
      
      // without parameters, display the current nick, or using, if the environment
      // contains no present nickname.
      if (!nick || nick.length < 1) {
        if (!ENVDATA.get('nick')) {
          throw '/nick Your desired nickname';
        }
      
        registerMessage('information', 'Current nick: "' + ENVDATA.get('nick') + '"');
      } else {
        ENVDATA.set('nick', nick);
        registerMessage('information', 'Changed nick to: "' + nick + '"')
      }
      
      return true;
    },
    world: function(args) {
      var using = 'Usage: /world world-ID [password] [admin password]\nIf you\'re interested ' +
            'in creating your own virtual world, please specify 0 as world-ID.';
    
      if (args.length < 1) {
        if (!ENVDATA.get('world')) {
          throw using;
        }
        
        var msg = 'World: "' + ENVDATA.get('world') + '". Password: "' +
          ENVDATA.get('worldPwd') + '". ';
        
        var adminPwd = ENVDATA.get('adminPwd');
        if (msg) {
          msg += 'Admin password: "' + adminPwd + '".';
        }
      
        registerMessage('information', msg);
      } else {
        // World ID is an integer uniquely identifying the world. For creating new ones,
        // zero is the one to use.
        var worldId = ENVCOM.trim(args[0]);
        if (isNaN(worldId)) {
          throw using;
        }
      
        ENVDATA.set('world', worldId);
        
        // Password is an optional parameter.
        ENVDATA.set('worldPwd', args.length > 1 ? ENVCOM.trim(args[1]) : null);
        ENVDATA.set('adminPwd', args.length > 2 ? ENVCOM.trim(args[2]) : null);
        
        registerMessage('information', 'Connecting to world "' + ENVDATA.get('world') + '"...');
        
        ENVAPP.processCommand('lword', { 
          world:    ENVDATA.get('world'), 
          worldPwd: ENVDATA.get('worldPwd'), 
          adminPwd: ENVDATA.get('adminPwd'),
          suppress: true
        }, 
        // Success callback. This function will be invoked when the world password is correct 
        // and/or the world itself was created, as the user himself/herself is authenticated.
        function(data) {
          ENVDATA.set('token', data.message.token);
          ENVDATA.set('lastID', 0);
          registerMessage('information', 'Connected to world "' + data.message.ID + '". Acquiring history...');
          startUpdateProcess();
        }, 
        // Failure callback. This function will be invoked when the world password is inaccurate
        // or the user is not authenticated to create virtual world chats.
        function() { 
          ENVDATA.set('world', null);
          ENVDATA.set('worldPwd', null);
        });
      }
      
      return true;
    },
    image: function(args) {
      if (args.length > 0) {
        ENVDATA.set('background-image', args[0]);
      
        var img = new Image();
        img.onload = function() {
          $('body').removeClass('loading').css('background-image', 'url(' + 
            ENVDATA.get('background-image') + ')');
        };
        img.src = ENVDATA.get('background-image');
        $('body').addClass('loading');
      }
      
      return false;
    },
    persistmethod: function() {
      ENVDATA.setStorage('switch');
      return true;
    },
    resume: function(args) {
      if (!ENVDATA.get('token') || !ENVDATA.get('world')) {
        throw 'No point to resume from. Please connect to a world and invoke /persistMethod and make sure that it\'s set to local storage.';
      }
      
      ENVDATA.set('lastID', 0);
      startUpdateProcess();
      
      registerMessage('information', 'Resuming world "' + ENVDATA.get('world') + '". Loading history...');
      return true;
    },
    clear: function (args) {
      var keepLocal = true; 
      if (args.length < 1) {
        registerMessage('information', 'Usage: /clear message-type [message-type] [for-all]');
      } else  {
        var messageTypes = []; 
        
        for (var i = 0; i < args.length; ++i) {
          var param = args[i].toLowerCase();
          switch (param) {
            case 'information':
            case 'error':
            case 'message':
              messageTypes.push(param);
              break;
            case 'for-all':
              keepLocal = false; // Admin flag: pass this onto the web service to clear the messages permanently 
              break;
          }
        }
        
        for (var i = 0; i < messageTypes.length; ++i) {
          removeMessagesByType(messageTypes[i]);  
        }
      }
      
      return keepLocal;
    },
    edit: function() {
      var editMode = ENVDATA.get('editMode') == 'true';
      var $messages = $('.chat-message span');
      
      if (!editMode) {
        editMode = true;
      } else {
        editMode = false;
      }
      
      ENVDATA.set('editMode', editMode ? 'true' : 'false');
      hookEditCode($messages);
      
      registerMessage('information', 'Edit mode ' + (editMode ? 'enabled' : 'disabled') + '.');
      
      return true;
    },
    debug_version: function () {
      registerMessage('information', 'You\'re using ' + navigator.appVersion);
      return true;
    }
  };
  
  function startUpdateProcess() {
    if (_process) {
      return false;
    }
    
    // Chat process that polls the chat service every five seconds. A date indicator also
    // keep track of the update frequency dates, which is used to modify the response received
    // from the service. Note that the service is capped.
    _process = window.setInterval(function() {
        ENVAPP.processCommand('update ' + ENVDATA.get('lastID'), { suppress: true }, function(data) {
          var id = ENVDATA.get('lastID');
          
          if (!id || data.messageId > id) {
            ENVDATA.set('lastID', data.messageId);
          }
        });
      }, 5000);
    
    // Unload the process upon leave
    $(document).bind('unload', clearUpdateProcess);
    
    return true;
  }
  
  function clearUpdateProcess() {
    if (_process) {
      window.clearInterval(_process);
      $(document).unbind('unload', clearUpdateProcess);
    }
  }
  
  function editEnabled() {
    return ENVDATA.get('editMode') == 'true';
  }
  
  function hookEditCode(messageObj, messageId) {
    if (/string/i.test(typeof messageObj)) {
      var encapsulatedMessage = '<span rel="message-' + messageId + '"';
            
      if (editEnabled()) {
        encapsulatedMessage += ' spellchecking="false" contenteditable="true" onkeydown="ENVINT.editRecorded(event)"';
      }
      
      messageObj = encapsulatedMessage + '>' + messageObj + '</span>';
    } else {
      var editMode = editEnabled();
      
      if (editMode) {
        $(messageObj).bind('keydown', editRecorded);
      } else {
        $(messageObj).unbind('keydown', editRecorded);
      }
      
      $(messageObj).attr('contenteditable', editMode ? 'true' : 'false').attr('spellchecking', 'false');
    }
    
    return messageObj;
  }
  
  function editRecorded(ev) {
    if (ev.keyCode != 13) {
      return;
    }
  
    var item = ev.srcElement || document.activeElement;
    if (!item) {
      return;
    }
    
    ev.preventDefault(); // suppress the key event
    
    var value = $(item).text(); // extract the textual content - ignore the HTML
    var id = parseInt(($(item).attr('rel') || '').substr(8)); // extract the message ID.
    
    if (isNaN(id) || id < 1) { // make sure that the message ID is correct
      return; 
    }
    
    if (value.length > 0 || (value.length < 1 && confirm('Are you sure you want to delete this message?'))) {
      ENVAPP.processCommand('edit ' + id + ' ' + value, { suppress: true }, function() {
        if (value.length < 1) {
          $(item).parent().remove();
        }
      });
    }
  }
  
  function registerMessage(messageType, message, nick, messageId) {
    var html = null;
    
    // Replace all new lines with HTML line break tags
    message = message.replace(/\r\n|\r|\n/g, '<br />');
    
    switch (messageType) {
      case 'information':
      case 'error':
        nick = 'System'; // deliberately fall through
      case 'emotion':
      case 'message':
        if (messageType === 'message') {
          message = hookEditCode(message, messageId);
        }
      
        html = '<div class="chat-' + messageType + '">' + message + '<div class="chat-nick"> ' + nick + ' </div></div>';
        break;
      default:
        registerMessage('error', 'Unrecognised message type "' + messageType + '".');
    }
    
    // Append the message to the message list ordered by date
    var $window = $('#scroll-window');
    $window.append(html);
    
    // Scroll to the bottom-most item in the DIV-element
    $window.get(0).scrollTop = $window.get(0).scrollHeight;
  }
  
  function removeMessagesByType(messageType) {
    var $window = $('#scroll-window');
    $window.find('.chat-' + messageType).remove();
  }

  function processInternal(args) {
    if (args && ENVCOM.isArray(args) && args.length > 0) {
      var token = '/' + args.join(' '); // this will hinder repetition & enable history navigation
      var cmd = (new String(args[0])).toLowerCase().replace(/\-/g, '_');
      
      // cancel if the previous command equals the current one
      if (args.length > 1 && token === _previousCommands[_previousCommands.length - 1]) { 
        return true;
      }
      
      // save the command in the list of previous commands
      _previousCommands.push(token);
      
      // cap history to twenty entries
      if (_previousCommands.length > 20) {
        _previousCommands.splice(0, 1);
      }
    
      if (_commands[cmd]) {
        var commandProcessed = false;
        try {
          commandProcessed = _commands[cmd](ENVCOM.getArguments(args));
        } catch (e) {
          registerMessage('error', e.toString());
        }
        return commandProcessed;
      }
    }
    return false;
  }
  
  function getInternalCommand(offset) {
    if (offset < 0 || offset >= _previousCommands.length) {
      return null;
    }
    
    return _previousCommands[offset];
  }
  
  function getInternalCommandCount() {
    return _previousCommands.length;
  }
  
  return {
    process: processInternal,
    registerMessage: registerMessage,
    getCommand: getInternalCommand,
    getCommandCount: getInternalCommandCount,
    editRecorded: editRecorded
  };
})();

// ------------------------------------------------------------
// MAIN APPLICATION 
// Routes internal and external commandlets to its appropriate
// maintainers. Communicates with the server.
// ------------------------------------------------------------
var ENVAPP = (function() { 
  function splitCommand(cmd) {
    if (!(/string/i.test(typeof cmd))) {
      return null;
    }
    
    if (cmd[0] === '/') {
      cmd = cmd.substr(1);
    }
    
    return cmd.split(' ');
  }

  function processCommand(cmd, additionalData, callback, failCallback) {
    var parts = splitCommand(cmd);
    
    // The additional data variable 'suppress' circumvents the local environment
    // command check, and thereto the chat history.
    if (!additionalData || (additionalData && !additionalData.suppress)) {
      if (parts.length < 1 || ENVINT.process(parts)) {
        return;
      }
    }
    
    var nick       = ENVDATA.get('nick');
    var worldToken = ENVDATA.get('token');
    
    if (!nick) {
      ENVINT.registerMessage('error', 'Please specify a nick before continuing. See /nick.');
      return;
    }
    
    var data = {  
      command:  parts[0], 
      args:     ENVCOM.getArguments(parts), 
      nick:     nick,
      token:    worldToken
    };
    
    if (additionalData) {
      for (var key in additionalData) {
        if (data[key] === undefined) {
          data[key] = additionalData[key];
        }
      }
    }
    
    // pie
    jQuery.ajax({
      url: 'api/experience/receiveCommand',
      data: data,
      type: 'post',
      dataType: 'json',  // web service only communicates using JSON
      success: function(msg) {
        if (!msg.succeeded) {
          // Inform the user interface of the error that prevented the desired instruction from
          // finishing.
          ENVINT.registerMessage('error', parts[0] + ': '  + msg.error);
          
          if (failCallback) {
            failCallback(msg.error);
          }
        } else {
          var responses = msg.response;
          
          // always transform the response to an array
          if (!ENVCOM.isArray(responses)) {
            responses = [ responses ];
          }
          
          // iterate through each response and generate a response appropriately,
          // feeding the information to the interface.
          for (var i = 0; i < responses.length; ++i) {
            if (responses[i].message) {
              if (responses[i].messageType) {
                ENVINT.registerMessage(responses[i].messageType, 
                                       responses[i].message, 
                                       responses[i].nick,
                                       responses[i].messageId);
              } else {
                ENVINT.process(splitCommand(responses[i].message));
              }
            }
          
            if (callback) {
              callback(responses[i]);
            }
          }
        }
      }
    });
  }

  function processMessage(msg) {
    if (msg[0] !== '/') {
      msg = '/say ' + msg;
    }
    
    processCommand(msg.substr(1));
  }
  
  function navigateChatHistory(textBox, direction) {
    if (direction === 0) {
      ENVDATA.set('chat-offset', 0);
    } else {
      var offset = parseInt(ENVDATA.get('chat-offset'));
      
      offset += direction;
      
      var cmd = ENVINT.getCommand(ENVINT.getCommandCount() + offset);
      if (!cmd) {
        offset = 0;
        cmd = '';
      }
      
      $(textBox).val(cmd);
      ENVDATA.set('chat-offset', offset);
    }
  }
  
  return {
    keyDown: function(ev) {
      switch (ev.keyCode) {
        case 13: {
          if (ev.shiftKey) {
            processMessage($(this).val());
            navigateChatHistory(0);
            ev.preventDefault();
            $(this).val('');
          }
        } break;
        /*
        case 38: { // up
          navigateChatHistory(this, -1);
          ev.preventDefault();
        } break;
        case 40: { // down
          navigateChatHistory(this, 1);
          ev.preventDefault();
        } break;*/
      }
    },
    processCommand: processCommand
  };
})();

$(function() {
  $('[name=message]').keydown(ENVAPP.keyDown).focus();
  
  $('#opacity-level').change(function() {
    $('#chat-window').css('background', 'rgba(0,0,0,' + ($(this).val() / 100.0) + ')');
    }).val(
      (/([0-9\.]+)\)$/.exec($('#chat-window').css('background-color')))[1] * 100
    );
  
  $('#config-hotspot').hover(function() {
    $('#config-window').css('top', '0px');
  });
  
  $('#config-window').mouseleave(function() {
    $(this).css('top', '-' + $(this).outerHeight() + 'px');
  });
});