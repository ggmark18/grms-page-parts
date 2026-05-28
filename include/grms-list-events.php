<?php
class GRMS_EventList {
    static $week = [
        '日', //0
        '月', //1
        '火', //2
        '水', //3
        '木', //4
        '金', //5
        '土', //6
    ];

    function __construct() {

        add_shortcode('grms_event_list', array( &$this, 'event_list'));
        if ( is_admin() ) {
            add_action('admin_menu', array( &$this, 'add_event_fields' ));
            add_action('admin_head', array( &$this, 'show_event_fields' ));
            add_filter('use_block_editor_for_post', array( &$this, 'editor_select' ),0,2);
            add_action('save_post', array( &$this, 'save_event_fields'), 11, 1);
        }
    }

    function editor_select( $flag, $post ) {
        /*
        if ( isset( $_GET['grms-type'] ) ) {
            if ($_GET['grms-type'] == 'event') {
                return false;
            }
        } else if( has_tag('event', $post) ) {
            return false;
        }
        */
        return true;
    }

    function add_event_fields() {
        add_submenu_page('edit.php', 'イベント投稿追加', 'イベント投稿追加', 'manage_options', 'post-new.php?grms-type=event',null,2);
                         //                         array(&$this, 'new_event'), 2);
        add_meta_box( 'grms-event-setting', 'イベント情報', array( &$this,'insert_event_fields'), 'post', 'normal','high');
    }
    function show_event_fields() {

        if ( isset( $_GET['grms-type'] ) ) {
            if ($_GET['grms-type'] == 'event') {
                global $post;
                //スラッグを指定するための配列
                $args = array('slug'=>'event');
                //タグの場合
                $event_tag = get_tags($args);
                if( !empty($event_tag)) {
                    wp_set_post_tags($post->ID, array($event_tag[0]->name), true);
                }
            }
        }
        if ( has_tag( 'event' ) ) {
            echo "<style>#grms-event-setting {display:block;}</style>";
        } else {
            echo "<style>#grms-event-setting {display:none;}</style>";
        }
    }

