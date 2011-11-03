<?php
/**
 * transfer.inc.php
 *
 * Copyright (c) 2009-2011 PayFast (Pty) Ltd
 * 
 * LICENSE:
 * 
 * This payment module is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 3 of the License, or (at
 * your option) any later version.
 * 
 * This payment module is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
 * License for more details.
 * 
 * @author     Jonathan Smit
 * @copyright  Portions Copyright Devellion Limited 2005
 * @copyright  2009-2011 PayFast (Pty) Ltd
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://www.payfast.co.za/help/cube_cart
 */

// Fetch configuration information for
$module = fetchDbConfig( 'PayFast' );

// {{{ repeatVars()
/**
 * repeatVars
 *
 * The following variables are available for use:
 *
 *   $orderInv['productId']         - product id as an integer
 *   $orderInv['name']				- product name as a varchar
 *   $orderInv['price']				- price of each product (inc options)
 *   $orderInv['quantity']			- quantity of products as an integer
 *   $orderInv['product_options']	- products attributes as test
 *   $orderInv['productCode']		- product code as a varchar
 *   $i								- This is the current incremented integer starting at 0
 */
function repeatVars()
{
    return( false );
}

// {{{ fixedVars()
/**
 * fixedVars
 *
 * The following variables are available for use:
 *
 *  $cart_order_id					- cart order id as a varchar
 *  $ccUserData[0]['email']			- Customers email address
 *  $ccUserData[0]['title']			- Customers title (Mr Miss etc...)
 *  $ccUserData[0]['firstName']		- Customers first name
 *  $ccUserData[0]['lastName']		- Customers last name
 *  $ccUserData[0]['add_1']			- Invoice Address line 1
 *  $ccUserData[0]['add_2']			- Invoice Address line 1
 *  $ccUserData[0]['town']			- Invoice Town or city
 *  $ccUserData[0]['county']		- Invoice County or state
 *  $ccUserData[0]['postcode']		- Invoice Post/Zip Code
 *  $ccUserData[0]['country']		- Invoice country Id we can look up the
 *                                    country name like this:
 *  								  countryName($ccUserData[0]['country']);
 *  $ccUserData[0]['phone']			- Contact phone no
 *  $ccUserData[0]['mobile']		- Mobile/Cell phone number
 *
 *  $basket['delInf']['title']		- Delivery title (Mr Miss etc...)
 *  $basket['delInf']['firstName']	- Delivery customers first name
 *  $basket['delInf']['lastName']	- Delivery customers last name
 *  $basket['delInf']['add_1']		- Delivery Address line 1
 *  $basket['delInf']['add_2']		- Delivery Address line 1
 *  $basket['delInf']['town']		- Delivery Town or city
 *  $basket['delInf']['county']		- Delivery County or state
 *  $basket['delInf']['postcode']	- Delivery Post/Zip Code
 *  $basket['delInf']['country']	- Delivery country Id we can look up the
 *                                    country name like this:
 *                                    countryName($basket['delInf']['country']);
 *
 *  $basket['subTotal'] 			- Order Subtotal (exTax and Shipping)
 *  $basket['grandTotal']			- Basket total which has to be paid (inc Tax and Shipping).
 *  $basket['tax']					- Total tax to pay
 *  $basket['shipCost']				- Shipping price
 */
