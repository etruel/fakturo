<?php
/**
 * Fakturo Reports Class for Sales Summary.
 *
 * @package Fakturo
 * @subpackage Report
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * expenses_summary_report class.
 *
 * @since 0.6
 */
class expenses_summary_report {


    /**
     * Get all salespeople
     */
    private static function get_salespeople() {
        global $wpdb;
        
        $query = "SELECT DISTINCT 
            pm.meta_value AS seller_id,
            u.display_name 
            FROM {$wpdb->postmeta} pm 
            JOIN {$wpdb->posts} p ON p.ID = pm.post_id 
            JOIN {$wpdb->users} u ON u.ID = pm.meta_value
            WHERE pm.meta_key = 'invoice_saleman' 
            AND p.post_type = 'fktr_sale'
            ORDER BY u.display_name";
            
        return $wpdb->get_results($query);
    }

    /**
     * Extract total amount from post excerpt
     */
    private static function extract_total_sum($excerpt) {
        $total_sum = 0;
        $excerpts = explode('Cliente:', $excerpt);
        foreach ($excerpts as $exc) {
            if (preg_match('/Total: (\d+)/', $exc, $matches)) {
                $total_sum += intval($matches[1]);
            }
        }
        return $total_sum;
    }

    /**
     * Add hooks for reports.
     */
    public static function hooks() {
        add_action('report_page_before_content_expenses_summary', array(__CLASS__, 'before_content'), 10, 2);
        add_action('report_page_content_expenses_summary', array(__CLASS__, 'content'), 10, 2);
        add_filter('get_objects_reports_expenses_summary', array(__CLASS__, 'get_objects'), 10, 3);
    }

