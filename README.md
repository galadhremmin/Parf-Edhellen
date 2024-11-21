# Parf Edhellen
This is the source code for [elfdict.com](http://www.elfdict.com), a non-profit, free dictionary online for Tolkien's languages. Maintained by Leonard. Follow me on twitter at [@parmaeldo](https://twitter.com/parmaeldo).

## Service configuration
Ensure that the following dependencies are installed:

```
php8.2-curl php8.2-gd php8.2-intl php8.2-mbstring php8.2-mysql php8.2-readline php8.2-xml php8.2-zip
```

Configure the database using the model files. Execute the script files in ascending order, starting with schema.sql. You can apply the migrations once you've got Laravel configured. To configure Laravel, run the following commands sequentially:

```
gh repo clone galadhremmin/Parf-Edhellen
cd Parf-Edhellen/src
npm install
mkdir bootstrap/cache
chmod 755 bootstrap/cache
mkdir -p storage/framework/{sessions,views,cache}
chmod -R 775 storage/framework
cp .env.example .env
vim .env # configure appropriately
composer install
php artisan key:generate
php artisan storage:link
```

> Always make sure to [follow Laravel's guidelines and best practices](https://laravel.com/docs/9.x/deployment) before moving the app into production.

## Development

Run *Parf Edhellen* locally by executing `php artisan serve`. You need to compile the TypeScript application by running `npm run watch`. 

Make sure to run Node v16. Parf Edhellen isn't currently compatible with Node 17+.

## Want to help out?
If you are interested in helping out, please get in touch with [galadhremmin](https://github.com/galadhremmin).
You can also help us by donating. Please visit [elfdict.com](http://www.elfdict.com) for more information.

## Documentation
### Audit trail
The audit trail consists of activities. Activities are specified as constants within the _App\Models\AuditTrail_ class, and utilised throughout the application. The _App\Repositories\AuditTrailRepository_ contains the necessary functionality for converting activities (which are integers) into human-readable strings.

_Note_: audit trail model objects with the property _is_admin_ set to 1 (= true) can only be seen by administrators.

### Cookies
The following cookie names are used by the application:

| Cookie name | Description |
|-------------|-------------|
| ed-usermode | Administrators can give a cookie with this name the value _incognito_ to hide their activity. | 

### System errors
The schema _system_errors_ contain information about client-side as well as server-side exceptions. Common exceptions (404 Page not found, 401 Unauthorized, etc.) are separated from the rest by the _is_common_ column. 

Uncaught client-side exceptions are caught by the _onerror_ event, passed to a web API, and logged. Refer to the API documentation for more information.

# Coding style
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

# License
ElfDict (Parf Edhellen; elfdict.com) is licensed in accordance with [AGPL](https://tldrlegal.com/license/gnu-affero-general-public-license-v3-(agpl-3.0)).
