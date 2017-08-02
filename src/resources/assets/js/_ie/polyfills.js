// https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent/CustomEvent
(function () {

  if ( typeof window.CustomEvent === "function" ) return false;

  function CustomEvent ( event, params ) {
    params = params || { bubbles: false, cancelable: false, detail: undefined };
    var evt = document.createEvent( 'CustomEvent' );
    evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
    return evt;
   }

  CustomEvent.prototype = window.Event.prototype;

  window.CustomEvent = CustomEvent;
})();

// RequestAnimationFrame polyfill
(function() {
    var lastTime = 0;
    var vendors = ['ms', 'moz', 'webkit', 'o'];
    for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
        window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
        window.cancelAnimationFrame = window[vendors[x]+'CancelAnimationFrame'] 
                                   || window[vendors[x]+'CancelRequestAnimationFrame'];
    }
 
    if (!window.requestAnimationFrame)
        window.requestAnimationFrame = function(callback, element) {
            var currTime = new Date().getTime();
            var timeToCall = Math.max(0, 16 - (currTime - lastTime));
            var id = window.setTimeout(function() { callback(currTime + timeToCall); }, 
              timeToCall);
            lastTime = currTime + timeToCall;
            return id;
        };
 
    if (!window.cancelAnimationFrame)
        window.cancelAnimationFrame = function(id) {
            clearTimeout(id);
        };
}());

// https://github.com/epiloque/element-dataset
(function() {
    if (!document.documentElement.dataset &&
        (
            !Object.getOwnPropertyDescriptor(HTMLElement.prototype, 'dataset') ||
            !Object.getOwnPropertyDescriptor(HTMLElement.prototype, 'dataset').get
        )
    ) {
        const descriptor = {}

        descriptor.enumerable = true

        descriptor.get = function get () {
            const element = this
            const map = {}
            const attributes = this.attributes

            function toUpperCase (n0) {
                return n0.charAt(1).toUpperCase()
            }

            function getter () {
                return this.value
            }

            function setter (name, value) {
                if (typeof value !== 'undefined') {
                    this.setAttribute(name, value)
                } else {
                    this.removeAttribute(name)
                }
            }

            for (let i = 0; i < attributes.length; i += 1) {
                const attribute = attributes[i]

                // This test really should allow any XML Name without
                // colons (and non-uppercase for XHTML)

                if (attribute && attribute.name && (/^data-\w[\w-]*$/).test(attribute.name)) {
                    const name = attribute.name
                    const value = attribute.value

                    // Change to CamelCase

                    const propName = name.substr(5).replace(/-./g, toUpperCase)

                    Object.defineProperty(map, propName, {
                        enumerable: descriptor.enumerable,
                        get: getter.bind({ value: value || '' }),
                        set: setter.bind(element, name)
                    })
                }
            }
            return map
        }

        Object.defineProperty(HTMLElement.prototype, 'dataset', descriptor)
    }
})();