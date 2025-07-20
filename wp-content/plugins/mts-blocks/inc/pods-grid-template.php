<?php
// Optional enhanced version of inc/pods-grid-template.php
// Only modify this if you want additional filter features
?>

<!-- Enhanced Filter Container (wraps your existing filter) -->
<div class="filter-container">
    <label for="termFilter" class="filter-label">
        
        Search <?php echo esc_attr($this->columns["title"]); ?>s
    </label>
    
    <!-- Your existing filter input (enhanced with CSS) -->
    <input type="text" id="termFilter" class="term-filter" placeholder="Search by name, content, or related terms...">
    
    <!-- Optional: Add quick clear button -->
    <button type="button" id="clearFilter" class="clear-filter-btn" style="display:none;">
        ✕ Clear
    </button>
</div>

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
                'prev_text' => '‹ Previous',
                'next_text' => 'Next ›'
            ]);
            ?>
        </div>
    <?php endif;
    wp_reset_postdata();
else : ?>
    <div class="no-results">
        <p>No <?php echo esc_html($this->pod_type); ?>s found.</p>
    </div>
<?php endif; ?>

<script>
jQuery(document).ready(function($) {
    const $filter = $('#termFilter');
    const $clearBtn = $('#clearFilter');
    const $gridContainer = $('#grid-container');
    
    function loadGrid(page = 1, search = '') {
        // Add loading state
        $gridContainer.addClass('filtering');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'filter_pods_grid',
                page: page,
                search: search,
                pod_type: '<?php echo esc_js($this->pod_type); ?>'
            },
            success: function(response) {
                // Remove loading state
                $gridContainer.removeClass('filtering');
                
                // Update content with fade effect
                $('.table-row, .pagination').addClass('fade-out');
                
                setTimeout(() => {
                    $('.table-row, .pagination').remove();
                    $('.table-header').after(response);
                    $('.table-row').addClass('fade-in');
                }, 200);
                
                // Show/hide clear button
                $clearBtn.toggle(search.length > 0);
            },
            error: function() {
                $gridContainer.removeClass('filtering');
                console.error('Filter request failed');
            }
        });
    }

    // Enhanced input handling
    let searchTimeout;
    $filter.on('input', function() {
        const searchValue = $(this).val();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Debounce search (wait 300ms after user stops typing)
        searchTimeout = setTimeout(() => {
            loadGrid(1, searchValue);
        }, 300);
    });

    // Clear filter functionality
    $clearBtn.on('click', function() {
        $filter.val('').focus();
        loadGrid(1, '');
    });

    // Enhanced pagination with loading state
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const page = url.includes('paged=') ? url.split('paged=')[1].split('&')[0] : 1;
        loadGrid(page, $filter.val());
    });

    // Keyboard shortcuts
    $filter.on('keydown', function(e) {
        if (e.key === 'Escape') {
            $clearBtn.click();
        }
    });

    // Show clear button if filter has value on load
    if ($filter.val().length > 0) {
        $clearBtn.show();
    }
});
</script>

<style>
/* Additional styles specific to the enhanced template */
.clear-filter-btn {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 0.25rem;
    font-size: 0.9rem;
    transition: color 0.2s ease;
}

.clear-filter-btn:hover {
    color: #d32f2f;
}

.filter-container {
    position: relative;
}

.no-results {
    text-align: center;
    padding: 3rem 2rem;
    color: #666;
    background: white;
    border-radius: 12px;
    border: 1px solid #dee2e6;
}

.no-results p {
    margin: 0;
    font-size: 1.1rem;
}

.no-results:before {
    content: '🔍';
    display: block;
    font-size: 3rem;
    opacity: 0.5;
    margin-bottom: 1rem;
}
</style>