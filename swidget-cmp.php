<?php
/******************************************************************************
Plugin Name: SWidget for CMP
Plugin URI: https://github.com/CMP-Studio/swidget-cmp
GitHub Plugin URI: CMP-Studio/swidget-cmp
Description: Siriusware Widget for Carnegie Museusms of Pittsburgh
Version: 1.3.2
Author: Carnegie Museums of Pittsburgh
Author URI: http://www.carnegiemuseums.org
License: GPLv2 or later
******************************************************************************/
namespace SWCMP;
require_once dirname( __FILE__ ) . '/settingsPage.php';

//Session handling via DB
//see https://pippinsplugins.com/storing-session-data-in-wordpress-without-_session/
if (!class_exists("EDD_Session")) {
	include_once dirname( __FILE__ ) . '/edd/class-edd-session.php';
}
define('EDD_PLUGIN_DIR', '');
define('EDD_USE_PHP_SESSIONS', false);
$swcmpSessions = false;

const SWCMP_AS_ARRAY = false;
const SWCMP_AS_JSON = true;
const SWCMP_DEBUGMODE = false;


if (!esc_attr(get_option("sw_data_consent"), false))
{	
	return <<<EOT
	<script type='text/javascript'>
	  console.error("SWidget for Carnegie Museums plugin not loaded.");
      console.error("Please click the checkbox agreeing to send/receive data in the plugin's preferences.");
	</script>
EOT;
}

function swcmp_get_url($path)
{
  $domain = "sales.carnegiemuseums.org";
  return "https://" . $domain . $path;
}

//Initialize
if(!function_exists('swcmp_shortcodes_init')){
  function swcmp_session_init()
  {
    global $swcmpSessions;
    if (class_exists("EDD_Session"))
	{
	  $swcmpSessions = new \EDD_Session();
	  if ($swcmpSessions->should_start_session()) {
		$swcmpSessions->init();
	  }
	}
	else {		//fallback to our old PHP session solution
	  if ((function_exists('session_status') && session_status() !== PHP_SESSION_ACTIVE)
	  || !session_id()) {
		session_start();
	  }
	}
  }
  function swcmp_shortcodes_init()
  {
    swcmp_init_checkout();
    swcmp_init_cart();
  }
  function swcmp_scripts_init()
  {
    wp_enqueue_script('swidget-script', swcmp_get_url("/widget/ecommerce-widget.js"), array('jquery'), false, true);
	// load the javascript on the page; do an async load so it doesn't slow things down.
    add_filter('script_loader_tag', __NAMESPACE__ . '\swcmp_async_swidget', 10, 2);
    function swcmp_async_swidget($tag, $handle) {
      if ('swidget-script' !== $handle) {
        return $tag;
      }
      return str_replace(' src', ' id="swidget-script" async src', $tag);
    }
  }
  add_action('init', __NAMESPACE__ . '\swcmp_session_init', 1);
  add_action('init', __NAMESPACE__ . '\swcmp_shortcodes_init');
  add_action('wp_enqueue_scripts', __NAMESPACE__ . '\swcmp_scripts_init');
}

function swcmp_get_settings($atts=[], $asJSON=SWCMP_AS_JSON)
{
  $jsonSettings = array();
  //correlation between the settings in the settingsPage and those in the widget JS
  $swidgetMappings = array(
    "sw_date_format" => "dateFormat",
    "sw_low_qty" => "lowQty",
    "sw_display_product_name" => "displayName",
    "sw_open_tab" => "openInNewTab",
    "sw_display_checkout_link" => "displayCheckoutLink",
	"sw_show_only_one_time" => "showTimeIfOnlyOne",
    "sw_radio_cutoff" => "radioCutoff",
    "sw_msg_loading" => "messageLoading ",
    "sw_msg_expired" => "messageExpired",
    "sw_msg_low_qty" => "messageLowQty",
    "sw_msg_sold_out" => "messageSoldOut",
    "sw_msg_add_to_cart" => "messageAddToCart",
    "sw_msg_too_early" => "messageTooEarly",
    "sw_msg_offline_only" => "messageOffline",
    "sw_txt_free" => "txtFreeItem",
    "sw_txt_free_checkout" => "txtFreeCheckout",
    "sw_txt_fee" => "txtAdditionalFee",
    "sw_txt_checkout" => "txtCheckoutBtn",
    "sw_txt_add_to_cart" => "txtAddToCartBtn",
    "sw_txt_checkout_link" => "txtCheckoutLink",
    "sw_txt_cart" => "txtCartCheckoutBtn",
    "sw_txt_discount" => "txtDiscount",
    "sw_txt_member_discount" => "txtMemberDisc",
	"sw_txt_members_only" => "txtMembersOnly",	//TODO: Is sw_ setting right?
	"sw_txt_pay_what_you_wish" => "txtPayWhatYouWish",
    "sw_fill_dates" => "fillDates",
    "sw_txt_select_new_date" => "txtSelectNewDate",
    "sw_txt_select_new_time" => "txtSelectNewTime",
	"sw_txt_date_dropdown_placeholder" => "txtDateDropdownPlaceholder",
	"sw_txt_time_dropdown_placeholder" => "txtTimeDropdownPlaceholder",
  );

  foreach ($swidgetMappings as $key=>$value) {
    //override our options with any passed in via shortcode
    $keyOption = (array_key_exists($key, $atts) ? $atts[$key] : get_option($key, ""));
    if($keyOption !== "")
    {
	  if ($keyOption == "true" || $keyOption == "false") 
      {
		$jsonSettings[$value] = filter_var($keyOption, FILTER_VALIDATE_BOOLEAN, array("flags"=>FILTER_NULL_ON_FAILURE));
      }
      else if($key == "sw_low_qty")
      {
        $jsonSettings[$value] = intval($keyOption);
      }
      else
      {
        $jsonSettings[$value] = $keyOption;
      }
    }
  }

  return ($asJSON ? json_encode($jsonSettings) : $jsonSettings);
}

