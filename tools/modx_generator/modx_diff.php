<?php
/**
 *
 * @package MODX Generator
 * @version $Id: modx_diff.php 158 2009-12-25 16:09:30Z tumba25 $
 * @copyright (c) tumba25
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
 *
 */

/**
 * Parses the diff generated by PEAR::Text_diff
 * $diff = new Text_Diff('native', array($old_file, $new_file));
 * $renderer = new Text_Diff_Renderer_inline();
 * $file_diff = $renderer->render($diff));
 */
class modx_diff
{
	/**
	 * The chars that serparate words.
	 * These will be used for the in-line edits.
	 * Need to figure out what chars should be counted as separators.
	 */
	private $separator = array(
		' ', // Space
		'	', // tab
		'(',
		')',
		'[',
		']',
		'{',
		'}',
		'->',
		'<',
		'>',
		'\'',
		'"',
		',',
		'.',
		';',
		'!',
		'\\',
		'/',
		'=',
		'?',
		'@',
//		'$',
//		'_',
	);
	/**
	 * Runs the diff
	 *
	 * @param $diff, the raw diff from Text_diff.
	 * @param $f, string, the file to diff, will be removed before release.
	 */
	public function parse($diff, $f = '')
	{
		if (!empty($f))
		{
			$this->file = $f;
		}

		// We need a array to parse
		$file_diff = explode("\n", $diff);

		// Send the diff for the first run
		$file_diff = $this->parse_diff_first_pass($file_diff);

		// And the second run
		$file_diff = $this->parse_diff_second_pass($file_diff);

		return($file_diff);
	}

