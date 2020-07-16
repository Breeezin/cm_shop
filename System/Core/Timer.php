<?php
	class Timer {
	
		var $children = array();
		var $name;
		var $started;
		var $finished;
		var $parent = NULL;
		var $totalTimes = array();
		var $actualTimes = array();
	
		function __construct($name='Timer',$parent = NULL) {
			$this->started = getmicrotime();
			$this->name = $name;
			$this->parent = $parent;
		}
	
		function start($what) {
			$temp = new Timer($what,$this);
			return $temp;
		}
		
		function finish($what) {
			global $cfg;
			$this->finished = getmicrotime();
			if ($what != $this->name) {
				if (!isset($cfg) or $cfg['debugMode']) {
					die("Timer '{$this->name}' started but not finished!");
				}
			}
			if ($this->parent != NULL) {
				// Yeah this is a bit strange; adding the class to the 
				// parent's list of children now. You'd expect 
				// it to be added when its created. But if i do
				//		 array_push($this->children,$temp);
				// in the 'start' method after its created, it doesn't
				// work for some strange reason
				array_push($this->parent->children,$this);
				return $this->parent;
			} else {
				return $this;
			}
		}
		
		function stop() {
			$this->finish('Timer');
		}
		
		function report() {
			print("<ul><li>{$this->name} ");
			if (count($this->children)) print("Start");
				
			$innerExecution = 0;		
			foreach($this->children as $child) $innerExecution += $child->report();				
			
			$value = $this->finished - $this->started;
			$displayTime = number_format($value, 3, '.', '');	
			$displayExec = number_format($value-$innerExecution, 3, '.', '');	
			
			if (count($this->children)) print("<li>{$this->name} Finish. Total = {$displayTime}, ");
			print("<b>{$displayExec}</B></UL>");			

			if (!is_array($GLOBALS['timerTotalTimes'])) $GLOBALS['timerTotalTimes'] = array();
			if (!is_array($GLOBALS['timerActualTimes'])) $GLOBALS['timerActualTimes'] = array();
			if (!is_array($GLOBALS['timerTimesCalled'])) $GLOBALS['timerTimesCalled'] = array();
			
			ss_paramKey($GLOBALS['timerTotalTimes'],$this->name,0);
			ss_paramKey($GLOBALS['timerActualTimes'],$this->name,0);
			ss_paramKey($GLOBALS['timerTimesCalled'],$this->name,0);
			
			$GLOBALS['timerTotalTimes'][$this->name] += ($value-$innerExecution);
			$GLOBALS['timerActualTimes'][$this->name] += $value;
			$GLOBALS['timerTimesCalled'][$this->name]++;
			
			
			return $value;
		}
		
		function reportOverall() {
			
			print("<table>");
			foreach($GLOBALS['timerActualTimes'] as $section => $time) {
				print("<tr>");
				print("<td>$section</td>");
				print("<td>".number_format($GLOBALS['timerActualTimes'][$section], 3, '.', '')."</td>");
				print("<td><b>".number_format($GLOBALS['timerTotalTimes'][$section], 3, '.', '')."</b></td>");
				print("<td>".$GLOBALS['timerTimesCalled'][$section]."</td>");
				print("</tr>");
			}
			print("</table>");
		}
		
	}

	global $timer;
	global $timerTotalTimes;
	global $timerTimesCalled;
	global $timerActualTimes;
	$timer = new Timer();

?>
