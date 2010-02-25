<?php
/**
 *
 *===================================================================
 *
 *  MOD Author Welcome Package - Readme File Generator
 *-------------------------------------------------------------------
 *	Script info:
 * SVN ID:		$Id$
 * Copyright:	(c) 2010 -- Obsidian
 * License:		http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 * Package:		welcome_package
 *
 *===================================================================
 *
 */

// This is all that we will actually run.  Simple, is it not?  :D
/**
 * @ignore
 */
require './generator_config.php';
readme_config::init();
$readme = new readme();
file_put_contents(readme_config::$readme_file_path . 'readme.html', $readme);
echo 'Done.' . PHP_EOL;
exit;

/**
 * =========================================================================
 * DO NOT ALTER ANYTHING BEYOND THIS POINT UNLESS ABSOLUTELY NECESSARY
 * =========================================================================
 */

/**
 * These functions parse input from the txt files and output the HTML.
 */

/**
 * Parses an input txt file for docs
 * @param string $filename - The filename to parse
 * @return string - fauxcode->html parsed string pulled from the txt file
 */
function parse($filename)
{
	if(!file_exists('./txt/' . $filename))
	{
		echo 'File /txt/' . $filename . ' does not exist' . PHP_EOL . 'Generator failed.' . PHP_EOL;
		exit;
	}

	// Load the file
	$text = file_get_contents('./txt/' . $filename);

	// Build our array of PCRE patterns and their replacements
	$fauxcode = array(
		// bold
		"#\[b\](.*?)\[/b\]#is" => '<span style="font-weight: bold">$1</span>',
		// italic
		"#\[i\](.*?)\[/i\]#is" => '<span style="font-style: italic">$1</span>',
		// underline
		"#\[u\](.*?)\[/u\]#is" => '<span style="text-decoration: underline">$1</span>',
		// color
		'#\[color\=(.*?)\](.*?)\[/color\]#is' => '<span style="color: $1">$2</span>',
		// link with custom title
		'#\[url\=(.*?)\](.*?)\[/url\]#is' => '<a href="$1" title="$2" class="postlink">$2</a>',
		// link
		"#\[url\](.*?)\[/url\]#is" => '<a href="$1" title="$1" class="postlink">$1</a>',
		// image
		"#\[img\](.*?)\[/img\]#is" => '<img src="$0" alt="Image" />',
		// code
		"#\[code\](.*?)\[/code\]#is" => '<div class="codebox"><pre>$1</pre></div>',
		// header
		"#\[h\](.*?)\[/h\]#is" => '<h3>$1</h3>',
		// subheader
		"#\[hh\](.*?)\[/hh\]#is" => '<h4>$1</h4>',
		// warning
		"#\[warning\](.*?)\[/warning\]#is" => '<br /><div class="info-warning"><div class="warn-label">Warning!</div><div class="warn-text">$1</div></div><div class="clear"></div>',
		// info box
		"#\[info\](.*?)\[/info\]#is" => '<div class="info"><div class="info-inner"><strong>Information</strong>: &nbsp; &nbsp; $1</div></div><br />'
	);

	return preg_replace(array_keys($fauxcode), array_values($fauxcode), $text);
}

/**
 * The class that generates the readme file.
 */
class readme
{
	// Revision number for this document
	protected $revision = '$Id$';

	protected $data = '';

