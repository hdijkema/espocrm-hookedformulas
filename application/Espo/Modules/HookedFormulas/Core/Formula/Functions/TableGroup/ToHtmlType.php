<?php
# vim: ts=4 sw=4 et:
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2020 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: https://www.espocrm.com
 *
 * EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\TableGroup;

use Espo\Core\Exceptions\Error;

class ToHtmlType extends \Espo\Core\Formula\Functions\Base 
{
    private function filterEmail($e) 
    {
        if (preg_match('/[^@]+[@][^[@]+/', $e)) {
            $e = str_replace('@', '&#64;', $e);
        }
        return $e;
    }

    public function process(\StdClass $item)
    {

        if (count($item->value) != 1) throw new Error("Formula table\\toHtml: needs <table> as argument.");

        $table = $this->evaluate($item->value[0]);
        $ncols = count($table->header);

        $html = '';
        $rows = $table->rows;
        $styles = $table->styles;
        $cols = $table->header;
        $ncols = count($cols);
        $nrows = count($rows);

        $columns = [];
        $order = [];
        $has_widths = false; 
        for($i = 0; $i < $ncols; $i++) {
            if (isset($cols[$i]['sort'])) {
                $order_obj = (object)[];
                $order_obj->index = $i;
                $order_obj->sort = $cols[$i]['sort'];
                array_push($order, $order_obj);
            }
            $col_obj = (object)[];
            if (isset($cols[$i]['width'])) {
                $col_obj->width = $cols[$i]['width'] . '%';
                $has_widths = true;
            }
            if (isset($cols[$i]['align'])) {
                $col_obj->className = 'dt-' . $cols[$i]['align'];
            } 
            array_push($columns, $col_obj);
        }

        $filename = $table->filename;
        $id = 'data_table_id_' . uniqid();

        $html = '';
        $html .= '<div class="datatable">';
        $html .= '<table id="' . $id . '"' .
                      ' filename="' . $filename . '"' .
                      ' order="' . base64_encode(json_encode($order)) . '"' . 
                      ' columns="' . base64_encode(json_encode($columns)) . '"' .
                      ' pagelength="' . $table->rows_per_page . '"' .
                      ' has_widths="' . $has_widths . '"' . 
                      '><thead><tr>';

        for($i = 0; $i < $ncols; $i++) {
            $column = htmlentities($cols[$i]['column']);
            
            if (isset($cols[$i]['align'])) {
                $align = $cols[$i]['align'];
                if ($align == 'right') { $align = 'center'; }	// The header is left aligned or centered. We assume that right aligned column headers are not what we want, 
                                                                // Although right aligned entries are exactly what one wants. 
                $align = 'text-align:'. $align . ';';
            } else {
                $align = '';
            }

            if ($align != '') {
                $th = '<th style="'.$align.'">';
            } else {
                $th = '<th>';
            } 

            $html .= $th.$column.'</th>';
        }
        $html .= '</tr></thead><tbody>';

        for($i = 0; $i < $nrows; $i++) {
            $style = $styles[$i];

            $html .= '<tr>';
            $row = $rows[$i];
            for($j = 0; $j < $ncols; $j++) {

                $val = $row[$j];
                if (is_array($val)) {
                    if (isset($val['target'])) { $target = ' target="'.$val['target'].'" '; }
                    else { $target = ''; }
                    $title = htmlentities($this->filterEmail($val['val']));
                    $val = '<a href="'.$val['href'].'"'.$target.'>'.htmlentities($val['val']).'</a>';
                } else {
                    $val = htmlentities($val);
                    $title = $this->filterEmail($val);
                }

                if (isset($cols[$j]['ellipsis'])) {
                    $el = 'max-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;';
                } else {
                    $el = '';
                }

                if (isset($cols[$j]['align'])) {
                    $align = 'text-align:'.$cols[$j]['align'].';';
                } else {
                    $align = '';
                }

                if ($style != '') {
                    $td = '<td style="'.$style.$el.$align.'" title="'.$title.'">';
                } else if ($el != '') {
                    $td = '<td style="'.$el.$align.'" title="'.$title.'">';
                } else if ($align != '') {
                    $td = '<td style="'.$align.'" title="'.$title.'">';
                } else {
                    $td = '<td title="'.$title.'">';
                }

                $html .= $td . $val . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        $html .= '</div>';

        return $html;
    }
}

