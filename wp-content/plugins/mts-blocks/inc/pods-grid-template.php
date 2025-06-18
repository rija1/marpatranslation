<input type="text" id="termFilter" class="term-filter" placeholder="Filter <?php echo esc_attr($this->pod_type); ?>s...">
 
 <?php if ($query->have_posts()) : ?>
    <div class="mts-block-table" id="grid-container">
        <div class="table-header">
            <?php foreach ($this->columns as $label) : ?>
                <div><?php echo esc_html($label); ?></div>
            <?php endforeach; ?>
        </div>
 
        <?php while ($query->have_posts()) : 
            $query->the_post();
            $pod = pods($this->pod_type, get_the_ID());
        ?>
            <div class="table-row">
                <?php foreach (array_keys($this->columns) as $column) : ?>
                    <div><?php echo $this->get_column_value($column, $pod); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endwhile; ?>
    </div>
 
    <?php if ($query->max_num_pages > 1) : ?>
        <div class="pagination">
            <?php
            echo paginate_links([
                'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                'format' => '?paged=%#%',
                'current' => $paged,
                'total' => $query->max_num_pages,
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;'
            ]);
            ?>
        </div>
    <?php endif;
    wp_reset_postdata();
 else : ?>
    <p>No items found.</p>
 <?php endif; ?>
 
 <style>
 /* Your existing styles here */
 </style>
 

 <script>
jQuery(document).ready(function($) {
    function loadGrid(page = 1, search = '') {
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'filter_pods_grid',
                page: page,
                search: search,
                pod_type: '<?php echo esc_js($this->pod_type); ?>' // Dynamic pod type
            },
            success: function(response) {
                $('.table-row, .pagination').remove();
                $('.table-header').after(response);
            }
        });
    }

    $('#termFilter').on('input', function() {
        loadGrid(1, $(this).val());
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        loadGrid(page, $('#termFilter').val());
    });
});
</script>