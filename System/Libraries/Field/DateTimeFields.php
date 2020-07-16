<?php
/*
 	List of fields in this file
 	
 		TimeStampField
 		CreditCardExpiryDateField
 		DateField
 		DateTimeField
*/

// ############# TimeStamp Field ############# //

class TimeStampField extends HiddenField {
	function valueSQL() {
		return date("'Y-m-d H:i:s'");
	}
}
 
class CreditCardExpiryDateField extends Field  {
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		// Expects value in dd/mm/yyyy format
		$value = ss_HTMLEditFormat($verify ? $this->verifyValue : $this->value);
		$name  = $verify ? $this->name.'_V'   : $this->name;
		
		$month = '';
		$year = '';
		if (strlen($value) and ListLen($value,'/') == 2) {
			$month = ListFirst($value,'/');
			$year = ListLast($value,'/');
		}
		
		if ($this->displayType == 'hidden') {
			$returnVal = "<INPUT TYPE=\"HIDDEN\" NAME=\"$name\" VALUE=\"$value\">";
		} elseif ($this->displayType == 'output') {
			$returnVal = "$value";
		} else {
			$returnVal = "";
			$returnVal .= "<select name=\"ExpiryMonth\" onChange=\"updateExpiryDate(this.form)\">\n";
			for($i=1; $i<=12; $i++) {
				if ($i < 10)
					$returnVal .= "<option ".(($month == '0'.$i)?'selected':'')." value='0$i'>0$i</option>\n";
				else 
					$returnVal .= "<option ".(($month == $i)?'selected':'')." value='$i'>$i</option>\n";
			}
			$returnVal .= "</select>&nbsp;<select name=\"ExpiryYear\" onChange=\"updateExpiryDate(this.form)\">\n";
			$temp = (int)date("Y");
			for($i=$temp; $i<=($temp+10); $i++) {
				$returnVal .= "<option ".(($year == $i)?'selected':'')." value='$i'>$i</option>\n";
			}
			$returnVal .= "</select><INPUT TYPE=\"hidden\" NAME=\"$name\" VALUE=\"$value\">";
		}
		$returnVal .= <<< EOD
		<SCRIPT language="javascript">
		function updateExpiryDate(theForm) {
			year = theForm.ExpiryYear.options[theForm.ExpiryYear.selectedIndex].value;
			month = theForm.ExpiryMonth.options[theForm.ExpiryMonth.selectedIndex].value;
	
			theForm.TrCreditCardExpiry.value = month + "/" + year;
		}
		</SCRIPT>
EOD;
		return $returnVal;
	}

	function validate() {
		if ($this->value != NULL) {
			$separator = '/';
			
			// Check theres two values separated by slashes
			if (count(explode($separator,$this->value)) != 2) 
				return "$this->displayName must be in mm/yy or mm/yyyy format.";
			
			// Check a valid date was supplied
			list($month,$year) = explode($separator,$this->value);
		
			if (($month == '') || ($year == '') || (strlen($month) !=2) || (strlen($year) != 4 && strlen($year) != 2) || !is_numeric($month) || !is_numeric($year)) {
				return "$this->displayName must be numeric and in mm/yy or mm/yyyy format.";
			}
			if (strlen($year) == 2) $year = "20$year";
			if ($year < date('Y') || ($year == date('Y') && $month < date('m')) ) {
				return "$this->displayName is invalid date.";
			}
	
		}		
		return NULL;
	}

	function valueSQL() {
		if (($this->value != NULL) && (strlen($this->value) > 0)) {
			$separator = '/';
			list($month,$year) = explode($separator,$this->value);
			
			// Adjust the year for 2 digit years
			$year = ss_AdjustTwoDigitYear($year);
			
			return "'$month/$year'";
		} else {
			return 'NULL';
		}
	}
}

class DateField extends Field {
	var $dateFormat = 'Y-m-d';	
	var $displayDateFormat = 'yyyy-mm-dd';	
	var $tableFormat = false;
	
	function displayValue($value) {
        if (ss_optionExists('Date Field Format')){
            $this->dateFormat = ss_optionExists('Date Field Format');
        }
        if (strlen($value) and ss_optionExists('Date Field Custom Format')) {
			return safePre1970FormatDate($value, ss_optionExists('Date Field Custom Format'));
		} else if (strlen($value) and ss_optionExists('Date Field Pre 1970')) { 
			return safePre1970FormatDate($value, $this->dateFormat);
		} else if (strlen($value)) 
			return formatDateTime($value, $this->dateFormat);
		else 
			return '';			
	}
	function display($verify = FALSE, $form = 'adminForm', $multi = FALSE, $class = NULL) {
		// Expects value in dd/mm/yyyy format
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		$dateValue = '';
		if ($value == 'Now') {
			$dateValue = date($this->dateFormat);				
		}
		
		if (strlen($value))	$dateValue = formatDateTime($value,$this->dateFormat);
		
		$displayformat = $this->displayDateFormat;		
		if (strlen($displayformat)) {
			
			$displayformat = "($displayformat)";
		}
	
		if (!$this->showCalendar){
			
			if ($this->tableFormat) {
				$result = "<table><tr><td><INPUT CLASS=\"{$this->class}\" TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"$name\" VALUE=\"$dateValue\" MAXLENGTH=\"$this->maxLength\"></td></tr><tr><td>$displayformat</td></tr></table>";				
			} else {
				$result = "<INPUT CLASS=\"{$this->class}\" TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"$name\" VALUE=\"$dateValue\" MAXLENGTH=\"$this->maxLength\"> $displayformat";				
			}
		} else {			
			$result = <<< EOD
			
		<INPUT CLASS="{$this->class}" TYPE="TEXT" SIZE="{$this->size}" NAME="{$name}" VALUE="$dateValue" MAXLENGTH="$this->maxLength">
		<SPAN CLASS="textSmall" style="vertical-align: bottom;">&nbsp;<A HREF="Javascript:void(0);" ONCLICK="x=Math.round((screen.availWidth-250)/2);y = Math.round((screen.availHeight-280)/2); window.open({$name}_getCalendarURL(),'Calendar','width=250,height=280,top='+y+',left='+x+',screeenY='+y+',screenX='+x);"><img src="System/Libraries/Field/Images/i-calendar.gif" border="0" alt="Select a date"></A></span>&nbsp;<SPAN CLASS=\"formborder\">$displayformat</SPAN>
	<script language="Javascript">

	function {$name}_getCalendarURL() {
		form = document.$form;
		theURL = 'index.php?act=Calendar.SelectDate&Format={$this->dateFormat}&OnClick=opener.document.forms.$form.{$name}.value=date;window.close();';
		
		theDate = form.{$name}.value.split('-');		
		year= new Date().getYear();
		month= new Date().getMonth()+1;
		if (theDate.length == 3) {
			year = theDate[0];
			month = theDate[1].toUpperCase();
		}
		return theURL+'&Month='+month+'&Year='+year;
	}
	</SCRIPT>
EOD;
	}
		return $result;
	}

