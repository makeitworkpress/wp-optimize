<?php
/**
 * Class wrapper for removing unnecessary WordPress functions
 *
 * @author Michiel Tramper, https://www.makeitworkpress.com
 */
namespace MakeitWorkPress\WP_Optimize;
use WP_Error as WP_Error;

defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

class Optimize {
        
    /**
     * Holds the configurations for the optimizations
     * 
     * @var array
     * @access private
     */
    private $optimize = [];
            
    /** 
     * Constructor
     *
     * @param array $optimize The optimalizations to conduct
     */
    public function __construct( array $optimizations = [] ) {

        $defaults =  [
            'block_external_HTTP'       => false,
            'defer_CSS'                 => false,
            'defer_JS'                  => false,
            'disable_comments'          => false,
            'disable_block_styling'     => false,
            'disable_embed'             => false,
            'disable_emoji'             => true,
            'disable_feeds'             => false,
            'disable_heartbeat'         => false,
            'disable_jquery'            => false,
            'disable_jquery_migrate'    => true,
            'disable_rest_api'          => false,
            'disable_RSD'               => true,
            'disable_shortlinks'        => true,  
            'disable_theme_editor'      => false,                     
            'disable_version_numbers'   => true,            
            'disable_WLW_manifest'      => true,
            'disable_WP_version'        => true,            
            'disable_XMLRPC'            => true,
            'jquery_to_footer'          => true,
            'limit_comments_JS'         => true,
            'limit_revisions'           => true,
            'remove_comments_style'     => true,
            'slow_heartbeat'            => true
        ];
        
        $this->optimize = wp_parse_args($optimizations, $defaults);
        $this->optimize();

    }
    
    /**
     * Hit it! Runs eachs of the functions if enabled
     */
    private function optimize(): void {
        foreach($this->optimize as $key => $value) {
            if( $value === true && method_exists($this, $key) ) {
                $this->$key();
            }  
        } 
    }
    
    /**
     * Block plugins to connect to external http's
     */  
    private function block_external_HTTP(): void {
        if( ! is_admin() ) {
            add_filter( 'pre_http_request', function() {
                return new WP_Error('http_request_failed', __('Request blocked by WP Optimize.'));    
            }, 100 );
        }
    }