//The checkout widget
function swcmp_init_checkout()
{
  function swcmp_checkout($atts=[], $content=null, $tag='')
  {
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $co_atts = shortcode_atts([
      "site" => null,
      "item" => null
    ], $atts, $tag);
    //Start Output
    $site = intval($co_atts["site"]);
    $item = intval($co_atts["item"]);
    $settings = swcmp_get_settings($atts);
    $class = "swcmp_{$site}_{$item}";

    $out = <<<EOT
    <script>
    jQuery(document).ready(function() {
	  jQuery("#swidget-script").one('load swidgetLoaded', function() {
        jQuery(".$class").swQuickCheckout($site, $item, $settings);
      });
      if (jQuery.fn.swQuickCheckout) {
        jQuery("#swidget-script").trigger('swidgetLoaded');
      }
    });
    </script>
    <div class="swidget-holder $class"></div>
EOT;

    return $out;
  }
  
  function swcmp_checkouttimed($atts=[], $content=null, $tag='')
  {
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $co_atts = shortcode_atts([
      "site" => null,
      "group" => null
    ], $atts, $tag);
    //Start Output
    $site = intval($co_atts["site"]);
    $group = intval($co_atts["group"]);
    $settings = swcmp_get_settings($atts);
    $class = "swcmp_{$site}_{$group}";

    $out = <<<EOT
    <script>
    jQuery(document).ready(function() {
	  jQuery("#swidget-script").one('load swidgetLoaded', function() {
        jQuery(".$class").swTTQuickCheckout($site, $group, $settings);
      });
      if (jQuery.fn.swTTQuickCheckout) {
        jQuery("#swidget-script").trigger('swidgetLoaded');
      }
    });
    </script>
    <div class="swidget-holder $class"></div>
EOT;

    return $out;
  }

  add_shortcode('swcheckout', __NAMESPACE__ . '\swcmp_checkout');
  add_shortcode('swcheckouttimed', __NAMESPACE__ . '\swcmp_checkouttimed');
}


//Cart based functions
function swcmp_init_cart()
{
  function swcmp_cart($atts=[], $content=null, $tag='')
  {
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $co_atts = shortcode_atts([
      "site" => null
    ], $atts, $tag);
    $site = intval($co_atts["site"]);
    $settings = swcmp_get_settings($atts);

    $cart = swcmp_get_cart($site);
    if(!isset($cart) && SWCMP_DEBUGMODE) return "No cart found for site $site";

    $class = "swcmp_cart_$cart";

    $out = <<<EOT
    <script>
    jQuery(document).ready(function() {
	  jQuery("#swidget-script").one('load swidgetLoaded', function() {
        jQuery(".$class").swCart($cart, $settings);
      });
      if (jQuery.fn.swCart) {
        jQuery("#swidget-script").trigger('swidgetLoaded');
      }
    });
    </script>
    <div class="swidget-cart-holder $class" data-cart="$cart"></div>
EOT;

    return $out;
  }

  function swcmp_addtocart($atts=[], $content=null, $tag='')
  {
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $co_atts = shortcode_atts([
      "site" => null,
      "item" => null
    ], $atts, $tag);
    //Start Output
	$site = intval($co_atts["site"]);
    $item = intval($co_atts["item"]);
    $settings = swcmp_get_settings($atts);
    
	$cart = swcmp_get_cart($site);
    if(!isset($cart)) return "";

    $class = "swcmp_$site" . "_" . $item;

    $out = <<<EOT
    <script>
    jQuery( document ).ready(function(){
	  jQuery("#swidget-script").one('load swidgetLoaded', function() {
        jQuery(".$class").swAddToCart($cart, $site, $item, $settings);
      });
      if (jQuery.fn.swAddToCart) {
        jQuery("#swidget-script").trigger('swidgetLoaded');
      }
    });
    </script>
    <div class="swidget-holder $class" data-cart="$cart"></div>
EOT;

    return $out;
  }
  
  function swcmp_addtocarttimed($atts=[], $content=null, $tag='')
  {
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $co_atts = shortcode_atts([
      "site" => null,
      "group" => null
    ], $atts, $tag);
    //Start Output
	$site = intval($co_atts["site"]);
    $group = intval($co_atts["group"]);
    $settings = swcmp_get_settings($atts);
    
	$cart = swcmp_get_cart($site);
    if(!isset($cart) && SWCMP_DEBUGMODE) return "No cart found for site $site";

    $class = "swidget_$site" . "_" . $group;

    $out = <<<EOT
    <script>
    jQuery( document ).ready(function(){
	  jQuery("#swidget-script").one('load swidgetLoaded', function() {
        jQuery(".$class").swTTAddToCart($cart, $site, $group, $settings);
      });
      if (jQuery.fn.swTTAddToCart) {
        jQuery("#swidget-script").trigger('swidgetLoaded');
      }
    });
    </script>
    <div class="swidget-holder $class" data-cart="$cart"></div>
EOT;

    return $out;
  }

  add_shortcode('swcart', __NAMESPACE__ . '\swcmp_cart');
  add_shortcode('swaddtocart', __NAMESPACE__ . '\swcmp_addtocart');
  add_shortcode('swaddtocarttimed', __NAMESPACE__ . '\swcmp_addtocarttimed');
}


