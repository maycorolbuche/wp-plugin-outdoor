<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
    <a class="page-title-action" href='javascript:' onclick='outd_open_media_window()'>Adicionar Mídia</a>
    <a class="page-title-action" href='?page=outdoor_preview'>Visualizar Outdoor</a>

    <hr class="wp-header-end">

    <ul class="subsubsub">
        <li class="all">
            <a href="<?php echo sanitize_text_field($url . $symbol) ?>outdoor_status=all" <?php echo ($status == '' || $status == 'all' ? sanitize_text_field($current) : '') ?>>
                Todos <span class="count">(<?php echo $qt ?>)</span>
            </a> |
        </li>
        <li class="active">
            <a href="<?php echo sanitize_text_field($url . $symbol) ?>outdoor_status=active" <?php echo ($status == 'active' ? sanitize_text_field($current) : '') ?>>
                Ativos <span class="count">(<?php echo $qt_active ?>)</span>
            </a> |
        </li>
        <li class="inactive">
            <a href="<?php echo sanitize_text_field($url . $symbol) ?>outdoor_status=inactive" <?php echo ($status == 'inactive' ? sanitize_text_field($current) : '') ?>>
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