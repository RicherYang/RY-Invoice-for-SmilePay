<?php

namespace RY\Invoice\V20260805\ListTable;

defined('ABSPATH') or exit;

include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class Track extends \WP_List_Table
{
    public function get_columns()
    {
        return [
            'year' => _x('Year', 'Track table header', 'ry-invoice-for-smilepay'),
            'term' => _x('Term', 'Track table header', 'ry-invoice-for-smilepay'),
            'code' => _x('Code', 'Track table header', 'ry-invoice-for-smilepay'),
            'start_no' => _x('Start number', 'Track table header', 'ry-invoice-for-smilepay'),
            'end_no' => _x('End number', 'Track table header', 'ry-invoice-for-smilepay'),
            'now_no' => _x('Current number', 'Track table header', 'ry-invoice-for-smilepay'),
            'unused' => _x('Unused quantity', 'Track table header', 'ry-invoice-for-smilepay'),
            'trackcode' => _x('Track code', 'Track table header', 'ry-invoice-for-smilepay'),
            'status' => _x('Status', 'Track table header', 'ry-invoice-for-smilepay'),
        ];
    }

    protected function column_unused($item)
    {
        if (empty($item['now_no'])) {
            return '';
        }

        $total = (int) $item['end_no'] - (int) $item['start_no'] + 1;
        $used = (int) $item['now_no'] - (int) $item['start_no'] + 1;
        $unused = $total - $used;
        return sprintf('%d ( %.1f%% )', $unused, $unused / $total * 100);
    }

    protected function column_default($item, $column_name)
    {
        return $item[$column_name] ?? '';
    }

    public function display()
    {
        echo '<table class="wp-list-table ' . implode(' ', ['widefat', 'striped', 'table-view-list']) . '">';
        $this->print_table_description();
        echo '<thead><tr>';
        $this->print_column_headers();
        echo '</tr></thead>';
        echo '<tbody id="the-list">';
        $this->display_rows_or_placeholder();
        echo '</tbody></table>';
    }
}
