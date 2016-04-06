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
            'no_embed'                  => false  
);

### Create instance
Create a new instance of the WP_Optimize class with your optimisations array as arguments.

$optimize = new MT_WP_Optimize($optimisations);
