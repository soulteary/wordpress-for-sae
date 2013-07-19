<?php
/**
 * WordPress For SAE PassWord Reset Tools
 * @soulteary 2013.7 [http://www.soulteary.com]
 *
 */

define('FILE_NAME', basename($_SERVER['SCRIPT_FILENAME']));

require( dirname(__FILE__) . '/wp-load.php' );

function makeTpl($title, $event = false){
    ?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>WordPress For SAE &rsaquo; <?=$title;?></title>
        <?php
        wp_admin_css( 'wp-admin', true );
        wp_admin_css( 'colors-fresh', true );
        ?>
        <meta name='robots' content='noindex,nofollow' />
    </head>
    <body class="login login-action-login wp-core-ui">
    <div id="login">
        <h1><a href="http://cn.wordpress.org/" title="基于 WordPress">WordPress For SAE</a></h1>
        <?php if($event == 'TOKEN'):?>
        <div id="login_error"><strong>错误</strong>：您输入的Secret Key不正确。<br></div>
        <?php elseif('RESET-EMPTY'==$event):?>
        <style type="text/css">
            .login #login_error {
                background-color: #E8F8FF;
                border-color: #009BCC;
            }
        </style>
        <div id="login_error"><strong>提示</strong>：请将表单填写完整。<br></div>
        <?php elseif('RESET-ERROR'==$event):?>
        <div id="login_error"><strong>错误</strong>：重置密码失败。<br></div>
        <?php elseif('RESET-DONE' == $event):?>
        <style type="text/css">
            .login #login_error {
                background-color: #E9FFE8;
                border-color: #00CC49;
            }
        </style>
        <div id="login_error"><strong>成功</strong>：重置密码完成。<br></div>
        <?php endif;?>
        <?php if(!$event||'TOKEN'==$event):?>
        <form name="loginform" id="loginform" action="./<?=FILE_NAME?>" method="post">
            <p>
                <label for="token">Sina App Engine Secret Key<br />
                    <input type="password" name="token" id="token" class="input" value="" size="20" /></label>
            </p>
            <p class="submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large"
                       value="验证" />
            </p>
        <?php elseif('RESET-EMPTY' == $event || 'RESET-ERROR' == $event):?>
        <form name="loginform" id="loginform" action="./<?=FILE_NAME?>?token=<?=TOKEN;?>" method="post">
            <p>
                <label for="username">帐号名称<br />
                    <input type="text" name="username" id="username" class="input" value="" size="20" /></label>
            </p>
            <p>
                <label for="password">新的密码<br />
                    <input type="password" name="password" id="password" class="input" value="" size="20" /></label>
            </p>
            <p class="submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large"
                       value="修改" />
            </p>
        <?php elseif('RESET-DONE' == $event):?>
        <form name="loginform" id="loginform" method="post">
            <p class="submit">
                <style type="text/css">
                    #bye-bye{
                        float: left;
                        margin: 0 0 0 86px;
                    }
                </style>
                <a id="bye-bye" class="button button-primary button-large" href="./wp-login.php" />前往登录</a>
            </p>
        <?php endif;?>
        </form>
    </div>
    <div class="clear"></div>
    </body>
    </html>
<?php
    exit();
}




define('TOKEN', SAE_SECRETKEY);
if(!isset($_REQUEST['token'])||empty($_REQUEST['token'])){
    makeTpl('权限验证');
}else{
    if($_REQUEST['token'] != TOKEN){
        makeTpl('权限验证','TOKEN');
    }
}

//include("wp-config.php");
//include("wp-blog-header.php");



if (!isset($_POST['password'])||!isset($_POST['username'])||empty($_POST['username'])||empty($_POST['username'])) {
    makeTpl('重置帐号','RESET-EMPTY');
} else {
    $sql = "UPDATE " . $wpdb->users . " SET user_pass = '" . md5($_POST['password']) . "' WHERE user_login = '".$_POST['username']."'";
    if ($link = $wpdb->query($sql)) {
        makeTpl('重置帐号','RESET-DONE');
        exit;
    } else {
        makeTpl('重置帐号','RESET-ERROR');
    }
}
?>