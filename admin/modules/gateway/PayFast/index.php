<?php
/**
 * index.php
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

// {{{ Standard CubeCart
include( "../../../../includes/ini.inc.php" );
include( "../../../../includes/global.inc.php" );
require_once( "../../../../classes/db.inc.php" );
$db = new db();
include_once( "../../../../includes/functions.inc.php" );
$config = fetchDbConfig( "config" );

include_once( "../../../../language/". $config['defaultLang']."/lang.inc.php" );
$enableSSl = 1;
include_once( "../../../../includes/sslSwitch.inc.php" );
include( "../../../includes/auth.inc.php" );
include( "../../../includes/header.inc.php" );

if( permission( "settings", "read" ) == FALSE )
{
	header( "Location: ". $GLOBALS['rootRel'] ."admin/401.php" );
	exit;
}

if( isset( $_POST['module'] ) )
{
	include( "../../status.php" );
	include( "../../../includes/functions.inc.php" );
	$module = fetchDbConfig( $_GET['folder'] );
	$msg = writeDbConf( $_POST['module'], $_GET['folder'], $module );
}

$module = fetchDbConfig( $_GET['folder'] );
// }}}

// {{{ PayFast
$formActionUrl = $GLOBALS['rootRel'] .'admin/modules/'. $_GET['module'] .'/'. $_GET['folder'] .
    '/index.php?module='. $_GET['module'] .'&amp;folder='. $_GET['folder'];

$default = array(
    'desc' => 'PayFast',
    'merchant_id' => '',
    'merchant_key' => '',
    'server' => 0,
    'default' => 1,
    'status' => 0,
    'debug_log' => 0,
    'debug_email' => '',
    );

$data = array();
foreach( $default as $key => $val )
    $data[$key] = !isset( $module[$key] ) ? $default[$key] : $module[$key];
// }}}
?>

<!-- Initial Paragraph -->
<p>
<a href="http://www.payfast.co.za/" target="_blank">
<img src="logo.gif" width="114" height="31" border="0" alt="PayFast" title="PayFast" /></a>
</p>

<p>
Please <a href="http://www.payfast.co.za/user/register" target="_blank">register</a> on PayFast to use this module.
</p>

<p>
Your <em>Merchant ID</em> and <em>Merchant Key</em> are available on your <a href="http://www.payfast.co.za/acc/integration" target="_blank">Integration page</a> on the PayFast website.
</p>

<!-- Echo message -->
<?php if( isset( $msg ) ){ echo stripslashes( $msg ); } ?>

<!-- Form for variables -->
<form action="<?php echo $formActionUrl; ?>" method="post" enctype="multipart/form-data">
<table border="0" cellspacing="0" cellpadding="3" class="mainTable">
<tr>
    <td colspan="2" class="tdTitle">Configuration Settings </td>
</tr>
<tr>
    <td align="left" class="tdText"><strong>Status:</strong></td>
    <td class="tdText">
	<select name="module[status]">
		<option value="1" <?php if( $data['status'] == 1 ) echo "selected='selected'"; ?>>Enabled</option>
		<option value="0" <?php if( $data['status'] == 0 ) echo "selected='selected'"; ?>>Disabled</option>
    </select>	</td>
</tr>
<tr>
    <td align="left" class="tdText"><strong>Default:</strong></td>
    <td class="tdText">
    	<select name="module[default]">
    		<option value="1" <?php if( $data['default'] == 1 ) echo "selected='selected'"; ?>>Yes</option>
    		<option value="0" <?php if( $data['default'] == 0 ) echo "selected='selected'"; ?>>No</option>
    	</select>
    </td>
</tr>
<tr>
  	<td align="left" class="tdText"><strong>Description:</strong>	</td>
    <td class="tdText"><input type="text" name="module[desc]" value="<?php echo $data['desc']; ?>" class="textbox" size="30" /></td>
</tr>
<tr>
    <td><br /></td>
</tr>
<tr>
    <td align="left" class="tdText"><strong>Merchant ID:</strong></td>
    <td class="tdText"><input type="text" name="module[merchant_id]" value="<?php echo $data['merchant_id']; ?>" class="textbox" size="30" /></td>
</tr>
<tr>
    <td align="left" class="tdText"><strong>Merchant Key:</strong></td>
    <td class="tdText">
        <input type="text" name="module[merchant_key]" value="<?php echo $data['merchant_key']; ?>" class="textbox" size="30" />
    </td>
</tr>
<tr>
    <td align="left" class="tdText">
        <strong>Server:</strong>
    </td>
    <td class="tdText">
        <select name="module[server]">
            <option value="0" <?php if( $data['server'] == 0 ) echo "selected='selected'"; ?>>Test</option>
            <option value="1" <?php if( $data['server'] == 1 ) echo "selected='selected'"; ?>>Live</option>
        </select>
    </td>
</tr>
<tr>
    <td><br></td>
</tr>
<tr>
    <td align="left" class="tdText">
        <strong>Debug Log:</strong>
    </td>
    <td class="tdText">
        <select name="module[debug_log]">
            <option value="0" <?php if( $data['debug_log'] == 0 ) echo "selected='selected'"; ?>>Off</option>
            <option value="1" <?php if( $data['debug_log'] == 1 ) echo "selected='selected'"; ?>>On</option>
        </select>
    </td>
</tr>
<tr>
    <td align="left" class="tdText">
        <strong>Debug Email:</strong>
    </td>
    <td class="tdText">
        <input type="text" name="module[debug_email]" value="<?php echo $data['debug_email']; ?>" class="textbox" size="30" />
    </td>
</tr>
<tr>
    <td align="right" class="tdText">&nbsp;</td>
    <td class="tdText"><input type="submit" class="submit" value="Edit Config" /></td>
</tr>
</table>
</form>

<br />

<!-- Notes -->
<table cellpadding="0" cellspacing="1" align="left">
<tr>
    <td valign="top" nowrap="nowrap"><strong>** NB</strong></td>
    <td valign="top">
        Sandbox testing will only work from an Internet accessible server as PayFast needs to communicate directly with your site<br />
        (This will <b>NOT</b> work on a "local" testing site!)
    </td>
</tr>
</table>

<?php include("../../../includes/footer.inc.php"); ?>