    /**
     * Print HTML before content on report page.
     */
    public static function before_content($request, $ranges) {
        wp_enqueue_script('jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array('jquery'), WPE_FAKTURO_VERSION, true);
        wp_enqueue_script('fakturo_reports_expenses_summary', FAKTURO_PLUGIN_URL . 'assets/js/reports-expenses-summary.js', array('jquery'), WPE_FAKTURO_VERSION, true);
        
        // Add jQuery script for custom date range handling
        add_action('admin_footer', function() {
            ?>
            <script>
            jQuery(document).ready(function($) {
                // Function to toggle custom date inputs
                function toggleCustomDates() {
                    if ($('#range').val() === 'other') {
                        $('#custom_dates').slideDown();
                    } else {
                        $('#custom_dates').slideUp();
                    }
                }

                // Initial state
                toggleCustomDates();

                // Handle range select change
                $('#range').on('change', toggleCustomDates);

                // Form validation
                $('form[name="filter_form"]').on('submit', function(e) {
                    if ($('#range').val() === 'other') {
                        var fromDate = $('#from_date').val();
                        var toDate = $('#to_date').val();
                        
                        if (!fromDate || !toDate) {
                            e.preventDefault();
                            alert('<?php echo esc_js(__("Please select both start and end dates", "fakturo")); ?>');
                            return false;
                        }
                        
                        if (fromDate > toDate) {
                            e.preventDefault();
                            alert('<?php echo esc_js(__("Start date cannot be later than end date", "fakturo")); ?>');
                            return false;
                        }
                    }
                });
            });
            </script>
            <?php
        });
    }

    /**
     * Get sales data within the specified date range
     */
    public static function get_sales_data($request, $ranges) {
        global $wpdb;
        
        $date_condition = "";
        if (!empty($ranges['from']) && !empty($ranges['to'])) {
            $date_condition = $wpdb->prepare(
                " AND p.post_date BETWEEN %s AND %s",
                date('Y-m-d H:i:s', $ranges['from']),
                date('Y-m-d H:i:s', $ranges['to'])
            );
        }

        $seller_condition = "";
        if (!empty($request['seller_id'])) {
            $seller_condition = $wpdb->prepare(
                " AND pm2.meta_value = %s",
                $request['seller_id']
            );
        }

        $query = "SELECT 
           pm2.meta_value AS invoice_saleman,
            u.user_email,
            u.display_name,
            COUNT(*) AS total,
            GROUP_CONCAT(p.post_content SEPARATOR ' ') AS post_content,
            GROUP_CONCAT(p.post_date SEPARATOR ', ') AS post_date,
            GROUP_CONCAT(p.post_excerpt SEPARATOR 'Cliente:') AS post_excerpt 
            FROM {$wpdb->posts} p 
            LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id 
                AND pm1.meta_key = 'post_type' 
                AND pm1.meta_value = 'fktr_sale' 
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id 
                AND pm2.meta_key = 'invoice_saleman' 
            LEFT JOIN {$wpdb->users} u ON p.post_author = u.ID
            WHERE p.post_status = 'publish' 
            AND p.post_type = 'fktr_sale' 
            AND pm1.meta_key IS NOT NULL 
            AND pm2.meta_key IS NOT NULL
            {$date_condition}
            {$seller_condition}
            GROUP BY pm2.meta_value, u.ID";

        return $wpdb->get_results($query);
    }

    public static function content($request, $ranges) {
        $escalas = get_terms(array(
            'taxonomy' => 'fktr_commission_scales',
            'hide_empty' => false,
        ));
    
        self::get_form_filters($request);
        $sales_data = self::get_sales_data($request, $ranges);
    
        echo '<div class="fktr_report-info">';
        echo '<div class="fktr_info-item"><h3>' . sprintf(__('Date: since %s til %s', 'fakturo'),
            date_i18n(get_option('date_format'), $ranges['from']),
            date_i18n(get_option('date_format'), $ranges['to'])) . '</h3></div></div>';
    
        if (empty($sales_data)) {
            echo '<div style="clear: both; text-align: center;"><h2>' . __("No sales found in this date range") . '</h2></div>';
            return;
        }
    
        echo '<div class="fktr_reports_container">
            <div class="fktr_table-resp">
                <table class="wp-list-table widefat fixed striped posts">
                    <thead>
                        <tr>
                        <th>' . __('Name', 'fakturo') . '</th>
                            <th>' . __('Email', 'fakturo') . '</th>
                            <th>' . __('Total Sales', 'fakturo') . '</th>
                            <th>' . __('Last Sale Date', 'fakturo') . '</th>
                            <th>' . __('Sales', 'fakturo') . '</th>
                            <th>' . __('Commissions', 'fakturo') . '</th>
                        </tr>
                    </thead>
                    <tbody id="the-list">';
    
                    $grand_total_commission = 0;

        $base_scale = null;
        foreach ($escalas as $escala) {
            $desc = json_decode($escala->description, true);
           
            if (!empty($desc['type']) && $desc['type'] === 'base') {
                $base_scale = $desc;
                break;
            }
        }

        foreach ($sales_data as $sale) {
            $matching_scale = null;
    
            foreach ($escalas as $escala) {
                $desc = json_decode($escala->description, true);
                if (!empty($desc['seller_id']) && $desc['seller_id'] == $sale->invoice_saleman) {
                    $matching_scale = $desc;
                    break;
                }
            }
            
            if (!$matching_scale && $base_scale) {
                $matching_scale = $base_scale;
            }
    
            $total_sum = self::extract_total_sum($sale->post_excerpt);
           // $grand_total += $total_sum;
    
            $percentage = 0;
            if ($matching_scale && !empty($matching_scale['ranges'])) {
                foreach ($matching_scale['ranges'] as $range) {
                    if ($sale->total >= $range['from'] && $sale->total <= $range['to']) {
                        $percentage = $range['percentage'];
                        break;
                    }
                }
            }
          
            $commission = ($sale->total * $total_sum) * ($percentage/100);
            $grand_total_commission += $commission;
    
            echo '<tr>
            <td>' . esc_html($sale->display_name) . '</td>
                <td>' . esc_html($sale->user_email) . '</td>
                <td>' . esc_html($sale->total) . '</td>
                <td>' . esc_html(date_i18n(get_option('date_format'), strtotime($sale->post_date))) . '</td>
                <td>' . number_format($total_sum, 2, '.', ',') . '</td>
                <td>' . number_format($commission, 2, '.', ',') . '</td>
            </tr>';
        }
    
        echo '<tr class="total-row" style="font-weight: bold; background-color: #f0f0f0;">
                <td colspan="5">' . __('Grand Total', 'fakturo') . '</td>
                <td>' . number_format($grand_total_commission, 2, '.', ',') . '</td>
              </tr>';
    
        echo '</tbody></table></div></div>';

        echo '<style>
        @media print {
            body > *:not(.wp-list-table.widefat.fixed.striped.posts) {
                display: none !important;
            }
            .wp-list-table.widefat.fixed.striped.posts {
                display: block !important;
                width: 100% !important;
                border: none !important;
            }
            .wp-list-table.widefat.fixed.striped.posts th,
            .wp-list-table.widefat.fixed.striped.posts td {
                border: 1px solid #000 !important;
            }
        }
    </style>';

    // Agregar scripts necesarios para PDF
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>';

    // Agregar script para exportación
    echo '<script>
    document.getElementById("print-table-pdf").addEventListener("click", function() {
        var tables = document.querySelectorAll(".wp-list-table.widefat.fixed.striped.posts");
        
        if (tables.length > 0) {
            var { jsPDF } = window.jspdf;
            var doc = new jsPDF();
            
            var currentY = 10;
            
            tables.forEach(function(table, index) {
                if (index > 0) {
                    if (currentY > doc.internal.pageSize.height - 20) {
                        doc.addPage();
                        currentY = 10;
                    } else {
                        currentY += 10;
                    }
                }
                
                if (doc.autoTable) {
                    doc.autoTable({
                        html: table,
                        startY: currentY,
                        didDrawPage: function(data) {
                            currentY = data.cursor.y;
                        }
                    });
                }
            });

            doc.save("commission_report.pdf");
        } else {
            alert("No se encontraron tablas para exportar!");
        }
    });

    document.getElementById("download-table-csv").addEventListener("click", function() {
        var table = document.querySelector(".wp-list-table.widefat.fixed.striped.posts");
        if (table) {
            var rows = Array.from(table.querySelectorAll("tr"));
            var csvContent = "";
            
            rows.forEach(function(row) {
                var cols = Array.from(row.querySelectorAll("th, td"));
                var rowData = cols.map(function(col) {
                    return "\\"" + col.innerText.replace(/"/g, "\\"\\"") + "\\"";
                }).join(",");
                
                csvContent += rowData + "\\n";
            });

            var blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
            var link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "commission_report.csv";
            link.style.visibility = "hidden";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert("No se encontró la tabla para exportar!");
        }
    });
    </script>';
    }

