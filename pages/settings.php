<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php
    outd_check_errors( 'outd_group' );
    ?>
    <form action="options.php" method="post">
    <?php 
        settings_fields( 'outd_group' );
        do_settings_sections( 'outd_page1' );
        do_settings_sections( 'outd_page2' );
        do_settings_sections( 'outd_page3' );
        submit_button( 'Salvar Configurações' );
    ?>
    </form>
</div>