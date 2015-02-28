<?php
/**
* Блок корзина товаров
* 
* @author Igor Ognichenko
* @copyright Copyright (c)2007-2010 by Kasseler CMS
* @link http://www.kasseler-cms.net/
* @filesource blocks/block-shopping_cart.php
* @version 2.0
*/
if (!defined('BLOCK_FILE')) {
    Header("Location: ../index.php");
    exit;
}
global $main, $shop;

main::add2script("modules/shop/script.js");

$ids = ""; $i=1; $sum = 0;
$id = rand(0, 5);
echo "<table width='100%'><tr><td height='18'><h2 class='shopping_cart_block'>{$main->lang['shopping_cart_block']}</h2></td><td align='center'><img src='includes/images/loading/small.gif' alt='Loading' style='display: none;' class='shopping_cart_loading' /></td><td align='right'><a href='#' onclick='return clear_shoped();' class='clear_shoped'>{$main->lang['shopping_cart_clears']}</a></td></tr><tr><td colspan='3'>".
"<div class='shopping_cart' id='vertscroll_{$id}'>";
if(isset($_SESSION['shopping_cart']) AND is_array($_SESSION['shopping_cart']) AND count($_SESSION['shopping_cart'])>0){
    foreach($_SESSION['shopping_cart'] AS $key => $count) $ids .= "{$key},";
    $ids = mb_substr($ids, 0, mb_strlen($ids)-1);
    $result = $main->db->sql_query("SELECT * FROM ".SHOP." WHERE id IN({$ids}) ORDER BY BINARY(title)");
    $count = $main->db->sql_numrows($result);         
    echo "<table width='100%' class='reset'>";
    while(($row = $main->db->sql_fetchrow($result))){
        if(!isset($_SESSION['shopping_cart'][$row['id']])) continue;
        $sum += $row['pay']*$_SESSION['shopping_cart'][$row['id']];
        echo "<tr class='row_cont'>
            <td>{$row['title']}</td>
            <td width='50'><div class='input_counter'><span><input class='hidden_pay_{$row['id']}' value='{$row['pay']}' type='hidden' name='hidden_pay_{$row['id']}' /><input type='text' class='cart_count_{$row['id']}' name='cart_count_{$row['id']}' value='{$_SESSION['shopping_cart'][$row['id']]}' readonly='readonly' /></span><a class='plus' href='#' onclick=\"return add_goods_update({$row['id']}, '+', {$row['pay']}, '{$shop['symbol_rate']}')\"><img src='includes/images/pixel.gif' alt='+' title='{$main->lang['add_goods']}' /></a><a class='minus' href='#' onclick=\"return add_goods_update({$row['id']}, '-', {$row['pay']}, '{$shop['symbol_rate']}')\"><img src='includes/images/pixel.gif' alt='-' title='{$main->lang['subtract_goods']}' /></a></div></td>
            <td width='20' nowrap='nowrap'><b style='font-size: 10px;' class='sum_product_{$row['id']}'>".($row['pay']*$_SESSION['shopping_cart'][$row['id']])." {$shop['symbol_rate']}</b></td>
            <td width='20'><a class='delete' href='#' onclick='return delete_product({$row['id']});'><img src='includes/images/pixel.gif' alt='{$main->lang['delete']}' title='{$main->lang['delete']}' /></a></td>
        </tr>";
        $i++;
    }
    echo "</table>";    
} else echo "<center><i style='color: #888888'>{$main->lang['empty_cart']}</i></center>";

echo "</div><table width='100%'><tr><td><h5>{$main->lang['sum_price']}:</h5></td><td align='right'><div class='orders_pay'><h2 class='price'>{$sum} {$shop['symbol_rate']}</h2></div></td></tr></table>";
echo ((isset($_GET['do']) AND $_GET['do']!='order' AND $_GET['do']!='send_order') OR !isset($_GET['do'])) ? "<a href='".$main->url(array('module' => 'shop', 'do' => "order"))."' id='bay_product'></a><input onclick=\"location.href=document.getElementById('bay_product').href;\" type='submit' class='fbutton' value='{$main->lang['bay_product']}' />" : "";
echo "</td></tr></table>";
?>