function fixedVars()
{
    // Variable initialization
	global $module, $basket, $ccUserData, $cart_order_id, $config;

    // Include PayFast common file
    define( 'PF_DEBUG', ( $module['debug_log'] ? true : false ) );
    include_once( 'payfast_common.inc' );

    // Create URLs    
	$notifyUrl = $GLOBALS['storeURL'] .'/modules/gateway/PayFast/notify.php';
	$returnUrl = $GLOBALS['storeURL'] .'/confirmed.php?act=conf&amp;oid='. base64_encode( $cart_order_id ) .'&amp;pg='. base64_encode( 'PayFast' );
	$cancelUrl = $GLOBALS['storeURL'] .'/confirmed.php?act=conf&amp;oid='. base64_encode( $cart_order_id ) .'&amp;c=1';

    // Create description
    $description = '';
    foreach( $basket['invArray'] as $item )
        $description .= $item['quantity'] .' x '. $item['name'] .' @ '.
            number_format( $item['price']/$item['quantity'], 2, '.', ',' ) .'ea = '.
            number_format( $item['price'], 2, '.', ',' ) .'; ';  
    $description .= 'Shipping = '. $basket['shipCost'] .'; ';
    $description .= 'Tax = '. $basket['tax'] .'; ';
    $description .= 'Total = '. $basket['grandTotal'];

    // Create other
    $amount = sprintf( "%.2f", $basket['grandTotal'] );

    // Set data for form posting
    $data = array(
        //// Merchant details
        'merchant_id' => $module['merchant_id'],
        'merchant_key' => $module['merchant_key'],
        'return_url' => $returnUrl,
        'cancel_url' => $cancelUrl,
        'notify_url' => $notifyUrl,

        //// Customer details
		'name_first' => substr( trim( $ccUserData[0]['firstName'] ), 0, 100 ),
		'name_last' => substr( trim( $ccUserData[0]['lastName'] ), 0, 100 ),
        'email_address' => substr( trim( $ccUserData[0]['email'] ), 0, 255 ),
		//'address1' => $ccUserData[0]['add_1'],
		//'address2' => $ccUserData[0]['add_2'],
		//'city' => $ccUserData[0]['town'],
		//'state' => $ccUserData[0]['county'],
		//'country' => $basket['delInf']['country'],
		//'zip' => $ccUserData[0]['postcode'],

        //// Item details
		'item_name' => $GLOBALS['config']['storeName'] .' Purchase, Order #'. $cart_order_id,
		'item_description' => substr( trim( $item_description ), 0, 255 ),
        'amount' => $amount,
		'm_payment_id' => $cart_order_id,
		'currency_code' => $config['defaultCurrency'],
        
        // Other details
        'user_agent' => PF_USER_AGENT,
    );

    // Create hidden form variables
    $hiddenVars = '';
    foreach( $data as $key => $val )
	   $hiddenVars .= '<input type="hidden" name="'. $key .'" value="'. $val .'">';

	return( $hiddenVars );
}
// }}}
// {{{ success()
/**
 * success
 *
 * This function is called by confirmed.inc.php to determine whether a
 * a payment was successful or not.
 *
 * For PayFast and ITN this should always be true as there might be a delay
 * in the payment process which will correct itself given some time at which
 * point an ITN will be triggered.
 */
function success()
{
    // Variable initialization
	global $db, $glob, $module, $basket, $lang;

    // Get order id
    $cart_order_id = preg_replace( '/[^a-zA-Z0-9_\-\+]/', '', base64_decode( $_GET['oid'] ) );

    // If order was cancelled
	if( isset( $_GET['c'] ) && ( $cart_order_id == $basket['cart_order_id'] ) )
    {
        // Return false so order failed page is displayed
        $retVal = false;
	}
	// If order was NOT cancelled
    else
    {
        // Return true so that success page is displayed
		$retVal = true;

        // Check if payment has been received for order
        $result = $db->select(
            "SELECT `status`
            FROM `".$glob['dbprefix']."CubeCart_order_sum`
            WHERE `cart_order_id` = ". $db->mySQLSafe( $cart_order_id ) );

        // If payment hasn't been confirmed yet, provide alternative message
        // If it has, then continue as normal
		if( $result[0]['status'] != 2 )
		{
            // Change the success message to be more accurate for PayFast
            $lang['front']['confirmed']['order_success'] =
                'Thank you. Your order was successful but payment receipt has'.
                ' not yet been confirmed.<br><br>You don\'t need to do'.
                ' anything further and will receive an email once payment'.
                ' has been finalised. You can also check the'.
                ' <a href="'. $GLOBALS['storeURL'] .'/cart.php?act=viewOrders">'.
                ' order status</a> on your account. Please visit again soon.';
        }
	}

    return( $retVal );
}

//// Select which gateway to use
// Sandbox (server = 0)
if( $module['server'] == 0 )
{
	$formAction = "https://sandbox.payfast.co.za/eng/process";
	$formMethod = "post";
	$formTarget = "_self";
}
// Live (server = 1)
else
{
	$formAction = "https://www.payfast.co.za/eng/process";
	$formMethod = "post";
	$formTarget = "_self";
}

// Transfer automatically, but don't update status with return
$transfer = 'auto';
$stateUpdate = false;
?>