	function processDatabaseInputValues($primaryKey = -1) {
		// Convert database format to the dd/mm/yyyy format expected
		// by the display and validate methods
		if (($this->value != NULL) && (strlen($this->value) > 0)) {		
			$this->value = formatDateTime($this->value, $this->dateFormat);			
			$this->verifyValue = $this->value;
		}
	}
	
	function validate() {
		if ($this->value != NULL) {			
			$separator = '-';
		
			// Check theres three values separated by slashes
			if (count(explode($separator,$this->value)) != 3) 
				return "$this->displayName must be in yyyy-mm-dd format.".$this->value;
				
			// Check a valid date was supplied
			list($year, $month,$day) = explode($separator,$this->value);
			
			if (
			($day == '') || ($month == '') || ($year == '') ||					
			(checkdate($month,$day,ss_AdjustTwoDigitYear($year)) == FALSE)
			) {
				return "$this->displayName must be in yyyy-mm-dd format and must be a valid date.";
			}
		}						
	
		return NULL;
	}
	
	function valueSQL() {
		if (($this->value != NULL) && (strlen($this->value) > 0)) {
			if ($this->showCalendar) {
				return "'{$this->value}'";
			} else {
				$separator = '-';
				list($year,$month,$day) = explode($separator,$this->value);
				
				// Adjust the year for 2 digit years
				$year = ss_AdjustTwoDigitYear($year);
				
				return "'$year-$month-$day'";
			}
		} else {
			return 'NULL';
		}
	}
}

