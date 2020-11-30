<?php
namespace SWCMP;

function swcmp_get_base_settings() 
{
  return array(
    //the 2nd value in the value array will be the input type ("yn" will be converted into yes/no radio buttons)
    "sw_date_format" => array("Date Format (see <a href='https://momentjs.com/docs/#/parsing/string-format/' target='_new'>this guide</a> for possible tokens to use)", "text"),
    "sw_low_qty"  => array("Low Qty", "number"),
	"sw_radio_cutoff" => array("Radio Button Cutoff for Timed Items (max before change to dropdown)", "number"),
    "sw_display_product_name"  => array("Display Product Name", "yn"),
    "sw_open_tab" => array("Open checkout in new tab?", "yn"),
    "sw_display_checkout_link" => array("Dispaly checkout link after adding items:", "yn"),
    "sw_fill_dates" => array("Fill all dates for timed items?", "yn"),
    //"sw_show_only_one_time" => array("Show time selector if only one option?", "yn")
  );
}

function swcmp_get_message_settings()
{
  return array(
    "sw_msg_loading" => "Loading",
    "sw_msg_too_early" => "Not yet on sale",
    "sw_msg_offline_only" => "Offline Sales Only",
    "sw_msg_expired" => "Expired",
    "sw_msg_low_qty" => "Low Quantity",
    "sw_msg_sold_out" => "Sold Out",
    "sw_msg_add_to_cart" => "Add to Cart"
  );
}

function swcmp_get_text_settings()
{
  return array (
    "sw_txt_free" => "Free Item",
    "sw_txt_fee" => "Additional Fee",
    "sw_txt_checkout" => "Checkout Button (Quick)",
    "sw_txt_cart" => "Checkout Button (Cart)",
    "sw_txt_free_checkout" => "Checkout Button (Free item)",
    "sw_txt_add_to_cart" => "Add To Cart Button",
	"sw_txt_checkout_link"	=> "Checkout Link",
    "sw_txt_discount" => "Discount",
    "sw_txt_member_discount" => "Member Discount",
	"sw_txt_members_only" => "Member Exclusive",
	"sw_txt_pay_what_you_wish" => "Pay what you wish (#{price} can be used as a placeholder)",
    "sw_txt_select_new_date" => "Select a different date",
    "sw_txt_select_new_time" => "Select a different time",
	"sw_txt_date_dropdown_placeholder" => "Select a date",
	"sw_txt_time_dropdown_placeholder" => "Select a time",
  );
}

add_action('admin_menu', function() {
  add_options_page('SWidget Wordpress Config', 'SWidget for CMP', 'manage_options', 'swidget', __NAMESPACE__ . '\swcmp_generate_config_page');
});

add_action('admin_init', function() {
  register_setting('swidget-cmp', 'sw_data_consent');
  //Base Options
  $baseSettings = swcmp_get_base_settings();
  foreach ($baseSettings as $key => $value) {
    register_setting('swidget-cmp', $key);
  }
  //Messages
  $msgSettings = swcmp_get_message_settings();
  foreach ($msgSettings as $key => $value) {
    register_setting('swidget-cmp', $key);
  }
  //Text
  $txtSettings = swcmp_get_text_settings();
  foreach ($txtSettings as $key => $value) {
    register_setting('swidget-cmp', $key);
  }
});

function swcmp_generate_config_page()
{
  $baseSettings = swcmp_get_base_settings();
  $msgSettings = swcmp_get_message_settings();
  $txtSettings = swcmp_get_text_settings();
  ?>
  <script type="text/javascript">
  jQuery(document).ready(function() {
    function checkDataConsent() {
	  if(jQuery("input#sw_data_consent").is(":checked")) {
		jQuery("table#swidget-settings input").not("#sw_data_consent").attr("disabled", false);
	  }
	  else {
	    jQuery("table#swidget-settings input").not("#sw_data_consent").attr("disabled", true);
	  }
	  console.log(jQuery("input#sw_data_consent").is(":checked"));
	}

	checkDataConsent();
	jQuery("input#sw_data_consent").click(checkDataConsent);
  });
  </script>
  <style type="text/css">
    .wrap table code {
	  font-size: 0.8em;
	  margin-left: 1em;
    }
  </style>

  <div class="wrap">
    <h1>Siriusware Widget for CMP Settings</h1>
    <p> See the <a href="https://github.com/CMP-Studio/swidget-cmp/blob/main/readme.md" target="_blank">readme</a> for additional information</p>
   <form action="options.php" method="post">
     <?php
     settings_fields('swidget-cmp');
     do_settings_sections('swidget-cmp');
     ?>
     <table id="swidget-settings">
	   <tr>
         <td>
           <label for="sw_data_consent">I hereby consent to data being sent to the sales.carnegiemuseums.org server.<br/><em>(Plugin will not work if unchecked)</em></label>
         </td>
         <td>
		   <label><input id="sw_data_consent" name="sw_data_consent" type="checkbox" value="true" <?php echo checked(get_option('sw_data_consent'), 'true'); ?> /></label>
         </td>
       </tr>

	   <tr>
         <td colspan="2"><h2>General</h2></td>
       </tr>
	   <?php
       foreach ($baseSettings as $key => $value_array) { ?>
         <tr>
           <td>
             <label for="<?php echo $key; ?>"><?php echo $value_array[0]; ?></label><br/><code><?php echo $key; ?></code>
           </td>
           <td>
		     <label>
		     <?php
			   $type = $value_array[1];
			   //convert "yn" into yes/no radio buttons, or otherwise just give this field the type specified
		       switch($type) {
			     case "yn":
			       $yesChecked = esc_attr(get_option($key)) == 'true' ? 'checked="checked"' : '';
			       $noChecked = esc_attr(get_option($key)) == 'false' ? 'checked="checked"' : '';
			       echo <<<END_HTML
               <input id="$key" name="$key" type="radio" value="true" $yesChecked /> Yes
             </label><br/>
             <label>
               <input id=$key" name="$key" type="radio" value="false" $noChecked  /> No
END_HTML;
			       break;
			     default:
				   echo "<input id=\"$key\" name=\"$key\" type=\"$type\" value=\"" . esc_attr(get_option($key)) . "\" /n";
			       break;
			   }
		     ?>
             </label>
           </td>
         </tr>
       <?php } ?>

       <tr>
         <td colspan="2"><h2>Messages</h2></td>
       </tr>
       <?php
       foreach ($msgSettings as $key => $value) {  ?>
         <tr>
           <td>
             <label for="<?php echo $key; ?>"><?php echo $value; ?></label><br/><code><?php echo $key; ?></code>
           </td>
           <td>
             <input id="<?php echo $key; ?>" name="<?php echo $key; ?>" type="text" value="<?php echo esc_attr(get_option($key)); ?>" />
           </td>
         </tr>
         <?php } ?>

         <tr>
           <td colspan="2"><h2>Text Modification</h2></td>
         </tr>
         <?php
         foreach ($txtSettings as $key => $value) {  ?>
           <tr>
             <td>
               <label for="<?php echo $key; ?>"><?php echo $value; ?></label><br/><code><?php echo $key; ?></code>
             </td>
             <td>
               <input id="<?php echo $key; ?>" name="<?php echo $key; ?>" type="text" value="<?php echo esc_attr(get_option($key)); ?>" />
             </td>
           </tr>
           <?php } ?>

     </table>
     <button name="save">Save</button>
   </form>
 </div>
  <?php
}
