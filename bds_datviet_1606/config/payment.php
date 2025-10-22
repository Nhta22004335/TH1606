<?php
// config/payment.php
return [
  'content_prefix' => 'Thanh toán BĐS',
  'vat_percent'    => 0,  // nếu cần VAT thì đổi số %

  'vietqr' => [
    // mã ngân hàng theo chuẩn VietQR (ví dụ: MB, VCB, ACB, BIDV, TPB...)
    'bank_code'    => 'MB',
    // số tài khoản nhận tiền
    'account'      => '0783816386',
    // tên chủ tài khoản (không dấu càng ổn định hơn)
    'account_name' => 'NGUYEN THE LUC',
  ],
];