class DateTimeField extends Field {
	var $hourLimit = null;
	function display($verify = FALSE, $form = 'adminForm', $multi = FALSE, $class = NULL) {
		// Expects value in dd/mm/yyyy format
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if (is_array($this->hourLimit)) {
			ss_paramKey($this->hourLimit, 'Min', 0);
			ss_paramKey($this->hourLimit, 'Max', 23);
			$minHour = $this->hourLimit['Min'];
			$maxHour = $this->hourLimit['Max'];
		} else {
			$minHour = 0;
			$maxHour = 23;
		}
		if ($value == null) {
			$dateValue = "";
			$timeValue = "";
		} else {
			$dateValue = formatDateTime($value,'Y-m-d');
			$timeValue = formatDateTime($value,'H:i');
		}
		
			
		if (!$this->showCalendar){
			$result = "<INPUT CLASS=\"{$this->class}\" TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"$name\" VALUE=\"$value\" MAXLENGTH=\"$this->maxLength\"> <SPAN CLASS=\"formborder\">(dd/mm/yyyy)</SPAN>&nbsp;&nbsp;&nbsp;<INPUT CLASS=\"{$this->class}\" TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"{$name}_Time\" MAXLENGTH=\"5\" VALUE=\"\"><SPAN CLASS=\"AdminNote\">(hh:mm)</SPAN>";
			
		} else {
			
			if ($this->required) 
				$hourOptions = '<option value=""></option>';
			else 
				$hourOptions = '';			
			$i = $minHour;
			while($i <= $maxHour) {
				$selected = '';		
				if ($i < 10) {
					if (ListFirst($timeValue, ':') == "0$i") {
						$selected = 'SELECTED';		
					}
					$hourOptions .= "<option value='$i' $selected>0$i</option>";
				} else {
					if (ListFirst($timeValue, ':') == "$i") {
						$selected = 'SELECTED';		
					}
					$hourOptions .= "<option value='$i' $selected>$i</option>";
				}
				$i++;
			}
			
			
			if ($this->required) 
				$minOptions = '<option value=""></option>';
			else 
				$minOptions = '';				
			$i = 0;
			while($i <= 59) {
				$selected = '';			
				if ($i < 10) {
					if (ListLast($timeValue, ':') == "0$i") {
						$selected = 'SELECTED';		
					}
					$minOptions .= "<option value='$i' $selected>0$i</option>";
				} else {
					if (ListLast($timeValue, ':') == "$i") {
						$selected = 'SELECTED';		
					}
					$minOptions .= "<option value='$i' $selected>$i</option>";
				}
				$i++;
			}
			$result = <<< EOD
			
	<INPUT CLASS="{$this->class}" TYPE="TEXT" SIZE="{$this->size}" NAME="{$name}_Date" VALUE="$dateValue" MAXLENGTH="$this->maxLength" ONCHANGE="{$name}_FixDateTime();">&nbsp; 
	<SPAN CLASS="textSmall"><A HREF="Javascript:void(0);" ONCLICK="x=Math.round((screen.availWidth-250)/2);y = Math.round((screen.availHeight-280)/2); window.open({$name}_getCalendarURL(),'Calendar','width=250,height=280,top='+y+',left='+x+',screeenY='+y+',screenX='+x);"><img src="System/Libraries/Field/Images/i-calendar.gif" border="0" alt="Select a date"></A></span>&nbsp;&nbsp;&nbsp; 
	<select CLASS="{$this->class}" name="{$name}_TimeHour" ONCHANGE="{$name}_FixDateTime();">$hourOptions</select><select CLASS="{$this->class}" name="{$name}_TimeMinute" ONCHANGE="{$name}_FixDateTime();">$minOptions</select>
	<INPUT NAME="{$name}" value="{$value}" TYPE="hidden">

	<script language="Javascript">
	function {$name}_FixDateTime() {
		// Do the date first
		form = document.$form;
		theDate = form.{$name}_Date.value.split(/[^A-Za-z0-9]+/);		
		months = new Array('JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
		year = '1970';
		month = '01';			
		day = '01';
		hours = '12';
		minutes = '00';
		
		if (theDate.length == 3) {
			// Grab the values 
			month = theDate[1];			
			day = theDate[2];
			year = theDate[0];
			
			// Convert month to a number
			for (var i=0; i<months.length; i++) {
				if (month == months[i]) {
					month = i+1;
					break;
				}
			}
			
			// leading 0s and 19s and 20s
			if (day < 10) day = '0'+Math.round(day);
			if (month < 10) month = '0'+Math.round(month);
			if (year < 10) {
				year = '200'+Math.round(year);
			} else if (year < 70) {	
				year = '20'+Math.round(year);
			} else if (year > 70 & year < 100) {
				year = '19'+Math.round(year);
			}
			if (day > 31) day = 1;
			if (day == 0) day = 1;
			hours = form.{$name}_TimeHour.options[form.{$name}_TimeHour.selectedIndex].value;
			minutes = form.{$name}_TimeMinute.options[form.{$name}_TimeMinute.selectedIndex].value;
				
					
			form.$name.value = year+'-'+month+'-'+day+' '+hours+':'+minutes+':00';		
			
			if (form.$name.value == '1970-01-01 12:00:00') {
				form.$name.value = '';
			}			
		} else if (theDate.length == 0) {
			form.$name.value = '';
		}
		
		
	}

	function {$name}_getCalendarURL() {
		form = document.$form;
		theURL = 'index.php?act=Calendar.SelectDate&Format=Y-m-d&OnClick=opener.document.forms.$form.{$name}_Date.value=date;opener.FixDateTime();window.close();';
		theDate = form.{$name}.value.split(/[^A-Za-z0-9]+/);
		year= new Date().getYear();
		month= new Date().getMonth()+1;
		if (theDate.length == 3) {
			year = theDate[2];
			month = theDate[0].toUpperCase();
			
			// Convert month to a number
			for (var i=0; i<months.length; i++) {
				if (month == months[i]) {
					month = i+1;
					break;
				}
			}
			if (year < 10) {
				year = '200'+Math.round(year);
			} else if (year < 70) {	
				year = '20'+Math.round(year);
			} else if (year > 70 & year < 100) {
				year = '19'+Math.round(year);
			}
		}
		return theURL+'&Month='+month+'&Year='+year;
	}
	</SCRIPT>
EOD;
			

		}
		return $result;
	}

	function processDatabaseInputValues($primaryKey = -1) {
		// Convert database format to the dd/mm/yyyy format expected
		// by the display and validate methods
		if (($this->value != NULL) && (strlen($this->value) > 0)) {
			
			if (!$this->showCalendar){
				list($year,$month,$day) = explode('-',$this->value);						
			
				$this->value = "$day/$month/$year";			
				$this->verifyValue = $this->value;
			} 			
		}		
	}
	
	function validate() {
		if ($this->value != NULL) {
			if (!$this->showCalendar){
				$separator = '/';
			
				// Check theres three values separated by slashes
				if (count(explode($separator,$this->value)) != 3) 
					return "$this->displayName must be in dd/mm/yy or dd/mm/yyyy format.";
				// Check a valid date was supplied
				list($day,$month,$year) = explode($separator,$this->value);
				
				if (
				($day == '') || ($month == '') || ($year == '') ||					
				(checkdate($month,$day,ss_AdjustTwoDigitYear($year)) == FALSE)
				) {
				return "$this->displayName must be in dd/mm/yy or dd/mm/yyyy format and must be a valid date.";
				}
			}									
		}		
		return NULL;
	}

	function valueSQL() {
		if (($this->value != NULL) && (strlen($this->value) > 0)) {						
			return "'{$this->value}'";
		} else {
			return 'NULL';
		}
	}
}



class TimeField extends Field {
	var $hourLimit = null;
	function display($verify = FALSE, $form = 'adminForm', $multi = FALSE, $class = NULL) {
		// Expects value in dd/mm/yyyy format
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if (is_array($this->hourLimit)) {
			ss_paramKey($this->hourLimit, 'Min', 0);
			ss_paramKey($this->hourLimit, 'Max', 23);
			$minHour = $this->hourLimit['Min'];
			$maxHour = $this->hourLimit['Max'];
		} else {
			$minHour = 0;
			$maxHour = 23;
		}
		if ($value == null) {			
			$timeValue = "";
		} else {			
			$timeValue = formatDateTime($value,'H:i');
		}
		
					
			
		if ($this->required) 
			$hourOptions = '<option value=""></option>';
		else 
			$hourOptions = '';			
		$i = $minHour;
		while($i <= $maxHour) {
			$selected = '';		
			if ($i < 10) {
				if (ListFirst($timeValue, ':') == "0$i") {
					$selected = 'SELECTED';		
				}
				$hourOptions .= "<option value='$i' $selected>0$i</option>";
			} else {
				if (ListFirst($timeValue, ':') == "$i") {
					$selected = 'SELECTED';		
				}
				$hourOptions .= "<option value='$i' $selected>$i</option>";
			}
			$i++;
		}
		
		
		if ($this->required) 
			$minOptions = '<option value=""></option>';
		else 
			$minOptions = '';				
		$i = 0;
		while($i <= 59) {
			$selected = '';			
			if ($i < 10) {
				if (ListLast($timeValue, ':') == "0$i") {
					$selected = 'SELECTED';		
				}
				$minOptions .= "<option value='$i' $selected>0$i</option>";
			} else {
				if (ListLast($timeValue, ':') == "$i") {
					$selected = 'SELECTED';		
				}
				$minOptions .= "<option value='$i' $selected>$i</option>";
			}
			$i++;
		}
		
			$result = <<< EOD
				
	<select CLASS="{$this->class}" name="{$name}_TimeHour" ONCHANGE="{$name}_FixTime();">$hourOptions</select><select CLASS="{$this->class}" name="{$name}_TimeMinute" ONCHANGE="{$name}_FixTime();">$minOptions</select>
	<INPUT NAME="{$name}" value="{$value}" TYPE="hidden">

	<script language="Javascript">
	function {$name}_FixTime() {
		// Do the date first
		form = document.$form;			
				
		hours = form.{$name}_TimeHour.options[form.{$name}_TimeHour.selectedIndex].value;
		minutes = form.{$name}_TimeMinute.options[form.{$name}_TimeMinute.selectedIndex].value;
				
					
		form.$name.value = hours+':'+minutes+':00';		
						
		
	}
	</SCRIPT>
EOD;
			
		
		return $result;
	}

