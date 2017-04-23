<?php
/**
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/

// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------

return
	array(
		"base_url" => "http://localhost/lib/hybridauth/",

		"providers" => array (
			// openid providers
			"OpenID" => array (
				"enabled" => true
			),

			"Yahoo" => array (
				"enabled" => false,
				"keys"    => array ( "key" => "", "secret" => "" ),
			),
			
			"Google" => array (
				"enabled" => true,
				"keys"    => array ( "id" => "978144015297-fljq8hsq8h0m8h09tl0ma408tqq925o0.apps.googleusercontent.com", "secret" => "soQ9kAzonPlaTFF4ynMaocdU" ),
        "scope"   => 'profile https://www.googleapis.com/auth/userinfo.email'
			),

			"Facebook" => array (
				"enabled" => true,
				"keys"    => array ( "id" => "802497593177250", "secret" => "0ba66b029014e820d3dd5b6356e01302" ),
			  "scope"   => "email",
			  "trustForwarded" => true
			),

			"Twitter" => array (
				"enabled" => true,
				"keys"    => array ( "key" => "bTHi6ObFiCXtGQCaG4DIJ7PtS", "secret" => "1BXYQLjzZGzYvZH4CcOPZC4BE6KZyj6cJEUJASNxRqXyOwkDW8" )
			),

			// windows live
			"Live" => array (
				"enabled" => false,
				"keys"    => array ( "id" => "", "secret" => "" )
			)
		),

		// If you want to enable logging, set 'debug_mode' to true.
		// You can also set it to
		// - "error" To log only error messages. Useful in production
		// - "info" To log info and error messages (ignore debug messages)
		"debug_mode" => false,

		// Path to file writable by the web server. Required if 'debug_mode' is not false
		"debug_file" => "",
	);