    /**
     * Generate the filters form
     */
    public static function get_form_filters($request) {
        $array_range = array(
            'today' => __('Today', 'fakturo'),
            'yesterday' => __('Yesterday', 'fakturo'),
            'this_week' => __('This Week', 'fakturo'),
            'last_week' => __('Last Week', 'fakturo'),
            'this_month' => __('This Month', 'fakturo'),
            'last_month' => __('Last Month', 'fakturo'),
            'this_quarter' => __('This Quarter', 'fakturo'),
            'last_quarter' => __('Last Quarter', 'fakturo'),
            'this_year' => __('This Year', 'fakturo'),
            'last_year' => __('Last Year', 'fakturo'),
            'other' => __('Custom', 'fakturo')
        );

        $array_range = apply_filters('report_filters_range', $array_range, $request);

        // Get salespeople for the dropdown
        $salespeople = self::get_salespeople();
        
        $select_range_html = '<select name="range" id="range">';
        foreach ($array_range as $key => $value) {
            $select_range_html .= '<option value="' . $key . '" ' . selected($key, $request['range'], false) . '>' . $value . '</option>';
        }
        $select_range_html .= '</select>';

        // Create salesperson dropdown
        $select_seller_html = '<select name="seller_id" id="seller_id">
            <option value="">' . __('All Salespeople', 'fakturo') . '</option>';
        foreach ($salespeople as $seller) {
            $selected = isset($request['seller_id']) && $request['seller_id'] == $seller->seller_id ? 'selected' : '';
            $select_seller_html .= '<option value="' . esc_attr($seller->seller_id) . '" ' . $selected . '>' . 
                esc_html($seller->display_name) . '</option>';
        }
        $select_seller_html .= '</select>';

        // Custom date range inputs with improved styling
        $from_date = isset($request['from_date']) ? esc_attr($request['from_date']) : '';
        $to_date = isset($request['to_date']) ? esc_attr($request['to_date']) : '';

        $date_inputs_html = '<div id="custom_dates" class="fktr_date-ranges" style="display:none;">';
		$date_inputs_html .= '<div class="fktr_date-from"><label for="from_date">' . __( 'From', 'fakturo' ) . '</label>';
		$date_inputs_html .= '<input type="date" name="from_date" id="from_date" value="'.$from_date.'" /></div>';
		$date_inputs_html .= '<div class="fktr_date-from"><label for="to_date">' . __( 'To', 'fakturo' ) . '</label>';
		$date_inputs_html .= '<input type="date" name="to_date" id="to_date" value="'.$to_date.'" /></div>';
		$date_inputs_html .= '</div>';

        echo '<div id="div_filter_form" class="fktr_filter-form">
            <form name="filter_form" method="get" action="' . admin_url('admin.php') . '">
                <div class="fktr_filter-options">
                    <input type="hidden" name="page" value="fakturo_reports"/>
                    <input type="hidden" name="sec" value="' . $request['sec'] . '"/>
                    <div class="fktr_filter-row">
                        <div class="fktr_filter-item">
                            <label for="range">' . __('Date Range', 'fakturo') . '</label>
                            ' . $select_range_html . '
                        </div>
                        <div class="fktr_filter-item">
                            <label for="seller_id">' . __('Salesperson', 'fakturo') . '</label>
                            ' . $select_seller_html . '
                        </div>
                    </div>
                    ' . $date_inputs_html . '
                    <input type="submit" class="button-secondary" value="' . __('Filter', 'fakturo') . '"/>
                </div>
                <div class="fktr_filter-actions">
                    <input id="print-table-pdf" type="button" class="button-secondary" value="' . __('PDF', 'fakturo') . '"/>
                    <input id="download-table-csv" type="button" class="button-secondary" value="' . __('CSV', 'fakturo') . '"/>
                </div>
            </form>
        </div>
        <style>
        .fktr_filter-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        .fktr_filter-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .fktr_filter-item label {
            font-weight: bold;
        }
        </style>';
    }

    /**
     * Get objects for the report
     */
    public static function get_objects($return, $request, $ranges) {
        return self::get_sales_data($request, $ranges);
    }
}

// Initialize the hooks
expenses_summary_report::hooks();