	/**
	 * parse_diff_first_pass
	 * Parse the diff first run
	 *
	 * @param $file_diff, array, the raw diff.
	 * @return $diff_arr, array, contains the parsed diff for the next run.
	 */
	private function parse_diff_first_pass($file_diff)
	{
		// A temporary array for the diff
		$diff_arr = array();

		$end = count($file_diff);
		// The first and last thing in the array is <modx-change> and </modx-change>.
		// Let's remove them, they will only confuse later.
		if (substr($file_diff[0], 0, 13) == '<modx-change>')
		{
			$file_diff[0] = substr($file_diff[0], 13);
		}
		$key = $end - 1;
		$len = strlen($file_diff[$key]);
		if ($len >= 14)
		{
			if (substr($file_diff[$key], $len - 14) == '</modx-change>')
			{
				$file_diff[$key] = substr($file_diff[$key], 0, $len - 14);
			}
		}

		// Let's step trough the array and arrange it to something more handy.
		$cnt = 0;

		for ($key = 0; $key < $end; $key++)
		{
			if (strpos($file_diff[$key], '<modx-change>') !== false)
			{
				// This is a change with possible inline edits. Let's find out.
				// Start by turning it back to a string.
				$str = $file_diff[$key];
				while (strpos($file_diff[$key], '</modx-change>') === false)
				{
					$str .= "\n" . $file_diff[++$key];
					if ($key >= $end)
					{
						break;
					}
				}
				// Strip <modx-change> and </modx-change>
				$str = $this->strip_tag_group($str, 'modx-change');
				// Get the original and changed lines.
				$orig = $this->rem_tag_data($str, 'modx-add');
				$orig = $this->strip_tag_group($orig, 'modx-del');
				$new = $this->rem_tag_data($str, 'modx-del');
				$new = $this->strip_tag_group($new, 'modx-add');

				// Now explode them with the new and old separated.
				$orig_arr = explode("\n", $orig);
				$new_arr = explode("\n", $new);
				$orig_size = sizeof($orig_arr);
				$new_size = sizeof($new_arr);

				if ($orig_size == $new_size)
				{
					// We have equal number of lines. Mostly one.
					for ($i = 0; $i < $orig_size; $i++)
					{
						if (trim($orig_arr[$i]) == trim($new_arr[$i]))
						{
							// We don't care about leading or trailing whitespace changes.
							$diff_arr[$cnt] = $orig_arr[$i];
						}
						else if (substr(trim($orig_arr[$i]), 0, 1) == '*' && substr(trim($new_arr[$i]), 0, 1) == '*' || (substr(trim($orig_arr[$i]), 0, 2) == '//' && substr(trim($new_arr[$i]), 0, 2) == '//'))
						{
							// If the line starts with * or // we don't care about inline stuff. Just replace the whole line.
							// These lines are typically comments or file headers.
							$diff_arr[$cnt]['del'] = $orig_arr[$i];
							$diff_arr[$cnt]['add'] = $new_arr[$i];
							$diff_arr[$cnt]['type'] = EDIT;
						}
						else
						{
							// These are true inlines. Lets save them for the next run.
							$diff_arr[$cnt]['del'] = $orig_arr[$i];
							$diff_arr[$cnt]['add'] = $new_arr[$i];
							$diff_arr[$cnt]['type'] = INLINE;
						}
						$cnt++;
					}
				}
				else if ($new_size && $orig_size && $new_size > $orig_size)
				{

					// Contains both in-line edits and add-after or add-before.
					// Let's start with guessing which are most likely the in-lines.
					$inlines = array();
					$prev_hit = -1;
					for ($i = 0; $i < $orig_size; $i++)
					{
						// The first edit can't be matched with the last if there are more to come.
						$preserved = $new_size - ($orig_size - $i - 1);
						$hit = $top_proc = 0;
						foreach ($new_arr as $line => $value)
						{
							// No need to check preserved lines.
							if ($line >= $preserved)
							{
								break;
							}
							// $precent is the difference between the lines.
							$percent = similar_text($orig_arr[$i], $value);

							// $top_proc contains the highest match for this line so far. So $percent must be higher for $hit to change.
							// $prev_hit contains the last match so the inline edits comes in the right order.
							// $preserved contains the number of lines that must be preserved for the edits to come. The first edit can't match with the last line
							$hit = ($percent > $top_proc && $line > $prev_hit && $line < $preserved) ? $line : $hit;
							// Can't fill $top_proc before the previous hit is passed.
							$top_proc = ($line > $prev_hit) ? (($percent > $top_proc) ? $percent : $top_proc) : 0;
						}

						// If we didn't get any hits on this one let's put it in the last possible place. Don't ask why.
						$hit = (!$hit && !$top_proc) ? $preserved - 1 : $hit;

						$inlines[$i] = $hit;
						$prev_hit = $hit;
					}
					unset($prev_hit, $preserved, $hit, $top_proc, $line, $value, $percent);
					// Got the inlines. Now put them in the right places...
					$j = 0;
					$cnt--;
					$in_edit = false;

					for ($i = 0; $i < $new_size; $i++)
					{
						if (isset($inlines[$j]) && $inlines[$j] == $i)
						{
							$cnt++;
							if (isset($new_arr[$i]) && isset($orig_arr[$j]) && trim($orig_arr[$j]) != trim($new_arr[$i]))
							{
								$diff_arr[$cnt]['del'] = $orig_arr[$j++];
								$diff_arr[$cnt]['add'] = $new_arr[$i];
								$diff_arr[$cnt]['type'] = INLINE;
							}
							else
							{
								$diff_arr[$cnt] = $orig_arr[$j++];
							}
							$in_edit = false;
						}
						else
						{
							if (!$in_edit)
							{
								$cnt++;
								$diff_arr[$cnt]['type'] = EDIT;
								$diff_arr[$cnt]['add'] = '';
								$in_edit = true;
							}
							$diff_arr[$cnt]['add'] .= (($diff_arr[$cnt]['add'] == '') ? '' : "\n") . $new_arr[$i];
						}
					}

					$cnt++;
				}
				else if ($new_size && $orig_size && $new_size < $orig_size)
				{
					// Contains both in-line edits and lines to delete.
					// We need to guess the in-lines.
					$inlines = array();
					$prev_hit = 0;
					for ($i = 0; $i < $new_size; $i++)
					{
						// $orig_arr[] will have more lines than $new_arr[]
						// We need to save space for all in-lines.
						$preserved = $orig_size - ($new_size - $i - 1);
						$hit = $top_proc = 0;
						foreach ($orig_arr as $line => $value)
						{
							// No need to check preserved lines.
							if ($line >= $preserved)
							{
								break;
							}
							$percent = similar_text($new_arr[$i], $value);

							// $top_proc contains the highest match for this line so far. So $percent must be higher for $hit to change.
							// $prev_hit contains the last match so the inline edits comes in the right order.
							// $preserved contains the number of lines that must be preserved for the edits to come. The first edit can't match with the last line
							$hit = ($percent > $top_proc && $line > $prev_hit && $line < $preserved) ? $line : $hit;

							// Can't fill $top_proc before the previous hit is passed.
							$top_proc = ($line > $prev_hit) ? (($percent > $top_proc) ? $percent : $top_proc) : 0;
						}
						$hit = (!$hit && !$top_proc) ? $preserved - 1 : $hit;
						$inlines[$i] = $hit;
						$prev_hit = $hit;
					}
					unset($prev_hit, $preserved, $hit, $top_proc, $line, $value, $percent);
					// Got the inlines. Now put them in the right places...
					$j = 0;
					$cnt--;
					$in_edit = false;
					for ($i = 0; $i < $orig_size; $i++)
					{
						if (isset($inlines[$j]) && $inlines[$j] == $i)
						{
							$cnt++;
							if (isset($new_arr[$j]) && isset($orig_arr[$i]) && trim($orig_arr[$i]) != trim($new_arr[$j]))
							{
								$diff_arr[$cnt]['del'] = $orig_arr[$i];
								$diff_arr[$cnt]['add'] = $new_arr[$j++];
								$diff_arr[$cnt]['type'] = INLINE;
							}
							else
							{
								$diff_arr[$cnt] = $orig_arr[$i];
							}
							$in_edit = false;
						}
						else
						{
							if (!$in_edit)
							{
								$cnt++;
								$diff_arr[$cnt]['type'] = EDIT;
								$diff_arr[$cnt]['del'] = '';
								$in_edit = true;
							}
							$diff_arr[$cnt]['del'] .= (($diff_arr[$cnt]['del'] == '') ? '' : "\n") . $orig_arr[$i];
						}

					}
					$cnt++;
				}
			}

			else if (strpos($file_diff[$key], '<modx-add>') !== false)
			{
				// These are simple adds, just needs to be copied to the diff_arr
				$diff_arr[$cnt] = array();
				$diff_arr[$cnt]['type'] = EDIT;
				$diff_arr[$cnt]['add'] = $file_diff[$key];
				while (strpos($file_diff[$key], '</modx-add>') === false)
				{
					$diff_arr[$cnt]['add'] .= "\n" . $file_diff[++$key];
					if ($key >= $end)
					{
						break;
					}
				}
				// Strip <modx-add> and </modx-add>
				$diff_arr[$cnt]['add'] = $this->strip_tag_group($diff_arr[$cnt]['add'], 'modx-add');
				$cnt++;
			}

			else if (strpos($file_diff[$key], '<modx-del>') !== false)
			{
				// These are deletes. Lines to remove.
				// They usually stops in the in-line section but some might end up here.
				$diff_arr[$cnt] = array();
				$diff_arr[$cnt]['type'] = EDIT;
				$diff_arr[$cnt]['del'] = $file_diff[$key];
				while (strpos($file_diff[$key], '</modx-del>') === false)
				{
					$diff_arr[$cnt]['del'] .= "\n" . $file_diff[++$key];
					if ($key >= $end)
					{
						break;
					}
				}
				// Strip <modx-del> and </modx-del>
				$diff_arr[$cnt]['del'] = $this->strip_tag_group($diff_arr[$cnt]['del'], 'modx-del');
				$cnt++;
			}

			else
			{
				// Unchanged lines. Needed later for the finds.
				$diff_arr[$cnt++] = $file_diff[$key];
			}
		}

		return($diff_arr);
	}

