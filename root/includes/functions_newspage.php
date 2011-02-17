<?php
/**
*
* @package - NV newspage
* @copyright (c) nickvergessen http://www.flying-bits.org/
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* Trims a bbcode text to a given length.
* If it does not contain any bbcodes we make a little short cut,
* else we fall back to some Board3-Portal-Code.
*/
function newspage_trim_bbcode_text($message, $bbcode_uid, $length)
{
	if (utf8_strlen($message) < ($length + 25))
	{
		return $message;
	}

	if (!$bbcode_uid)
	{
		return utf8_substr($message, 0, $length) . '...';
	}
	return get_sub_taged_string($message, $bbcode_uid, $length);
}

/**
* The Code below is copied from Board3 Portal v1.0.6
*
* I don't really know, how this code works, but it does work.
* It trims text to a given length and closes the bbcodes correctly.
* I just added utf8-support by replacing strlen and substr with their utf8_* substitutes.
*
* @author avaren aka. ice http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=389575
*/

function get_sub_taged_string($str, $bbuid, $maxlen)
{
	$sl = $str;
	$ret = '';
	$ntext = '';
	$lret = '';
	$i = 0;
	$cnt = $maxlen;
	$last = '';
	$arr = array();

	while((utf8_strlen($ntext) < $cnt) && (utf8_strlen($sl) > 0))
	{
		$sr = '';
		if (utf8_substr($sl, 0, 1) == '[')
		{
			$sr = utf8_substr($sl,0,utf8_strpos($sl,']')+1);
		}
		/* GESCHLOSSENE HTML-TAGS BEACHTEN */
		if (utf8_substr($sl, 0, 2) == '<!')
		{
			$sr = get_next_bbhtml_part($sl);
			$ret .= $sr;
		} 
		else if (utf8_substr($sl, 0, 1) == '<')
		{
			$sr = utf8_substr($sl,0,utf8_strpos($sl,'>')+1);
			$ret .= $sr;
		}
		else if (is_valid_bbtag($sr, $bbuid))
		{
			if ($sr[1] == '/')
			{
				/* entfernt das endtag aus dem tag array */
				$tarr = array();
				$j = 0;
				foreach ($arr as $elem)
				{
					if (strcmp($elem[1],$sr) != 0) 
					{
						$tarr[$j++] = $elem;
					}
				}
				$arr = $tarr;
			}
			else
			{
				$arr[$i][0] = $sr;
				$arr[$i++][1] = get_end_bbtag($sr, $bbuid);
			} 
			$ret .= $sr;
		}
		else
		{
			$sr = get_next_word($sl);
			$ret .= $sr;
			$ntext .= $sr;
			$last = $sr;
		}
		$sl = utf8_substr($sl, utf8_strlen($sr), utf8_strlen($sl)-utf8_strlen($sr));
	}
	
	$ap = '';

	foreach ($arr as $elem)
	{
		$ap = $elem[1] . $ap;
	}

	$ret .= $ap;
	$ret = trim($ret);
	if(utf8_substr($ret, -4) == '<!--')
	{
		$ret .= ' -->';
	}
	$ret = add_endtag($ret);
	$ret = $ret . '...';
	return $ret;
}


function get_next_bbhtml_part($str)
{
	$lim =  utf8_substr($str,0,utf8_strpos($str,'>')+1);
	return utf8_substr($str,0,utf8_strpos($str, $lim, utf8_strlen($lim))+utf8_strlen($lim));
}

// Don't let them mess up the complete portal layout in cut messages and do some real AP magic
function is_valid_bbtag($str, $bbuid)
{
	return (utf8_substr($str,0,1) == '[') && (utf8_strpos($str, ':'.$bbuid.']') > 0);
}

function get_end_bbtag($tag, $bbuid)
{
	$etag = '';
	for($i=0;$i<utf8_strlen($tag);$i++)
	{
		if ($tag[$i] == '[') 
		{
			$etag .= $tag[$i] . '/';
		}
		else if (($tag[$i] == '=') || ($tag[$i] == ':'))
		{
			if ($tag[1] == '*')
			{
				$etag .= ':m:'.$bbuid.']';
			}
			else if (utf8_substr($tag, 0, 6) == '[list=')
			{
				$etag .= ':o:'.$bbuid.']';
			}
			else if (utf8_substr($tag, 0, 5) == '[list')
			{
				$etag .= ':u:'.$bbuid.']';
			}
			else 
			{
				$etag .= ':'.$bbuid.']';
			}
			break;
		} 
		else 
		{
			$etag .= $tag[$i];
		}
	}
	return $etag;
}

function get_next_word($str)
{
	$ret = '';
	for($i=0;$i<utf8_strlen($str);$i++)
	{
		switch ($str[$i])
		{
			case ' ': //$ret .= ' '; break; break;
				return $ret . ' ';
			case '\\': 
				if ($str[$i+1] == 'n') return $ret . '\n';
			case '[': if ($i != 0) return $ret;
			default: $ret .= $str[$i];
		}    
	}
	return $ret;
}


/**
* check for invalid link tag at the end of a cut string
*/
function add_endtag ($message = '')
{
	$check = (int) strripos($message, '<!-- m --><a ');
	$check_2 = (int) strripos($message, '</a><!--');
	
	if(((isset($check) && $check > 0) && ($check_2 <= $check)) || ((isset($check) && $check > 0) && !isset($check_2)))
	{
		$message .= '</a><!-- m -->';
	}
	
	return $message;
}
