<?php
/**
 * Plugin Name: Plaghunter
 * Description: Protect your WordPress blog against image theft
 * Version: 1.1
 * Author: Marco Verch
 * Author URI: http://www.plaghunter.com/
 * License: GPL2
 */

add_action('admin_menu', 'ph_plaghunter_admin_menu');
add_action( 'wp_ajax_plaghunter', 'ph_plaghunter_callback' );
add_action( 'admin_footer', 'ph_plaghunter_javascript' );
//add_filter('get_pagenum_link','my_pagenum_link');

function ph_plaghunter_admin_menu() {
    $page_title = 'Protect your WordPress blog against image theft';
    $menu_title = 'Plaghunter';
    $capability = 'manage_options';
    $menu_slug = 'plaghunter-settings';
    $function = 'ph_plaghunter_settings';
    add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
}

function ph_plaghunter_settings() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
if($_POST)
{
$validate=file_get_contents('http://www.plaghunter.com/engine/api/api.php?function=removeImage&apiKey='.$_POST['plaghunter_apikey'].'&userId='.$_POST['plaghunter_userid']);
$json=json_decode($validate);
if($json->Message=='OK')
{
if(!add_option( 'plaghunter_userid',$_POST['plaghunter_userid'], '', 'yes' ))
{
update_option( 'plaghunter_userid',$_POST['plaghunter_userid']);
}
if(!add_option( 'plaghunter_apikey',$_POST['plaghunter_apikey'], '', 'yes' ))
{
update_option( 'plaghunter_apikey',$_POST['plaghunter_apikey']);
}
echo '<div class="updated"><p>API details updated successfuly.</p></div>';
}
else
{
echo '<div class="error"><p>ERROR: Authentification failed.</p></div>';
}
}
echo '<div class="wrap">
<h2>Protect your WordPress blog against image theft</h2>
<img style="padding-left:100px;" src="'.plugins_url('plaghunter.png' , __FILE__ ).'" />
<form method="post" action="">
<table class="form-table">
<tbody><tr valign="top">
<th scope="row"><label for="plaghunter_userid">Your USER ID <a href="http://www.plaghunter.com/api/" target="_new">(Get UserID here)</a></label></th>
<td><input name="plaghunter_userid" id="plaghunter_userid" value="'.get_option( 'plaghunter_userid').'" class="regular-text" type="text"></td>
</tr>
<tr valign="top">
<th scope="row"><label for="plaghunter_apikey">Your API KEY <a href="http://www.plaghunter.com/api/" target="_new">(Get API KEY here)</a></label></th>
<td><input name="plaghunter_apikey" id="plaghunter_apikey" value="'.get_option( 'plaghunter_apikey').'" class="regular-text" type="text"></td>
</tr>
</tbody></table>
<p class="submit"><input name="submit" id="submit" class="button button-primary" value="Save settings" type="submit"> <span style="margin-left:50px;">Do not have a Plaghunter account? <a href="http://www.plaghunter.com/en/?utm_source=WordpressPlugin&utm_medium=Link&utm_campaign=WordpressPlugin" target="_new">Register here for free</a></span></p> </form>
<hr/>
</div>';

