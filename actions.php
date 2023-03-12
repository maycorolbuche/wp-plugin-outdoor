<?php
// don't load directly
if (!defined('ABSPATH')) {
    die('-1');
}

$outd_table_name = $wpdb->prefix . 'outdoor';

$outd_url = get_admin_url() . 'admin.php';
$oud_symbol = '?';

if (isset($_GET["page"])) {
    $outd_url = $outd_url . $oud_symbol . 'page=' . esc_html($_GET['page']);
    $oud_symbol = '&';
}
if (isset($_GET["orderby"])) {
    $outd_url = $outd_url . $oud_symbol . 'orderby=' . esc_html($_GET['orderby']);
    $oud_symbol = '&';
}
if (isset($_GET["order"])) {
    $outd_url = $outd_url . $oud_symbol . 'order=' . esc_html($_GET['order']);
    $oud_symbol = '&';
}
if (isset($_GET["paged"])) {
    $outd_url = $outd_url . $oud_symbol . 'paged=' . esc_html($_GET['paged']);
    $oud_symbol = '&';
}
if (isset($_GET["outdoor_status"])) {
    $outd_url = $outd_url . $oud_symbol . 'outdoor_status=' . esc_html($_GET['outdoor_status']);
    $oud_symbol = '&';
}

function outd_adjust_lines()
{
    global $wpdb, $outd_table_name;

    $result = $wpdb->get_results(" SELECT outdoor_id FROM  $outd_table_name ORDER BY outdoor_status,outdoor_order ");

    foreach ($result as $order => $page) {
        $wpdb->update($outd_table_name,
            array(
                'outdoor_order' => $order + 1,
            ),
            array(
                'outdoor_id' => $page->outdoor_id,
            )
        );
    }

}

if (isset($_POST["action_row"]) && $_POST["action_row"] == 'edit') {

    $_field = sanitize_text_field($_POST["field"]);
    $_value = sanitize_text_field($_POST["value"]);
    $_id = sanitize_text_field($_POST["id_row"]);

    if ($_field == "outdoor_order") {
        $result = $wpdb->get_results(" SELECT outdoor_id,outdoor_order FROM  $outd_table_name WHERE outdoor_id = " . $_id);
        if ($result[0]->outdoor_order < $_value) {
            $_value += 1;
        }

        $wpdb->query("UPDATE $outd_table_name SET outdoor_order = outdoor_order + 1 WHERE outdoor_order >= $_value");
    }

    $wpdb->query("UPDATE $outd_table_name SET $_field = '$_value' WHERE outdoor_id = $_id");

    outd_adjust_lines();
    header('Location: ' . $outd_url);
    exit;
}

if (isset($_POST["outd_add_media"])) {

    $ids = explode(',', sanitize_text_field($_POST["outd_add_media"]));

    $wpdb->query("UPDATE $outd_table_name SET outdoor_order = outdoor_order + " . count($ids) . " WHERE outdoor_status = 'active'");

    foreach ($ids as $order => $id) {
        if ($id > 0) {
            $wpdb->insert($outd_table_name,
                array(
                    'post_id' => absint($id),
                    'outdoor_status' => 'active',
                    'outdoor_order' => absint($order),
                )
            );
        }
    }

    outd_adjust_lines();
}

if (isset($_REQUEST["action"]) && $_REQUEST["action"] == 'delete') {
    $ids = [];
    if (is_array($_REQUEST["element"])) {
        foreach ($_REQUEST["element"] as $item) {
            $ids[] = absint($item);
        }
    } else {
        $ids[] = absint($_REQUEST["element"]);
    }

    foreach ($ids as $id) {
        $wpdb->delete($outd_table_name,
            array(
                'outdoor_id' => $id,
            )
        );
    }

    outd_adjust_lines();

    header('Location: ' . $outd_url);
    exit;
}

if (isset($_REQUEST["action"]) && ($_REQUEST["action"] == 'active' || $_REQUEST["action"] == 'inactive')) {
    $ids = [];
    if (is_array($_REQUEST["element"])) {
        foreach ($_REQUEST["element"] as $item) {
            $ids[] = absint($item);
        }
    } else {
        $ids[] = absint($_REQUEST["element"]);
    }

    foreach ($ids as $id) {
        $wpdb->update($outd_table_name,
            array(
                'outdoor_status' => sanitize_text_field($_REQUEST["action"]),
            ),
            array(
                'outdoor_id' => $id,
            )
        );
    }

    outd_adjust_lines();

    header('Location: ' . $outd_url);
    exit;
}
