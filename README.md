# wp-optimize
The WP Optimize class provides a wrapper to optimize WordPress and remove unnecessary or unwanted functions and scripts.

## Usage
Include the WP Optimize class in your plugin, theme or child theme file. 

### Choose your optimisations 
Determine which optimizations to run by setting an array of optimisations.
A true value will execute the given optimisation.

Currently, the class defaults to the following optimisations:

            $optimisations = array(
                        'no_scripts_styles_version' => true,
                        'no_wp_version'             => true,
                        'no_feed'                   => false,
                        'no_shortlinks'             => true,
                        'no_rsd_manifest'           => true,
                        'no_wp_emoji'               => true,
                        'disable_xmlrpc'            => true,
                        'block_external_http'       => true,
                        'stop_heartbeat'            => false,
                        'disable_comments'          => false,
                        'no_jquery'                 => false,
                        'no_embed'                  => false,  
                        'defer_js'                  => false,  
                        'defer_css'                 => false  
            );
            
**no_scripts_styles_version (boolean)**
Removes the version trail in enqueued scripts and styles.

**no_wp_version (boolean)**
Removes the WP version from the head section of the site.

**no_feed (boolean)**
Disables the post feeds.

**no_shortlinks (boolean)**
Removes the shortlinks in the head section of the site.

**no_rsd_manifest (boolean)**
Removes the RSD and WLW Manifest links in the head section of the site.

**no_wp_emoji (boolean)**
Removes the scripts that are enqueued for displaying emojis.

**disable_xmlrpc (boolean)**
Disables the xmlrpc functionality.

**block_external_http (boolean)**
Block requests to external http on the front-end side. Thus, blocks all request that are done by plugins to external addresses.

**stop_heartbeat (boolean)**
Unregisters the heartbeat scripts, which is usually responsible for autosaves.

**disable_comments (boolean)**
Disables the comments functionality and removes it from the admin menu.

**no_jquery (boolean)**
Removes the default jQuery script

**no_embed (boolean)**
Removes the script files that are enqueued by the WordPress media embed system.

**defer_js (boolean)**
Adds defer="defer" to all enqueued JavaScript files

**defer_css (boolean)**
Defers all registered scripts using the loadCSS function from the Filament Group.     

### Create instance
Create a new instance of the WP_Optimize class with your optimisations array as arguments.

            $optimize = new Classes\WP_Optimize\MT_WP_Optimize($optimisations);
