<?php
// don't load directly
if (!defined('ABSPATH')) {
    die('-1');
}

require_once OUTD_DIR . 'actions.php';
require_once OUTD_DIR . 'table.php';

add_action('init', 'outd_register_shortcodes');
add_action('admin_init', 'outd_register_settings');
add_action('admin_enqueue_scripts', 'outd_load_scripts');
add_action('admin_menu', 'outd_custom_menu');

function outd_load_scripts()
{
    wp_enqueue_media();
    wp_enqueue_style('outd-style', OUTD_URL_CSS . 'style.css', array(), filemtime(OUTD_DIR_CSS . 'style.css'), 'all');
    wp_enqueue_script('outd-scripts', OUTD_URL_JS . 'scripts.js', array(), filemtime(OUTD_DIR_JS . 'scripts.js'), true);
}

function outd_custom_menu()
{
    global $outd_sample_page;

    $outd_sample_page = add_menu_page(
        'Outdoor',
        'Outdoor',
        'manage_options',
        'outdoor',
        'outd_main_page',
        'dashicons-desktop',
        10
    );

    add_submenu_page(
        'outdoor',
        'Visualizar',
        'Visualizar',
        'manage_options',
        'outdoor_preview',
        'outd_preview_page',
        20
    );

    add_submenu_page(
        'outdoor',
        'Configurações',
        'Configurações',
        'manage_options',
        'outdoor_settings',
        'outd_settings_page',
        30
    );

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

function outd_main_page()
{
    global $wpdb;

    if (!current_user_can('manage_options')) {
        return;
    }

    $table = new Outd_List_Table();
    $url = get_admin_url() . 'admin.php';
    $symbol = '?';

    if (isset($_GET["page"])) {
        $url = $url . $symbol . 'page=' . sanitize_text_field($_GET['page']);
        $symbol = '&';
    }
    if (isset($_GET["orderby"])) {
        $url = $url . $symbol . 'orderby=' . sanitize_text_field($_GET['orderby']);
        $symbol = '&';
    }
    if (isset($_GET["order"])) {
        $url = $url . $symbol . 'order=' . sanitize_text_field($_GET['order']);
        $symbol = '&';
    }

    if (isset($_GET["outdoor_status"]) && $_GET["outdoor_status"] != '') {
        $status = sanitize_text_field($_GET["outdoor_status"]);
    } else {
        $status = "active";
    }
    $current = 'class="current" aria-current="page"';

    $table_name = $wpdb->prefix . 'outdoor';
    $qt_active = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE outdoor_status = 'active'");
    $qt_inactive = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE outdoor_status = 'inactive'");
    $qt = $qt_active + $qt_inactive;

    require_once OUTD_DIR_PAGES . 'main.php';
}

function outd_preview_page()
{
    global $wpdb;

    if (!current_user_can('manage_options')) {
        return;
    }

    require_once OUTD_DIR_PAGES . 'preview.php';
}

function outd_settings_page()
{
    global $wpdb;

    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['settings-updated'])) {
        add_settings_error('outd_options', 'outd_message', 'Configurações salvas com sucesso!', 'success');
    }

    settings_errors('outd_options');

    require_once OUTD_DIR_PAGES . 'settings.php';
}

function outd_register_shortcodes()
{
    add_shortcode('outdoor', 'outd_add_shortcode');
    function outd_add_shortcode()
    {
        global $wpdb;

        ob_start();
        require_once OUTD_DIR . 'view.php';
        return ob_get_clean();
    }
}

function outd_register_settings()
{
    register_setting('outd_group', 'outdoor_options', 'outd_validate');

    add_settings_section(
        'outd_main_section',
        'Como usar o plugin?',
        null,
        'outd_page1'
    );

    add_settings_field(
        'outd_shortcode',
        'Shortcode',
        'outd_shortcode_callback',
        'outd_page1',
        'outd_main_section'
    );

    add_settings_section(
        'outd_2nd_section',
        'Opções do Plugin',
        null,
        'outd_page2'
    );

    add_settings_field(
        'outd_random',
        'Ordem aleatória?',
        'outd_random_callback',
        'outd_page2',
        'outd_2nd_section',
        array(
            'label_for' => 'outd_random',
        )
    );

    add_settings_section(
        'outd_3rd_section',
        'Opções da Tabela',
        null,
        'outd_page3'
    );

    add_settings_field(
        'outd_tb_thumb',
        'Exibir miniatura?',
        'outd_tb_thumb_callback',
        'outd_page3',
        'outd_3rd_section',
        array(
            'label_for' => 'outd_tb_thumb',
        )
    );
}

function outd_validate($input)
{
    $new_input = array();
    foreach ($input as $key => $value) {
        $new_input[$key] = sanitize_text_field($value);
    }
    return $new_input;
}

function outd_shortcode_callback($args)
{
    ?>
    <span>Use o código (shortcode) <b>[outdoor]</b> para exibir o botão de "Iniciar Outdoor" em sua página/post/widget</span>
    <?php
}

function outd_random_callback($args)
{
    $options = get_option('outdoor_options');
    ?>
        <input
            type="checkbox"
            name="outdoor_options[random]"
            id="outd_random"
            value="1"
            <?php if (isset($options['random'])) {checked("1", $options['random'], true);}?>
        />
        <label for="random">Se marcado, as mídias seguirão em ordem aleatória.</label>

    <?php
}

function outd_tb_thumb_callback($args)
{
    $options = get_option('outdoor_options');
    ?>
        <input
            type="checkbox"
            name="outdoor_options[thumb]"
            id="outd_tb_thumb"
            value="1"
            <?php if (isset($options['thumb'])) {checked("1", $options['thumb'], true);}?>
        />
        <label for="thumb">
            Se marcado, a tabela exibirá uma coluna com a miniatura do vídeo/imagem.
            <br>
            <small><i>(Pode causar perda de desempenho)</i></small>

        </label>

    <?php
}

function outd_check_errors()
{
    global $wpdb;

    $outd_table_name = $wpdb->prefix . 'outdoor';

    $result = $wpdb->get_results("SHOW TABLES LIKE '$outd_table_name'");
    if (count($result) <= 0) {
        $url = get_admin_url() . 'admin.php?page=' . sanitize_text_field($_GET['page']) . '&action=reinstall';
        add_settings_error('outd_err', 'outd_message', 'Outdoor - Houve uma falha na instalação deste plugin. <a href="' . esc_html($url) . '">Clique aqui</a> para tentar corrigir o problema!', 'error');
        settings_errors('outd_err');
    }
}