# swidget-cmp
Siriusware Widget for the Carnegie Museums of Pittsburgh

Allows the site of the Carnegie Museums of Pittsburgh to display Siriusware widgets using shortcodes.  See below for descriptions of these shortcodes.
### Shortcodes

#### Add to Cart

`[swaddtocart site="siteID" item="itemID"]`   
_To use add to cart, the cart widget AND the item widget must be the on the same page._

#### Quick Checkout
``[swcheckout site="siteID" item="itemID"]``

#### Add to Cart Timed
`[swaddtocarttimed site="siteID" group="groupCode"]`   
_To use add to cart, the cart widget AND the item widget must be the on the same page._

#### Quick Checkout Timed
`[swcheckouttimed site="siteID" group="groupCode"]`

#### Cart
`[swcart site="siteID"]`   
_The cart widget should be on every page that a user could navigate to from an item widget. In most cases, it should be in the header for the site, so it's always available._


### Options Reference

All options can be managed on the WordPress admin page for the swidget plugin.


#### General

* **Date Format** - How dates are displayed in placeholders.  Uses the moment.js library.  [Info on formats found here](http://momentjs.com/docs/#/displaying/format/)
* **Low Qty** - The point when the *low quantity* message shows
* **Radio Button Cutoff for Timed Items** - the maximum number before the input changes to dropdown
* **Display Product Name** - Show the name of the product (defaults to Yes)
* **Open checkout in new tab** - Open the checkout page in a new tab/window
* **Dispaly checkout link after adding items**
* **Fill all dates for timed items**
* **Show time selector if only one option**

#### Messages

* **Loading** - The message that displays while the tickets are loading
* **Not yet on sale** - Message displayed if the product is not yet available to purchase
* **Offline sales only** - Message displayed when the item is available to be sold in Siriusware but *not* with e-commerce
* **Expired** - The message that is displayed when the item is no longer on sale
* **Low Quantity** - The message that displays when there is low quantity
* **Sold Out** - The message that displays when the item is sold out
* **Add To Cart** - A message for when an item is added to cart (Note: only for swaddtocart widgets)

#### Text Modification

* **Free** - The text to display when an item is free (Replaces $0.00).
* **Additional Fee**- The text for additional fees
* **Checkout Button**- The text for the checkout button. *Note:* There are separate entries for the quick widget and the cart widget
* **Checkout Button (Free item)**- This replaces the *quick* checkout text if all items in the widget are free
* **Add to Cart Button** - Text for the cart widget's checkout button
* **Checkout Link** - The text to display as the checkout link
* **Discount** - The text for discounts
* **Member Discount** - The text to show how much one would pay if they are a member
* **Member Exclusive** - The text to indicate that an item is for members only
* **Select New Date** - The label text for the date selector (timed ticketing)
* **Select New Time** - The label text for the time selector (timed ticketing)
* **Date Dropdown Placeholder** - The default text for the date selector (timed ticketing)
* **Time Dropdown Placeholder** - The default text for the time selector (timed ticketing)
* **Pay What you Wish Placeholder** - The default text for the pay what you wish message


### Placeholders

Place holders are a special string which will be replace with information from the item.  They can be used in any of the above options.

* `#{stock}` - How many tickets are remaining
* `#{name}` - The name of the ticket
* `#{start_sale}` - When the tickets go on sale
* `#{end_sale}` - When the tickets go off sale (both online and offline)

### Options Hierarchy

The following is the priority of where the widget gets it's settings from (lower numbers trump higher numbers)

1. Options set in WP admin
1. Default settings from the widget itself.

### Message Hierarchy

The following is the priority of the messages

1. Past sale end
1. Sold Out
1. Offline sales only
1. Prior to sale start
1. [No message, ticket can be sold]


### Frequently Asked Questions

= Who is this plugin designed for? =

The webmasters of the four Carnegie Museums of Pittsburgh can can use this plugin on their WordPress sites to sell tickets for their various offerings.

= What does this plugin actually do? =

It adds shortcodes to WordPress that will render the specified ecommerce item/group/cart on the page (via empty divs and jQuery commands).

= Does it use any external libraries? =

Other than WordPress' installation of jQuery and our own self-hosted JavaScript file, the plugin includes and makes use of [Moment.js](https://momentjs.com/) for date handling and  [Pippin Williamson](https://github.com/pippinsplugins)'s [EDD_Session class](https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/master/includes/class-edd-session.php) for session handling.

= Can I use this plugin for other purposes? =

Sure, if it will help you out.


### Changelog

= 1.3.2 =

* Further changes to generic names

= 1.3.1 =

* Added namespace, session sanitation/validation and replace CURL with HTTP API

= 1.3.0 =

* First version designed for the WordPress Plugins directory

= 1.2.1 =

* Better error handling (and falls back to PHP sessions if EDD class not present)

= 1.2.0 =

* Adding Pay What You Wish setting


== Upgrade Notice ==

= 1.3.0 =
First release designed to be listed on WordPress Plugins
