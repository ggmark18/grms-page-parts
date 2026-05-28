<?php
trait AuthCodeParts {
    
  public function load_authcode() {
      $this->addSettingSections(
            array(
                'section_id'    => 'each_authcode_setting',
            ),
        );
        $this->addSettingFields(
            'each_authcode_setting',  // target section ID
            array(
                'field_id'  => 'authcode1',
                'type'      => 'text',
                'title'     => '認証コード1',
                'attributes' => array(
                    'size' => 16,
                 ),
            ),
            array(
                'field_id'  => 'authcode2',
                'type'      => 'text',
                'title'     => '認証コード2',
                'attributes' => array(
                    'size' => 16,
                 ),
            ),
            array(
                'field_id'  => 'authcode3',
                'type'      => 'text',
                'title'     => '認証コード3',
                'attributes' => array(
                    'size' => 16,
                 ),
            ),
            array(
                'field_id'  => 'authcode4',
                'type'      => 'text',
                'title'     => '認証コード4',
                'attributes' => array(
                    'size' => 16,
                 ),
            ),
            array(
                'field_id'  => 'authcode5',
                'type'      => 'text',
                'title'     => '認証コード5',
                'attributes' => array(
                    'size' => 16,
                 ),
            ),
        );
    }
    public function do_authcode() {
        submit_button();
    }
}
