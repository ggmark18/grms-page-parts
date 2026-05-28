<?php
trait SMTPServerParts {
    
  public function load_smtpserver() {
      $this->addSettingSections(
            array(
                'section_id'    => 'smtp_common_setting',
            ),
        );
        $this->addSettingFields(
            'smtp_common_setting',  // target section ID
            array(
                'field_id'  => 'host',
                'type'      => 'text',
                'title'     => 'SMTPホスト名',
                'attributes' => array(
                    'size' => 40,
                 ),
            ),
            array(
                'field_id'  => 'port',
                'type'      => 'text',
                'title'     => 'ポート番号',
                'default' => '597',
                'attributes' => array(
                    'size' => 4
                 ),
            ),
            array(
                'field_id'  => 'username',
                'type'      => 'text',
                'title'     => 'ユーザー名',
                'attributes' => array(
                    'size' => 40
                 ),
            ),
            array(
                'field_id'  => 'password',
                'type'      => 'password',
                'title'     => 'パスワード',
                'attributes' => array(
                    'size' => 40
                 ),
            ),
            array(
                'field_id'  => 'from',
                'type'      => 'text',
                'title'     => '送信者メールアドレス',
                'attributes' => array(
                    'size' => 40
                 ),
            )
        );
    }
    public function do_smtpserver() {
        submit_button();
    }
}
