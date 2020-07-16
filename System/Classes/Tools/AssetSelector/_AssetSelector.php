<?PHP
class AssetSelector extends Plugin {
	
	function __construct() {
		$this->pluginDirectory = dirname(__FILE__);
		parent::__construct();
	}
	
	function exposeServices() {
		$prefix = 'AssetSelector';
		return array(
			"$prefix.LinkDisplay"			=>	array('method'	=>	'displayLink'),
			"$prefix.ImageDisplay"			=>	array('method'	=>	'displayImage'),
			"$prefix.SharedImageDisplay"	=>	array('method'	=>	'sharedImageDisplay'),
		);
	}
	
	function displayLink() {
		$this->display->title = "Link Selector";
		//require("model_displayLink.php");
		require("view_displayLink.php");
	}
	
	
	function displayImage() {
		$this->display->title = "Image Selector";
		require("model_displayImage.php");
		require("view_displayImage.php");
		
	}
	
	function sharedImageDisplay() {
		$this->display->title = "Shared Image Selector";
		require("model_sharedImageDisplay.php");
		require("view_sharedImageDisplay.php");				
	}
	
}
?>
