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
  
  return {
    set: set,
    get: get
  };
})();

// ------------------------------------------------------------
// INTERNAL FUNCTIONALITY
// Maintains configuration properties and interaction that 
// should not be sent to and is not supported by the server. 
// ------------------------------------------------------------
var ENVINT = (function() {
  var _process = null;

  var _commands = {
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
    },
    world: function(args) {
      var using = 'Usage: /world world-ID [password] [admin password]\nIf you\'re interested ' +
            'in creating your own virtual world, please specify 0 as world-ID.';
    
      if (args.length < 1) {
        if (!ENVDATA.get('world')) {
          throw using;
        }
      
        registerMessage('information', 'World: "' + ENVDATA.get('world') + '". Password: "' +
          ENVDATA.get('worldPwd') + '".');
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
        
        ENVAPP.processCommand('lword', { 
          world:    ENVDATA.get('world'), 
          worldPwd: ENVDATA.get('worldPwd'), 
          adminPwd: ENVDATA.get('adminPwd') 
        }, 
        // Success callback. This function will be invoked when the world password is correct 
        // and/or the world itself was created, as the user himself/herself is authenticated.
        function(data) {
          ENVDATA.set('token', data.message.token);
          startUpdateProcess();
        }, 
        // Failure callback. This function will be invoked when the world password is inaccurate
        // or the user is not authenticated to create virtual world chats.
        function() { 
          ENVDATA.set('world', null);
          ENVDATA.set('worldPwd', null);
        });
      }
    }
  };
  
  function startUpdateProcess() {
    if (_process) {
      return;
    }
    
    // Chat process that polls the chat service every five seconds. A date indicator also
    // keep track of the update frequency dates, which is used to modify the response received
    // from the service. Note that the service is capped.
    _process = window.setInterval(function() {
        ENVAPP.processCommand('update ' + ENVDATA.get('lastID'), null, function(data) {
          var id = ENVDATA.get('lastID');
          
          if (!id || data.messageId > id) {
            ENVDATA.set('lastID', data.messageId);
          }
        });
      }, 5000);
    
    // Unload the process upon leave
    $(document).bind('unload', clearUpdateProcess);
  }
  
  function clearUpdateProcess() {
    if (_process) {
      window.clearInterval(_process);
    }
  }
  
  function registerMessage(messageType, message, nick) {
    var html = null;
    
    // Replace all new lines with HTML line break tags
    message = message.replace(/\r\n|\r|\n/g, '<br />');
    
    switch (messageType) {
      case 'information':
      case 'error':
        nick = 'System'; // deliberately fall through
      case 'emotion':
      case 'message':
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

  function processInternal(args) {
    if (args && args.length > 0) {
      var cmd = (new String(args[0])).toLowerCase().replace(/\-/g, '_');
    
      if (_commands[cmd]) {
        try {
          _commands[cmd](ENVCOM.getArguments(args));
        } catch (e) {
          registerMessage('error', e);
        }
        return true;
      }
    }
    
    
    return false;
  }
  
  return {
    process: processInternal,
    registerMessage: registerMessage
  };
})();

// ------------------------------------------------------------
// MAIN APPLICATION 
// Routes internal and external commandlets to its appropriate
// maintainers. Communicates with the server.
// ------------------------------------------------------------
var ENVAPP = (function() { 
  function processCommand(cmd, additionalData, callback, failCallback) {
    var parts = cmd.split(' ');
    
    if (parts.length < 1 || ENVINT.process(parts)) {
      return;
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
            if (responses[i].message && responses[i].messageType) {
              ENVINT.registerMessage(responses[i].messageType, 
                                     responses[i].message, 
                                     responses[i].nick);
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
  
  return {
    keyDown: function(ev) {
      if (ev.keyCode == 13) {
        processMessage($(this).val());
        ev.preventDefault();
        $(this).val('');
      }
    },
    processCommand: processCommand
  };
})();

$(function() {
  $('[name=message]').keydown(ENVAPP.keyDown).focus();
  $('#opacity-level').change(function() {
    $('#chat-window').css('opacity', $(this).val() / 100.0);
  }).val($('#chat-window').css('opacity') * 100.0);
});