    function event_list( $attr ) {
        
        $content = "";
        
        $post_args = array();
        
        $cat = NULL;
        if( $attr && array_key_exists('tag', $attr) ) {
            $tag_attr = $attr['tag'];
            $tag  = get_tags(array('slug' => $tag_attr));
            if( count($tag) > 0 ) {
                $post_args['tag__in'] = array($tag[0]->term_id);
            }
        }
        $post_args['meta_query'] = array(
            'key' => 'event_start',
            'value'   => date( "Y/m/d" ),
            'compare' => '>=',);
        $post_args['meta_key'] = 'event_start';
        $post_args['order']    = 'ASC';
        $post_args['orderby']  = 'meta_value';


        $today = new DateTime();

        //$content .= '<p class="grms-event-today">';
        //$content .= $today->format('Y年n月j日');
        //$content .= '（'.GRMS_EventList::$week[$today->format('w')].'）';
        //$content .= '</p>';

        $today_year = $today->format('Y');
        $today_month = $today->format('n');
        $today_day = $today->format('j');

        $current_year = $today_year;
        $current_month = "";
        $current_day = "";


        //        $content .= '<div class="grms-event-month">';
        //        $content .= $current_month.'月';
        //        $content .= '</div>';
        
        $post_query = new WP_Query( $post_args );

        if ( $post_query->have_posts() ) {
            while ( $post_query->have_posts() ) {
                $post_query->the_post();
                $post_id = get_the_id();
                
                $event_start = explode(" ",get_post_meta($post_id, 'event_start', true));
                $event_end = explode(" ",get_post_meta($post_id, 'event_end', true));

                preg_match('/^([0-9]+)\/([0-9]+)\/([0-9]+)/', $event_start[0], $matches);
                $start_datetime = new DateTime($matches[1].'-'.$matches[2].'-'.$matches[3]);

                $event_year  = $matches[1];
                $event_month = ltrim($matches[2],"0");
                $event_day = ltrim($matches[3],"0");

                $month_div = "";
                $todays_event = ( $event_year == $today_year
                                  && $event_month == $today_month
                                  && $event_day == $today_day ) ? true : false;
                if( $event_month != $current_month ) {
                    $month_div .= '<div class="grms-event-month">';
                    if( $event_year != $current_year ) {
                        $month_div .= $event_year.'年';
                    }
                    $month_div .= $event_month.'月';
                    $month_div .= '</div>';
                }

                if( strlen($month_div) && !$todays_event ) {
                    $content .= $month_div;
                }

                $current_year = $event_year;
                $current_month = $event_month;
                $current_day = $event_day;

                $content .= '<div class="row grms-active-article">';
                $content .= '<div class="col-sm-9 grms-active-posts">';
                
                $content .= '<div class="grms-active-posts__category ">';
                $content .= '<div class="event-date ">';

                $content .= $this->makeEvetDateString( $event_start, $start_datetime, $event_end, $todays_event,
                                                       ($event_year == $today_year) );
                $content .= '</div>';
                $content .= '</div>';
                
                $content .= '<div class="grms-active-posts__title">';
                $content .= '<a href="?p='.$post_id.'"><span class="title">';
                $content .= get_the_title();
                $content .= '</span></a></div>';

                $excerpt = "";
                $event_place = get_post_meta($post_id, 'event_place', true);
                if( strlen($event_place) > 0 ) {
                    $excerpt = "場所：".$event_place;
                } else {
                    $excerpt = get_the_excerpt();
                }
                
                $content .= '<div class="grms-active-posts__content">';
                $content .= $excerpt;
                $content .= '</div>';
                
                $content .= "</div>";

                $daydiff = $today->diff($start_datetime)->format('%a');

                // TODO: 7 from plugin parameter
                $thumbnail_class = 'grms-active-thumbnail';
                if( $daydiff < 7 ) $thumbnail_class .= "-near";
                
                $content .= '<div class="col-sm-3 '.$thumbnail_class.'" ><a href="?p='.$post_id.'">';
                $imgid = get_post_thumbnail_id();
                $imageurl = wp_get_attachment_image_src($imgid, 'small-thumbnail' );
                $content .= '<img src="'.$imageurl[0].'" height="120px" class="mb-0"/>';
                
                $content .= '<div class="grms-category-title">';                
                $post_categories = wp_get_post_categories( $post_id );
                $cats = array();
                foreach( $post_categories as $c ){
                    $cat = get_category( $c );
                    $content .= $cat->name;
                }
                $content .= '</div>';
                
                $content .= '</a></div>';
                
                $content .= "</div>";
            }

            /* オリジナルの投稿データを復元
             * 注意: WP_Query を使っているのでオリジナルの $wp_query を壊すことは
             * なく、wp_reset_query() によってリセットする必要はありません。
             * 投稿データを wp_reset_postdata() で復元することだけが必要です。
             */
            wp_reset_postdata();
        }
        return $content;
    }
    function makeEvetDateString( $event_start, $start_datetime, $event_end, $todays_event, $this_year ) {
        $start_day = $event_start[0];
        $start_time = (count($event_start) > 1) ? $event_start[1] : null;
        $end_day = (count($event_end) > 0) ? $event_end[0] : null;
        $end_time = (count($event_end) > 1) ? $event_end[1] : null;
        
        $event_term = "";
        
        if( !$todays_event ) {
            if( $this_year ) {
                $event_term .= $start_datetime->format('n月j日');
            } else {
                $event_term .= $start_datetime->format('Y年n月j日');
            }
            $event_term .= '('.GRMS_EventList::$week[$start_datetime->format('w')].')';            
        }
        
        if ( $start_time ) {
            $event_term .= " ".$start_time;
        }
        if ( $end_day ) {

            preg_match('/^([0-9]+)\/([0-9]+)\/([0-9]+)/', $end_day, $matches);
            $end_datetime = new DateTime($matches[1].'-'.$matches[2].'-'.$matches[3]);
            
            $event_term .= " - ";
            if( $start_day == $end_day ) {
                if ( $end_time ) {
                    $event_term .= $end_time;
                }
            } else {
                if( $this_year ) {
                    $event_term .= $end_datetime->format('n月j日');
                } else {
                    $event_term .= $end_datetime->format('Y年n月j日');
                }
                $event_term .= '('.GRMS_EventList::$week[$end_datetime->format('w')].')';            

                if ( $end_time ) {
                    $event_term .= " ".$end_time;
                }
            }
        }
        return $event_term;
    }