	function processDatabaseInputValues($primaryKey = -1) {
		// Convert database format to the dd/mm/yyyy format expected
		// by the display and validate methods
		if (($this->value != NULL) && (strlen($this->value) > 0)) {
			
			if (!$this->showCalendar){
				list($year,$month,$day) = explode('-',$this->value);						
			
				$this->value = "$day/$month/$year";			
				$this->verifyValue = $this->value;
			} 			
		}		
	}
	
	function validate() {
		if ($this->value != NULL) {
			if (!$this->showCalendar){
				$separator = '/';
			
				// Check theres three values separated by slashes
				if (count(explode($separator,$this->value)) != 3) 
					return "$this->displayName must be in dd/mm/yy or dd/mm/yyyy format.";
				// Check a valid date was supplied
				list($day,$month,$year) = explode($separator,$this->value);
				
				if (
				($day == '') || ($month == '') || ($year == '') ||					
				(checkdate($month,$day,ss_AdjustTwoDigitYear($year)) == FALSE)
				) {
				return "$this->displayName must be in dd/mm/yy or dd/mm/yyyy format and must be a valid date.";
				}
			}									
		}		
		return NULL;
	}

	function valueSQL() {
		if (($this->value != NULL) && (strlen($this->value) > 0)) {						
			return "'{$this->value}'";
		} else {
			return 'NULL';
		}
	}
}



class MonthlyScheduleField extends TextField  {
	var $monthFormat = 'M';		
	var $options = array();
	var $class = 'mainTable';
	var $tableclass = 'availabilityTable';
	function displayValue($value, $showMonths = NULL, $options = NULL, $howmany = 6) {
		
		// make an initial array for months to display		
		$monthlySchedule = array();			
		$today = getdate(); 
		$year = $today['year'];		
		$tableMonths = "";
		$tableYears = "";
		$tableMonthFields = "";		
		
		$months = array();
		$monthCounter = 1;
		$tempCounter = 0;
		for($i = $today['mon']; $i <= 12; $i++) {			
			$months[$tempCounter] = array('month' => $i, 'value' => null);			
			if ($monthCounter > $howmany) break;
			$tableMonths .= "<TD class=\"{$this->class}\" align=\"center\"><b>".date($this->monthFormat, mktime(0,0,0,$i,1,$year))."</b></TD>";															
			$monthCounter++;
			$tempCounter++;
		}
		$monthlySchedule[0] = array('year' => $year, 'months' =>$months);							 		
		$tableYears = "<TD class=\"{$this->class}\" colspan='".(12 - $today['mon'] + 1)."' align ='center'><b>$year</b></TD>";		
		
		if($today['mon'] > 1) {
			$nextYear = $year + 1;
			$months = array();
			$tempCounter = 0;
			for($i = 1; $i < $today['mon']; $i++) {
				$months[$tempCounter++] = array('month' => $i, 'value' => null);
				if ($monthCounter > $howmany) break;
				$tableMonths .= "<TD class=\"{$this->class}\" align=\"center\"><b>".date($this->monthFormat, mktime(0,0,0,$i,1,$nextYear))."</b></TD>";											
				
				$monthCounter++;
				$tempCounter++;
			}
			$tableYears .= "<TD class=\"{$this->class}\" colspan='".($today['mon']-1)."' align ='center'><b>$nextYear</b></TD>";		
			
			$monthlySchedule[1] = array('year' => $nextYear, 'months' =>$months);						
		}
		
		$valueSchedule = array();
		if (strlen($value))	{
			$valueSchedule = unserialize($value);
		} 
		// assign monthly values from the value to the initial array 
		// if the month of year is exist in the initial array	
		//ss_DumpVarDie($valueSchedule);
		
		$monthCounter = 1;
		foreach($monthlySchedule as  $aYearSchedule) {
			$theMonthSchedules = null;
						
			foreach($valueSchedule as $theYearSchedule) {
				if ($aYearSchedule['year'] == $theYearSchedule['year']) {
					$theMonthSchedules = $theYearSchedule['months'];										
					break;
				}
			}
			if (count($theMonthSchedules)) {				
				foreach ($aYearSchedule['months'] as $aMonthSchedule) {
					$found = false;
					if ($monthCounter > $howmany) break;
					foreach ($theMonthSchedules as $theMonthSchedule) {
						if ($theMonthSchedule['month'] == $aMonthSchedule['month']) {
							$temp = $options[0];
							if (array_key_exists($theMonthSchedule['value'], $options))
								$temp = $options[$theMonthSchedule['value']];
							$tableMonthFields .= "<TD class=\"{$this->class}\" aligh='center'><center>$temp</center></TD>";													
							$found = true;
							break;
						}
					}
					if (!$found) {
						$tableMonthFields .= "<TD class=\"{$this->class}\" aligh='center'><center>{$options[0]}</center></TD>";
					}	
					
					$monthCounter++;				
				}
			} else {				
				foreach ($aYearSchedule['months'] as $aMonthSchedule) {					
					if ($monthCounter > $howmany) break;
					$tableMonthFields .= "<TD class=\"{$this->class}\" aligh='center'><center>{$options[0]}</center></TD>";						
					$monthCounter++;					
				}
			}						
		}
		
		
		$result =  "";
		if ($showMonths)
			$result =  "<TABLE width=\"100%\" cellpadding=\"3\" cellspacing=\"0\" class=\"{$this->tableclass}\">";
		if ($showMonths) $result .="<TR>{$tableYears}</TR><TR>{$tableMonths}</TR><TR>";
		
		$result .="{$tableMonthFields}";	
		if ($showMonths)
			$result .=  "</TR></TABLE>";
		return $result;
	}
	
