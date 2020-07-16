<?php
class ImportExport extends Plugin {


	function inputFilter() {
		parent::inputFilter();
		$needSecurity = true;
		
		if ( array_key_exists('act',$_REQUEST)  and ( $_REQUEST['act'] == 'CancelDenieds' or $_REQUEST['act'] == 'GetExchangeRates' or substr($_REQUEST['act'], 0, 9) == 'SendEmail'))
		{
			$needSecurity = false;	
		}
		
		if ($needSecurity) {
			$result = new Request('Security.Authenticate',array(
				'Permission'	=>	'CanAdministerAtLeastOneAsset',
			));	
		}
	}
	
	function exposeServices() {
		return array(			
			'Export.Page'	=>	array('method'	=>	'exportPage'),
			'Export.AllPages'	=>	array('method'	=>	'exportAllPages'),
			'Export.users'	=>	array('method'	=>	'exportUsers'),
			'Export.BlackList'	=>	array('method'	=>	'exportBlackList'),
			'Export.StockList'	=>	array('method'	=>	'exportStockList'),
			'Import.Page'	=>	array('method'	=>	'importPage'),
			'Import.Gallery'	=>	array('method'	=>	'importGallery'),
			'Import.users'	=>	array('method'	=>	'importUsers'),
			'Import.UsersList'	=>	array('method'	=>	'importUsersList'),
			'Import.UsersPrompt'	=>	array('method'	=>	'importUsersPrompt'),
			'Import.countries'	=>	array('method'	=>	'importCountries'),
			'Import.Products'	=>	array('method'	=>	'importProducts'),
			'Import.ProductsList'	=>	array('method'	=>	'importProductsList'),
			'Import.ProductsPrompt'	=>	array('method'	=>	'importProductsPrompt'),
			'Import.AllPages'	=>	array('method'	=>	'importAllPages'),
			'TestMaori'	=>	array('method'	=>	'testMaori'),
			'TestMaori2'	=>	array('method'	=>	'testMaori2'),
			'GetExchangeRates'	=>	array('method'	=>	'getExchangeRates'),
			'SendEmail1'	=>	array('method'	=>	'sendEmail1'),
			'SendEmail2'	=>	array('method'	=>	'sendEmail2'),
			'SendEmail3'	=>	array('method'	=>	'sendEmail3'),
			'SendEmail4'	=>	array('method'	=>	'sendEmail4'),
			'SendEmail5'	=>	array('method'	=>	'sendEmail5'),
			'SendEmail6'	=>	array('method'	=>	'sendEmail6'),
			'SendEmail7'	=>	array('method'	=>	'sendEmail7'),
			'SendEmail8'	=>	array('method'	=>	'sendEmail8'),
			'SendEmail9'	=>	array('method'	=>	'sendEmail9'),
			'SendEmail10'	=>	array('method'	=>	'sendEmail10'),
			'SendEmail11'	=>	array('method'	=>	'sendEmail11'),
			'SendEmail12'	=>	array('method'	=>	'sendEmail12'),
			'SendEmail13'	=>	array('method'	=>	'sendEmail13'),
			'SendEmail14'	=>	array('method'	=>	'sendEmail14'),
			'SendEmail15'	=>	array('method'	=>	'sendEmail15'),
			'SendEmail16'	=>	array('method'	=>	'sendEmail16'),
			'SendEmail17'	=>	array('method'	=>	'sendEmail17'),
			'SendEmail18'	=>	array('method'	=>	'sendEmail18'),
			'SendEmail19'	=>	array('method'	=>	'sendEmail19'),
			'CancelDenieds'	=>	array('method'	=>	'cancelDenieds'),
			'Import.CarsPrompt'	=>	array('method'	=>	'importCarsPrompt'),
			'Import.Cars'	=>	array('method'	=>	'importCars'),
			'Import.CarsList'	=>	array('method'	=>	'importCarsList'),
			'Import.BulkTest'	=>	array('method'	=>	'bulkTest'),
		);
	}

	function cancelDenieds() {
		require('model_cancelDenieds.php');
	}

	function bulkTest() {
		require('query_bulkTest.php');
	}

	function getExchangeRates() {
		$this->display->layout = 'None';
		require('model_getExchangeRates.php');	
	}

	function sendEmail1() {
		$this->display->layout = 'None';
		require('model_sendEmail1.php');	
	}

	function sendEmail2() {
		$this->display->layout = 'None';
		require('model_sendEmail2.php');	
	}

	function sendEmail3() {
		$this->display->layout = 'None';
		require('model_sendEmail3.php');	
	}

	function sendEmail4() {
		$this->display->layout = 'None';
		require('model_sendEmail4.php');	
	}

	function sendEmail5() {
		$this->display->layout = 'None';
		require('model_sendEmail5.php');	
	}

	function sendEmail6() {
		$this->display->layout = 'None';
		require('model_sendEmail6.php');	
	}

