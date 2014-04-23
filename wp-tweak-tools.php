<?php
/**
 * WordPress For SAE Tweak Tools
 * 2014.4.21
 * [@soulteary](http://www.soulteary.com)
 */
error_reporting(0);

define('FILE_NAME', basename($_SERVER['SCRIPT_FILENAME']));

require( dirname(__FILE__) . '/wp-load.php' );

function sy_check_mysql(){
    $db=mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS);
    if($db){
        return true;
    }else{
        return false;
    }
}

function sy_check_mc(){
    $mc = memcache_init();
    if($mc){
        $mc->flush();
        return true;
    }else{
        return false;
    }
}

function sy_check_storage(){
    $sy['stor'] = new SaeStorage(SAE_ACCESSKEY, SAE_SECRETKEY);
    $sy['domain'] = $_SERVER['HTTP_APPNAME'].'-wordpress';
    $sy['stor_list'] = $sy['stor']->listDomains();
    if($sy['stor_list']){
        if(in_array($sy['domain'],$sy['stor_list'])){
            $sy['stor']->setDomainAttr("wordpress",array("private"=>false));
            return true;
        }else{
            if($sy['stor']->createDomain($sy['domain'])){
                $sy['stor']->setDomainAttr("wordpress",array("private"=>false));
                return true;    //创建成功。
            }else{
                return false;   //创建失败
            }
        }
    }else{
        if($sy['stor']->createDomain($sy['domain'])){
            $sy['stor']->setDomainAttr("wordpress",array("private"=>false));
            return true;    //创建成功。
        }else{
            return false;   //创建失败
        }
    }
}

if(!validate_current_theme()){
    $themes = wp_get_themes();
    $theme_count = count($themes);
    if($theme_count){
        $theme_item = array_keys($themes);
        switch_theme($theme_item[rand(0, $theme_count-1)]);
    }
}

function makeTpl($title, $event = false){
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>WordPress For SAE &rsaquo; <?=$title;?></title>
    <?php
    wp_admin_css( 'wp-admin', true );
    wp_admin_css( 'colors-fresh', true );
    wp_admin_css( 'login', true );
    ?>
    <meta name='robots' content='noindex,nofollow' />
</head>
<body class="login login-action-login wp-core-ui locale-zh-cn">
<div id="login">
    <div id="loginform">
        <h1><a href="http://cn.wordpress.org/" title="基于 WordPress">WordPress For SAE</a></h1>
        <?php if($event == 'TOKEN'):?>
            <div id="login_error"><strong>错误</strong>：您输入的Secret Key不正确。<br></div>
        <?php elseif('TOOLS-INFO'==$event):?>
            <style type="text/css">
                .login #login_error {
                    background-color: #E8F8FF;
                    border-color: #009BCC;
                }
            </style>
            <div id="login_error">请前往SAE管理后台获取应用的Secret Key。<br></div>
        <?php elseif('SERVER-CHECK' == $event):?>
            <style type="text/css">
                .login #login_error {
                    background-color: #E9FFE8;
                    border-color: #00CC49;
                }
            </style>
            <div id="login_error"><strong>提示</strong>：正在检查各项依赖的服务，请稍等。<br></div>
        <?php endif;?>
        <?php if('TOOLS-INFO'==$event||'TOKEN'==$event):?>
        <form name="loginform" id="loginform" action="./<?=FILE_NAME?>" method="post" style="padding-bottom: 20px;">
            <p>
                <label for="token">Sina App Engine Secret Key<br />
                <input type="password" name="token" id="token" class="input" value="" size="20" /></label>
            </p>
            <p class="submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="验证" />
            </p>
            <?php elseif('SERVER-CHECK' == $event):?>
        <form name="loginform" id="loginform" action="./<?=FILE_NAME?>" method="post" style="padding-bottom: 20px;">
            <input type="hidden" name="token" value="<?=md5(TOKEN);?>"/>
        <?php
            $service['mc'] = sy_check_mc();
            $service['storage'] = sy_check_storage();
            $service['mysql'] = sy_check_mysql();
            $service['ret'] = $service['mc'] && $service['storage'] && $service['mysql'];
        ?>
            <style type="text/css">
                #services-list{
                    margin-bottom: 20px;
                }
                #services-list li{
                    font-size: 15px;
                    text-align: right;
                }
                #services-list li strong{
                    margin-right: 5px;
                    float: left;
                }
            </style>
            <ul id="services-list">
                <li><strong>MYSQL数据库:</strong><?=$service['mysql']?'正常':'故障'?></li>
                <li><strong>MemCache:</strong><?=$service['mc']?'正常':'故障'?></li>
                <li><strong>Storage数据存储:</strong><?=$service['storage']?'正常':'故障'?></li>
            </ul>
            <p class="submit">
                <?php if($service['ret']):?>
                <a style="float: left;margin: 0 0 0 86px;" class="button button-primary button-large" href="./wp-login.php" />返回博客</a>
                <?php else:?>
                <a class="button button-primary button-large" target="_blank" href="http://weibo.com/firendless"
                    />去微博寻求帮助</a>
                <?php endif;?>
            </p>
        <?php endif;?>
        </form>
    </div>
</div>
<div class="clear"></div>
</body>
</html>
<?php
exit();
}

define('TOKEN', SAE_SECRETKEY);
if(!isset($_POST['token'])||empty($_POST['token'])){
    makeTpl('管理员验证','TOOLS-INFO');
}else{
    if($_POST['token'] != TOKEN && ($_POST['token'] != md5(TOKEN))){
        makeTpl('管理员验证','TOKEN');
    }
}

makeTpl('检查依赖的服务','SERVER-CHECK');
?>