	/**
	 * parse_diff_second_pass
	 * Parse the diff a second time.
	 * @param $file_diff, array containing the diff to parse.
	 * @return $file_diff, array the diff ready to be written.
	 */
	private function parse_diff_second_pass($file_diff)
	{
		$last_change = -1;
		$find = '';

		// Some of the inlines might need to be converted to normal edits.
		$file_diff = $this->check_inlines($file_diff);

		foreach ($file_diff as $num => &$row)
		{
			// Let's make sure this element has not been removed.
			if(!isset($row))
			{
				continue;
			}
			// If there is any changes it's a array. Otherwise a string.
			if (is_array($row) && empty($row['add-type']))
			{
				if (empty($row['add']) && empty($row['del']))
				{
					// Some edits only wants to add or remove a newline. Let's ignore them.
					if (isset($row['add']))
					{
						// Where adding a LF, just remove the element
						unset($file_diff[$num]);
					}
					if (isset($row['del']))
					{
						// Someone wants to remove a newline. We might need it for a find.
						unset($file_diff[$num]);
						$file_diff[$num] = '';
					}
					continue;
				}

				// These are either INLINE or EDIT.
				if ($row['type'] == INLINE)
				{
					// All inlines have both del and add set.
					$this->gen_find($file_diff, $num, $last_change, $row['find'], $row['del']);

					// $num_edits is not used now. There was a plan so I'll leave it for now it the plan returns.
					// $row['changes'] contains all in-line edits for this line.
					$num_edits = $this->gen_inline_find($row['del'], $row['add'], $row['changes']);

					// Need to keep track of the last change for the finds.
					$last_change = $num;
				}
				else if ($row['type'] == EDIT)
				{
					if (!empty($row['add']) && !empty($row['del']))
					{
						// These should only be comments
						// All comments gets replaced. We don't care about inline stuff for them.
						// They need two finds to make sure we replace the right line.
						$this->gen_find($file_diff, $num, $last_change, $row['find'], $row['del']);
						$row['add-type'] = REPLACE;
					}
					else
					{
						// Edits have only add or del set.
						// If this is a del it might just be a move to the other side of a LF
						if (!empty($row['del']))
						{
							// Let's check to be sure.
							$i = $num + 1;
							while (isset($file_diff[$i]) && $file_diff[$i] == '')
							{
								$i++;
							}
							if (!empty($file_diff[$i]['add']) && $file_diff[$i]['add'] == $row['del'])
							{
								// They are identical. Only a LF that's in the wrong place
								// We'll ignore this. Place back the old and remove the new.
								$old_string = $row['del'];
								unset($file_diff[$i], $file_diff[$num]);
								$file_diff[$num] = $old_string;
								continue;
							}
						}

						if (empty($row['add']) && !empty($row['del']))
						{
							// This is a delete. We need to pass the deleted string to gen_find.
							$row['add-type'] = $this->gen_find($file_diff, $num, $last_change, $row['find'], $row['del']);
						}
						else
						{
							$row['add-type'] = $this->gen_find($file_diff, $num, $last_change, $row['find']);
						}
						if ($row['find'][0] == chr(0))
						{
							unset($row['find']);
						}
					}
					$last_change = $num;
				}
				else
				{
					// Huh?
					// Should be imposible to get here. But if somebody succeeds, I want to hear about it.
					echo 'Tell somebody you got here' . "\n";
				}
			}
		}
		unset($num, $row);

		// There might be some deleted keys so lets get them in order again.
		$file_diff = $this->reset_keys($file_diff);

		// Double adds needs to be merged to one.
		$file_diff = $this->merge_addafter($file_diff);

		$file_diff = $this->mark_finds($file_diff);

		return($file_diff);
	}

