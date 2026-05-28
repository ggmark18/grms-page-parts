<?php
add_action( 'wpcf7_init', 'wpcf7_add_apgifu_tag' );
function wpcf7_add_apgifu_tag() {
    wpcf7_add_form_tag("grms_tablepress_select","grms_tablepress_select");
}
function grms_tablepress_select($attr, $content = null) {
    $tid = null;
    $notitle = false;
    $options = $attr['options'];
    $name = $options[0];
    foreach ($options as $option):
        if ( $option === reset($options)) continue;
        if( preg_match('/tid:([0-9]+)/', $option, $matches) ) {
            $tid = $matches[1];
        } else if( $option === 'notitle' ) {
            $notitle = true;
        }
    endforeach;
    if( $tid ) {
        $datas = TablePress::$controller->model_table->load($tid);
        $datas = $datas['data'];
        $content = '<table border="0" class="'.$name.'">';
        foreach ($datas as $data):
            if ($notitle && $data === reset($datas)) continue;
            $content .= '<tr><td width="10px">';
            $content .= '<input type="checkbox" name="'.$name.'[]" value="'.$data[0].'"></td><td>';
            $content .= $data[0];
            $content .= '</td><tr>';
       endforeach;
       $content .= '</table>';
    }
    return $content;
}
