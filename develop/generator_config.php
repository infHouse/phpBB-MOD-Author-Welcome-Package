<?php

/**
 * Readme configuration class
 * Edit this to alter the contents of the readme file that is generated
 */
class readme_config
{
	/**
	 * @var string - The page's title
	 */
	public static $page_title = '';

	/**
	 * @var string - A description of the page.
	 */
	public static $page_desc = '';

	/**
	 * @var string - A subtitle for the page if we want one.
	 */
	public static $page_subtitle = '';

	/**
	 * @var array - Array of author's information.  See below for the format.  (anything you don't want to appear, leave empty/blank/0/null)
	 */
	public static $author_info = array();

	/**
	 * @ignore
	 *
	 * 	self::$author_info = array(
	 * 		// Here we assign an author their ID, and begin defining their information
	 * 		1 => array(
	 *			// Your username goes here.
	 *			'name' 			=> 'username',
	 *			// A rank color, if you have one
	 *			'color'			=> '',
	 *			// A link to your phpBB.com profile, if you wish to link to it.
	 *			'phpbb_com'		=> 'http://www.phpbb.com/community/memberlist.php?mode=viewprofile&amp;u=480595',
	 *			// The location of your avatar, if you have one.  Note that the var readme_config::$image_root_path will automatically be appended.
	 *			'avatar'		=> 'xkcd_avvy.png',
	 *			// The width of your avatar, if you have one.
	 *			'avatar_wid'	=> 100,
	 *			// The height of your avatar, if you have one.
	 *			'avatar_hei'	=> 100,
	 *			// A rank title, if you have one.
	 *			'rank'			=> 'Jr. MOD Validator',
	 *		),
	 *		// You can repeat the previous array as many times as necessary.
	 *	);
	 *
	 */

	/**
	 * @var array - Stuff that we want put into the page's meta tags.
	 */
	public static $meta_info = array();

	/**
	 * @var string - The intro message for this page.
	 */
	public static $intro = '';

	/**
	 * @var string - A disclaimer for your readme file, just in case you want one.
	 */
	public static $disclaimer = '';

	/**
	 * @var array - The meat of the matter; the content of the readme.  Format is below.
	 */
	public static $main_data = array();

	/**
	 * @ignore
	 *
	 * 	self::$main_data = array(
	 * 		// Here we begin defining a section.
	 * 		array(
	 * 			// This is the title of the section. It is often best to make this unique from every other section, but not required.
	 *			'section_title'		=> 'A Random Test Section',
	 *			// Here we define the contents of the section
	 *			'contents'			=> array(
	 *				// Here is a chunk of content that will appear on the readme.
	 *				array(
	 *					// This is the actual content.  Content should be stored in a fauxcode-formatted text file in the develop/txt/ directory, and end with the .txt file extension.
	 *					'content' 		=> parse('some_txt_file.txt'),
	 * 					// This is who will appear as the "author" of the content.  The ID number used should be that of one assigned previously in readme_config::$author_info.  If you want a specific section to appear to have no author, use NULL instead of an author ID.
	 *					'author'		=> 1,
	 *				),
	 *				// You can repeat the previous array as many times as necessary.
	 *			),
	 *		),
	 *		// You can repeat the previous array as many times as necessary.
	 * 	);
	 *
	 */

	/**
	 * @var string - The footer for the page
	 */
	public static $footer = '';

	/**
	 * @var string - Root path for style/CSS related HTML includes
	 */
	public static $style_root_path = '';

	/**
	 * @var string - Root path for included images, such as avatars and screenshots.
	 */
	public static $image_root_path = '';

	/**
	 * @var string - The location of the readme file that we'll be dumping the generated content to
	 */
	public static $readme_file_path = '';

	/**
	 * @var boolean - Should we forcibly disable the display of authors for the entire readme?
	 */
	public static $disable_author_mode = false;

