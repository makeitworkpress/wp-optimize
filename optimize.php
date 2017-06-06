<?php
/**
 * Class wrapper for removing unnecessary WordPress functions
 *
 * @author Michiel Tramper - https://www.michieltramper.com & https://www.makeitworkpress.com
 */
namespace WP_Optimize;
use WP_Error as WP_Error;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Optimize {
        
    /**
     * Holds the configurations for the optimizations
     */
    private $optimize = array();
            
    /** 
     * Constructor
     *
     * @param array $optimize The optimalizations to conduct
     */
    public function __construct(Array $optimizations = array()) {
        $defaults =  array(
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
        
        $this->optimize = wp_parse_args($optimizations, $defaults);
        $this->optimize();
    }
    
    /**
     * Hit it! Runs eachs of the functions if enabled
     */
    private function optimize() {

        foreach($this->optimize as $key => $value) {
            
            if( $value === true && method_exists($this, $key) ) {
                $this->$key();
            }
            
        }
        
    }
    
    /**
     * Block plugins to connect to external http's
     */  
    private function blockExternalHTTP() {
        if( ! is_admin() ) {
            add_filter( 'pre_http_request', function() {
                return new WP_Error('http_request_failed', __('Request blocked by WP Optimize.'));    
            }, 100 );
        }
    }
    
    /**
     * Defers all CSS using loadCSS from the Filament Group. Thanks dudes and dudettes!
     */
    private function deferCSS() {
        
        // Rewrite our object context
        $object = $this;
        
        // Dequeue our CSS and save our styles. Please note - this function removes conditional styles for older browsers
        add_action( 'wp_enqueue_scripts', function() use( $object ) {
            
            global $wp_styles;

            // Save the queued styles
            foreach( $wp_styles->queue as $style ) {    
                $object->styles[]   = $wp_styles->registered[$style];  
                $dependencies       = $wp_styles->registered[$style]->deps;

                if( ! $dependencies)
                    continue;

                // Add dependencies, but only if they are not included yet
                foreach( $dependencies as $dependency ) { 
                    $object->styles[] = $wp_styles->registered[$dependency];
                }                

            }
            
            // Remove duplicate values because of the dependencies
            $object->styles = array_unique( $object->styles, SORT_REGULAR );

            // Dequeue styles and their dependencies except for conditionals
            foreach( $object->styles as $style ) {
                wp_dequeue_style($style->handle);
            }            
            
        }, 9999);        
        
        // Load our CSS using loadCSS
        add_action( 'wp_head', function() use( $object ) {
         
            $output = '<script>function loadCSS(a,b,c,d){"use strict";var e=window.document.createElement("link"),f=b||window.document.getElementsByTagName("script")[0],g=window.document.styleSheets;return e.rel="stylesheet",e.href=a,e.media="only x",d&&(e.onload=d),f.parentNode.insertBefore(e,f),e.onloadcssdefined=function(b){for(var c,d=0;d<g.length;d++)g[d].href&&g[d].href.indexOf(a)>-1&&(c=!0);c?b():setTimeout(function(){e.onloadcssdefined(b)})},e.onloadcssdefined(function(){e.media=c||"all"}),e}';

            foreach( $object->styles as $style ) { 

                if( isset($style->extra['conditional'] ) ) 
                    continue;
                
                // Load local assets
                if( strpos($style->src, 'http') === false )    
                    $style->src = site_url() . $style->src;

                $output .= 'loadCSS("' . $style->src . '", "", "' . $style->args . '");';           
            }

            $output .= '</script>';
            
            echo $output;
            
        }, 9999);
        
    }    
    
    /**
     * Defers all JS
     */
    private function deferJS() {
        add_filter( 'script_loader_tag', function( $tag ) {
            return str_replace( ' src', ' defer="defer" src', $tag );    
        }, 10, 1 );    
    }
    
    /**
     * Removes the support and appearance of comments
     */  
    private function disableComments() {
        
        // by default, comments are closed.
        if( is_admin() ) {
            update_option( 'default_comment_status', 'closed' ); 
        }
        
        // Closes plugins
        add_filter( 'comments_open', '__return_false', 20, 2 );
        add_filter( 'pings_open', '__return_false', 20, 2 );
        
        // Removes admin support for post types and menus
        add_action( 'admin_init', function() {
            
            $post_types     = get_post_types();
            
            foreach($post_types as $post_type) {
                if (post_type_supports($post_type, 'comments') ) {
                    remove_post_type_support($post_type, 'comments');
                    remove_post_type_support($post_type, 'trackbacks');
                }
            }
            
        }); 
        
        // Removes menu in left dashboard meun
        add_action( 'admin_menu', function() {
            remove_menu_page('edit-comments.php');
        } );
        
        // Removes comment menu from admin bar
        add_action( 'wp_before_admin_bar_render', function() {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu('comments');              
        } );              
        
    }
    
    
    /**
     * Removes the Embed Javascript and References        
     */    
    private function disableEmbed() {
        add_action( 'init', function() {
        
            // Removes the oEmbed JavaScript.
            remove_action('wp_head', 'wp_oembed_add_host_js'); 

            // Removes the oEmbed discovery links.
            remove_action('wp_head', 'wp_oembed_add_discovery_links');        

            // Remove the oEmbed route for the REST API epoint.
            remove_action('rest_api_init', 'wp_oembed_register_route');

            // Disables oEmbed auto discovery.
            remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
            
            // Turn off oEmbed auto discovery.
            add_filter( 'embed_oembed_discover', '__return_false' );            
            
        });
        
    }     
    
    /**
     * Disables the access to Rest API
     */
    private function disableRestApi() {
        
        // Remove the REST API lines from the HTML Header
        remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );

        // Filters for WP-API version 2.x
        add_filter('rest_enabled', '__return_false');
        add_filter('rest_jsonp_enabled', '__return_false');        
        
    }
    
    /**
     * Disables XML RPC. Warning, makes some functions unavailable!
     */
    private function disableXMLRPC() {
        
        if( is_admin() ) {
            update_option( 'default_ping_status', 'closed' ); // Might do something else here to reduce our queries  
        }
        
        // Remove RSD XMLRPC Link
        remove_action('wp_head', 'rsd_link');  
        
        add_filter( 'xmlrpc_enabled', '__return_false' ); 
        add_filter( 'pre_update_option_enable_xmlrpc', '__return_false' );
        add_filter( 'pre_option_enable_xmlrpc', '__return_zero' );       
        
        /**
         * Unsets xmlrpc headers
         *
         * @param array $headers The array of wp headers    
         */
        add_filter( 'wp_headers', function( $headers ) {
            if( isset( $headers['X-Pingback'] ) ) {
                unset( $headers['X-Pingback'] );
            }
            return $headers;              
        }, 10, 1 );
        
        /**
         * Unsets xmlr methods for pingbacks
         *
         * @param array $methods The array of xmlrpc methods
         */ 
        add_filter( 'xmlrpc_methods', function( $methods ) {
            unset( $methods['pingback.ping'] );
            unset( $methods['pingback.extensions.getPingbacks'] );
            return $methods;
        }, 10, 1  );
        
    }    
    
    /**
     * Puts jquery inside the footer
     */
    private function jqueryToFooter() {
        add_action( 'wp_enqueue_scripts', function() {
            wp_deregister_script( 'jquery' );
            wp_register_script( 'jquery', includes_url( '/js/jquery/jquery.js' ), false, NULL, true );
            wp_enqueue_script( 'jquery' );           
        } );    
    }     
         
    /**
     * Removes WP Emoji
     */
    private function removeEmoji() {
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        
        /**
         * Removes Emoji from the TinyMCE Editor
         *
         * @param array $plugins The plugins hooked onto the TinyMCE Editor
         */
        add_filter( 'tiny_mce_plugins', function( $plugins ) {
            if ( ! is_array($plugins) ) {
                return array();
            }
            return array_diff($plugins, array('wpemoji'));            
        }, 10, 1 );       
    }    
      
    /**
     * Removes links to RSS feeds
     */
    private function removeFeeds() {        
        remove_action( 'wp_head', 'feed_links_extra', 3 ); 
        remove_action( 'wp_head', 'feed_links', 2 );   
        add_action( 'do_feed', array($this, 'removeFeedsHook'), 1 );
        add_action( 'do_feed_rdf', array($this, 'removeFeedsHook'), 1 );
        add_action( 'do_feed_rss', array($this, 'removeFeedsHook'), 1 );
        add_action( 'do_feed_rss2', array($this, 'removeFeedsHook'), 1 );
        add_action( 'do_feed_atom', array($this, 'removeFeedsHook'), 1 );        
    }  
    
    /**
     * Removes the actual feed links
     */
    public function removeFeedsHook() {
        wp_die( '<p>' . __('Feed disabled by WP Optimize.') . '</p>' );
    }
    
    /**
     * Removes the WP Heartbeat Api. Caution: this disables the autosave functionality 
     */
    private function removeHeartbeat() {
        add_action('admin_enqueue_scripts', function() {
            wp_deregister_script('heartbeat');    
        });
	}
    
    /**
     * Deregisters jQuery.
     */    
    private function removejQuery() {
        add_action( 'wp_enqueue_scripts', function() {
            wp_deregister_script('jquery');
        } );
    }
    
    /**
     * Removes the WP Shortlink 
     */
    private function removeShortlinks() { 
        remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );        
    }    
    
    /**
     * Removes the version hook on scripts and styles
     *
     * @uses MT_WP_Optimize::no_scripts_styles_version_hook
     */
    private function removeVersionNumbers() {
        add_filter( 'style_loader_src', array($this, 'removeVersionNumbersHook'), 20000 );
        add_filter( 'script_loader_src', array($this, 'removeVersionNumbersHook'), 20000 ); 
    }
    
    /**
     * Removes version numbers from scripts and styles. 
     * The absence of version numbers increases the likelyhood of scripts and styles being cached.
     *
     * @param string @target_url The url of the script
     */
    public function removeVersionNumbersHook($target_url = '') { 
        
        if( strpos( $target_url, 'ver=' ) ) {
            $target_url = remove_query_arg( 'ver', $target_url );
        }
        
        return $target_url;
        
    }
    
    /**
     * Removes WLW manifest bloat
     */
    private function removeWLWManifest() {
        remove_action('wp_head', 'wlwmanifest_link');   
    }   
       
    /**
     * Removes the WP Version as generated by WP
     */
    private function removeWPVersion() {
        remove_action( 'wp_head', 'wp_generator' ); 
        add_filter( 'the_generator', '__return_null' ); 
    }
      
}