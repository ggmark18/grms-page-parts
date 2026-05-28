<?php
trait BaseInfoParts {
    public function load_baseinfo() {
        $this->addSettingSections(
            array(
                'section_id'    => 'baseinfo_setting',
            )
        );
        $this->addSettingFields(
            'baseinfo_setting',  // target section ID
            array(
                'field_id'  => 'title',
                'type'      => 'text',
                'title'     => 'サイトタイトル',
                'attributes' => array(
                    'size' => 40,
                    'placeholder' => 'XXX県人会'
                )
            ),
            array(
                'field_id'  => 'officetitle',
                'type'      => 'text',
                'title'     => '事務局タイトル',
                'attributes' => array(
                    'size' => 40,
                    'placeholder' => 'XXX県人会事務局'
                )
            ),
            array(
                'field_id'  => 'newslettertitle',
                'type'      => 'text',
                'title'     => '会報誌タイトル',
                'attributes' => array(
                    'size' => 40,
                    'placeholder' => 'XXX県人会報'
                )
            ),
            array(
                'field_id'  => 'newsletter_permlink',
                'type'      => 'text',
                'title'     => '会報誌ページリンク先',
                'attributes' => array(
                    'size' => 40
                )
            ),
            array(
                'field_id'  => 'number_of_newsletter',
                'type'      => 'number',
                'title'     => '表示会報誌数',
                'attributes' => array(
                    'size' => 2
                ),
                'default' => '4'
            ),
            array(
                'field_id'  => 'width_max_thumbnail',
                'type'      => 'number',
                'title'     => 'サムネイル最大枠幅',
                'attributes' => array(
                    'size' => 4
                ),
                'default' => '1118'
            ),
            array(
                'field_id'  => 'width_pic_thumbnail',
                'type'      => 'number',
                'title'     => 'サムネイル画像幅',
                'attributes' => array(
                    'size' => 4
                ),
                'default' => '550'
            ),
            array(
                'field_id'  => 'shop_recomend_title',
                'type'      => 'text',
                'title'     => 'ショップ紹介タイトル',
                'attributes' => array(
                    'size' => 40,
                    'placeholder' => 'グルメ情報'
                )
            ),
            array(
                'field_id'  => 'shop_page_id',
                'type'      => 'text',
                'title'     => 'ショップ紹介ページID',
                'attributes' => array(
                    'size' => 5,
                )
            )
        );
    }
    public function do_baseinfo() {
        submit_button();
    }
}