	/**
	 * merge_adds
	 *
	 * Sometimes when rows are added with just empty lines between them they get
	 * their own add-after with no find in the last one.
	 * That would result in <find> - <add-after> - <add-after>
	 * This function takes care of that.
	 */
	private function merge_addafter($file_diff)
	{
		$temp_arr = array();

		foreach ($file_diff as $key => &$value)
		{
			// If the next $value gets merged to this one it also is unset.
			if (isset($value))
			{
				// This only affect edits. Not in-lines.
				if (is_array($value) && $value['type'] == EDIT && $value['add-type'] == ADD_AFTER)
				{
					// We have a edit that's add-after.
					// There might be some empty lines between. They cant be used as find, but we need to get past them
					$i = $key + 1;
					while (isset($file_diff[$i]) && is_string($file_diff[$i]) && $file_diff[$i] == '')
					{
						$i++;
					}

					// Is the next $value also a edit wiht add-after but no find?
					if (isset($file_diff[$i]) && is_array($file_diff[$i]) && $file_diff[$i]['type'] == EDIT && $file_diff[$i]['add-type'] == ADD_AFTER && !isset($file_diff[$i]['find']))
					{
						// The next $value is a add-after with no find.
						// Lets merge them together with a empty line between.
						$value['add'] .= "\n\n" . $file_diff[$i]['add'];
						$temp_arr[] = $value;
						unset($file_diff[$i]);
					}
					else
					{
						$temp_arr[] = $value;
					}
				}
				else
				{
					$temp_arr[] = $value;
				}
			}
		}
		unset($key, $value);

		return($temp_arr);
	}

	/**
	 * check_inlines
	 *
	 * Converts some in-lines to normal edits.
	 * In-lines resulting in a commet or where both old and new are comments gets converted.
	 * In-lines where old only contains whitespace needs also to be converted.
	 */
	private function check_inlines($file_diff)
	{
		$diff_arr = array();
		$cnt = 0;
		foreach ($file_diff as $key => $row)
		{
			// We'll only check in-lines.
			if (is_array($row) && $row['type'] == INLINE)
			{
				$temp_add = trim($row['add']);
				$temp_del = trim($row['del']);

				// With comments the whole line gets replaced. No inline for them.
				if (substr($temp_add, 0, 1) == '*' || substr($temp_add, 0, 2) == '//')
				{
					$row['type'] = EDIT;
					$diff_arr[$cnt++] = $row;
				}

				// We make in-line edits where the find only contains whitespace to a edit.
				// There will not be anything in the line to in-line find.
				else if ($temp_del == '')
				{
					// We'll put the new line first
					$diff_arr[$cnt]['type'] = EDIT;
					$diff_arr[$cnt]['add'] = $row['add'];
					$cnt++;

					// The old line needs also to be there
					$diff_arr[$cnt++] = $row['del'];
				}
				else
				{
					$diff_arr[$cnt++] = $row;
				}
			}
			else
			{
				$diff_arr[$cnt++] = $row;
			}
			unset($file_diff[$key]);
		}

		return($diff_arr);
	}

