<?php
	$text = stripslashes($_POST['content']);
	$temptext = tempnam('/tmp', 'spell_');
	if ((!isset($_POST['dictionary'])) || (strlen(trim($_POST['dictionary'])) < 1))
	{
	    $lang = 'en_NZ';
	} //if
	else
	{
	    $lang = $_POST['dictionary'];
	} //else
	$aspellcommand = 'cat '.$temptext.' | /usr/local/bin/aspell -a --mode=none --add-filter=sgml --lang='.$lang.' --rem-sgml-check=alt';
	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="all" href="spell-check-style.css" />
</head>
<body onload="window.parent.finishedSpellChecking();">';

if (trim($text) != "")
{
    if ($fd = fopen($temptext, 'w'))
    {
        $textarray = explode("\n", $text);
        fwrite ($fd, "!\n");
        foreach ($textarray as $key=>$value)
        {
            // adding the carat to each line prevents the use of aspell commands within the text...
            fwrite($fd, "^$value\n");
        }
        fclose($fd);
        
        // next run aspell
        $return = shell_exec($aspellcommand);
		unlink($temptext);
        $returnarray = explode("\n", $return);
        $returnlines = count($returnarray);
//print_r(htmlentities($return));
        $textlines = count($textarray);
        
        $lineindex = -1;
        $poscorrect = 0;
        $counter = 0;
        foreach ($returnarray as $key=>$value)
        {
            // if there is a correction here, processes it, else move the $textarray pointer to the next line
            if (substr($value, 0, 1) == '&')
            {
                $correction = explode(' ', $value);
                $word = $correction[1];
                $absposition = substr($correction[3], 0, -1) - 1;
                $position = $absposition + $poscorrect;
                $niceposition = $lineindex.','.$absposition;
                $suggstart = strpos($value, ':') + 2;
                $suggestions = substr($value, $suggstart);
                $suggestionarray = explode(', ', $suggestions);

                $suggestion_tt = '<span class="HA-spellcheck-suggestions">';

                foreach ($suggestionarray as $key=>$value)
                {
                    $suggestion_tt .= $value.',';
                }
                $suggestion_tt = substr($suggestion_tt, 0, strlen($suggestion_tt) - 1).'</span>';
                $beforeword = substr($textarray[$lineindex], 0, $position);
                $afterword = substr($textarray[$lineindex], $position + strlen($word));
                $textarray[$lineindex] = $beforeword.'<span class="HA-spellcheck-error">'.$word.'</span>'.$suggestion_tt.$afterword;
                $poscorrect = $poscorrect + strlen($suggestion_tt) + 41;
            }
            elseif (substr($value, 0, 1) == '#')
            {
                $correction = explode(' ', $value);
                $word = $correction[1];
                $absposition = $correction[2] - 1;
                $position = $absposition + $poscorrect;
                $niceposition = $lineindex.','.$absposition;
                $beforeword = substr($textarray[$lineindex], 0, $position);
                $afterword = substr($textarray[$lineindex], $position + strlen($word));
                $textarray[$lineindex] = $beforeword.$word.$afterword;
                $textarray[$lineindex] = $beforeword.'<span class="HA-spellcheck-error">'.$word.'</span><span class="HA-spellcheck-suggestions">'.$word.'</span>'.$afterword;
//                $poscorrect = $poscorrect;
                $poscorrect = $poscorrect + 88 + strlen($word);
            }
            else
            {
                //print "Done with line $lineindex, next line...<br><br>";
                $poscorrect = 0;
                $lineindex = $lineindex + 1;
            }
         }
     }
}
foreach ($textarray as $key=>$value)
{
	echo $value;
}

echo '<div id="HA-spellcheck-dictionaries">en_NZ,en_US,es,fr</div></body></html>';
?> 