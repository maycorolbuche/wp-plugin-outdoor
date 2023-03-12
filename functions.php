<?php
// don't load directly
if (!defined('ABSPATH')) {
    die('-1');
}

require_once OUTD_DIR . 'actions.php';
require_once OUTD_DIR . 'table.php';

function outd_load_scripts()
{
    wp_enqueue_media();
    wp_enqueue_style('outd-style', OUTD_URL_CSS . 'style.css', array(), filemtime(OUTD_DIR_CSS . 'style.css'), 'all');
    wp_enqueue_script('outd-scripts', OUTD_URL_JS . 'scripts.js', array(), filemtime(OUTD_DIR_JS . 'scripts.js'), true);
}
add_action('admin_enqueue_scripts', 'outd_load_scripts');

add_action('admin_menu', 'outd_custom_menu');
function outd_custom_menu()
{
    global $outd_sample_page;

    $outd_sample_page = add_menu_page(
        'Outdoor',
        'Outdoor',
        'manage_options',
        'outdoor',
        'outd_render_page',
        'dashicons-desktop',
        10
    );
/*
    add_submenu_page(
        'outdoor',
        'Visualizar',
        'Visualizar',
        'manage_options',
        'outdoor_preview',
        null,
        10
    );
*/
    add_action("load-$outd_sample_page", "outd_sample_screen_options");
}

// add screen options
function outd_sample_screen_options()
{

    global $outd_sample_page;
    global $table;

    $screen = get_current_screen();

    // get out of here if we are not on our settings page
    if (!is_object($screen) || $screen->id != $outd_sample_page) {
        return;
    }

    $args = array(
        'label' => 'Elementos por página',
        'default' => 20,
        'option' => 'elements_per_page',
    );
    add_screen_option('per_page', $args);

    $table = new Outd_List_Table();

}

function outd_render_page()
{
    global $wpdb;

    $table = new Outd_List_Table();
    $url = get_admin_url() . 'admin.php';
    $symbol = '?';

    if (isset($_GET["page"])) {
        $url = $url . $symbol . 'page=' . esc_html($_GET['page']);
        $symbol = '&';
    }
    if (isset($_GET["orderby"])) {
        $url = $url . $symbol . 'orderby=' . esc_html($_GET['orderby']);
        $symbol = '&';
    }
    if (isset($_GET["order"])) {
        $url = $url . $symbol . 'order=' . esc_html($_GET['order']);
        $symbol = '&';
    }

    if (isset($_GET["outdoor_status"]) && $_GET["outdoor_status"] != '') {
        $status = esc_html($_GET["outdoor_status"]);
    } else {
        $status = "active";
    }
    $current = 'class="current" aria-current="page"';

    $table_name = $wpdb->prefix . 'outdoor';
    $qt_active = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE outdoor_status = 'active'");
    $qt_inactive = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE outdoor_status = 'inactive'");
    $qt = $qt_active + $qt_inactive;
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Outdoor</h1>
        <a class="page-title-action" href='javascript:' onclick='outd_open_media_window()'>Adicionar Mídia</a>
        <a class="page-title-action" href='<?php echo plugin_dir_url(__FILE__) ?>presentation.php' target='_blank'>Visualizar Outdoor</a>

        <hr class="wp-header-end">

        <ul class="subsubsub">
            <li class="all">
                <a href="<?php echo $url . $symbol ?>outdoor_status=all" <?php echo ($status == '' || $status == 'all' ? $current : '') ?>>
                    Todos <span class="count">(<?php echo $qt ?>)</span>
                </a> |
            </li>
            <li class="active">
                <a href="<?php echo $url . $symbol ?>outdoor_status=active" <?php echo ($status == 'active' ? $current : '') ?>>
                    Ativos <span class="count">(<?php echo $qt_active ?>)</span>
                </a> |
            </li>
            <li class="inactive">
                <a href="<?php echo $url . $symbol ?>outdoor_status=inactive" <?php echo ($status == 'inactive' ? $current : '') ?>>
                    Desativados <span class="count">(<?php echo $qt_inactive ?>)</span>
                </a>
            </li>
        </ul>

        <form method="post" novalidate>
            <input type="hidden" name="action_row">
            <input type="hidden" name="id_row">
            <input type="hidden" name="field">
            <input type="hidden" name="value">
        <?php
$table->prepare_items();
    $table->search_box('Buscar', 'search_id');
    $table->display();
    ?>
        </form>

    </div>

    <?php
}