	/**
	 * Resets the keys in a array to get rid off missing keys.
	 *
	 * @param $arr, array to reset.
	 */
	private function reset_keys($arr)
	{
		$temp_arr = array();

		$end = sizeof($arr);
		for ($i = 0, $j = 0; $i < $end; $i++)
		{
			if (isset($arr[$i]))
			{
				$temp_arr[$j++] = $arr[$i];
			}
		}
		return($temp_arr);
	}

	/**
	* strip_tags
	*
	* Strips unvanted stuff from fields...
	* <contributions-group></contributions-group>, <copy></copy> and so on.
	* @param $data, string or array, what to remove tags from.
	* @param $tag, string, the tag to remove.
	* @return $data, string, with the tag removed.
	*/
	private function strip_tag_group($data, $tag)
	{
		$data = preg_replace('<<' . $tag . '>>', '', $data);
		$data = preg_replace('<</' . $tag . '>>', '', $data);
		return($data);
	}

	/**
	* rem_tag_data
	*
	* Removes tags and their data
	* @param $data, string or array, what to remove tags and data from.
	* @param $tag, string, the tag to remove.
	* @return $data, string, with the tag and data removed.
	*/
	private function rem_tag_data($data, $tag)
	{
		$data = preg_replace('/<' . $tag . '\b[^>]*>(.*?)<\/' . $tag . '>/s', '', $data);
		return($data);
	}

	/**
	 * gen_find
	 * Generate the finds
	 * @param $file_diff, array, the diff to get the finds from.
	 * @param $num, int, the line number.
	 * @param $last_change, int, the linenumber for the last change, can't use that for our find.
	 * @param $find, array, the array to put the finds in.
	 * @param $inline, string, if this is a inline edit this contains the target line. We'll add it to its own find.
	 * @return $return, int. Is it add-after or add-before (if not in-line)?
	 */
	private function gen_find(&$file_diff, $num, $last_change, &$find, $inline = false)
	{
		$find = array();
		$find[0] = '';
		$rows = $return = 0;

		// $search_before = ($num - $last_change > 1) ? true : false;
		// Should this be a add-after or before.
		$search_before = ($num > 1) ? true : false;

		// If this is a inline and the line can't be mixed with any other line
		// between the last edit and this line, let's go with it.
		if ($inline && $this->is_unique($file_diff, $num, $last_change, $inline, true))
		{
			$find[0] = $inline;
			// Inlines don't care about any return value
			return;
		}

		// With deletes and in-line edits we always need to search befor the edit.
		$search_before = ($inline) ? true : $search_before;

		if ($search_before)
		{
			// This is a add after.
			// Need a temporary array to check if the find is unique.
			$find_arr = array();
			$cnt = 0;
			for ($i = $num - 1; $i > $last_change; $i--)
			{
				if ((isset($file_diff[$i]) && !is_string($file_diff[$i])) || !isset($file_diff[$i]) || $rows > MAX_SEARCH_ROWS - 1)
				{
					break;
				}
				$rows++;

				$find_arr[$cnt++] = $file_diff[$i];

				// Check if we need more in the find.
				if (isset($file_diff[$i]) && $this->is_unique($file_diff, $num, $last_change, $find_arr))
				{
					// If this find is uniqe, let's generate the find and get out of here.
					foreach ($find_arr as $line)
					{
						$find[0] = $line . (($find[0] == '') ? '' : "\n") . $find[0];
					}
					break;
				}
			}
			$return = ADD_AFTER;
		}

		if ($inline)
		{
			// If it's a inline edit we add the line in its own find.
			// That way we can be sure that the find starts in the right line.
			$i = (empty($find[0])) ? 0 : 1;
			$find[$i] = $inline;
			$rows = ($i == 1) ? MAX_SEARCH_ROWS : $rows + 1;
		}

		if (!$search_before) // || ($inline && $rows < MAX_SEARCH_ROWS))
		{
			// A add-before
			for ($i = $num + 1, $end = $num + 4; $i < $end; $i++)
			{
				if ((isset($file_diff[$i]) && !is_string($file_diff[$i])) || !isset($file_diff[$i]) || $rows > MAX_SEARCH_ROWS - 1)
				{
					break;
				}
				$rows++;
				// We don't check for unique finds in add before since they onlu occur at the beginning of files.
				$find[0] .= (($find[0] == '') ? '' : "\n") . $file_diff[$i];
			}
			$return = ADD_BEFORE;
		}

		// Recheck if we got a workable find or only a empty row.
		// Set to 0x00 and it will get remove later.
		if (empty($find[0]))
		{
			$find[0] = chr(0);
		}

		return($return);
	}

