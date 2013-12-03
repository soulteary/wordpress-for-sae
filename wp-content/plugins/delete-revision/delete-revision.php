<?php
/*
Plugin Name: Delete-Revision
Plugin URI: http://blog.gohsy.com/topics/tag/delete-revision
Description: Delete revision post from Database,let us now to drop Redundancy, lose weight !
Author: gohsy
Version: 1.3.1
Author URI: http://gohsy.com
*/

/*
Changelog

2009-06-30  v1.3.1
			Increased the database maintenance and the optimization
			增加数据库状态检测和修复优化

2008-11-24	v1.2
			An increase of the international language support
			增加国际化语言支持

2008-11-24  v1.1
			Fix bugs cann't runat where the datebase name is not the default "wp-*" and some description. 
			修复了因数据库名称前缀非默认的"wp-*"时的运行错误和资料中说明中数字问题。
2008-10-21	v1.0
			First public ver1.0
			首次发布1.0
*/

	if(!get_option('del_revision_no')) {update_option("del_revision_no",0);}
	if(!get_option('del_revision_getPosts')) {update_option("del_revision_getPosts",0);}
	

	$dr_locale = get_locale();
	$dr_mofile = dirname(__FILE__) . "/delete-revision-$dr_locale.mo";
	load_textdomain('delete-revision', $dr_mofile);
	
function delete_revision_main() {
    if (function_exists('add_options_page')) {
	add_options_page('Delete-Revision', 'Delete-Revision',8, basename(__FILE__), 'my_options_delete_revision');
    }
 }

add_action('admin_menu', 'delete_revision_main');

function my_options_delete_revision() {
	$dr_ver = '1.3.1';
	$get_my_posts = get_my_posts();
	$del_revision_no = get_option('del_revision_no');
	echo <<<EOT
	<div class="wrap">
		<h2>Delete-revision Manager  <font size=1>ver $dr_ver</font></h2>
		<div class="widget"><p style="margin:10px;">
EOT;
		printf(__("Now You have <span style='color:red;font-weight:bolder;'> %s </span> posts ,Up to now Delete-Revision has deteted <span id='revs_no' style='color:red;font-weight:bolder;'> %s </span> post revision of dedundancy,it's easy . Wish happy ending !",'delete-revision'), $get_my_posts,$del_revision_no);
		echo '</p></div>';

	if (isset($_POST['del_act'])) {
		delete_revision_act();
		$del_no = $_POST['rev_no'];
		update_option("del_revision_no",get_option("del_revision_no") + $del_no);
		echo '<div class="updated" style="margin-top:50px;"><p><strong>';
		printf(__("Deleted <span style='color:red;font-weight:bolder;'> %s </span> redundancy posts !",'delete-revision'),$del_no);	
		echo "</strong></p></div></div><script>
		var del_no = document.getElementById('revs_no').innerHTML;
		document.getElementById('revs_no').innerHTML = Number(del_no)+ $del_no;
		</script>";
	}
	elseif (isset($_POST['get_rev'])) {
		get_my_revision();
		
	}
	elseif (isset($_POST['maintain_mysql'])) {
		if ($_POST['operation'] == 'OPTIMIZE' ) {echo maintain_mysql('OPTIMIZE');}
		else echo maintain_mysql('CHECK');
	}
	else {
		echo '<form method="post" action="">';
		echo '<input class="button" type="submit" name="get_rev" value="';
		_e('Check Redundant Revision','delete-revision');
		echo '" />  <input class="button" type="submit" name="maintain_mysql" value="';
		_e('Database Optimization','delete-revision');
		echo '" /></form></div>';

	}
	
	echo '<br /><div class="widget"><div style="margin:12px;line-height: 1.5em;">';
	_e('Revision Post is in the 2.6 version of WordPress after the automatic accession to the revised edition of the journal preservation cause, you modify a log of each, will add a revision, if you modify many times, log on the few speeches, it will be a very frightening number!','delete-revision');
	_e('If you have 100 on the log, your revisiong redundancy may be as many as 1,000 articles!','delete-revision');
	echo '<br />';
	_e('Revision Manager is the end came, to delete a large number of redundant revision to increase the speed of implementation of the SQL statement, WordPress upgrade the speed there is a lot of benefits!','delete-revision');
	echo '<br />';
	_e('Thank you for your useing. I hope this will give you convenient plug-ins!','delete-revision');
	_e('Author','delete-revision');
	echo ':<a href="http://www.gohsy.com" target="_blank">http://www.gohsy.com</a></div></div>';
	
}