	function display($verify = FALSE, $formName = NULL, $multi = FALSE, $class = NULL) {
		// Expects value in dd/mm/yyyy format
		if ($verify) die("You cannot verify MonthlyScheduleField");
		if ($this->required and !count($this->options)) {
			die("Please define at least one option in the Weekly Schedule Field.");
		}
		$name  = $this->name;
		$form = $this->fieldSet->formName;
		$options = '';
		if (!$this->required) {
			$options = "<option value=''></option>";
		}
		
		foreach ($this->options as $desc => $option) {
			$options .=  "<option value='$option'>$desc</option>";
		}
		
		// make an initial array for months to display		
		$monthlySchedule = array();			
		$today = getdate(); 
		$year = $today['year'];		
		$tableMonths = "";
		$tableYears = "";
		$tableMonthFields = "";
		$jsMonthOrder = '';
		$jsSetScheduleValues = '';		
		$jsMontlySchedule = '';		
		
		$months = array();
		$tempCounter = 0;
		for($i = $today['mon']; $i <= 12; $i++) {			
			$months[$tempCounter] = array('month' => $i, 'value' => null);
			$tableMonths .= "<TD>".date($this->monthFormat, mktime(0,0,0,$i,1,$year))."</TD>";						
			$jsMonthOrder = ListAppend($jsMonthOrder, $i);
			$tableMonthFields .= "<TD><SELECT name=\"{$name}_Schedule_$i\" onchange=\"{$name}_LoadSchedule('$year', '$i');\">$options</SELECT></TD>";			
			$tempCounter++;
		}
		$monthlySchedule[0] = array('year' => $year, 'months' =>$months);							 		
		$tableYears = "<TD colspan='".(12 - $today['mon'] + 1)."' align ='left'>$year</TD>";		
		
		if($today['mon'] > 1) {
			$nextYear = $year + 1;
			$months = array();
			$tempCounter = 0;
			for($i = 1; $i < $today['mon']; $i++) {
				$months[$tempCounter++] = array('month' => $i, 'value' => null);
				$tableMonths .= "<TD>".date($this->monthFormat, mktime(0,0,0,$i,1,$nextYear))."</TD>";			
				$jsMonthOrder = ListAppend($jsMonthOrder, $i);
				$tableMonthFields .= "<TD><SELECT name=\"{$name}_Schedule_$i\" onchange=\"{$name}_LoadSchedule('$nextYear', '$i');\">$options</SELECT></TD>";			
				$tempCounter++;
			}
			$tableYears .= "<TD colspan='".($today['mon']-1)."' align ='left'>$nextYear</TD>";		
			
			$monthlySchedule[1] = array('year' => $nextYear, 'months' =>$months);						
		}
		//ss_DumpVarDie($this);
		$valueSchedule = array();
		if (strlen($this->value))	{
			//ss_DumpVar($this->value);
			$valueSchedule = unserialize($this->value);
		} 
	
		// assign monthly values from the value to the initial array 
		// if the month of year is exist in the initial array	
		$yearCounter	= 0;	
		//ss_DumpVar($monthlySchedule);
		//ss_DumpVarDie($valueSchedule,"hmm");
		
		foreach($monthlySchedule as  $aYearSchedule) {
			$theMonthSchedules = null;
			//$aYearSchedule['year']
			//$aYearSchedule['months']
			
			$jsMontlySchedule .= "var tempArray = new Array();\n";					
			$monCounter = 0;			
			foreach($valueSchedule as $theYearSchedule) {
				if ($aYearSchedule['year'] == $theYearSchedule['year']) {
					$theMonthSchedules = $theYearSchedule['months'];										
					break;
				}
			}
			if (count($theMonthSchedules)) {
				$monCounter = 0;
				foreach ($aYearSchedule['months'] as $aMonthSchedule) {
					$found = false;
					foreach ($theMonthSchedules as $theMonthSchedule) {
						if ($theMonthSchedule['month'] == $aMonthSchedule['month']) {
							$jsMontlySchedule .= "tempArray[$monCounter] = new {$name}_MonthSchedule({$theMonthSchedule['month']}, '{$theMonthSchedule['value']}');\n";
							$jsSetScheduleValues .= "setSelectedValue({$theMonthSchedule['month']}, '{$theMonthSchedule['value']}');\n";
							$found = true;
							break;
						}
					}
					if (!$found) {
						$jsMontlySchedule .= "tempArray[$monCounter] = new {$name}_MonthSchedule({$aMonthSchedule['month']}, '');\n";
					}
					$monCounter++;	
				}
			} else {
				$monCounter = 0;
				foreach ($aYearSchedule['months'] as $aMonthSchedule) {					
					$jsMontlySchedule .= "tempArray[$monCounter] = new {$name}_MonthSchedule({$aMonthSchedule['month']}, '');\n";					
					$monCounter++;	
				}
			}
			$jsMontlySchedule .= "monthlySchedule[$yearCounter] = new {$name}_YearSchedule({$aYearSchedule['year']}, tempArray);\n";
			$yearCounter++;		
		}						
		$htmlValue = ss_HTMLEditFormat($this->value);
			$result = <<< EOD
			
			<INPUT type="hidden" name="{$name}" value="$htmlValue">
<SCRIPT language="javascript">
	var monthlySchedule = new Array();
	var months = new Array();
	var monthOrder = new Array($jsMonthOrder);
	var theForm = document.forms.{$form};
	{$jsMontlySchedule}

	function setSelectedValue(month, selectedValue) {
		var selectedIndex = -1;
		var fieldName = '{$name}_Schedule_' + month;
		var theSelect = theForm[fieldName];
		
		originalLength = theSelect.options.length;		
		for(var i=originalLength-1; i >= 0; i--) {			
			if (theSelect.options[i].value == selectedValue) {
				selectedIndex = i;
				break;
			}
		}			
		
		theSelect.selectedIndex = selectedIndex;
	}
		
	function {$name}_YearSchedule(year, months) {		
		this.year = year;		
		this.months = months;			
	}
	
	function {$name}_MonthSchedule(month, value) {		
		this.month = month;		
		this.value = value;			
	}

	function {$name}_LoadSchedule(year, month) {			
		
		var fieldName = '{$name}_Schedule_' + month;
		var theSelect = theForm[fieldName];
		
		monthIndex = -1;
		for(i=0; i < monthlySchedule.length; i++) {
			if (monthlySchedule[i].year == year) {	
				tempArray = monthlySchedule[i].months;			
				for(j=0; j < monthlySchedule[i].months.length; j++) {
					if (monthlySchedule[i].months[j].month == month) {					
						monthlySchedule[i].months[j].value = theSelect.options[theSelect.selectedIndex].value;						
						break;
					}
				}		
			}		
		}	
		{$name}_DumpFieldSet();	
	}
	
	function replace(string,text,by) {
		// Replaces 'text' with 'by' in 'string'
	    var strLength = string.length, txtLength = text.length;
	    if ((strLength == 0) || (txtLength == 0)) return string;
	
	    var i = string.indexOf(text);
	    if ((!i) && (text != string.substring(0,txtLength))) return string;
	    if (i == -1) return string;
	
	    var newstr = string.substring(0,i) + by;
	
	    if (i+txtLength < strLength)
	        newstr += replace(string.substring(i+txtLength,strLength),text,by);
	
	    return newstr;
	}
	
	// Source: eskaly - http://php.cd/cowiki/Eskaly/Me 
	function {$name}_serialize (variable)
	{
	    switch (typeof variable)
	    {
	        case 'number':
	            if (Math.round(variable) == variable)
	                return 'i:'+variable+';';
	            else
	                return 'd:'+variable+';';
	        case 'boolean':
	            if (variable == true)
	                return 'b:1;';
	            else
	                return 'b:0;';
	        case 'string': 	        		
				var whitespace = new String('\\r\\n');
				var s = new String(variable);
				var newStr = new String();
				for(i=0;i<s.length;i++){
				     if (s.charAt(i) == whitespace) {    
				          newStr += "<BR>";
				     }else{
				          newStr += s.charAt(i)
				     }
				}
				
	        	return 's:'+newStr.length+':"'+newStr+'";';
	        	
	        case 'object':
	        	propCount = 0;
	        	for(var prop in variable) {
	        		propCount++;
	        	}
	            r = 'a:'+propCount+':{';
	            for(var prop in variable)
	            {
	                r+= {$name}_serialize(prop)+{$name}_serialize(variable[prop]);
	            }
	            r += '}';
	            return r;
	            break;
	        default:
	            return 'unkown type: '+typeof variable;
	    }
	}	
	// Write the whole field set array out into a
	// hidden field on the form
	function {$name}_DumpFieldSet() {
		var dumpData = new Array();
		
		// Loop through the Fields select list to get the data
		// so that it will be inserted in the correct order in the array
		for (var i=0; i < monthlySchedule.length; i++) {				
			dumpData[dumpData.length] = monthlySchedule[i];
		}
		formDef = {$name}_serialize(dumpData);
		//alert(formDef);
		document.forms.{$form}.{$name}.value = formDef;
	}
		
	</SCRIPT>
	<TABLE width="100%" cellpadding="0" cellspacing="1">
	<TR>		
		{$tableYears}		
	</TR>	
	<TR>		
		{$tableMonths}		
	</TR>	
	<TR>
		{$tableMonthFields}		
	</TR>		
	</TABLE>
	<script language="javascript">
	{$jsSetScheduleValues}
	</script>	
EOD;
	
		return $result;
	}
/*
	function processDatabaseInputValues($primaryKey) {
		// Convert database format to the dd/mm/yyyy format expected
		// by the display and validate methods
		/*
		if (($this->value != NULL) && (strlen($this->value) > 0)) {		
			$this->value = formatDateTime($this->value, $this->dateFormat);			
			$this->verifyValue = $this->value;
		}*
	}
*/

