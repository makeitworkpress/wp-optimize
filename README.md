# wp-optimize
The WP Optimize class provides a wrapper to optimize WordPress and remove unnecessary or unwanted functions and scripts.

WP Optimize is maintained by [Make it WorkPress](https://makeitwork.press/scripts/wp-optimize/).

## Usage
Include the WP Optimize class in your plugin, theme or child theme file by requiring or autoloading the given file. You can read more about autoloading in [the readme of wp-autoload](https://github.com/makeitworkpress/wp-autoload). 

### Choose your optimisations 
Determine which optimizations to run by setting an array of optimisations.
A true value will execute the given optimisation.

Currently, the class defaults to the following optimisations:

            $optimisations = array(
                'blockExternalHTTP'         => false, // Block requests to external http on the front-end side. Thus, blocks all request that are done by plugins to external addresses.
                'deferCSS'                  => false, // Adds defer="defer" to all enqueued JavaScript files.
                'deferJS'                   => true,  // Defers all registered scripts using the loadCSS function from the Filament Group.  
                'disableComments'           => false, // Disables the comments functionality and removes it from the admin menu.
                'disableEmbed'              => false, // Removes the script files that are enqueued by the WordPress media embed system.
                'disableEmoji'              => true,  // Removes the scripts that are enqueued for displaying emojis.
                'disableFeeds'              => false, // Removes the post feeds.
                'disableHeartbeat'          => false, // Unregisters the heartbeat scripts, which is usually responsible for autosaves.
                'disablejQuery'             => false, // Removes the default jQuery script.
                'disablejQueryMigrate'      => true,  // Removes the jQuery Migrate script.
                'disableRestApi'            => false, // Disables the rest api.
                'disableRSD'                => true,  // Removes the RDS link in the head section of the site.
                'disableShortlinks'         => true,  // Removes the shortlinks in the head section of the site.                     
                'disableVersionNumbers'     => true,  // Removes the version trail in enqueued scripts and styles.           
                'disableWLWManifest'        => true,  // Removes the WLW Manifest links in the head section of the site.
                'disableWPVersion'          => true,  // Removes the WP version from the head section of the site.           
                'disableXMLRPC'             => true,  // Disables the xmlrpc functionality.
                'jqueryToFooter'            => true,  // Moves the default jQuery script to the footer.
                'limitCommentsJS'           => true,  // Limits the JS for comments only to singular entities
                'limitRevisions'            => true,  // Limits the number of revisions to 5
                'removeCommentsStyle'       => true,  // Removes the .recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;} styling in the head section
                'slowHeartbeat'             => true   // Slows the heartbeat down to one per minute
            );
               

### Create instance
Create a new instance of the WP_Optimize class with your optimisations array as arguments.

            $optimize = new MakeitWorkPress\WP_Optimize\Optimize($optimisations);