    function insert_event_fields() {
        global $post;

        $this->insert_datetimepicker_html('開始','event_start');
        $this->insert_datetimepicker_html('終了','event_end');
        $this->insert_datetimepicker_script(array('event_start','event_end'));
            
        $value = get_post_meta($post->ID, 'event_place', true);
?>
        <div class="form-group place-field">
        <div class="input-group" id="event-place" >
          <label for="event-place" class="pt-2 pr-0"> 場所：</label>
           <input type="text" class="form-control" name="event_place" <?php echo 'value="'.$value.'"' ?> />
           <span class="input-group-append">
              <span class="input-group-text"><i class="fa fa-map"></i></span>
           </span>
        </div>
        </div>
<?php      
    }
    function insert_datetimepicker_html($label, $idbase) {
        global $post;
        
        $dpid = $idbase.'-date';
        $tpid = $idbase.'-time';

        $datetime = get_post_meta($post->ID, $idbase, true);
        $res = explode(" ", $datetime);
        $date = "";
        $time = "";
        if( count($res) > 0 ) {
            $date = $res[0];
        }
        if( count($res) > 1 ) {
            $time = $res[1];
        }

?>
    <div class="form-row datetime-field" >
      <div class="form-group col-sm-6">
        <div class="input-group date" <?php echo 'id="'.$dpid.'"' ?> >
           <label class="col-form-label"><?php echo $label ?>：</label>
             <input type="text" class="form-control" <?php echo 'name="'.$dpid.'" value="'.$date.'"' ?>/>
           <span class="input-group-append">
              <span class="input-group-text"><i class="fa fa-calendar"></i></span>
           </span>
        </div>
       </div>
      <div class="form-group col-sm-6">
        <div class="input-group date" <?php echo 'id="'.$tpid.'"' ?> >
                <input type="text" class="form-control" <?php echo 'name="'.$tpid.'" value="'.$time.'"' ?>/>
                <span class="input-group-append">
                    <span class="input-group-text"><i class="fa fa-clock-o"></i></span>
                </span>
            </div>
        </div>
    </div>
<?php
    }
    function insert_datetimepicker_script(array $ids) {
?>
    <script type="text/javascript">
    (function ($) {
<?php
        foreach ($ids as $id){
?>
            $( <?php echo '"#'.$id.'-date'.'"' ?> ).datetimepicker({
              dayViewHeaderFormat: 'YYYY年 M月',
              tooltips: {
                close: '閉じる',
                selectMonth: '月を選択',
                prevMonth: '前月',
                nextMonth: '次月',
                selectYear: '年を選択',
                prevYear: '前年',
                nextYear: '次年',
                selectTime: '時間を選択',
                selectDate: '日付を選択',
                prevDecade: '前期間',
                nextDecade: '次期間',
                selectDecade: '期間を選択',
                prevCentury: '前世紀',
                nextCentury: '次世紀'
             },
             format: 'YYYY/MM/DD',
             locale: 'ja',
             showClose: true
           });
           $( <?php echo '"#'.$id.'-time'.'"' ?> ).datetimepicker({
              tooltips: {
                close: '閉じる',
                pickHour: '時間を取得',
                incrementHour: '時間を増加',
                decrementHour: '時間を減少',
                pickMinute: '分を取得',
                incrementMinute: '分を増加',
                decrementMinute: '分を減少',
                pickSecond: '秒を取得',
                incrementSecond: '秒を増加',
                decrementSecond: '秒を減少',
                togglePeriod: '午前/午後切替',
                selectTime: '時間を選択'
              },
              format: 'HH:mm',
              locale: 'ja',
              showClose: true
           });
<?php
        }
?>
        })(jQuery);
    </script>
<?php
    }

    function new_event() {
        return "";
    }

    function save_event_fields( $post_id ) {
        if(!empty($_POST['event_start-date'])){
            $event_start = $_POST['event_start-date'];
            if(!empty($_POST['event_start-time'])){
                $event_start .= " ".$_POST['event_start-time'];
            }
            update_post_meta($post_id, 'event_start', $event_start ); //値を保存
        }else{ //題名未入力の場合
            delete_post_meta($post_id, 'event_start'); //値を削除
        }
        
        if(!empty($_POST['event_end-date'])){
            $event_start = $_POST['event_end-date'];
            if(!empty($_POST['event_end-time'])){
                $event_start .= " ".$_POST['event_end-time'];
            }
            update_post_meta($post_id, 'event_end', $event_start ); //値を保存
        }else{ //題名未入力の場合
            delete_post_meta($post_id, 'event_end'); //値を削除
        }
	
        if(!empty($_POST['event_place'])){
            update_post_meta($post_id, 'event_place', $_POST['event_place'] );
        }else{
            delete_post_meta($post_id, 'event_place');
        }
    }
}

$grms_event_list = new GRMS_EventList();
    

