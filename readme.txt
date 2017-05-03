PayFast Cube Cart v3 Module v1.10 for Cube Cart v3.0.20
-------------------------------------------------------
Copyright (c) 2008 PayFast (Pty) Ltd
You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.

INTEGRATION:
1. Unzip the module to a temporary location on your computer
2. Copy and paste the files into your CubeCart installation as they were extracted
- This should NOT overwrite any existing files or folders and merely supplement them with the PayFast files
- This is however, dependent on the FTP program you use
3. Login to the CubeCart admin console
4. Navigate to Modules ? Gateways
5. Click on PayFast logo (or on ‘Configure’ next to PayFast logo)
6. Change values in form:
- Set ‘Status’ to Enabled
7. Click ‘Edit Config’ button to save configuration
8. Open the file ‘includes/content/confirmed.inc.php’ with a text editor
9. Change the statement on line 49 as below:
- Old:
if(ereg("Authorize|WorldPay|Protx|SECPay|BluePay|mals-e|Nochex_APC|PayOffline",$pg)){
- New:
if(ereg("Authorize|WorldPay|Protx|SECPay|BluePay|mals-e|Nochex_APC|PayOffline|PayFast",$pg)){
10. Save and close the file
After completing these instructions, the module is installed and ready to be tested in the sandbox environment (The pre-populated Merchant ID and Merchant Key values are the generic sandbox test credentials).

I”m ready to go live! What do I do?

Cube Cart v3

In order to make the module ‘LIVE’, follow the instructions below:

1. Login to the CubeCart admin console
2. Navigate to Modules ? Gateways
3. Click on PayFast logo (or on ‘Configure’ next to PayFast logo)
4. Change values in form:
5. Set ‘Merchant ID’ to your Merchant ID
6. Set ‘Merchant Key’ to your Merchant Key
7. Set ‘Server’ to Live
8. Click ‘Edit Config’ button to save configuration


******************************************************************************
*                                                                            *
*    Please see the URL below for all information concerning this module:    *
*                                                                            *
*                  https://www.payfast.co.za/shopping-carts/cubecart/        *
*                                                                            *
******************************************************************************