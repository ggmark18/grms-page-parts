<?php
class GRMS_TablePress {
    function __construct() {
        add_shortcode('grms_committee', array( &$this, 'committee_list'));
    }

    function committee_list( $attr ) {
        $content = "";
        $datas = TablePress::$model_table->load($attr['tid']);
        if( is_array($datas) && array_key_exists('name', $datas) ) {
            $cname = $datas['name'];
            $datas = $datas['data'];
            $title = "";
            if( array_key_exists('title', $attr) && $attr['title'] == 'true' ) {
                $content .= '<div class="grms-committee-title">'.$cname.'</div>';
            }
            $content .= '<div class="grms-committee"><div style="display:none"><div style="display:none">';
            foreach ($datas as $data):
                if( $title != $data[0] ) {
                    $content .= '</div></div><div class="grms-group"><div class="grms-title">'.$data[0].'</div><div class="grms-itmes">';
                    $title = $data[0];
                }
            $content .= '<div class="grms-grid-item">'.$data[1].'</div>';
            endforeach;

            $content .=  '</div></div></div>';
       }
        return $content;
     }
}

$grms_tablepress = new GRMS_TablePress();
    

