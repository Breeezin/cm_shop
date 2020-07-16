<?php
class Holiday
{
	private $year = null;
	private $country = null;
	private $province = null;
	private $holidays_date = array();
	private $holidays = array(	'US' => array(
											'01-01',
											'01-Third Monday',
//											'01-20',
											'02-Third Monday',
											'05-Last Monday',
											'07-04',
											'09-First Monday',
											'10-Second Monday',
											'11-11',
											'11-Fourth Thursday',
											'12-25'
										),
								'Canada' => array(
											'01-01',
											'Easter-Last Friday',
											'07-01',
											'09-First Monday',
											'12-25',
											'Easter-First Monday' => array('QC'),
											'10-Second Monday' => array('AB','BC','MB','NT','NU','ON','QC','SK','YT','NB'),
											'12-26' => array('ON','NB'),
											'02-Third Monday' => array('AB','ON','SK','MB'),
											'08-First Monday' => array('BC','NB','NT','NU','SK'),
											'11-11' => array('AB','BC','NB','NL','NT','NU','PE','SK','YT'),
											'06-21'	=> array('NT'),
											'07-09' => array('NU'),
											'02-Third Monday' => array('PE')
										)
								);
	
    public function __construct($country, $province=null){
		if(!$country || !isset($this->holidays[$country])) return; 
		$this->country = $country;
		if($province!=null) $this->province = $province;
		$this->year = date('Y');
		$this->Make_Current_Year_Holidays();
	}
	
	private function Make_Current_Year_Holidays(){
		foreach($this->holidays[$this->country] as $idx => $val){
			if(is_array($val))
				$this->holidays_date[$idx] = $this->Parse_Date($idx);
			else
				$this->holidays_date[$idx] = $this->Parse_Date($val);
		}
	}
	
	private function Parse_Date($str){
		$easter = easter_date($this->year);
		$date = '';
		if(preg_match('/([0-9][0-9])-([0-9][0-9])/i',$str,$matches)>0)
			$date = $this->year."-".$matches[1]."-".$matches[2];
		elseif(preg_match("/([0-9][0-9])-(.*)/i",$str,$matches)>0)
			$date = date('Y-m-d',strtotime($matches[2], mktime(0,0,-1, intval($matches[1]), 1, $this->year)));
		elseif(preg_match('/easter-(.*)/i',$str,$matches)>0)
			$date = date('Y-m-d',strtotime($matches[1], $easter));
		return $date;
	}
	
	public function set_province($province){
		$this->province = $province;
	}
	
	public function is_holiday($date=null){
		if(!$date) $date = date('Y-m-d');
		$idx = false;
		
		foreach($this->holidays_date as $idx => $d){
			if($d==$date) break;
			$idx = false;
		}
		if($idx===false) return false;
		if(!is_array($this->holidays[$this->country][$idx])) return true;
		if($this->province){
			foreach($this->holidays[$this->country][$idx] as $v){
				if($v==$this->province) return true;
			}
			return false;
		}else
			return false;
	}
}
?>