	/**
	 * is_unique
	 *
	 * For contextual finds. Don't make the FINDs bigger than they need to be.
	 * Checks if the find is unique between the last change and the line to find.
	 * @param $file_diff, the huge diff array.
	 * @param $num, the postition for the line to find.
	 * @param $last_change, the position for the last change.
	 * @param $find, the string to check if it's unique.
	 * @return bool true if the find is uniqe, otherwise false.
	 */
	private function is_unique($file_diff, $num, $last_change, $find, $inline = false)
	{
		// A inline is a string. And those are easy to check.
		if ($inline)
		{
			for ($i = $last_change + 1; $i < $num; $i++)
			{
				// If $file_diff[$i] is a array, something has gone terribly wrong.
				if (isset($file_diff[$i]) && $find == $file_diff[$i])
				{
					return(false);
				}
			}
		}
		else
		{
			// A array needs more magic.
			$size = sizeof($find);

			$last_change = ($last_change < 0) ? 0 : $last_change + 1;
			// Need to trim and reverse $find.
			foreach ($find as &$line)
			{
				$line = trim($line);
			}
			unset($line);

			// We also need to remove empty lines from the beginning.
			$i = $size - 1;
			while ($i >= 0 && $find[$i] == '')
			{
				unset($find[$i--]);
			}

			$find = array_reverse($find);
			if (empty($find))
			{
				// We can't have finds only containing empty lines.
				return(false);
			}

			// Stop the search when we have FIND lines left. Those should match anyway.
			for ($i = $last_change, $end = $num - $size; $i < $end; $i++)
			{
				// If the first line in $find don't match, there is no need to check the rest.
				if (isset($file_diff[$i]))
				{
					if ($find[0] == trim($file_diff[$i]))
					{
						if ($size == 1)
						{
							// If find is only 1 line, we can return here.
							return(false);
						}

						// If the find contains more than one line we need to check the rest to.
						$match = true;
						$j = $i;
						foreach ($find as $line)
						{
							if ($line != trim($file_diff[$j]))
							{
								$match = false;
								break;
							}
							$j++;
						}

						// If we have a match, let's return telling so.
						if ($match)
						{
							return(false);
						}
					}
				}
			}
		}

		// The find is unique and can be used.
		return(true);
	}

