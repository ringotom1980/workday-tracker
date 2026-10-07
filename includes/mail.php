<?php

declare(strict_types=1);

function send_verification_code(string $email, string $code): bool
{
    // TODO: Wire PHPMailer with Hostinger SMTP settings in a local-only config.
    return mail($email, '驗證碼', '您的驗證碼是：' . $code);
}
