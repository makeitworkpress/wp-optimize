# wp-optimize
The WP Optimize class provides a wrapper to optimize WordPress and remove unnecessary or unwanted functions and scripts.

## Usage
Include the WP Optimize class in your plugin, theme or child theme file by requiring or autoloading the given file. 

### Choose your optimisations 
Determine which optimizations to run by setting an array of optimisations.
A true value will execute the given optimisation.

Currently, the class defaults to the following optimisations:

            $optimisations = array(
                'blockExternalHTTP'         => false,
                'deferCSS'                  => false,
                'deferJS'                   => false,
                'disableEmbed'              => false,
                'disableComments'           => false,
                'disableRestApi'            => false,
                'disableXMLRPC'             => true,
                'jqueryToFooter'            => true,
                'removeEmoji'               => true,
                'removeFeeds'               => false,
                'removeHeartbeat'           => false,
                'removejQuery'              => false,
                'removeShortlinks'          => true,
                'removeVersionNumbers'      => true,            
                'removeWLWManifest'         => true,
                'removeWPVersion'           => true
            );
            
**removeVersionNumbers (boolean)**
Removes the version trail in enqueued scripts and styles.

**removeWPVersion (boolean)**
Removes the WP version from the head section of the site.

**removeFeeds (boolean)**
Removes the post feeds.

**removeShortlinks (boolean)**
Removes the shortlinks in the head section of the site.

**removeWLWManifest (boolean)**
Removes the WLW Manifest links in the head section of the site.

**removeEmoji (boolean)**
Removes the scripts that are enqueued for displaying emojis.

**disableXMLRPC (boolean)**
Disables the xmlrpc functionality.

**blockExternalHTTP (boolean)**
Block requests to external http on the front-end side. Thus, blocks all request that are done by plugins to external addresses.

**removeHeartbeat (boolean)**
Unregisters the heartbeat scripts, which is usually responsible for autosaves.

**disableComments (boolean)**
Disables the comments functionality and removes it from the admin menu.

**disableRestApi (boolean)**
Disables the rest api.

**removejQuery (boolean)**
Removes the default jQuery script.

**jqueryToFooter (boolean)**
Moves the default jQuery script to the footer.

**disableEmbed (boolean)**
Removes the script files that are enqueued by the WordPress media embed system.

**deferJS (boolean)**
Adds defer="defer" to all enqueued JavaScript files.

**deferCSS (boolean)**
Defers all registered scripts using the loadCSS function from the Filament Group.     

### Create instance
Create a new instance of the WP_Optimize class with your optimisations array as arguments.

            $optimize = new WP_Optimize\Optimize($optimisations);