function get_my_posts() {
	global $wpdb;
	
	$sql = "SELECT ID
			FROM (
			$wpdb->posts
			)
			WHERE `post_type` = 'post'";
	$results = $wpdb -> get_results($sql);
	return count($results);
}

function get_my_revision() {
	global $wpdb;
	
	$sql = "SELECT `ID`,`post_date`,`post_title`,`post_modified`
			FROM (
			$wpdb->posts
			)
			WHERE `post_type` = 'revision'
			ORDER BY `ID` DESC";
	$results = $wpdb -> get_results($sql);
	if($results) {
	$res_no = count($results);
	echo "<table class='widefat'><thead>";
	echo "<tr><th width=30> Id </th><th width=450> Title </th><th width=180> Post date </th><th width=180> Last modified </th></tr></thead>";
	for($i = 0 ; $i < $res_no ; $i++) {
		echo "<tr><td>".$results[$i] -> ID."</td>";
		echo "<td>".$results[$i] -> post_title."</td>";
		echo "<td>".$results[$i] -> post_date."</td>";
		echo "<td>".$results[$i] -> post_modified."</td></tr>";
	}
	echo "</table><br />";
	echo <<<EOT
		<form method="post" action="">
		<input type="hidden" name="rev_no" value=" $res_no " />
EOT;
		echo '<input class="button-primary" type="submit" name="del_act" value="';
		printf(__('Yes , I would like to delete them!(A Total Of %s)','delete-revision'),$res_no);
		echo '" /><input class="button" type="submit" name="goback" value="';
		_e('No , I would not mind deleted!','delete-revision');
		echo '" /></form></div>';

	}
	else {echo "<div class=\"updated\" style=\"margin:50px 0;padding:6px;line-height:16pt;font-weight:bolder;\">";
	_e('Great, you have not found the wordpress redundant revision posts!<br />Today you will be another beautiful day!','delete-revision');
	echo "</div></div>";}
}

function delete_revision_act() {
	global $wpdb;
	
	$sql = "DELETE FROM $wpdb->posts WHERE post_type = 'revision'";
	$results = $wpdb -> get_results($sql);


}

function maintain_mysql($operation = "CHECK"){
		global $wpdb;
       
        $Tables = $wpdb -> get_results('SHOW TABLES IN '.DB_NAME);
        $query = "$operation TABLE ";

		$Tables_in_DB_NAME = 'Tables_in_'.DB_NAME;
        foreach($Tables as $k=>$v){
			$_tabName = $v -> $Tables_in_DB_NAME ;
           $query .= " `$_tabName`,";
        }

        $query = substr($query,0,strlen($query)-1);
        $result = $wpdb -> get_results($query);
		if ($operation == "OPTIMIZE") {
			return '<h3>'.__('Optimization of database completed.','delete-revision').'</h3>';
		}

        $res = "<table border=\"0\" class=\"widefat\">";
        $res .= "<thead><tr>
			<th>Table</th>
			<th>OP</th>
			<th>Status</th>
			</tr><thead>";
        $bgcolor = $color3;
		foreach($result as $j=>$o) {
            $res .= "<tr>";
            foreach ($o as $k=>$v) {
				$tdClass = $j%2 == 1 ? 'active alt' : 'inactive';
				if($k == 'Msg_type') continue;
				if($k == 'Msg_text' ) {
					if ($v == 'OK') {
					$res .= "<td class='$tdClass' ><font color='green'><b>$v</b></font></td>";
					}
					else {
					$res .= "<td class='$tdClass' ><font color='red'><b>$v</b></font></td>";
					}
				}
				else $res .= "<td class='$tdClass' >$v</td>";
            }
            $res .= "</tr>";
        }
        $res .= "<tfoot><tr><th colspan=3>".__('If the Status is green <font color=green>OK</font>, then indicated that is normal, does not need any operation, please go back; If is red, then click on the following button to optimize.','delete-revision')."</th></tr></tfoot></table>";
		$res .= "<br /><form method='post' action=''>
			<input name='operation' type='hidden' value='OPTIMIZE' />
			<input name='maintain_mysql' type='hidden' value='OPTIMIZE' />
			<input name='submit' type='submit' class='button-primary' value='".__('Optimize DataBase','delete-revision')."' /></form>";
        return $res;
    }

?>