    /**
     * Defers all CSS using loadCSS from the Filament Group. Thanks dudes and dudettes!
     */
    private function defer_CSS(): void {
        
        // Rewrite our object context
        $object = $this;
        
        // Dequeue our CSS and save our styles. Please note - this function removes conditional styles for older browsers
        add_action( 'wp_enqueue_scripts', function() use( $object ) {

            // Bail out if we are uzing the customizer preview
            if( is_customize_preview() ) {
                return;
            }            
            
            global $wp_styles;
            
            // Save the queued styles
            foreach( $wp_styles->queue as $style ) {    
                $object->styles[]   = $wp_styles->registered[$style];  
                $dependencies       = $wp_styles->registered[$style]->deps;
                
                if( ! $dependencies) {
                    continue;
                }
                
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

            // Bail out if we are uzing the customizer preview
            if( is_customize_preview() ) {
                return;
            }            
         
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
    private function defer_JS(): void {

        // Defered JS breaks the customizer or the Gutenberg Editor, hence we skip it here
        if( is_customize_preview() || is_admin() ) {
            return;
        }

        add_filter( 'script_loader_tag', function( $tag ) {
            return str_replace( ' src', ' defer="defer" src', $tag );    
        }, 10, 1 );    
    } 
    
    /**
     * Disables block styling
     */
    private function disable_block_styling(): void {
        add_action('wp_enqueue_scripts', function() {
            wp_dequeue_style( 'wp-block-library' );
            wp_dequeue_style( 'wp-block-library-theme' );
            wp_dequeue_style( 'wc-block-style' );
        }, 100);
    }
    
    /**
     * Disables the support and appearance of comments
     */  
    private function disable_comments(): void {
        
        // by default, comments are closed.
        if( is_admin() ) {
            update_option( 'default_comment_status', 'closed' ); 
        }
        
        // Closes plugins
        add_filter( 'comments_open', '__return_false', 20, 2 );
        add_filter( 'pings_open', '__return_false', 20, 2 );
        
        // Disables admin support for post types and menus
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
    private function disable_embed(): void {

        add_action( 'wp_enqueue_scripts', function() {
            wp_deregister_script('wp-embed');
        }, 100 );

        add_action( 'init', function() {
        
            // Removes the oEmbed JavaScript.
            remove_action( 'wp_head', 'wp_oembed_add_host_js' ); 

            // Removes the oEmbed discovery links.
            remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );        

            // Remove the oEmbed route for the REST API epoint.
            remove_action( 'rest_api_init', 'wp_oembed_register_route' );

            // Disables oEmbed auto discovery.
            remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
            
            // Turn off oEmbed auto discovery.
            add_filter( 'embed_oembed_discover', '__return_false' );            
            
        });
        
    }     
    
    /**
     * Disables the access to Rest API
     * Breaks a lot, so not really recommended to use.
     */
    private function disable_rest_api(): void {
        
        // Remove the references to the JSON api
        remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
        remove_action( 'rest_api_init', 'wp_oembed_register_route' );
        add_filter( 'embed_oembed_discover', '__return_false' );
        remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );
        remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );


        // Disable the API completely
        add_filter('json_enabled', '__return_false');
        add_filter('json_jsonp_enabled', '__return_false');
        add_filter('rest_enabled', '__return_false');
        add_filter('rest_jsonp_enabled', '__return_false');      
        
    }     
         
    /**
     * Removes WP Emoji
     */
    private function disable_emoji(): void {
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
    private function disable_feeds(): void {        
        remove_action( 'wp_head', 'feed_links_extra', 3 ); 
        remove_action( 'wp_head', 'feed_links', 2 );   
        add_action( 'do_feed', array($this, 'disable_feeds_hook'), 1 );
        add_action( 'do_feed_rdf', array($this, 'disable_feeds_hook'), 1 );
        add_action( 'do_feed_rss', array($this, 'disable_feeds_hook'), 1 );
        add_action( 'do_feed_rss2', array($this, 'disable_feeds_hook'), 1 );
        add_action( 'do_feed_atom', array($this, 'disable_feeds_hook'), 1 );        
    }  
    
    /**
     * Removes the actual feed links
     */
    public function disable_feeds_hook(): void {
        wp_die( '<p>' . __('Feed disabled by WP Optimize.') . '</p>' );
    }
    
    /**
     * Removes the WP Heartbeat Api. Caution: this disables the autosave functionality 
     */
    private function disable_heartbeat(): void {
        add_action('admin_enqueue_scripts', function() {
            wp_deregister_script('heartbeat');    
        });
    }
    
    /**
     * Deregisters jQuery.
     */    
    private function disable_jquery(): void {
        add_action( 'wp_enqueue_scripts', function() {
            wp_deregister_script('jquery');
        }, 100 );
    }     

    /**
     * Deregisters jQuery Migrate by removing the dependency.
     */    
    private function disable_jquery_migrate(): void {

        add_filter( 'wp_default_scripts', function( $scripts ) {
            if( ! empty($scripts->registered['jquery']) ) {
                $scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
            }
        } );

    }
  
    /**
     * Disables RSD Links, used by pingbacks
     */
    private function disable_RSD(): void { 
        remove_action('wp_head', 'rsd_link'); 
    }     
    
    /**
     * Removes the WP Shortlink 
     */
    private function disable_shortlinks(): void { 
        remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );        
    } 
    
    /**
     * Disables the theme and plugin editor
     */
    private function disable_theme_editor(): void {
        if ( ! defined('DISALLOW_FILE_EDIT') ) {
            define( 'DISALLOW_FILE_EDIT', true );
        }        
    }
    
    /**
     * Removes the version hook on scripts and styles
     *
     * @uses MT_WP_Optimize::no_scripts_styles_version_hook
     */
    private function disable_version_numbers(): void {
        add_filter( 'style_loader_src', array($this, 'disable_version_numbers_hook'), 9999 );
        add_filter( 'script_loader_src', array($this, 'disable_version_numbers_hook'), 9999 ); 
    }
    
    /**
     * Removes version numbers from scripts and styles. 
     * The absence of version numbers increases the likelyhood of scripts and styles being cached.
     *
     * @param string @target_url The url of the script
     * @return string @target_url The modified target url
     */
    public function disable_version_numbers_hook( string $target_url = '' ): string {
        
        if( strpos( $target_url, 'ver=' ) ) {
            $target_url = remove_query_arg( 'ver', $target_url );
        }
        
        return $target_url;
        
    }
    
    /**
     * Removes WLW manifest bloat
     */
    private function disable_WLW_manifest(): void {
        remove_action('wp_head', 'wlwmanifest_link');   
    }   
       
    /**
     * Removes the WP Version as generated by WP
     */
    private function disable_WP_version(): void {
        remove_action( 'wp_head', 'wp_generator' ); 
        add_filter( 'the_generator', '__return_null' ); 
    }  
    
    /**
     * Disables XML RPC. Warning, makes some functions unavailable!
     */
    private function disable_XMLRPC(): void {
        
        if( is_admin() ) {
            update_option( 'default_ping_status', 'closed' ); // Might do something else here to reduce our queries  
        }
        
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
    private function jquery_to_footer(): void {
        add_action( 'wp_enqueue_scripts', function() {
            wp_deregister_script( 'jquery' );
            wp_register_script( 'jquery', includes_url( '/js/jquery/jquery.js' ), false, NULL, true );
            wp_enqueue_script( 'jquery' );           
        } );    
    }

    /**
     * Limits the comment reply JS to the places where it's needed
     */
    private function limit_comments_JS(): void {
        
        add_action('wp_print_scripts', function() {
            if(is_singular() && (get_option('thread_comments') == 1) && comments_open() && get_comments_number() ) {
                wp_enqueue_script('comment-reply');     
            } else {
                wp_dequeue_script('comment-reply');
            }           
        }, 100);

    }
    
    /**
     * Limits post revisions
     */
    private function limit_revisions(): void {

        if( defined('WP_POST_REVISIONS') && (WP_POST_REVISIONS != false) ) {
            add_filter( 'wp_revisions_to_keep', function( $num, $post) {
                return 5;
            }, 10, 2 );
        } 

    }

    /**
     * Removes the styling added to the header for recent comments
     */
    private function remove_comments_style(): void {    
        add_action( 'widgets_init', function() {
            global $wp_widget_factory;
            remove_action( 'wp_head', [$wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'] );
        });  
    }  

    /**
     * Slows heartbeat to 1 minute
     */
    private function slow_heartbeat(): void {
        
        add_filter( 'heartbeat_settings', function($settings) {
            $settings['interval'] = 60; 
            return $settings;
        } );

    }    
      
}