<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <hr class="wp-header-end">

    <p>
        
    <?php
    require_once OUTD_DIR . 'view.php';
    ?>

    <script>
        document.getElementById("outd_btn_start").setAttribute("class", "")
        document.getElementById("outd_btn_start").classList.add("page-title-action");
    </script>
    <style>
        #outd_list_media{
            display:none;
        }
    </style>
</div>