	function sendEmail7() {
		$this->display->layout = 'None';
		require('model_sendEmail7.php');	
	}

	function sendEmail8() {
		$this->display->layout = 'None';
		require('model_sendEmail8.php');	
	}

	function sendEmail9() {
		$this->display->layout = 'None';
		require('model_sendEmail9.php');	
	}

	function sendEmail10() {
		$this->display->layout = 'None';
		require('model_sendEmail10.php');	
	}

	function sendEmail11() {
		$this->display->layout = 'None';
		require('model_sendEmail11.php');	
	}

	function sendEmail12() {
		$this->display->layout = 'None';
		require('model_sendEmail12.php');	
	}

	function sendEmail13() {
		$this->display->layout = 'None';
		require('model_sendEmail13.php');	
	}

	function sendEmail14() {
		$this->display->layout = 'None';
		require('model_sendEmail14.php');	
	}

	function sendEmail15() {
		$this->display->layout = 'None';
		require('model_sendEmail15.php');	
	}

	function sendEmail16() {
		$this->display->layout = 'None';
		require('model_sendEmail16.php');	
	}

	function sendEmail17() {
		$this->display->layout = 'None';
		require('model_sendEmail17.php');	
	}

	function sendEmail18() {
		$this->display->layout = 'None';
		require('model_sendEmail18.php');	
	}

	function sendEmail19() {
		$this->display->layout = 'None';
		require('model_sendEmail19.php');	
	}

	function exportpageGenerate(&$content) {
		return include('inc_exportpageGenerate.php');
	}
	function importpageGenerate(&$content) {
		return include('inc_importpageGenerate.php');
	}
		
	function testMaori() {
		$this->display->layout = 'None';
		require('view_testMaori.php');	
	}
	
	function testMaori2() {
		$this->display->layout = 'None';
		require('view_testMaori2.php');	
	}

	function exportPage() {
		$this->display->layout = 'None';
		require('query_exportPage.php');	
		return include('model_exportPage.php');	
	}

	function exportUsers() {
		$this->display->layout = 'None';
		require('query_exportUsers.php');
		require('view_exportUsers.php');
	}

	function exportBlackList() {
		$this->display->layout = 'None';
		require('query_exportBlackList.php');
		require('view_exportBlackList.php');
	}

	function exportStockList() {
		$this->display->layout = 'None';
		require('query_exportStockList.php');
		require('view_exportStockList.php');
	}

	function exportAllPages() {
		$this->display->layout = 'None';
		require('query_exportAllPages.php');
		require('view_exportAllPages.php');
	}
	function importGallery() {
		$this->display->layout = 'None';	
		require('model_importGallery.php');
	}


	function importCarsPrompt() {
		$this->display->layout = 'Administration';
		require('query_importCarsPrompt.php');
		require('model_importCarsPrompt.php');
		require('view_importCarsPrompt.php');
	}
	
	function importCars() {
		$this->display->layout = 'Administration';
		require('model_carsImport.php');
		//require('query_importCars.php');
		//require('view_importCars.php');
	}	
	
	function importCarsList() {
		$this->display->layout = 'Administration';
		require('query_importCarsList.php');
		require('view_importCarsList.php');
	}	
	
	function importUsersPrompt() {
		$this->display->layout = 'Administration';
		require('query_importUsersPrompt.php');
		require('model_importUsersPrompt.php');
		require('view_importUsersPrompt.php');
	}

	function importProductsPrompt() {
		$this->display->layout = 'Administration';
		require('query_importProductsPrompt.php');
		require('model_importProductsPrompt.php');
//		require('view_importProductsPrompt.php');
	}

	function importProductsList() {
		$this->display->layout = 'None';
		require('query_importProductsList.php');
		require('view_importProductsList.php');
	}	

	function importProducts() {
		$this->display->layout = 'None';
		require('query_importProducts.php');
		require('view_importProducts.php');
	}	
	
	
	function importUsers() {
		$this->display->layout = 'None';
		require('query_importUsers.php');
		require('view_importUsers.php');
	}	

	function importUsersList() {
		$this->display->layout = 'None';
		require('query_importUsersList.php');
		require('view_importUsersList.php');
	}	
	
	function importCountries() {
		$this->display->layout = 'None';
		require('query_importCountries.php');
		require('model_importCountries.php');
	}
	
	function importPage() {
		$this->display->layout = 'None';
		require('query_importPage.php');	
		return include('model_importPage.php');	
	}

	function importAllPages() {
		$this->display->layout = 'None';
		require('query_importAllPages.php');
		require('view_importAllPages.php');
	}	
	
	function assetPathToExportFile($path) {
		return str_replace('/','~',ss_withoutPreceedingSlash($path)).'.html';	
	}	

	function exportFileToAssetPath($path) {
		$path = stri_replace('%7E','~',$path);
		$path = stri_replace('%20',' ',$path);
		return substr(str_replace('~','/',ss_withPreceedingSlash($path)),0,-5);	
	}	
	
}
?>
