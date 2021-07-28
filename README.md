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
                'block_external_HTTP'       => false, // Block requests to external http on the front-end side. Thus, blocks all request that are done by plugins to external addresses.
                'defer_CSS'                 => false, // Adds defer="defer" to all enqueued JavaScript files.
                'defer_JS'                  => true,  // Defers all registered scripts using the loadCSS function from the Filament Group.  
                'disable_comments'          => false, // Disables the comments functionality and removes it from the admin menu.
                disable_block_styling       => false, // Removes default Gutenberg block styling
                'disable_embed'             => false, // Removes the script files that are enqueued by the WordPress media embed system.
                'disable_emoji'             => true,  // Removes the scripts that are enqueued for displaying emojis.
                'disable_feeds'             => false, // Removes the post feeds.
                'disable_heartbeat'         => false, // Unregisters the heartbeat scripts, which is usually responsible for autosaves.
                'disable_jquery'            => false, // Removes the default jQuery script.
                'disable_jquery_migrate'    => true,  // Removes the jQuery Migrate script.
                'disable_rest_api'          => false, // Disables the rest api.
                'disable_RSD'               => true,  // Removes the RDS link in the head section of the site.
                'disable_shortlinks'        => true,  // Removes the shortlinks in the head section of the site. 
                'disable_theme_editor'      => false, // Disables the file editor for themes and plugins                    
                'disable_version_numbers'   => true,  // Removes the version trail in enqueued scripts and styles.           
                'disable_WLW_manifest'      => true,  // Removes the WLW Manifest links in the head section of the site.
                'disable_WP_version'        => true,  // Removes the WP version from the head section of the site.           
                'disable_XMLRPC'            => true,  // Disables the xmlrpc functionality.
                'jquery_to_footer'          => true,  // Moves the default jQuery script to the footer.
                'limit_comments_JS'         => true,  // Limits the JS for comments only to singular entities
                'limit_revisions'           => true,  // Limits the number of revisions to 5
                'remove_comments_style'     => true,  // Removes the .recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;} styling in the head section
                'slow_heartbeat'            => true   // Slows the heartbeat down to one per minute
            );
               

### Create instance
Create a new instance of the WP_Optimize class with your optimisations array as arguments.

            $optimize = new MakeitWorkPress\WP_Optimize\Optimize($optimisations);