/*if($_GET['add'])
{
$add=file_get_contents('https://www.plaghunter.com/engine/api/api.php?apiKey='.get_option( 'plaghunter_apikey').'&userId='.get_option( 'plaghunter_userid').'&function=addImage&imageURL='.urlencode($_GET['add']));
$add=json_decode($add);
if($add->Message=='OK') {echo '<div class="updated"><p>Image successfuly added</p></div>';}
if($add->Message=='ERROR: Authentification failed.') {echo '<div class="error"><p>ERROR: Authentification failed.</p></div>';}
}
if($_GET['delete'])
{
$add=file_get_contents('https://www.plaghunter.com/engine/api/api.php?apiKey='.get_option( 'plaghunter_apikey').'&userId='.get_option( 'plaghunter_userid').'&function=removeImage&imageID='.$_GET['delete']);
$add=json_decode($add);
if($add->Message=='OK') {echo '<div class="updated"><p>Image successfuly removed</p></div>';}
if($add->Message=='ERROR: Authentification failed.') {echo '<div class="error"><p>ERROR: Authentification failed.</p></div>';}
}*/
if($_GET['status']==3)
{
echo '<div class="error"><p>ERROR: Authentification failed.</p></div>';
}
if($_GET['status']==2)
{
echo '<div class="updated"><p>Image successfuly removed</p></div>';
}
if($_GET['status']==1)
{
echo '<div class="updated"><p>Image successfuly added</p></div>';
}
$u=ph_get_images();
$p=ph_get_plaghunter_images();
$divid=1000;
$total=count($u);
$u=array_chunk($u,10);
$pn=1;
if($_GET['pn'])
{
$pn=intval($_GET['pn']);
}
$u=$u[$pn-1];
if(!empty($u))
{
foreach($u as $url)
{
if($p)
{
foreach($p as $pic)
{
$exists=false;
if($pic->imageURL==$url&&$pic->deleted=='0')
{
$exists=true;
$picid=$pic->imageID;
break;
}
}
}
echo '<div id="plaghunter"><div class="left"><img src="'.$url.'" width="300" height="300" /></div><div id="'.$divid.'" class="right">'.($exists?'<span style="color:green;">Protected by Plaghunter</span>':'<span style="color:red;">Not protected by Plaghunter</span>').'<br>'.($exists?'<a status="'.$picid.'" divid="'.$divid.'" rel="'.$url.'" class="plaghunter-link" <!--href="?page=plaghunter-settings&pn='.$pn.'&delete='.$picid.'-->">Remove from Plaghunter</a>':'<a status="0" divid="'.$divid.'" rel="'.$url.'" class="plaghunter-link" <!--href="?page=plaghunter-settings&pn='.$pn.'&add='.$url.'-->">Add to Plaghunter</a>').'</div></div><div style="clear:both;"></div>';
$divid++;
}

 $args = array(
	//'base'         => '%_%',
    //'base'=> 'http://wordpressss.tk/wp-admin/options-general.php%_%',
    'base' => @add_query_arg('pn','%#%',remove_query_arg( array('add','delete','status') )),
	'format'       => '?page=plaghunter-settings&pn=%#%',
	'total'        => ceil($total/10),
	'current'      => $pn,
	'show_all'     => true,
	//'mid_size'     => 5,
	'prev_next'    => True,
	'prev_text'    => __('<< Previous'),
	'next_text'    => __('Next >>'),
	'type'         => 'plain',
	'add_args'     => False,
	'add_fragment' => '',
	'before_page_number' => '',
	'after_page_number' => ''
);
//echo add_query_arg('pn','%#%',remove_query_arg(remove_query_arg( 'add' ),);
echo '<div class="tablenav-pages">'.paginate_links( $args ).'</div>';
echo '<style>#plaghunter{background-color:#FFFFCC;margin:10px;width:600px;height:310px;padding:10px;text-align:center;}
.left {float:left;padding-top:1%;}
.right {float:right;padding-top:20%;}
.tablenav-pages{padding-top:10px;text-align:center;}.form-table th {width:250px;}</style>';
}
else
{
echo '<p>No images found</p>';
}
}
function ph_get_plaghunter_images()
{
return json_decode(file_get_contents('http://www.plaghunter.com/engine/api/api.php?apiKey='.get_option( 'plaghunter_apikey').'&userId='.get_option( 'plaghunter_userid').'&function=getImages'));
}
function ph_get_images()
{
$query_images_args = array(
    'post_type' => 'attachment', 'post_mime_type' =>'image', 'post_status' => 'inherit', 'posts_per_page' => -1,
);
$query_images = new WP_Query( $query_images_args );
$images = array();
foreach ( $query_images->posts as $image) {
    $images[]= wp_get_attachment_url( $image->ID );
}
return $images;
}



function ph_plaghunter_javascript() {
?>
<script type="text/javascript" >
jQuery(document).ready(function($) {
$( ".plaghunter-link" ).live('click',function() {
var url=$(this).attr("rel");
var divid =$(this).attr("divid");
var status =$(this).attr("status");
var data = {
		'action': 'plaghunter',
		'url': url,
        'divid': divid,
        'status':status
	};
/*alert(Date.now());
	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	$.post(ajaxurl, data, function(response) {
        //$("#"+divid).html(response);
        alert(Date.now());
        location.reload(false);
	});*/
    $("#"+divid).html('<img src="<?=plugins_url('loading.gif' , __FILE__ );?>" />');
    $.ajax({
type: "POST",
url: ajaxurl,
data: data
})
.done(function( msg ) {
result=$.parseJSON(msg);
//alert(result.Message);
url=$(location).attr('href').split('&status');
if(status!=0&&result.Message=='OK')
{
//var statuss=2;
$("#"+divid).html('<span style="color:red;">Not protected by Plaghunter</span>');
}
else if(result.Message=='OK')
{
//var statuss=1;
$("#"+divid).html('<span style="color:green;">Protected by Plaghunter</span>');
}
else
{
var statuss=3;
location.href=url[0]+'&status='+statuss;
}
});


}); 
});
</script>
<?php
}


function ph_plaghunter_callback() {
//echo time()."\n";
	//global $wpdb; // this is how you get access to the database
    //$urls=get_plaghunter_images();
    //$url=$_POST['url'];
    if($_POST['status']) { 
    /*$ch = curl_init('http://www.plaghunter.com/engine/api/api.php?apiKey='.get_option( 'plaghunter_apikey').'&userId='.get_option( 'plaghunter_userid').'&function=removeImage&imageID='.$_POST['status']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
echo curl_exec($ch);
curl_close($ch);*/
    echo file_get_contents('https://www.plaghunter.com/engine/api/api.php?apiKey='.get_option( 'plaghunter_apikey').'&userId='.get_option( 'plaghunter_userid').'&function=removeImage&imageID='.$_POST['status']);
    }
    else { 
    
    /*$ch = curl_init('http://www.plaghunter.com/engine/api/api.php?apiKey='.get_option( 'plaghunter_apikey').'&userId='.get_option( 'plaghunter_userid').'&function=addImage&imageURL='.urlencode($_POST['url']));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
echo curl_exec($ch);
curl_close($ch);*/
    echo file_get_contents('https://www.plaghunter.com/engine/api/api.php?apiKey='.get_option( 'plaghunter_apikey').'&userId='.get_option( 'plaghunter_userid').'&function=addImage&imageURL='.urlencode($_POST['url']));
    }
//echo time()."\n";
exit; // this is required to return a proper result
}