	function validate() {			
		
		$valueSchedule = array();
		if (strlen($this->value))	{
			$valueSchedule = unserialize($this->value);
		} 
		if ($this->required) {
			$errors = '';
			
			foreach($valueSchedule as  $aYearSchedule) {
				$theMonthSchedules = null;
												
				foreach ($aYearSchedule['months'] as $aMonthSchedule) {
					if (!strlen($aMonthSchedule['value'])) {
						$errors =  ListAppend($errors, date($this->monthFormat, mktime(0,0,0,$aMonthSchedule['month'],1,2000)));										
					}
				}
			} 
											
			if (strlen($errors)) {
				return "Please set the schedule value(s) of ".$errors;
			}
		}
		return NULL;
	}
}


class DateFromToTimesField extends Field {
	var $displayCalenderIconFront = false;
	var $hourLimit = null;
	var $minPeriod = 1;
	var $dateDataTable = null;
	var $dateDataPrimary = null;
	var $dateFromDataField = null;
	var $timeToDataField = null;
	var $dateFormat = 'Y-m-d';
	var $dateFormatYearIndex = '0';
	var $dateFormatMonthIndex = '1';
	var $dateFormatDayIndex = '2';
	
	function display($verify = FALSE, $form = 'adminForm', $multi = FALSE, $class = NULL) {
		// Expects value in dd/mm/yyyy format
		$value = $verify ? $this->verifyValue : $this->value;
		$name  = $verify ? $this->name.'_V'   : $this->name;
		if (is_array($this->hourLimit)) {
			ss_paramKey($this->hourLimit, 'Min', 0);
			ss_paramKey($this->hourLimit, 'Max', 23);
			$minHour = $this->hourLimit['Min'];
			$maxHour = $this->hourLimit['Max'];
		} else {
			$minHour = 0;
			$maxHour = 23;
		}
		$dateValue = '';
		$timeFromValue = '';
		$timeToValue = '';
		
		if ($value == null) {
			$value = array('DateFrom' => null,'TimeTo' => null);
		} else {			
			if (is_array($value) and $value['DateFrom'] != null) {
				$dateValue = formatDateTime($value['DateFrom'],$this->dateFormat);
				$timeFromValue = formatDateTime($value['DateFrom'],'H:i');				
				$timeToValue = $value['TimeTo'];	
			} else {
				$value = array('DateFrom' => null,'TimeTo' => null);					
			}
		}
		
			
		if (!$this->showCalendar){
			$result = "<INPUT CLASS=\"{$this->class}\" TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"$name\" VALUE=\"$value\" MAXLENGTH=\"$this->maxLength\"> <SPAN CLASS=\"formborder\">(dd/mm/yyyy)</SPAN>&nbsp;&nbsp;&nbsp;<INPUT CLASS=\"{$this->class}\" TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"{$name}_Time\" MAXLENGTH=\"5\" VALUE=\"\"><SPAN CLASS=\"AdminNote\">(hh:mm)</SPAN>";		
		} else {			
			if ($this->required) 
				$hourOptions = '<option value=""></option>';
			else 
				$hourOptions = '';			
			$i = $minHour;
			while($i <= $maxHour) {
				$selected = '';		
				if ($i < 10) {
					if (ListFirst($timeFromValue, ':') == "0$i") {
						$selected = 'SELECTED';		
					}
					$hourOptions .= "<option value='$i' $selected>0$i</option>";
				} else {
					if (ListFirst($timeFromValue, ':') == "$i") {
						$selected = 'SELECTED';		
					}
					$hourOptions .= "<option value='$i' $selected>$i</option>";
				}
				$i++;
			}
			
			
			if ($this->required) 
				$minOptions = '<option value=""></option>';
			else 
				$minOptions = '';				
			$i = 0;
			while($i <= 59) {
				$selected = '';			
				if ($i < 10) {
					if (ListLast($timeFromValue, ':') == "0$i") {
						$selected = 'SELECTED';		
					}
					$minOptions .= "<option value='0$i' $selected>0$i</option>";
				} else {
					if (ListLast($timeFromValue, ':') == "$i") {
						$selected = 'SELECTED';		
					}
					$minOptions .= "<option value='$i' $selected>$i</option>";
				}
				$i = $i + $this->minPeriod;
			}
			
			// optioins for to time field
			if ($this->required) 
				$hourToOptions = '<option value=""></option>';
			else 
				$hourToOptions = '';			
			$i = $minHour;
			while($i <= $maxHour) {
				$selected = '';		
				if ($i < 10) {
					if (ListFirst($timeToValue, ':') == "0$i") {
						$selected = 'SELECTED';		
					}
					$hourToOptions .= "<option value='0$i' $selected>0$i</option>";
				} else {
					if (ListFirst($timeToValue, ':') == "$i") {
						$selected = 'SELECTED';		
					}
					$hourToOptions .= "<option value='$i' $selected>$i</option>";
				}
				$i++;
			}
			
			
			if ($this->required) 
				$minToOptions = '<option value=""></option>';
			else 
				$minToOptions = '';				
			$i = 0;
			while($i <= 59) {
				$selected = '';			
				if ($i < 10) {
					if (ListLast($timeToValue, ':') == "0$i") {
						$selected = 'SELECTED';		
					}
					$minToOptions .= "<option value='0$i' $selected>0$i</option>";
				} else {
					if (ListLast($timeToValue, ':') == "$i") {
						$selected = 'SELECTED';		
					}
					$minToOptions .= "<option value='$i' $selected>$i</option>";
				}
				$i = $i + $this->minPeriod;
			}
			$extra = '';
			//if(ss_isItUs()) $extra = 'alert(year + " " + month + " " + day);';
	$result = "<INPUT CLASS=\"{$this->class}\" TYPE=\"TEXT\" SIZE=\"{$this->size}\" NAME=\"{$name}_Date\" VALUE=\"$dateValue\" MAXLENGTH=\"{$this->maxLength}\" ONCHANGE=\"{$name}_FixDateTime();\">&nbsp;";
	if ($this->displayCalenderIconFront) {
		$result = "<SPAN CLASS=\"textSmall\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"x=Math.round((screen.availWidth-250)/2);y = Math.round((screen.availHeight-280)/2); window.open({$name}_getCalendarURL(),'Calendar','width=250,height=280,top='+y+',left='+x+',screeenY='+y+',screenX='+x);\"><img src=\"System/Libraries/Field/Images/i-calendar.gif\" border=\"0\" alt=\"Select a date\"></A></span>&nbsp;".$result;
	} else {
		$result .= "&nbsp;<SPAN CLASS=\"textSmall\"><A HREF=\"Javascript:void(0);\" ONCLICK=\"x=Math.round((screen.availWidth-250)/2);y = Math.round((screen.availHeight-280)/2); window.open({$name}_getCalendarURL(),'Calendar','width=250,height=280,top='+y+',left='+x+',screeenY='+y+',screenX='+x);\"><img src=\"System/Libraries/Field/Images/i-calendar.gif\" border=\"0\" alt=\"Select a date\"></A></span>";		
	}
	
			
			$result .= <<< EOD
			
	&nbsp;&nbsp;&nbsp; 
	From: <select CLASS="{$this->class}" name="{$name}_FromTimeHour" ONCHANGE="{$name}_FixDateTime();">$hourOptions</select><select CLASS="{$this->class}" name="{$name}_FromTimeMinute" ONCHANGE="{$name}_FixDateTime();">$minOptions</select>&nbsp;&nbsp;
	To: <select CLASS="{$this->class}" name="{$name}_ToTimeHour">$hourToOptions</select><select CLASS="{$this->class}" name="{$name}_ToTimeMinute">$minToOptions</select>
	<INPUT NAME="{$name}" value="{$value}" TYPE="hidden">
	
	<script language="Javascript">
	function {$name}_FixDateTime() {
		// Do the date first
		form = document.$form;
		theDate = form.{$name}_Date.value.split(/[^A-Za-z0-9]+/);		
		months = new Array('JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
		year = '1970';
		month = '01';			
		day = '01';
		hours = '12';
		minutes = '00';
		
		if (theDate.length == 3) {
			// Grab the values 			
			var yearindex = {$this->dateFormatYearIndex};
			var monthindex = {$this->dateFormatMonthIndex};	
			var dayindex = {$this->dateFormatDayIndex};	
			month = theDate[yearindex];			
			day = theDate[dayindex];
			year = theDate[monthindex];
			$extra
			// Convert month to a number
			for (var i=0; i<months.length; i++) {
				if (month == months[i]) {
					month = i+1;
					break;
				}
			}
			
			// leading 0s and 19s and 20s
			if (day < 10) day = '0'+Math.round(day);
			if (month < 10) month = '0'+Math.round(month);
			if (year < 10) {
				year = '200'+Math.round(year);
			} else if (year < 70) {	
				year = '20'+Math.round(year);
			} else if (year > 70 & year < 100) {
				year = '19'+Math.round(year);
			}
			
			if (day > 31) day = 1;
			if (day == 0) day = 1;
			$extra
			hours = form.{$name}_FromTimeHour.options[form.{$name}_FromTimeHour.selectedIndex].value;
			minutes = form.{$name}_FromTimeMinute.options[form.{$name}_FromTimeMinute.selectedIndex].value;
				
					
			form.$name.value = year+'-'+month+'-'+day+' '+hours+':'+minutes+':00';		
			
			if (form.$name.value == '1970-01-01 12:00:00') {
				form.$name.value = '';
			}			
		} else if (theDate.length == 0) {
			form.$name.value = '';
		}		
	}

	function {$name}_getCalendarURL() {
		form = document.$form;
		theURL = 'index.php?act=Calendar.SelectDate&Format={$this->dateFormat}&OnClick=opener.document.forms.$form.{$name}_Date.value=date;opener.{$name}_FixDateTime();window.close();';
		theDate = form.{$name}.value.split(/[^A-Za-z0-9]+/);
		year= new Date().getYear();
		month= new Date().getMonth()+1;
		if (theDate.length == 3) {
			var yearindex = {$this->dateFormatYearIndex};
			var monthindex = {$this->dateFormatMonthIndex};			
			
			
			year = theDate[yearindex];
			month = theDate[monthindex].toUpperCase();
			
			// Convert month to a number
			for (var i=0; i<months.length; i++) {
				if (month == months[i]) {
					month = i+1;
					break;
				}
			}
			if (year < 10) {
				year = '200'+Math.round(year);
			} else if (year < 70) {	
				year = '20'+Math.round(year);
			} else if (year > 70 & year < 100) {
				year = '19'+Math.round(year);
			}
		}
		return theURL+'&Month='+month+'&Year='+year;
	}
	</SCRIPT>
EOD;
			

		}
		return $result;
	}
	
	function displayValue($value) {
		//ss_DumpVarDie($value);
		if ($value == null) {
			return '';
		} else {
			if (is_array($value)) {
				$dateValue = formatDateTime($value['DateFrom'],$this->dateFormat);
				$timeFromValue = formatDateTime($value['DateFrom'],'H:i');				
				$timeToValue = $value['TimeTo'];	
				return $dateValue." ".$timeFromValue."-".$timeToValue;
			}
		}
		return '';
	}
	function processFormInputValues( ) {
		$this->value = array();
		ss_paramKey($this->value, 'DateFrom','');
		ss_paramKey($this->value, 'TimeTo','');		
		
						
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name, '');
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name.'_ToTimeHour', '');
		ss_paramKey($this->fieldSet->ATTRIBUTES, $this->name.'_ToTimeMinute', '');
					
