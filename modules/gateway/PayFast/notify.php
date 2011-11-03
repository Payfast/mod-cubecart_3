<?php
/**
 * notify.php
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

// {{{ CubeCart v3 Header
include( "../../../includes/ini.inc.php" );
include( "../../../includes/global.inc.php" );
require_once( "../../../classes/db.inc.php" );
$db = new db();
include_once( "../../../includes/functions.inc.php" );
$config = fetchDbConfig( "config" );
include_once( "../../../language/". $config['defaultLang'] ."/lang.inc.php" );
include( "../../../includes/currencyVars.inc.php" );
// }}}

// Fetch module configuration
$module = fetchDbConfig( "PayFast" );

// Include PayFast common file
define( 'PF_DEBUG', ( $module['debug_log'] ? true : false ) );
include_once( 'payfast_common.inc' );

// Variable Initialization
$pfError = false;
$pfNotes = array();
$pfData = array();
$pfHost = ( ( $module['server'] != 1 ) ? 'sandbox' : 'www' ) .'.payfast.co.za';
$pfParamString = '';
$pfDebugEmail = ( strlen( $module['debug_email'] ) > 0 ) ?
    $module['debug_email'] : $GLOBALS['config']['masterEmail'];

pflog( 'PayFast ITN call received' );

//// Notify PayFast that information has been received
if( !$pfError )
{
    header( 'HTTP/1.0 200 OK' );
    flush();
}

//// Get data sent by PayFast
if( !$pfError )
{
    pflog( 'Get posted data' );

    // Posted variables from ITN
    $pfData = pfGetData();

    pflog( 'PayFast Data: '. print_r( $pfData, true ) );

    if( $pfData === false )
    {
        $pfError = true;
        $pfNotes[] = PF_ERR_BAD_ACCESS;
    }
}

//// Verify security signature
if( !$pfError )
{
    pflog( 'Verify security signature' );

    // If signature different, log for debugging
    if( !pfValidSignature( $pfData, $pfParamString ) )
    {
        $pfError = true;
        $pfNotes[] = PF_ERR_INVALID_SIGNATURE;
    }
}

//// Verify source IP (If not in debug mode)
if( !$pfError && !PF_DEBUG )
{
    pflog( 'Verify source IP' );
    
    if( !pfValidIP( $_SERVER['REMOTE_ADDR'] ) )
    {
        $pfError = true;
        $pfNotes[] = PF_ERR_BAD_SOURCE_IP;
    }
}

//// Retrieve order from CubeCart
if( !$pfError )
{
    $orderId = $pfData['m_payment_id'];
    $order = $db->select(
        "SELECT `prod_total`, `comments`
        FROM `". $glob['dbprefix'] ."CubeCart_order_sum`
        WHERE `cart_order_id` = ". $db->mySQLsafe( $orderId ) );

    if( empty( $order ) )
    {
        $pfError = false;
        $pfNotes[] = PF_ERR_ORDER_INVALID;
    }
}

//// Verify data
if( !$pfError )
{
    pflog( 'Verify data' );

    $pfValid = pfValidData( $pfHost, $pfParamString );

    if( !$pfValid )
    {
        $pfError = true;
        $pfNotes[] = PF_ERR_BAD_ACCESS;
    }
}

//// Perform checks
if( !$pfError )
{
    // Check that order has not been previously processed
	$chkOrderId = $db->select(
        "SELECT `cart_order_id`
        FROM `". $glob['dbprefix'] ."CubeCart_order_sum`
        WHERE `sec_order_id` = ".$db->mySQLsafe( $pfData['pf_payment_id'] ) );

	if( !empty( $chkOrderId ) )
    {
		$pfError = true;
		$pfNotes[] = PF_ERR_ORDER_PROCESSED;
	}

    // Check the merchant ID matches
	if( $pfData['merchant_id'] !== trim( $module['merchant_id'] ) )
	{
		$pfError = true;
        $pfNotes[] = PF_ERR_MERCHANT_ID_MISMATCH;
    }

	// Check PayFast amount matches order amount
	if( !pfAmountsEqual( $pfData['amount_gross'], $order[0]['prod_total'] ) )
    {
		$pfError = true;
		$pfNotes[] = PF_ERR_AMOUNT_MISMATCH;
	}
}

//// Check status
if( !$pfError )
{
    pflog( 'Check status and update order' );

	// Check the payment_status is Completed
	if( $pfData['payment_status'] == 'COMPLETE' )
        $pfNotes[] = PF_MSG_OK;
    else
    {
		$pfError = true;

		switch( $pfData['payment_status'] )
        {
    		case 'FAILED':
                $pfNotes[] = PF_MSG_FAILED;
    			break;

			case 'PENDING':
                $pfNotes[] = PF_MSG_PENDING;
    			break;

			default:
                $pfNotes[] = PF_ERR_UNKNOWN;
    			break;
		}
	}
}

// Update order comments
// This is always done to provide feedback
$noteMsg = '';
foreach( $pfNotes as $note )
    $noteMsg .= '- '. $note ."\r\n";
$noteMsg = substr( $noteMsg, 0, -2 );

$date = strftime( $config['timeFormat'] );

$pfUpdate = array();
if( empty( $summary[0]['comments'] ) )
    $pfUpdate['comments'] =
        "PayFast (". $date ."): \r\n". $noteMsg;
else
	$pfUpdate['comments'] =
        $summary[0]['comments'] ."\r\n\r\nPayFast (". $date ."): \r\n". $noteMsg;

$pfUpdate['comments'] = $db->mySQLSafe( $pfUpdate['comments'] );

pflog( "New comments:\n". print_r( $pfUpdate, true ) );

$res = $db->update( $glob['dbprefix']."CubeCart_order_sum",
    $pfUpdate, "cart_order_id=". $db->mySQLSafe( $orderId ) );

// Process payment by updating order with PayFast ID and showing order
// success page. We only update with order ID here as the presence or
// absense of this ID is the only way we have of knowing whether or not
// the order has been processed SUCCESSFULLY.
if( !$pfError )
{
    pflog( 'Complete order' );

	$pfUpdate['sec_order_id'] = $db->mySQLSafe( $pfData['pf_payment_id'] );

	$res = $db->update( $glob['dbprefix'].'CubeCart_order_sum',
        $pfUpdate, 'cart_order_id='. $db->mySQLSafe( $orderId ) );

    // Call CubeCart order processing code
	$cart_order_id = $orderId;
	include( '../../../includes/orderSuccess.inc.php' );
}

// Close log
pflog( '', true );
?>