/* Helper functions */
function swcmp_get_cart($site)
{
  global $swcmpSessions;
  $name = "swidget_cart_$site";
  $cartPresence = (is_a($swcmpSessions, "EDD_Session") ? $swcmpSessions->get($name) : isset($_SESSION[$name]));
  if (!$cartPresence)
  {
    //No cart: must create one
    $url = swcmp_get_url("/api/v1/cart/create?site=$site");
    $result = swcmp_curl_call($url);
    $json = json_decode($result);

    if($json) 
	{
	  if ($json->success) {
		return swcmp_save_cart($name, $json->cart);
	  }
	  elseif (SWCMP_DEBUGMODE) {
		echo "<!-- No can do with " . $json->url. ": " . $json->error . "-->\n";
	  }
	} elseif (SWCMP_DEBUGMODE) {
	  echo "<!-- no JSON returned! -->\n";
	}
  }
  else
  {
	$cart = (is_a($swcmpSessions, "EDD_Session") ? $swcmpSessions->get($name) : swcmp_get_php_session($name));
    $url = swcmp_get_url("/api/v1/cart/check?site=$site&cart=$cart&recreate=true");
    $result = swcmp_curl_call($url);
    $json = json_decode($result);

    if($json->success)
    {
      if($json->valid)
      {
        //Cart is still valid
        return $cart;
      }
      else if(isset($json->cart))
	  {
        return swcmp_save_cart($name, $json->cart);
      }
    }
  }

  return null;
}

function swcmp_save_cart($name, $cart)
{
  global $swcmpSessions;
  $updateName = $name . "_update";

  if (is_a($swcmpSessions, "EDD_Session")) 
  {
	if($swcmpSessions->get($name) && $swcmpSessions->get($updateName))
	{
	  $lastUpdate = intval($swcmpSessions->get($updateName));
	  if(abs($lastUpdate - time()) <= 100) return $swcmpSessions->get($name);
	}
	$swcmpSessions->set($updateName, time());
	$swcmpSessions->set($name, $cart);
  }
  else {
    if(isset($_SESSION[$name]) && isset($_SESSION[$updateName]))
    {
      $lastUpdate = intval($_SESSION[$updateName]);
      if(abs($lastUpdate - time()) <= 100) return swcmp_get_php_session($name);
    }
    $_SESSION[$updateName] = time();
    $_SESSION[$name] = $cart;
  }

  if (SWCMP_DEBUGMODE) {
	echo "<!-- we just set $name to $cart and $updateName to time() -->\n";
  }
  return $cart;
}

function swcmp_curl_call($url)
{
  $result = wp_remote_get($url, [
	'headers' => array("User-Agent:" => "Wordpress Swidget")
  ]);
  if (!$result || is_a($result, "WP_Error") || !array_key_exists("body", $result)) {
	echo "<!-- What happened? " .print_r($result, true). " -->\n";
  }

  return $result["body"];
}

function swcmp_get_php_session($name) 
{
	//we expect our session name to be the same as we set it, and its value to be a cart ID
	if (!preg_match('/^swidget_cart_[1-99](_update){0,1}$/', $name) || !is_int($_SESSION[$name])) { 
		if (SWCMP_DEBUGMODE) { echo "<!-- invalid session! -->\n"; }
		return null; 
	}
	return $_SESSION[$name];
}