		$this->value['DateFrom'] = $this->fieldSet->ATTRIBUTES[$this->name];
		$this->value['TimeTo'] = $this->fieldSet->ATTRIBUTES[$this->name.'_ToTimeHour'].':'.$this->fieldSet->ATTRIBUTES[$this->name.'_ToTimeMinute'];
	}
	
	function processDatabaseInputValues($primaryKey = -1) {
		
		if ($primaryKey > 0) {	
			$row = getRow("SELECT {$this->dateDataPrimary},{$this->dateFromDataField}, {$this->timeToDataField} FROM {$this->dateDataTable} WHERE {$this->dateDataPrimary} = $primaryKey");			
			if (strlen($row[$this->dateDataPrimary])) {
				if (strlen($row[$this->dateFromDataField])) {
					$this->value = array('DateFrom' => $row[$this->dateFromDataField],'TimeTo' => $row[$this->timeToDataField]);
				} else {
					$this->value = array('DateFrom' => null,'TimeTo' => null);
				}
			} else {
				$this->value = array('DateFrom' => null,'TimeTo' => null);
			}
		} else {
			$this->value = array('DateFrom' => null,'TimeTo' => null);
		}
	}
	
	function validate() {
		if ($this->value != NULL) {
			if (!$this->showCalendar){
				$separator = '/';
			
				// Check theres three values separated by slashes
				if (count(explode($separator,$this->value)) != 3) 
					return "$this->displayName must be in dd/mm/yy or dd/mm/yyyy format.";
				// Check a valid date was supplied
				list($day,$month,$year) = explode($separator,$this->value);
				
				if (
				($day == '') || ($month == '') || ($year == '') ||					
				(checkdate($month,$day,ss_AdjustTwoDigitYear($year)) == FALSE)
				) {
				return "$this->displayName must be in dd/mm/yy or dd/mm/yyyy format and must be a valid date.";
				}
			}									
		}		
		return NULL;
	}

	// This function should return the sql required to insert this field. This can 
	// return a null value for those fields that do not actually enter into the db.
	function updateSQL() {		
		$dateFrom = strlen($this->value['DateFrom'])?"'".escape($this->value['DateFrom'])."'":'null';
		$timeTo = strlen($this->value['TimeTo'])?"'".escape($this->value['TimeTo'])."'":'null';
		return "{$this->dateFromDataField} = {$dateFrom}, {$this->timeToDataField} = {$timeTo}";		
	}
	
	function insertSQLField() {
		if ($this->name == 'us_name') {
			return "{$this->dateFromDataField}, {$this->timeToDataField}";
		}
		
		
	}
	
	function insertSQLValue() {
		$dateFrom = strlen($this->value['DateFrom'])?"'".escape($this->value['DateFrom'])."'":'null';
		$timeTo = strlen($this->value['TimeTo'])?"'".escape($this->value['TimeTo'])."'":'null';
		
		return "$dateFrom, $timeTo";	
		
	}
}

?>