	/**
	 * gen_inline_find
	 * Generate inline finds
	 * @param $del, the original string
	 * @param $add, the string with the new stuff
	 * @param $changes, the to put the changes in. There can be more than one in-line change per line
	 * @return $cnt, int, the number of edits in this line.
	 */
	private function gen_inline_find($del, $add, &$changes)
	{
		// str_split messes up the non English chars.
		$add_arr = $this->split_string($add);
		$del_arr = $this->split_string($del);

		// Let's run a diff and see what we get.
		$row_diff = $this->line_diff($del_arr, $add_arr);
		unset($del_arr, $add_arr);

		$cnt = 0;

		// Need to remember the last find so we don't try to reuse it.
		$last_find = -1;

		// We need to keep track of the last post in the array. We can't use isset() for this.
		$end = sizeof($row_diff);

		foreach ($row_diff as $key => &$value)
		{
			if (is_array($value))
			{
				if (empty($value['del']) && empty($value['add']))
				{
					// Nothing to add or remove.
					unset($value);
					continue;
				}

				// Is this a replace or a add?
				else if (!empty($value['add']) && empty($value['del']))
				{
					// This is a add
					// We want at least 6 chars for the inline find.
					$changes[$cnt]['inline-find'][0] = '';
					if ($key <= 2)
					{
						// The change is in the beginning of the line.
						$i = ($key + 1 > $last_find) ? $key + 1 : $last_find + 1;
						while ($i < $end && !@is_array($row_diff[$i]))
						{
							// $row_diff[$i] might have been removed.
							$changes[$cnt]['inline-find'][0] .= (isset($row_diff[$i])) ? $row_diff[$i] : '';
							$i++;
						}
						$last_find = $i - 1;
						$changes[$cnt]['add-type'] = ADD_BEFORE;
					}
					else
					{
						$i = $key - 1;
						while ($i >= 0 && !@is_array($row_diff[$i]))
						{
							// We have enough finds to do a add after.
							$changes[$cnt]['inline-find'][0] = (isset($row_diff[$i]) && $i > $last_find) ? $row_diff[$i] . $changes[$cnt]['inline-find'][0] : $changes[$cnt]['inline-find'][0];
							$i--;
						}
						$last_find = $key - 1;
						// The string comes in backwards so we need to turn it right.
						$changes[$cnt]['add-type'] = ADD_AFTER;
					}
					$changes[$cnt]['add'] = '';
					foreach ($value['add'] as $char)
					{
						// What to add before or after
						$changes[$cnt]['add'] .= $char;
					}
					$cnt++;
				}
				else
				{
					// This is a replace
					$inline_find = '';
					foreach ($value['del'] as $char)
					{
						$inline_find .= $char;
					}
					$inline_add = '';
					if (isset($value['add']))
					{
						foreach ($value['add'] as $char)
						{
							$inline_add .= $char;
						}
					}

					// In-line replaces needs two finds.
					$i = $key - 1;
					while ($i >= 0 && !@is_array($row_diff[$i]))
					{
						// The replaces are always add after.
						// This find is just to make sure the replace searches for the right string to replace.
						$str = (isset($changes[$cnt]['inline-find'][0])) ? $changes[$cnt]['inline-find'][0] : '';
						$changes[$cnt]['inline-find'][0] = (isset($row_diff[$i]) && $i > $last_find) ? $row_diff[$i] . $str : $str;
						$i--;
					}
					$last_find = $key - 1;

					// We'll ignore the first find if it only contains whitespace
					$i = (trim($changes[$cnt]['inline-find'][0]) == '') ? 0 : 1;
					$changes[$cnt]['inline-find'][$i] = $inline_find;
					$changes[$cnt]['add'] = $inline_add;
					$changes[$cnt]['add-type'] = REPLACE;
					$cnt++;
				}

				// Unset empty finds so mark_finds get a easier job.
				// Can't use empty() here because that removes strings containing only a zero.
				if (isset($changes[$cnt - 1]['inline-find'][0]) && $changes[$cnt - 1]['inline-find'][0] == '')
				{
					unset($changes[$cnt-1]['inline-find']);
				}
			}
		}
		unset($key, $value,$char);

		// Before returning, let's get sure the right in-line edits are closed.
		$changes = $this->mark_finds($changes, true);

		return($cnt);
	}

/**
 * Saved this for now so we can go back to char diff if we want
	private function gen_inline_find($del, $add, &$changes)
	{
		// str_split messes up the non English chars.
		$add_arr = $this->split_string($add);
		$del_arr = $this->split_string($del);

		// Let's run a diff and see what we get.
		$row_diff = $this->line_diff($del_arr, $add_arr);
		unset($del_arr, $add_arr);

		$cnt = 0;

		// Need to remember the last find so we don't try to reuse it.
		$last_find = -1;

		// We need to keep track of the last post in the array. We can't use isset() for this.
		$end = sizeof($row_diff);

		foreach ($row_diff as $key => &$value)
		{
			if (is_array($value))
			{
				if (empty($value['del']) && empty($value['add']))
				{
					// Nothing to add or remove.
					unset($value);
					continue;
				}

				// Is this a replace or a add?
				else if (!empty($value['add']) && empty($value['del']))
				{
					// This is a add
					// We want at least 6 chars for the inline find.
					$changes[$cnt]['inline-find'][0] = '';
					if ($key <= 6)
					{
						// The change is in the beginning of the line.
						$i = ($key + 1 > $last_find) ? $key + 1 : $last_find + 1;
						while ($i < $end && !@is_array($row_diff[$i]))
						{
							// $row_diff[$i] might have been removed.
							$changes[$cnt]['inline-find'][0] .= (isset($row_diff[$i])) ? $row_diff[$i] : '';
							$i++;
						}
						$last_find = $i - 1;
						$changes[$cnt]['add-type'] = ADD_BEFORE;
					}
					else
					{
						$i = $key - 1;
						while ($i >= 0 && !@is_array($row_diff[$i]))
						{
							// We have enough finds to do a add after.
							$changes[$cnt]['inline-find'][0] = (isset($row_diff[$i]) && $i > $last_find) ? $row_diff[$i] . $changes[$cnt]['inline-find'][0] : $changes[$cnt]['inline-find'][0];
							$i--;
						}
						$last_find = $key - 1;
						// The string comes in backwards so we need to turn it right.
						$changes[$cnt]['add-type'] = ADD_AFTER;
					}
					$changes[$cnt]['add'] = '';
					foreach ($value['add'] as $char)
					{
						// What to add before or after
						$changes[$cnt]['add'] .= $char;
					}
					$cnt++;
				}
				else if (!empty($value['add']) && !empty($value['del']))
				{
					// This is a replace
					$inline_find = '';
					foreach ($value['del'] as $char)
					{
						$inline_find .= $char;
					}
					$inline_add = '';
					foreach ($value['add'] as $char)
					{
						$inline_add .= $char;
					}

					$changes[$cnt]['inline-find'][0] = $inline_find;
					$changes[$cnt]['add'] = $inline_add;
					$changes[$cnt]['add-type'] = REPLACE;
					$cnt++;
				}
				else if (empty($value['add']) && !empty($value['del']))
				{
					// This is a delete
					$inline_find = '';
					foreach ($value['del'] as $char)
					{
						$inline_find .= $char;
					}

					$changes[$cnt]['inline-find'][0] = '';
					foreach ($value['del'] as $char)
					{
						$changes[$cnt]['inline-find'][0] .= $char;
					}
					$changes[$cnt]['add'] = '';
					$changes[$cnt]['add-type'] = REPLACE;
					$cnt++;
				}
				// Unset empty finds so mark_finds get a easier job.
				// Can't use empty() here because that removes strings containing only a zero.
				if (isset($changes[$cnt - 1]['inline-find'][0]) && $changes[$cnt - 1]['inline-find'][0] == '')
				{
					unset($changes[$cnt-1]['inline-find'][0]);
				}
			}
		}
		unset($key, $value,$char);

		// Before returning, let's get sure the right in-line edits are closed.
		$changes = $this->mark_finds($changes, true);

		return($cnt);
	}
**/