	public static function init()
	{
		// We'll set our various filepaths first.
		self::$readme_file_path = './../';
		self::$image_root_path = 'html/images/';
		self::$style_root_path = 'html/style/';

		// Now, for titles, descriptions, and that stuff.
		self::$page_title = 'phpBB3 MOD Author Welcome Package';
		self::$page_desc = 'Open Source at its finest';
		self::$page_subtitle = 'Building phpBB3 MODs';
		self::$meta_info = array(
			'copyright'		=> '2010 Obsidian',
			'keywords'		=> '',
			'description'	=> 'phpBB 3.0.x MOD Author Welcome Package',
			'emulate_ie7'	=> true, // Compatibility mode for that stupid browser that Microsoft makes
		);

		// The information about the author(s), and the ability to not need info about them.
		self::$disable_author_mode = false;
		self::$author_info = array(
			1 => array(
				'name' 			=> 'Obsidian',
				'color'			=> '',
				'phpbb_com'		=> 'http://www.phpbb.com/community/memberlist.php?mode=viewprofile&amp;u=480595',
				'avatar'		=> 'xkcd_avvy.png',
				'avatar_wid'	=> 100,
				'avatar_hei'	=> 100,
				'rank'			=> 'MOD Author',
			),
			2 => array(
				'name' 			=> 'SyntaxError90',
				'color'			=> '#660099',
				'phpbb_com'		=> 'http://www.phpbb.com/community/memberlist.php?mode=viewprofile&amp;u=873955',
				'avatar'		=> 'engie_hat.png',
				'avatar_wid'	=> 100,
				'avatar_hei'	=> 100,
				'rank'			=> 'MOD Team Member',
			),
		);

		// Some basic content.
		self::$intro = 'Hey there, and welcome to the phpBB MOD Author Welcome Package. <br />This guide is intended to help you become a MOD author; within, you will learn how to modify phpBB to suit your needs and the needs of others, and how to do it <em>right</em>.';
		self::$disclaimer = '';
		self::$footer = 'MOD Author Welcome Package &copy; 2010 ' . '<strong>Obsidian</strong>';

		// The CONTENT.
		self::$main_data = array(
			array(
				'section_title'		=> 'Introduction',
				'contents'			=> array(
					array(
						'content' 		=> parse('obsidian_intro.txt'),
						'author'		=> 1,
					),
					array(
						'content' 		=> parse('sam_intro.txt'),
						'author'		=> 2,
					),
				),
			),
			array(
				'section_title'		=> 'Coding for phpBB: What you will need',
				'contents'			=> array(
					array(
						'content' 		=> parse('needs.txt'),
						'author'		=> 1,
					),
				),
			),
			array(
				'section_title'		=> 'What is included within the Welcome Package',
				'contents'			=> array(
					array(
						'content' 		=> parse('included_overview.txt'),
						'author'		=> 1,
					),
					array(
						'content' 		=> parse('included_automod.txt'),
						'author'		=> NULL,
					),
					array(
						'content' 		=> parse('included_umil.txt'),
						'author'		=> NULL,
					),
					array(
						'content' 		=> parse('included_modx.txt'),
						'author'		=> NULL,
					),
				),
			),
			array(
				'section_title'		=> 'Coding requirements',
				'contents'			=> array(
					array(
						'content' 		=> 'blah',
						'author'		=> 2,
					),
				),
			),
			array(
				'section_title'		=> 'Coding resources and links',
				'contents'			=> array(
					array(
						'content' 		=> 'blah',
						'author'		=> 1,
					),
				),
			),
			array(
				'section_title'		=> 'Desired coding habits with phpBB',
				'contents'			=> array(
					array(
						'content' 		=> 'blah',
						'author'		=> 2,
					),
				),
			),
			array(
				'section_title'		=> 'Building larger MODs: Step by Step',
				'contents'			=> array(
					array(
						'content' 		=> 'blah',
						'author'		=> 1,
					),
				),
			),
			array(
				'section_title'		=> 'Third party tools for development',
				'contents'			=> array(
					array(
						'content' 		=> 'blah',
						'author'		=> 1,
					),
				),
			),
			array(
				'section_title'		=> 'Test',
				'contents'			=> array(
					array(
						'content' 		=> parse('test.txt'),
						'author'		=> 1,
					),
				),
			),
		);
	}
}
