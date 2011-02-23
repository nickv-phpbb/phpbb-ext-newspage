phpBB - Trim Message Tool
=========================

This tool contains a class, that is able to trim a message from the phpbb message_parser to a maximum length without breaking the bbcodes/smilies and links.

How to use
----------
    <?php
    include($phpbb_root_path . 'includes/trim_message/trim_message.' . $phpEx);
    include($phpbb_root_path . 'includes/trim_message/bbcodes.' . $phpEx);

    $object = new phpbb_trim_message($message, $bbcode_uid, $length);
    // Ready to get parsed:
    echo $object->message();

How to run tests
----------------
1. copy files from tests/ to [phpBB.git](https://github.com/phpbb/phpbb3/)/tests/
2. run phpBB tests

License
-------
Licensed under the terms of the GNU Public License:
http://opensource.org/licenses/gpl-license.php
