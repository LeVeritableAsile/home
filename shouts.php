<?php
require 'config.php';
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'include/parser.php';

// Retrieve arguments.
$action = isset($_GET['action']) ? $_GET['action'] : null;

if (isset($_GET['login']) && intval($_GET['login']) == 1)
{
    redirect('/');
}

if ($action == 'post')
{
    $errors = array();

    if ($forum_user['is_guest'])
        message($lang_common['Bad request']);

    $text = isset($_POST['text']) ? $_POST['text'] : '';
    $text = forum_linebreaks(forum_trim($text));
    $text = preparse_bbcode($text, $errors);
    if (!empty($errors))
        message($errors[0]);
        
    if ($text == '')
        message($lang_post['No message']);

    $query = array(
        'INSERT'    => 'sender_id, sender_ip, message, shouted',
        'INTO'      => 'shouts',
        'VALUES'     => $forum_user['id'].', \''.$forum_db->escape(get_remote_address()).'\', \''.$forum_db->escape($text).'\', '.time()
    );

    $forum_db->query_build($query) or error(__FILE__, __LINE__);
}

if ($action == 'view' || $action == 'post')
{
    // Convert to integer to prevent SQL injection.
    $from = isset($_GET['from']) ? intval($_GET['from']) : 0;
    $to = isset($_GET['to']) ? intval($_GET['to']) : 0;

    // Build query.
    $query = array(
        'SELECT'    => 'u.username AS username, s.message AS message, s.shouted AS shouted',
        'FROM'      => 'shouts as s',
        'JOINS'     => array(
            array(
                'INNER JOIN'    => 'users as u',
                'ON'            => 'u.id=s.sender_id'
            )
        ),
        'ORDER BY'	=> 's.shouted DESC'
    );

    if ($from && $to)
        $query['WHERE'] = "s.shouted BETWEEN {$from} AND {$to}";
    else 
        $query['LIMIT'] = '0, 40';

    // Execute query.
    $result = $forum_db->query_build($query) or error(__FILE__, __LINE__);

    $shouts = array();
    while ($row = $forum_db->fetch_assoc($result))
        $shouts[] = $row;

    $forum_db->free_result($result);

    // Perform formating.
    $shout_lastdate = 'invalid';
    foreach ($shouts as $shout)
    {
        $shout_date = format_time($shout['shouted'], FORUM_FT_DATE);
        if ($shout_lastdate != $shout_date)
        {
            $shout_lastdate = $shout_date;
?>
    <div class="home-shouts-date main-head"><?php echo $shout_date ?></div>
<?php
        }
        $shout_time = format_time($shout['shouted'], FORUM_FT_TIME);
        $shout_user = $shout['username'];
        $shout_message = parse_message($shout['message'], '0');
?>
    <div class="home-shouts-message">
        <span class="home-shouts-message-time"><?php echo $shout_time ?></span>
        <span class="home-shouts-message-user"><strong><?php echo $shout_user ?></strong></span>
        <div class="home-shouts-message-text"><?php echo $shout_message ?></div>
    </div>
<?php
    }
}

// End the transaction
$forum_db->end_transaction();

// Close the db connection (and free up any result data)
$forum_db->close();

