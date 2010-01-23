<?php
/**
 *
 *===================================================================
 *
 *  MOD Author Welcome Package - Readme File Generator
 *-------------------------------------------------------------------
 *	Script info:
 * Copyright:	(c) 2010 -- Obsidian
 * License:		http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 * Package:		welcome_package
 *
 *===================================================================
 *
 */

// This is all that we will actually run.  Simple, is it not?  :D
readme_config::init();
$readme = new readme();
file_put_contents(readme_config::README_FILE_PATH . 'readme.html', $readme);
echo 'Done.' . PHP_EOL;
exit;

/**
 * Readme configuration class
 * Edit this to alter the contents of the readme file that is generated
 */
class readme_config
{
	// Ye olde page title.
	public static $page_title = '';
	
	// And a description of the page
	public static $page_desc = '';
	
	// Hell, let's do a subtitle.
	public static $page_subtitle = '';
	
	// Author's information.
	// Should be an array containing elements "name", "avatar", and "rank".  Leave stuff blank if you don't want it to appear.
	public static $author_info = array();
	
	// Stuff that we...stuff...into the meta tags.
	public static $meta_info = array();
	
	// Intro message
	public static $intro = '';
	
	// Idiot warnings.  It's not mah fault you broke your shiz.
	public static $disclaimer = '';

	// And to the meat of the matter...
	public static $main_data = array();
	
	// Blah blah blah...stuff at the bottom
	public static $footer = '';

	// Root paths for style stuff and images
	const STYLE_ROOT_PATH = 'html/style/';
	const IMAGE_ROOT_PATH = 'html/images/';
	
	// The location of the readme file that we'll be dumping to
	const README_FILE_PATH = './../';

	// If we want this to be cold and impersonal, set to true.
	const DISABLE_AUTHOR_MODE = false;
	
	public static function init()
	{
		self::$page_title = 'phpBB3 MOD Author Welcome Package';
		self::$page_desc = 'How to get started building phpBB MODs';
		self::$page_subtitle = 'Building phpBB3 MODs';
		self::$author_info = array(
			'name' 			=> 'Obsidian',
			'avatar'		=> 'style/xkcd_avvy.png',
			'rank'			=> 'Jr. MOD Validator',
		);
		self::$meta_info = array(
			'copyright'		=> '2010 Obsidian',
			'keywords'		=> '',
			'description'	=> 'phpBB 3.0.x MOD Author Welcome Package',
			'emulate_ie7'	=> true, // Compatibility mode for that stupid browser that Microsoft makes
		);
		self::$intro = 'Hello there, and welcome to the phpBB MOD Author Welcome Package.  I am Obsidian, and I have been working with modifying phpBB for about two years now.  I`m writing this to help get you started with learning to build MODs for phpBB 3.0.x, and to provide you with the resources and tools you need to succeed.';
		self::$disclaimer = '';
		self::$main_data = array(
			array(
				'section_title'		=> 'test',
				'unique_name'		=> 'test',
				'contents'			=> array(
'This is just a test.

' . readme_html::code('MD5

	' . md5('test')),
				),
			),
	
		);
		self::$footer = 'MOD Author Welcome Package &copy; 2010 ' . readme_html::bold('Obsidian');
	}
}

/**
 * =========================================================================
 * DO NOT ALTER ANYTHING BEYOND THIS POINT UNLESS NECESSARY
 * =========================================================================
 */

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
	public function __toString()
	{
		// This is what we will return.  Append all data to this.
		// Build the header area.
		$return .= $this->append('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
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
			<link href="' . readme_config::STYLE_ROOT_PATH . 'stylesheet.css" rel="stylesheet" type="text/css" media="screen, projection" />
			</head>
			<body id="phpbb" class="section-docs">
			<div id="wrap">
				<a id="top" name="top" accesskey="t"></a>
				<div id="page-header">
					<div class="headerbar">
						<div class="inner"><span class="corners-top"><span></span></span>
						<div id="doc-description">
							<a href="#" id="logo"><img src="' . readme_config::STYLE_ROOT_PATH . 'site_logo.gif" alt="" /></a>
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
			
		$return .= $this->append('<p>' . readme_config::$intro . '</p>
			<h1>' . readme_config::$page_subtitle . '</h1>
			<div class="paragraph menu">
				<div class="inner"><span class="corners-top"><span></span></span>
				<div class="content">
					<ul>');

		// Build the list of topics that will be discussed
		foreach(readme_config::$main_data as $row)
		{
			$return .= $this->append("<li><a href=\"{$row['unique_name']}\">{$row['section_title']}</a></li>");
		}

		$return .= $this->append('</ul>
				</div>
				<span class="corners-bottom"><span></span></span></div>
			</div>');

		foreach(readme_config::$main_data as $row)
		{
			$return .= $this->append("<hr />
				<a name=\"{$row['unique_name']}\"></a><h2>{$row['section_title']}</h2>" . '
				<div class="paragraph">
					<div class="inner"><span class="corners-top"><span></span></span>
						<div class="content">') . "\n";
			foreach($row['contents'] as $content)
			{
				$return .= $this->append('<p>' . str_replace("\n", "<br />\n", $content) . '</p>', true);
			}
			$return .= $this->append('</div>
				<div class="back2top"><a href="#wrap" class="top">Back to Top</a></div>
				<span class="corners-bottom"><span></span></span></div>
			</div>');
		}

		$return .= $this->append('<!-- END DOCUMENT -->
					<div id="page-footer">
						<div class="version">' . readme_config::$footer . ' | ' . $this->revision . ' </div>
					</div>
				</div></div>
				<div>
					<a id="bottom" name="bottom" accesskey="z"></a>
				</div>
			</body>
			</html>');
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

// Something made to simplify code up within the readme_config class.
class readme_html
{
	public static function __callStatic($name, $arguments)
	{
		switch(strtolower($name))
		{
			case 'code':
				return "<div class=\"codebox\"><pre>{$arguments[0]}</pre></div>";
			break;
	
			case 'bold':
				return "<span style=\"font-weight: bold\">{$arguments[0]}</span>";
			break;
	
			case 'italic':
				return "<span style=\"font-style: italic\">{$arguments[0]}</span>";
			break;
	
			case 'underline':
				return "<span style=\"text-decoration: underline\">{$arguments[0]}</span>";
			break;
	
			case 'color':
				return "<span style=\"color: {$arguments[1]}\">{$arguments[0]}</span>";
			break;
	
			case 'link':
				return "<a href=\"{$arguments[1]}\" class=\"postlink\">{$arguments[0]}</a>";
			break;
	
			case 'image':
				return "<img src=\"{$arguments[1]}\" alt=\"{$arguments[0]}\" title=\"{$arguments[0]}\" />";
			break;

			case 'header':
				return "<h4>{$arguments[0]}</h4>";
			break;

			default:
				return "<!-- unsupported HTML type \"{$name}\" -->";
			break;
		}
	}
}
?>