	/**
	 * split_string
	 * splits a string to a array. str_split messes up multibyte chars.
	 * @param $str, string to split
	 * @return $arr, the array
	 */
	private function split_string($str)
	{
		$arr = array();

		$in_word = false;
		$cnt = -1;

		for ($i = 0, $end = strlen($str); $i < $end; $i++)
		{
			$char = substr($str, $i, 1);
			if (in_array($char, $this->separator))
			{
				// This is a separator char.
				$cnt++;
				$in_word = false;
				$arr[$cnt] = $char;
			}
			else
			{
				// Here we build words.
				if ($in_word)
				{
					$arr[$cnt] .= $char;
				}
				else
				{
					$cnt++;
					$in_word = true;
					$arr[$cnt] = $char;
				}
			}
		}

/**
 * Saved for the char diff.
		for ($i = 0, $end = strlen($str); $i < $end; $i++)
		{
			$arr[] = substr($str, $i, 1);
		}
**/

		return($arr);
	}

	/**
	 * Generate the inline diffs.
	 * From http://compsci.ca/v3/viewtopic.php?p=142539 slightly modified.
	 * @param $old, the original array
	 * @param $new, the modified array
	 * @return array with the diff
	 */
	function line_diff($old, $new)
	{
		$maxlen = 0;
		foreach ($old as $oindex => $ovalue)
		{
			$nkeys = array_keys($new, $ovalue);
			foreach ($nkeys as $nindex)
			{
				$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ? $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
				if ($matrix[$oindex][$nindex] > $maxlen)
				{
					$maxlen = $matrix[$oindex][$nindex];
					$omax = $oindex + 1 - $maxlen;
					$nmax = $nindex + 1 - $maxlen;
				}
			}
		}
		if ($maxlen == 0)
		{
			return array(array('del'=>$old, 'add'=>$new));
		}

		return array_merge($this->line_diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
			array_slice($new, $nmax, $maxlen),
			$this->line_diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
	}

	/**
	 * mark_finds
	 * Check if the next edit has a find or not.
	 * This is needed to know if the edit tag should be closed or not.
	 * Steps backwards trough the array and sets 'close' if the next change has a find in it.
	 * If not, the edit tag should not be closed.
	 *
	 * @param $diff_arr, The well known diff
	 * @return $diff_arr. The same array with the
	 */
	private function mark_finds($diff_arr, $inline = false)
	{
		// The last change always needs ot have close on.
		$close = true;
		// Step backwards trough the array
		$find = ($inline) ? 'inline-find' : 'find';
		for ($i = sizeof($diff_arr) - 1; $i > -1; $i--)
		{
			if (is_array($diff_arr[$i]))
			{
				if ($close)
				{
					$diff_arr[$i]['close'] = true;
				}

				// Now to check if this edit has any finds or not.
				if (isset($diff_arr[$i][$find]))
				{
					// This element has a find so the previous change should close its edit.
					$close = true;
				}
				else
				{
					// This element has not a find so the previous change should leave the edit open.
					$close = false;
				}
			}
		}

		return($diff_arr);
	}

}
?>