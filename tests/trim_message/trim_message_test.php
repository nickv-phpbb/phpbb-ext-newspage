<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/trim_message/trim_message.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/trim_message/bbcodes.php';

class phpbb_trim_message_test extends phpbb_test_case
{
	public function trim_message_data()
	{
		$messages = array(
			array(
				'message'		=> '[quote=&quot;nickv&quot;:l0nwstsc]foobar[/quote:l0nwstsc][quote=&quot;nickv&quot;:l0nwstsc]foobar[/quote:l0nwstsc]',
				'bbcode_uid'	=> 'l0nwstsc',
			),
			array(
				'message'		=> '<!-- s:geek: --><img src="{SMILIES_PATH}/icon_e_geek.gif" alt=":geek:" title="Geek" /><!-- s:geek: --><!-- s:geek: --><img src="{SMILIES_PATH}/icon_e_geek.gif" alt=":geek:" title="Geek" /><!-- s:geek: -->',
				'bbcode_uid'	=> 'foobar',
			),
			array(
				'message'		=> '[quote=&quot;[url=http&#58;//www&#46;example&#46;tdl/:2sda49fx][color=#00BF00:2sda49fx]bbcodes in quotes...[/color:2sda49fx][/url:2sda49fx]&quot;:2sda49fx][color=#FF0000:2sda49fx]hard[/color:2sda49fx]core[/quote:2sda49fx]',
				'bbcode_uid'	=> '2sda49fx',
			),
			array(
				'message'		=> '[list:1wmer8b2][*:1wmer8b2]1[/*:m:1wmer8b2][*:1wmer8b2]2[/*:1wmer8b2][/list:u:1wmer8b2]',
				'bbcode_uid'	=> '1wmer8b2',
			),
		);

		$cases = array(
			/**
			* Breaking within BBCodes
			*/
			array(
				'message_set' => 0, 'range_start' => 0, 'range_end' => 34, 'trimmed' => true, 'step_size' => 3,
				'expected' => ' [...]',
			),
			array(
				'message_set' => 0, 'range_start' => 35, 'range_end' => 35, 'trimmed' => true,
				'expected' => '[quote=&quot;nickv&quot;:l0nwstsc]f [...][/quote:l0nwstsc]',
			),
			array(
				'message_set' => 0, 'range_start' => 40, 'range_end' => 57, 'trimmed' => true, 'step_size' => 3,
				'expected' => '[quote=&quot;nickv&quot;:l0nwstsc]foobar [...][/quote:l0nwstsc]',
			),
			array(
				'message_set' => 0, 'range_start' => 58, 'range_end' => 90, 'trimmed' => true, 'step_size' => 3,
				'expected' => '[quote=&quot;nickv&quot;:l0nwstsc]foobar[/quote:l0nwstsc] [...]',
			),
			array(
				'message_set' => 0, 'range_start' => 91, 'range_end' => 91, 'trimmed' => true,
				'expected' => '[quote=&quot;nickv&quot;:l0nwstsc]foobar[/quote:l0nwstsc][quote=&quot;nickv&quot;:l0nwstsc] [...][/quote:l0nwstsc]',
			),
			array(
				'message_set' => 0, 'range_start' => 92, 'range_end' => 92, 'trimmed' => true,
				'expected' => '[quote=&quot;nickv&quot;:l0nwstsc]foobar[/quote:l0nwstsc][quote=&quot;nickv&quot;:l0nwstsc]f [...][/quote:l0nwstsc]',
			),
			array(
				'message_set' => 0, 'range_start' => 97, 'range_end' => 114, 'trimmed' => true, 'step_size' => 3,
				'expected' => '[quote=&quot;nickv&quot;:l0nwstsc]foobar[/quote:l0nwstsc][quote=&quot;nickv&quot;:l0nwstsc]foobar [...][/quote:l0nwstsc]',
			),
			array(
				'message_set' => 0, 'range_start' => 115, 'range_end' => 116, 'trimmed' => false,
				'expected' => '[quote=&quot;nickv&quot;:l0nwstsc]foobar[/quote:l0nwstsc][quote=&quot;nickv&quot;:l0nwstsc]foobar[/quote:l0nwstsc]',
			),
			array(
				'message_set' => 1, 'range_start' => 0, 'range_end' => 101, 'trimmed' => true, 'step_size' => 3,
				'expected' => ' [...]',
			),

			/**
			* Breaking within Smilies
			*/
			array(
				'message_set' => 1, 'range_start' => 102, 'range_end' => 203, 'trimmed' => true, 'step_size' => 3,
				'expected' => '<!-- s:geek: --><img src="{SMILIES_PATH}/icon_e_geek.gif" alt=":geek:" title="Geek" /><!-- s:geek: --> [...]',
			),
			array(
				'message_set' => 1, 'range_start' => 204, 'range_end' => 206, 'trimmed' => false,
				'expected' => '<!-- s:geek: --><img src="{SMILIES_PATH}/icon_e_geek.gif" alt=":geek:" title="Geek" /><!-- s:geek: --><!-- s:geek: --><img src="{SMILIES_PATH}/icon_e_geek.gif" alt=":geek:" title="Geek" /><!-- s:geek: -->',
			),

			/**
			* Breaking within Quotes with BBCodes in username.
			*/
			array(
				'message_set' => 2, 'range_start' => 0, 'range_end' => 155, 'trimmed' => true, 'step_size' => 5,
				'expected' => ' [...]',
			),
			array(
				'message_set' => 2, 'range_start' => 156, 'range_end' => 156, 'trimmed' => true, 'step_size' => 3,
				'expected' => '[quote=&quot;[url=http&#58;//www&#46;example&#46;tdl/:2sda49fx][color=#00BF00:2sda49fx]bbcodes in quotes...[/color:2sda49fx][/url:2sda49fx]&quot;:2sda49fx] [...][/quote:2sda49fx]',
			),
			array(
				'message_set' => 2, 'range_start' => 157, 'range_end' => 178, 'trimmed' => true,
				'expected' => '[quote=&quot;[url=http&#58;//www&#46;example&#46;tdl/:2sda49fx][color=#00BF00:2sda49fx]bbcodes in quotes...[/color:2sda49fx][/url:2sda49fx]&quot;:2sda49fx] [...][/quote:2sda49fx]',
			),
			array(
				'message_set' => 2, 'range_start' => 179, 'range_end' => 179, 'trimmed' => true,
				'expected' => '[quote=&quot;[url=http&#58;//www&#46;example&#46;tdl/:2sda49fx][color=#00BF00:2sda49fx]bbcodes in quotes...[/color:2sda49fx][/url:2sda49fx]&quot;:2sda49fx][color=#FF0000:2sda49fx] [...][/color:2sda49fx][/quote:2sda49fx]',
			),
			array(
				'message_set' => 2, 'range_start' => 201, 'range_end' => 201, 'trimmed' => true,
				'expected' => '[quote=&quot;[url=http&#58;//www&#46;example&#46;tdl/:2sda49fx][color=#00BF00:2sda49fx]bbcodes in quotes...[/color:2sda49fx][/url:2sda49fx]&quot;:2sda49fx][color=#FF0000:2sda49fx]hard[/color:2sda49fx]c [...][/quote:2sda49fx]',
			),
			array(
				'message_set' => 2, 'range_start' => 204, 'range_end' => 220, 'trimmed' => true,
				'expected' => '[quote=&quot;[url=http&#58;//www&#46;example&#46;tdl/:2sda49fx][color=#00BF00:2sda49fx]bbcodes in quotes...[/color:2sda49fx][/url:2sda49fx]&quot;:2sda49fx][color=#FF0000:2sda49fx]hard[/color:2sda49fx]core [...][/quote:2sda49fx]',
			),
			array(
				'message_set' => 2, 'range_start' => 221, 'range_end' => 222, 'trimmed' => false,
				'expected' => '[quote=&quot;[url=http&#58;//www&#46;example&#46;tdl/:2sda49fx][color=#00BF00:2sda49fx]bbcodes in quotes...[/color:2sda49fx][/url:2sda49fx]&quot;:2sda49fx][color=#FF0000:2sda49fx]hard[/color:2sda49fx]core[/quote:2sda49fx]',
			),

			/**
			* Breaking within lists
			*/
			array(
				'message_set' => 3, 'range_start' => 0, 'range_end' => 14, 'trimmed' => true, 'step_size' => 3,
				'expected' => ' [...]',
			),
			array(
				'message_set' => 3, 'range_start' => 15, 'range_end' => 26, 'trimmed' => true, 'step_size' => 3,
				'expected' => '[list:1wmer8b2] [...][/list:u:1wmer8b2]',
			),
			array(
				'message_set' => 3, 'range_start' => 27, 'range_end' => 27, 'trimmed' => true,
				'expected' => '[list:1wmer8b2][*:1wmer8b2] [...][/*:m:1wmer8b2][/list:u:1wmer8b2]',
			),
			array(
				'message_set' => 3, 'range_start' => 28, 'range_end' => 42, 'trimmed' => true, 'step_size' => 3,
				'expected' => '[list:1wmer8b2][*:1wmer8b2]1 [...][/*:m:1wmer8b2][/list:u:1wmer8b2]',
			),
			array(
				'message_set' => 3, 'range_start' => 43, 'range_end' => 54, 'trimmed' => true, 'step_size' => 3,
				'expected' => '[list:1wmer8b2][*:1wmer8b2]1[/*:m:1wmer8b2] [...][/list:u:1wmer8b2]',
			),
			array(
				'message_set' => 3, 'range_start' => 55, 'range_end' => 55, 'trimmed' => true,
				'expected' => '[list:1wmer8b2][*:1wmer8b2]1[/*:m:1wmer8b2][*:1wmer8b2] [...][/*:1wmer8b2][/list:u:1wmer8b2]',
			),
			array(
				'message_set' => 3, 'range_start' => 57, 'range_end' => 68, 'trimmed' => true, 'step_size' => 3,
				'expected' => '[list:1wmer8b2][*:1wmer8b2]1[/*:m:1wmer8b2][*:1wmer8b2]2 [...][/*:1wmer8b2][/list:u:1wmer8b2]',
			),
			array(
				'message_set' => 3, 'range_start' => 69, 'range_end' => 86, 'trimmed' => true, 'step_size' => 3,
				'expected' => '[list:1wmer8b2][*:1wmer8b2]1[/*:m:1wmer8b2][*:1wmer8b2]2[/*:1wmer8b2] [...][/list:u:1wmer8b2]',
			),
			array(
				'message_set' => 3, 'range_start' => 87, 'range_end' => 87, 'trimmed' => false,
				'expected' => '[list:1wmer8b2][*:1wmer8b2]1[/*:m:1wmer8b2][*:1wmer8b2]2[/*:1wmer8b2][/list:u:1wmer8b2]',
			),
			/*
			array(
				'message_set' => 3, 'range_start' => 0, 'range_end' => 15, 'trimmed' => false, 'step_size' => 3,
				'expected' => '[list:1wmer8b2][*:1wmer8b2]1[/*:m:1wmer8b2][*:1wmer8b2]2[/*:1wmer8b2][/list:u:1wmer8b2]',
			),
			*/
		);

		$test_cases = array();
		foreach ($cases as $case)
		{
			for ($i = $case['range_start']; $i <= $case['range_end']; $i++)
			{
				$test_cases[] = array(
					$messages[$case['message_set']]['message'],
					$messages[$case['message_set']]['bbcode_uid'],
					$i,
					$case['expected'],
					$case['trimmed'],
					(isset($case['incomplete']) ? $case['incomplete'] : ''),
				);

				if (isset($case['step_size']))
				{
					$i += ($case['step_size'] - 1);
					if ($i > $case['range_end'])
					{
						$i = $case['range_end'];
					}
				}
			}
		}

		return $test_cases;
	}

	/**
	* @dataProvider trim_message_data
	*/
	public function test_trim_message($message, $bbcode_uid, $length, $expected, $trimmed, $incomplete)
	{
		if ($incomplete)
		{
			$this->markTestIncomplete($incomplete);
		}

		$object = new phpbb_trim_message($message, $bbcode_uid, $length, ' [...]', 0);
		$this->assertEquals($expected, $object->message());
		$this->assertEquals($trimmed, $object->is_trimmed());
	}
}

