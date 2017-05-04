Parf Edhellen
==============
This is the source code for [elfdict.com](http://www.elfdict.com), a non-profit, free dictionary online for Tolkien's languages. Maintained by Leonard Wickmark. Follow me on twitter at [@parmaeldo](https://twitter.com/parmaeldo).

Version 1.7 is in production.

Want to help out?
-----------------

If you are interested in helping out, please get in touch with [galadhremmin](https://github.com/galadhremmin).
You can also help us by donating. Please visit [elfdict.com](http://www.elfdict.com) for more information.

I'd like to thank JetBrains for supporting ElfDict by giving us their excellent PHPStorm for free.

> 
> Note! We are currently transitioning to the Laravel framework. All such work is done in the src directory.
>

Coding style
------------
* \t must be replaced with four spaces for JavaScript and PHP, else two spaces.
* PHP is written in camelCase with exception for classes and interfaces, which are capitalized.
* SQL is written in upper case.
* Always a single space between statements, brackets and paranthesis.
* Curly brackets are positioned on a new line for methods, interfaces and classes, else the first bracket is positioned on the same line as the clause. Example:

      class A
      {
          public function sayHello() 
          {
              if (empty($this->_name)) {
                  echo 'Hello!';
              } else {
                  echo 'Hello, '.$this->_name;
              }
          }
      }
        
 * Conditional operators are restricted to one line, unless the resulting operation is long enough to warrant a split, at which point the first new line is positioned _before_ the question mark, and the second one new line _before_ the colon. Example:
 
      ```function doFruityThings() 
      {
          $fruitName = $fruit instanceof Apple ? 'apple' : 'fruit';
          $customer = $fruit instanceof Apple
              ? FruitVendor::sell($fruit, $appleAdvert, true) 
              : FruitVendor::discard($fruit);
      }
      ```

The source code is provided as-is. The code does not, in any shape or form, reflect best coding practices; it's a non-profit hobby project of mine.

License
-------
ElfDict (Parf Edhellen; elfdict.com) is licensed in accordance with [AGPL](https://tldrlegal.com/license/gnu-affero-general-public-license-v3-(agpl-3.0)).
