<?php
/**
 * Plugin Name: CF7 Dynamic Recipient
 * Plugin URI:  https://example.com/
 * Description: Contact Form 7 の宛先メールアドレスを安全に動的設定します。
 *              ① サイト受信用メール: ホワイトリストマッピングで部署別送信先を切り替え
 *              ② 自動返信メール: お客さんが入力したアドレスを検証してから使用
 * Version:     1.1.0
 * Author:      Custom
 * Text Domain: cf7-dynamic-recipient
 */

defined( 'ABSPATH' ) || exit;

// ============================================================
// 【設定①】サイト受信用メール（Mail 1）の設定
//
// フォームのセレクト/ラジオ値 → 実際の送信先メールアドレス のマッピング。
// CF7メール設定「宛先」には 'default' と同じ固定アドレスを入力してください。
// ============================================================
function cf7dr_get_recipient_config(): array {
    return [
        // --- フォームID: 123 の例 ---
        123 => [
            'field'   => 'recipient_type',   // 送信先を決めるフィールド名
            'default' => 'info@example.com', // フォールバック送信先
            'map'     => [
                'sales'   => 'sales@example.com',
                'support' => 'support@example.com',
                'general' => 'info@example.com',
            ],
        ],
    ];
}

// ============================================================
// 【設定②】自動返信メール（Mail 2）の設定
//
// お客さんが入力したメールアドレスフィールド名を指定します。
// CF7メール設定「Mail 2」の「宛先」には固定のダミーアドレス
// (例: noreply@example.com) を入力することで警告が消えます。
// このフィルターが送信直前に実際のお客さんアドレスに上書きします。
// ============================================================
function cf7dr_get_autoreply_config(): array {
    return [
        // --- フォームID: 123 の例 ---
        123 => [
            'email_field' => 'your-email', // フォーム内のメールアドレスフィールド名
        ],
        // --- フォームID: 456 の例 ---
        456 => [
            'email_field' => 'your-email',
        ],
    ];
}

// ============================================================
// メインフィルター: Mail 1（サイト受信）の宛先をホワイトリストで書き換え
// ============================================================
add_filter( 'wpcf7_mail_components', 'cf7dr_filter_recipient', 10, 3 );

function cf7dr_filter_recipient( array $components, WPCF7_ContactForm $form, WPCF7_Mail $mail ): array {

    // Mail 2（自動返信）はこのフィルターではなく下の cf7dr_filter_autoreply で処理
    if ( $mail->is_active() && 'mail_2' === $mail->name() ) {
        return cf7dr_filter_autoreply( $components, $form, $mail );
    }

    $config  = cf7dr_get_recipient_config();
    $form_id = $form->id();

    if ( ! isset( $config[ $form_id ] ) ) {
        return $components;
    }

    $submission = WPCF7_Submission::get_instance();
    if ( ! $submission ) {
        return $components;
    }

    $setting     = $config[ $form_id ];
    $posted_data = $submission->get_posted_data();
    $raw_value   = $posted_data[ $setting['field'] ] ?? '';

    // sanitize → ホワイトリスト照合のみ許可（任意アドレスは絶対通さない）
    $key       = sanitize_key( (string) $raw_value );
    $recipient = $setting['map'][ $key ] ?? $setting['default'];
    $recipient = sanitize_email( $recipient );

    if ( is_email( $recipient ) ) {
        $components['recipient'] = $recipient;
    }

    return $components;
}

// ============================================================
// サブフィルター: Mail 2（自動返信）の宛先をお客さんアドレスで書き換え
//
// セキュリティ上の考慮:
//   - CF7の [email] フィールド型が送信前にフォーマット検証済み
//   - さらに is_email() で二重チェック
//   - 無効なアドレスだった場合は送信をブロック（空文字にする）
// ============================================================
function cf7dr_filter_autoreply( array $components, WPCF7_ContactForm $form, WPCF7_Mail $mail ): array {

    $config  = cf7dr_get_autoreply_config();
    $form_id = $form->id();

    if ( ! isset( $config[ $form_id ] ) ) {
        return $components;
    }

    $submission = WPCF7_Submission::get_instance();
    if ( ! $submission ) {
        return $components;
    }

    $posted_data  = $submission->get_posted_data();
    $email_field  = $config[ $form_id ]['email_field'];
    $raw_email    = $posted_data[ $email_field ] ?? '';

    $customer_email = sanitize_email( (string) $raw_email );

    if ( is_email( $customer_email ) ) {
        // 正当なメールアドレスのみ宛先に設定
        $components['recipient'] = $customer_email;
    } else {
        // 無効なアドレスなら自動返信を送らない（空にすることでCF7がスキップ）
        $components['recipient'] = '';
    }

    return $components;
}

// ============================================================
// (オプション) 管理画面にデバッグ用のメモを表示
// ============================================================
add_action( 'wpcf7_admin_after_form', 'cf7dr_admin_notice' );

function cf7dr_admin_notice( WPCF7_ContactForm $form ): void {
    $form_id       = $form->id();
    $r_config      = cf7dr_get_recipient_config();
    $ar_config     = cf7dr_get_autoreply_config();
    $has_recipient = isset( $r_config[ $form_id ] );
    $has_autoreply = isset( $ar_config[ $form_id ] );

    if ( ! $has_recipient && ! $has_autoreply ) {
        return;
    }

    $lines = [];

    if ( $has_recipient ) {
        $s    = $r_config[ $form_id ];
        $rows = '';
        foreach ( $s['map'] as $key => $email ) {
            $rows .= sprintf(
                '<tr><td style="padding:2px 12px 2px 0"><code>%s</code></td><td>%s</td></tr>',
                esc_html( $key ), esc_html( $email )
            );
        }
        $lines[] = sprintf(
            '<strong>Mail 1（受信）</strong> — フィールド: <code>%s</code> / デフォルト: <code>%s</code>'
            . '<table style="margin-top:4px">%s</table>',
            esc_html( $s['field'] ), esc_html( $s['default'] ), $rows
        );
    }

    if ( $has_autoreply ) {
        $lines[] = sprintf(
            '<strong>Mail 2（自動返信）</strong> — 宛先フィールド: <code>%s</code>　'
            . '<span style="color:#888">※ CF7の「宛先」欄は固定ダミーアドレスを入力してください</span>',
            esc_html( $ar_config[ $form_id ]['email_field'] )
        );
    }

    echo '<div class="notice notice-info" style="padding:12px 16px;margin-top:16px">'
        . '<strong>CF7 Dynamic Recipient</strong> が有効です<br><br>'
        . implode( '<br><br>', $lines )
        . '</div>';
}
