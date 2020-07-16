<?php
requireOnceClass("Administration");

class Configuration extends Administration {

	function exposeServices() {
		$prefix = 'Configuration';
		return array_merge(
		 	array("${prefix}.Edit"		=>	array('method'	=>	'edit')),
			Administration::exposeServicesUsing($prefix)
		);		
	}
	
	function edit() {
		$this->display->layout = "Administration";
		$this->display->title = $this->ATTRIBUTES['BreadCrumbs'] + " : View/Edit Configuration";
		parent::edit();
	}
 	
	function inputFilter() {
		parent::inputFilter();
		$this->param('BreadCrumbs','Configuration');
		//$this->display->title = 'Confituration Administration';
		//$this->display->layout = 'Administration';
	}
	

	function __construct() {
		parent::__construct(array(
			'prefix'					=>	'Configuration',
			'singular'					=>	'Configuration',
			'plural'					=>	'Configurations',
			'tableName'					=>	'configuration',
			'tablePrimaryKey'			=>	'cfg_id',
			'tableDisplayFields'		=>	array('cfg_id'),
		));
		
		$this->addField(new TextField(array(
			'name'			=>	'cfg_website_name',
			'displayName'	=>	'Web Site Name',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'40',	'maxLength'	=>	'127',					
		)));

		$this->addField(new TextField(array(
			'name'			=>	'cfg_plaintext_server',
			'displayName'	=>	'Insecure Address',
			'note'			=>	'e.g. http://www.mywebsite.co.nz/ <br>** MUST have ending / !!!!! **',
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'40',	'maxLength'	=>	'255',
		)));

		$this->addField(new TextField(array(
			'name'			=>	'cfg_secure_server',
			'displayName'	=>	'Secure Address',
			'note'			=>	'e.g. https://www.securesitenz.co.nz/mywebsiteconz/ <br>** MUST have ending / !!!!! **',
			'required'		=>	false,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'40',	'maxLength'	=>	'255',
		)));
		
		$this->addField(new EmailField(array(
			'name'			=>	'cfg_email_address',
			'displayName'	=>	'Email Address',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'40',	'maxLength'	=>	'255',			
		)));
		
		$this->addField(new EmailField(array(
			'name'			=>	'cfg_bcc_address',
			'displayName'	=>	'BCC Email Address',
			'note'			=>	'Emails will be silently copied to this address.',
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
			'size'	=>	'40',	'maxLength'	=>	'255',					
		)));		

		$this->addField(new MemoField(array(
			'name'			=>	'cfg_keywords',
			'displayName'	=>	'Default Keywords',
			'note'			=>	'Keywords are displayed in a hidden area of all web pages. They are used by search engines.',
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,			
			'rows'	=>	'5',	'cols'		=>	'45',
			
		)));
	
		$this->addField(new MemoField(array(
			'name'			=>	'cfg_description',
			'displayName'	=>	'Default Description',
			'note'			=>	'The description is displayed in a hidden area of all web pages. It is used by search engines.',
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,			
			'rows'	=>	'5',	'cols'		=>	'45',
			
		)));
		
		
		$this->addField(new HTMLMemoField2 (array(
			'name'			=>	'cfg_contact_details',
			'displayName'	=>	'Contact Details',
			'note'			=>	null,
			'required'		=>	false,
			'verify'		=>	false,
			'unique'		=>	false,
			'default'		=>	null,
			'size'	=>	'50',	'maxLength'	=>	'255',
			'rows'	=>	'6',	'cols'		=>	'40',
			'width'	=>	'document.body.clientWidth*0.61',
			'height'	=>	200,
		)));
		
		if (ss_HasPermission('IsDeployer')) {
			$this->addField(new CheckBoxField(array(
				'name'			=>	'cf_is_service',
				'displayName'	=>	'Is Service Level',
				'note'			=>	null,
				'required'		=>	FALSE,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,			
				'rows'	=>	'5',	'cols'		=>	'45',
			
			)));
			
			$this->addField(new MemoField(array(
				'name'			=>	'cfg_options',
				'displayName'	=>	'Options',
				'note'			=>	"One option per line, Option=Value, default value = 1.<BR>Possible Options :
									<LI>Disk Space Allowance</LI>
									<LI>Advanced Administration</LI>
									<LI>Record Enquiries</LI>
									<LI>Members Area<BR>(control 'use' permissions)</LI>
									<LI>Member Edit Notification Email</LI>
									<LI>News Images</LI>
									<LI>CM Advanced Embed Asset Selector</LI>
									<LI>Email Spam Protection</LI>
									<LI>User Parent Data Collection Field</LI>									
									<LI>Member Expiry Date</LI>
									<LI>Member Edit Fields</LI>
									<LI>Payment Configuration</LI>									
									<LI>countries and Currencies Configurations</LI>
									<LI>Tell a Friend</LI>
									<LI>Monthly Schedule Field</LI>
									<LI>StyleSheet Picker</LI>
									<LI>Layout Subcontent Page</LI>
									<LI>Data Collection Content Layout Picker</LI>
									<LI>Newsletter Two Content Areas</LI><BR><BR>
									<LI>Email Form Record Enquiries</LI>
									<LI>Admin FCK Editor</LI>
									<BR><BR>Payment Related Options:
									<LI>Web Pay Company Name</LI>
									<LI>Web Pay CVV2</LI>
									<LI>Invoice Payment Option</LI>
									<LI>Pay On Collection Payment Option</LI>	
									<BR><BR>Shop Related Options:
									<LI>Sell Products</LI>
									<LI>Shop Members</LI>
									<LI>Shop Customer Join Newsletter</LI>
									<LI>Shop Category Images</LI>
									<LI>Shop Category Descriptions</LI>									
									<LI>Shop Product Images=2</LI>
									<LI>Shop Product Option Images</LI>	
									<LI>Shop Product No Normal Images</LI>	
									<LI>Shop Product No Large Images</LI>	
									<LI>Shop Product No Thumbnail Images</LI>	
									<LI>Shop Product Flash</LI>
									<LI>Shop Supplier Price</LI>
									<LI>Shop Product Option Freight</LI>
									<LI>Shop Product Stock Code Not Required</LI>
									<LI>Shop Product Multi-Country Prices</LI>
									<LI>Shop Engine Image Size=120x120</LI>
									<LI>Shop Engine Products Per Page=9</LI>
									<LI>Shop Discount Codes</LI>
									<LI>Shop Featured Products</LI>
									<LI>Shop Donations</LI>
									<LI>Shop Skip Search</LI>
									<LI>Shop Advanced Product Manage</LI>
									<br><br>
									<LI>Shop Search Layout=shop</LI>
									<LI>Shop Engine Layout=shop</LI>
									<LI>Shop Detail Layout=shop</LI>
									<LI>Shop Basket Layout=shop</LI>
									<LI>Shop Checkout Layout=shop</LI>
									<LI>Shop Category Menu Set</LI>
									<LI>Security Login Layout=default</LI>
									<br><br> 
									<LI>Shop Freight Zone countries</LI>
									<LI>Shop Non-NZD Currencies</LI>
									<LI>Featured Products</LI>
									<LI>Quicklist Products</LI>
									<LI>Product Order Limit</LI>
									<LI>DPS Processing</LI>
									<LI>ZipZap Processing</LI>																		
									<LI>Quick Order Categories</LI>									
									<LI>Hide From In Prices</LI>
									<LI>Hide Memeber's Menu</LI>
									<LI>Show Menu Description</LI>
									<LI>Shop_System Product Attributes</LI>
									<BR><BR>
									<LI>Show Country Note</LI>																		
									<LI>Newsletter Advanced Subscribe Form</LI>
									",
				'required'		=>	FALSE,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,			
				'rows'	=>	'45',	'cols'		=>	'45',
				
			)));
/*			
			$this->addField(new IntegerField(array(
				'name'			=>	'cfg_last_or_id',
				'displayName'	=>	'Last Order Number',
				'note'			=>	NULL,
				'message'		=>	'Please enter last order number',
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'10',	'maxLength'	=>	'8',					
			)));
			
			$this->addField(new IntegerField(array(
				'name'			=>	'cfg_last_us_id',
				'displayName'	=>	'Last Used User ID',
				'note'			=>	'This value should never be set to lower values!',
				'message'		=>	'Please enter last user id',
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'10',	'maxLength'	=>	'8',					
			)));
			$this->addField(new TextField(array(
				'name'			=>	'cfg_version',
				'displayName'	=>	'DB Version Number',
				'note'			=>	'Dont touch if you dont know what this is for.',
				'required'		=>	TRUE,
				'verify'		=>	FALSE,
				'unique'		=>	FALSE,
				'size'	=>	'40',	'maxLength'	=>	'127',					
			)));
*/

		}
	}

}
?>
