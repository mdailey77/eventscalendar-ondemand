<?php
/**
 * Plugin Name: Events Calendar - On-Demand Events
 * Plugin URI: http://www.mattdailey.net
 * Description: This extension plugin adds an on-demand checkbox and URL field to the metadata section in
 * the Add/Edit Event screen. Removes date/time field and changes status to on-demand for past events only.
 * Version: 1.0.0
 * Author: Matthew Dailey
 * Author URI: http://www.mattdailey.net
 * License: GPL2
 */

 // Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/*define( 'TRIBE_EVENTS_ONDEMAND_DIR', dirname( __FILE__ ) );
define( 'TRIBE_EVENTS_ONDEMAND_FILE', __FILE__ );*/
include_once( ABSPATH . 'wp-content/plugins/the-events-calendar/common/src/Tribe/Main.php' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );


global $Tribe_Events_OnDemand;
$Tribe_Events_OnDemand = new Tribe_Events_OnDemand;

class Tribe_Events_OnDemand {

    //Declare variables
    public $OnDemandURL = null;
    public $OnDemandCheckbox = null;

    // check if the Events Calendar plugin is activated
    function tribe_events_ondemand_activate(){
        // Require parent plugin
        if ( ! is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) and current_user_can( 'activate_plugins' ) ) {
            // Stop activation redirect and show error
            add_action('admin_notices', 'eventscalendar_not_activated');
        }
    }
    // shows error message in plugin admin screen
    function eventscalendar_not_activated() {
        printf(
        '<div class="error"><p>%s</p></div>',
        __('Events Calendar has to be activated before Events Calendar On-Demand can be activated.')
        );
    }
    public function OnDemandMetaBox($post) {
        $postId  = $post->ID;
        global $OnDemandCheckbox;
        global $OnDemandURL;

        if ( $post->post_type == 'tribe_events' ) {

            if ( $postId ) {

                $OnDemandURL = get_post_meta($postId, '_tribe_ondemandurl', true);
                $OnDemandCheckbox = get_post_meta($postId, '_tribe_ondemand', true);

                if ($OnDemandURL!='' && $OnDemandCheckbox=='yes' ) {
                    wp_enqueue_script('removedatetimefields', plugin_dir_url( __FILE__ ) .'removedatetimefields.js' );
                }

            }
        }
        ?>
        <div id='ondemandDetails' class="inside eventForm">
            <table cellspacing="0" cellpadding="0" id="ondemandInfo" class="OnDemandEventInfo">
                <tr class="ondemand">
                    <td><label for="ondemand-checkbox">On-Demand</td>
                    <td>
                        <input
                            tabindex="<?php tribe_events_tab_index(); ?>"
                            value="yes"
                            type="checkbox"
                            id="OnDemand"
                            name="OnDemandVal"
                            <?php if ( $OnDemandCheckbox == 'yes' ) echo 'checked="checked"'; ?>
                        />
                    </td>
                    <td>
                    </td>
                </tr>
                <tr class="ondemand">
                    <td><label for="ondemand-url">UberFlip URL</td>
                    <td>
                        <input
                            tabindex="<?php tribe_events_tab_index(); ?>"
                            type="text"
                            id="OnDemandUrl"
                            name="OnDemandUrlVal"
                            value='<?php if ( $OnDemandURL !='' ) echo $OnDemandURL; ?>'
                        />
                    </td>
                    <td>
                    <p id="ondemand-url-error" class="error">Please enter full URL including http://</p>
                    </td>
                </tr>
            </table>
        </div>
        <script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#OnDemand').change(function(){ 					// conditionally apply validation
					if(jQuery(this).is(':checked')) {					// after state of the checkbox is tested
						console.log( 'On-Demand: YES' );
						jQuery('#EventInfo tr:nth-child(2)').fadeOut();
						jQuery('.ondemandNotice').fadeIn();
						jQuery('#post').submit(function(e) {
	                        if (jQuery("#OnDemandUrl").val() != '') {
	                            return true;
	                        }else{
	                            alert("Please enter a UberFlip URL.");
	                            return false;
	                        }
	                    });
					} else {
						console.log( 'On-Demand: NO' );
						jQuery('#EventInfo tr:nth-child(2)').fadeIn();
						jQuery('.ondemandNotice').fadeOut();
						return true;
					}
				});
			});
        </script>
    <?php
    }
    // loads OnDemand Checkbox and URL
    public function addOnDemandBox($post) {
        add_meta_box(
        'tribe_events_ondemand',
        __('On-Demand Webinars'),
        array( $this, 'OnDemandMetaBox' ),
        'tribe_events',
        'normal',
        'high'
        );
    }
    public function addOnDemandMeta($event = null){
        global $event_id;
        global $OnDemandCheckbox;
        global $OnDemandURL;
        $OnDemandCheckbox = $_POST['OnDemandVal'];
        $OnDemandURL = $_POST['OnDemandUrlVal'];
        $event_id = Tribe__Main::post_id_helper( $event );

		if ( ! $event_id ) {
			return false;
		}
        // will add fields if they do not exist
        update_post_meta( $event_id, '_tribe_ondemand', $OnDemandCheckbox );
        update_post_meta( $event_id, '_tribe_ondemandurl', $OnDemandURL);
    }
   /* public function removeDateTime($OnDemandCheckbox, $OnDemandURL) {
        global $OnDemandCheckbox;
        global $OnDemandURL;
        if ($OnDemandURL!='' && $OnDemandCheckbox==1 ) {
            wp_enqueue_script('removedatetimefields', plugin_dir_url( __FILE__ ) .'removedatetimefields.js' );
        }
        return;
    } */

    function __construct() {
        add_action( 'admin_menu', array( $this, 'addOnDemandBox' ), 10, 1 );
        //add_action('admin_enqueue_scripts', array($this, 'removeDateTime'), 11, 2 );
        add_action( 'save_post', array( $this, 'addOnDemandMeta' ), 15, 2 );
    }
}; // End of Tribe_Events_OnDemand class

register_activation_hook( __FILE__, array( 'Tribe_Events_OnDemand', 'tribe_events_ondemand_activate' ) );
//register_deactivation_hook( __FILE__, array( 'Tribe_Events_OnDemand', 'deactivate' ) );
