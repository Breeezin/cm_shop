<?php
requireOnceClass('AssetTypes');

class ShopSystemAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_SHOPSYSTEM_';
	var $styleSheet = 'shop';
	var $prices = array();
	var $displayCurrency = null;
	var $enterCurrency = null;
	var $chargeCurrency = null;

	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		global $thisAsset;
		$thisAsset = $this;
		require_once('inc_functions.php');
		require('query_display.php');
	}

	function embed(&$asset) {
		$this->display($asset);
	}
	
	function properties(&$asset) {
		require('view_properties.php');
	}
	
	function defineFields(&$asset) {
		require('query_defineFields.php');
	}	

	function edit(&$asset) {
		require('view_edit.php');
	}	
	
	function processSave(&$asset) {
		require('model_processSave.php');
	}
	function delete(&$asset) {
		die("Please define the delete method for ShopSystemAsset");
	}
	
	function newAsset(&$asset) {
		// Make a directory for product images
		mkdir(ss_storeForAsset($asset->getID())."ProductImages");
		
		return null;
	}
	
	function setCurrencyCountry($countryThreeLetterCode,$byCurrency=false) {
		require('model_setCurrencyCountry.php');		
	}	

	function setDiscountCode($discountCode) {
		require('model_setDiscountCode.php');		
	}	

	function currencyConverter() {
		return include('query_currencyConverter.php');
	}	

	function getTaxCountryNote($type = 'standard') {
		return include('query_getTaxCountryNote.php');	
	}
	
	function getPrice($pr_id,$discount,$optionID = null,$type = 'HTML') {
		return include('query_getPrice.php');
	}

    //briar put these in for duty free --
    function getSpecials() {
        return include('query_getSpecials.php');
	}

    function getProdSpecial($SpCoID, $thisIndex) {
        return require('query_getSpecialPrice.php');
    }

    function getCatSpecial($ca_id, $thisIndex) {
         return require('query_getSpecialPrice.php');
    }
    //--

	function calculateTax($price) {
		return include('query_calculateTax.php');	
	}
	function calculateExtraFreight($basket = null,$zone = null) {
		return include('query_calculateFreight.php');
	}
	
	function setDefaultFreight() {
		return include('query_setDefaultFreight.php');
	}
	
	function getCurrencyFromCereal($cereal,$type) {
		return include('inc_getCurrencyFromCereal.php');
	}
	
	function getDisplayCurrency() {
		return $this->getChargeCurrency();

/*
		if( array_key_exists( "ChargeCurrency", $GLOBALS['cfg'] )
				 && is_array( $GLOBALS['cfg']['ChargeCurrency'] )
				 && array_key_exists( "DefaultCurrency", $_SESSION )
				 && array_key_exists( $_SESSION['DefaultCurrency'], $GLOBALS['cfg']['ChargeCurrency'] ) )
			$this->displayCurrency = $GLOBALS['cfg']['ChargeCurrency'][$_SESSION['DefaultCurrency']];

		if ($this->displayCurrency === null) {
			if (ss_optionExists('Shop Product Multi-Country Prices') !== false) {
				$this->displayCurrency = $_SESSION['Shop']['MultiCurrencyCountryDef'];	
			} else {
				$this->displayCurrency = $this->getCurrencyFromCereal($this->asset->cereal,'DISPLAY');
			}
		}
		return $this->displayCurrency;
*/
	}
	
	function getEnterCurrency() {
		if ($this->enterCurrency === null) {
			if (ss_optionExists('Shop Product Multi-Country Prices') !== false) {
				$this->enterCurrency = $_SESSION['Shop']['MultiCurrencyCountryDef'];	
			} else {				
				$this->enterCurrency = $this->getCurrencyFromCereal($this->asset->cereal,'ENTER');				
			}
		}
		return $this->enterCurrency;
	}
	
	function getChargeCurrency()
	{

		$row = getDefaultCurrencyEntry( );

		$this->chargeCurrency = array(
			'CurrencyCode'	=>	$row['po_currency'],
			'Symbol'		=>	$row['po_currency_symbol'],
			'AppearsBefore'	=>	$row['po_currency_symbol_before'],
			'Currency'		=>	$row['po_currency_name'],
			'Precision'		=>  $row['po_currency_precision'],
		);

		return $this->chargeCurrency;
	}
	
	function getOptions($product,$fieldsArray,$currentOptions=-1) {
		require('query_getOptions.php');	
		return include('view_getOptions.php');	
	}
	
	function formatPrice($forWhat,$price,$maxPrice = null, $sourceC = NULL) {
		
		$format = "%01.2f";
		$currency = $this->getDisplayCurrency();
		if( array_key_exists( 'Precision', $currency ) )
			$format = '%01.'.getDefaultCurrencyPrecision().'f';
		// This will need to be currency specific eventually
		if ($price !== null) {
			if ($forWhat == 'display') {

				$beforeSymbol = ($currency['AppearsBefore']?$currency['Symbol']:'');
				$afterSymbol = (!$currency['AppearsBefore']?$currency['Symbol']:'');
				
				if( $sourceC )
				{
					$price = $price * ss_getExchangeRate($sourceC,getDefaultCurrencyCode( ));
					if ($maxPrice !== null) {
						$maxPrice = $maxPrice * ss_getExchangeRate($sourceC,getDefaultCurrencyCode( ));
					}
				}
				
				if ( ( $maxPrice !== null ) && ($maxPrice > 0 ) && ( $maxPrice != $price ) ) {
					return "From $beforeSymbol".sprintf($format,$price)."$afterSymbol to $beforeSymbol".sprintf($format,$maxPrice)."$afterSymbol&nbsp;{$currency['CurrencyCode']}";	
				} else {
					return "$beforeSymbol".sprintf($format,$price)."$afterSymbol&nbsp;{$currency['CurrencyCode']}";	
				}
			} else if ($forWhat == 'displayApprox') {
				if( $sourceC == NULL )
				{
					$source = $this->getEnterCurrency();
					$sourceC = $source['CurrencyCode'];
				}
				$currency = array(
					'CurrencyCode'	=>	$_SESSION['Shop']['CurrencyCountry']['cn_currency_code'],
				);

				$price = $price * ss_getExchangeRate($sourceC,$_SESSION['Shop']['CurrencyCountry']['cn_currency_code']);
				if ($maxPrice !== null) {
					$maxPrice = $maxPrice * ss_getExchangeRate($sourceC,$_SESSION['Shop']['CurrencyCountry']['cn_currency_code']);
				}

				if ($maxPrice !== null and $maxPrice != $price) {
					return "From ~{$currency['CurrencyCode']} ".sprintf($format,$price)." to ~{$currency['CurrencyCode']} ".sprintf($format,$maxPrice);	
				} else {
					return "~{$currency['CurrencyCode']} ".sprintf($format,$price);
				}
			} else if ($forWhat == 'charge') {
				if( $sourceC == NULL )
				{
					$source = $this->getEnterCurrency();
					$sourceC = $source['CurrencyCode'];
				}
				$currency = $this->getChargeCurrency();
				$beforeSymbol = ($currency['AppearsBefore']?$currency['Symbol']:'');
				$afterSymbol = (!$currency['AppearsBefore']?$currency['Symbol']:'');
				
				/*
				$price = $price * ss_getExchangeRate($sourceC,$currency['CurrencyCode']);
				if ($maxPrice !== null) {
					$maxPrice = $maxPrice * ss_getExchangeRate($sourceC,$currency['CurrencyCode']);
				}
				*/
				
				if ($maxPrice !== null and $maxPrice != $price) {
					return "From $beforeSymbol".sprintf($format,$price)."$afterSymbol to $beforeSymbol".sprintf($format,$maxPrice)."$afterSymbol&nbsp;{$currency['CurrencyCode']}";	
				} else {
					return "$beforeSymbol".sprintf($format,$price)."$afterSymbol&nbsp;{$currency['CurrencyCode']}";	
				}
				
			}
		} else {
			return null;	
		}
	}
	
}

?>