	// Here we build the HTML page.
	// Get ready for some work.
	public function __construct()
	{
		// This is what we will return.  Append all data to this.
		// Build the header area.
		$this->append('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en" xml:lang="en">
			<head>
			<meta http-equiv="content-type" content="text/html; charset=utf-8" />
			<meta http-equiv="content-style-type" content="text/css" />
			<meta http-equiv="content-language" content="en" />
			<meta http-equiv="imagetoolbar" content="no" />
			<meta name="resource-type" content="document" />
			<meta name="distribution" content="global" />
			<meta name="copyright" content="' . readme_config::$meta_info['copyright'] . '" />
			<meta name="keywords" content="' . readme_config::$meta_info['keywords'] . '" />
			<meta name="description" content="' . readme_config::$meta_info['description'] . '" />
			' . ((readme_config::$meta_info['emulate_ie7']) ? '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />' : '') . '
			<title>' . readme_config::$page_title . '</title>
			<link href="' . readme_config::$style_root_path . 'stylesheet.css" rel="stylesheet" type="text/css" media="screen, projection" />
			<!-- $Id$ -->
			</head>
			<body id="phpbb" class="section-docs">
			<div id="wrap">
				<a id="top" name="top" accesskey="t"></a>
				<div id="page-header">
					<div class="headerbar">
						<div class="inner"><span class="corners-top"><span></span></span>
						<div id="doc-description">
							<a href="#" id="logo"><img src="' . readme_config::$style_root_path . 'site_logo.gif" alt="" /></a>
							<h1>' . readme_config::$page_title . '</h1>
							<p>' . readme_config::$page_desc . '</p>
							<p style="display: none;"><a href="#start_here">Skip</a></p>
						</div>
						<span class="corners-bottom"><span></span></span></div>
					</div>
				</div>
				<a name="start_here"></a>
				<div id="page-body">
			<!-- BEGIN DOCUMENT -->');

		// Let's take a breather...

		$this->append('<p>' . readme_config::$intro . '</p>
			<h1>' . readme_config::$page_subtitle . '</h1>
			<div class="paragraph menu">
				<div class="inner"><span class="corners-top"><span></span></span>
				<div class="content">
					<ul>');

		// Build the list of topics that will be discussed
		foreach(readme_config::$main_data as $key => $row)
		{
			$this->append("<li><a href='#" . md5($row['section_title'] . $key) . "'>{$row['section_title']}</a></li>");
		}

		$this->append('</ul>
				</div>
				<span class="corners-bottom"><span></span></span></div>
			</div>');

		foreach(readme_config::$main_data as $key => $row)
		{
			$this->append("<br /><hr />
				<a name='" . md5($row['section_title'] . $key) . "'></a><h2>{$row['section_title']}</h2>");
			foreach($row['contents'] as $c_row)
			{
				$this->append('<div class="paragraph">
					<div class="inner"><span class="corners-top"><span></span></span>
						<div class="content post" ' . ((readme_config::$disable_author_mode == true || $c_row['author'] === NULL) ? 'style="width: 100%;" ' : '') . '>');
				$this->append(str_replace("\n", "<br />\n", $c_row['content']), true);
				$this->append('</div>');
				if(readme_config::$disable_author_mode != true && $c_row['author'] !== NULL)
				{
					$this->append('<dl class="postprofile">
						<dt>
							<a href="' . readme_config::$author_info[$c_row['author']]['phpbb_com'] . '"><img src="' . readme_config::$image_root_path . readme_config::$author_info[$c_row['author']]['avatar'] . '" width="' . readme_config::$author_info[$c_row['author']]['avatar_wid'] . '" height="' . readme_config::$author_info[$c_row['author']]['avatar_hei'] . '" alt="User avatar" /></a><br />
							<a href="' . readme_config::$author_info[$c_row['author']]['phpbb_com'] . '" ' . ((readme_config::$author_info[$c_row['author']]['color']) ? 'style="color: ' . readme_config::$author_info[$c_row['author']]['color'] . ';" class="username-coloured"' : '') . '>' . readme_config::$author_info[$c_row['author']]['name'] . '</a>
						</dt>
						<dd>' . readme_config::$author_info[$c_row['author']]['rank'] . '</dd>
					</dl>');
				}
				$this->append('<div class="back2top"><a href="#wrap" class="top">Back to Top</a></div>
					<span class="corners-bottom"><span></span></span></div>
				</div>');
			}
		}

		$this->append('<!-- END DOCUMENT -->
					<div id="page-footer">
						<div class="version">' . readme_config::$footer . ' <br />Readme: $' . 'Id$' . ' | Generator: &#36;' . substr($this->revision, 1) . ' </div>
					</div>
				</div></div>
				<div>
					<a id="bottom" name="bottom" accesskey="z"></a>
				</div>
			</body>
			</html>');
	}

	// We output the HTML now.
	public function __toString()
	{
		return $this->data;
	}

	private function append($data, $tabsafe = false)
	{
		$data = explode("\n", $data);
		foreach($data as $row)
		{
			$this->data .= ((!$tabsafe) ? trim($row) : $row) . "\n";
		}
	}
}
