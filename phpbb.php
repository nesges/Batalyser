<?
    session_start();
    
    if(! $_SESSION['user_id']) {
        exit("101");
    }
    
    define('IN_PHPBB', true);
    $phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../../forum/';
    $phpEx = substr(strrchr(__FILE__, '.'), 1);
    include_once($phpbb_root_path . 'config.' . $phpEx);
    include_once($phpbb_root_path . 'common.' . $phpEx);
    include_once($phpbb_root_path . 'includes/functions.' . $phpEx);
    include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
            
    $user->session_begin($_SESSION['user_id']);
    $auth->acl($user->data);

    $op=($_POST['op']?$_POST['op']:$_GET['op']);

    global $db;

    switch($op) {
        case "bugreport":
            $subject = "";
            $message = "";
            if(isset($_GET['id'])) {
                $res = $db->sql_query("select subject, message from bugreport where id='".$_GET['id']."'");
                $row = $db->sql_fetchrow($res);
                $subject = $row['subject'];
                $message = $row['message'];
            }
            if(!$subject) {
                $subject= base64_decode($_GET['subject']);
                $message= base64_decode($_GET['message']);
            }
            $returnto = base64_decode($_POST['returnto']?$_POST['returnto']:$_GET['returnto']);
    
            $poll = $uid = $bitfield = $options = '';
            generate_text_for_storage($message, $uid, $bitfield, $options, true, true, true);
            
            $data = array( 
                'forum_id'              => 4,
                'topic_id'              => 0,
                'icon_id'               => false,
                'enable_bbcode'         => false,
                'enable_smilies'        => false,
                'enable_urls'           => false,
                'enable_sig'            => false,
                'message'               => $message,
                'message_md5'           => md5($message),
                'bbcode_bitfield'       => $bitfield,
                'bbcode_uid'            => $uid,
                'post_edit_locked'      => 0,
                'topic_title'           => $subject,
                'notify_set'            => false,
                'notify'                => false,
                'post_time'             => 0,
                'enable_indexing'       => true,        
                'force_approved_state'  => true
            );
            $post_url = submit_post('post', $subject, '', POST_NORMAL, $poll,  &$data);
            $post_url = preg_replace('#^(../)*forum#', 'http://batalyser.net/forum', $post_url);
            
            if(isset($returnto) && $returnto) {
                header("Location: ".str_replace('%5Bpost_url%5D', urlencode($post_url), $returnto));
            } else {
                print $post_url;
            }
    }
?>