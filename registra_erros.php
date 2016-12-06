<?php

register_shutdown_function('shut');
set_error_handler('handler');

//catch function
function shut() {
    $error = error_get_last();
    if ($error) {
        handler($error['type'], $error['message'], $error['file'], $error['line']);
    }
}

function handler($errno, $errstr, $errfile, $errline) {
    switch ($errno) {
        case E_ERROR: // 1 //
            $typestr = 'E_ERROR';
            break;
        case E_WARNING: // 2 //
            $typestr = 'E_WARNING';
            break;
        case E_PARSE: // 4 //
            $typestr = 'E_PARSE';
            break;
        case E_NOTICE: // 8 //
            $typestr = 'E_NOTICE';
            break;
        case E_CORE_ERROR: // 16 //
            $typestr = 'E_CORE_ERROR';
            break;
        case E_CORE_WARNING: // 32 //
            $typestr = 'E_CORE_WARNING';
            break;
        case E_COMPILE_ERROR: // 64 //
            $typestr = 'E_COMPILE_ERROR';
            break;
        case E_CORE_WARNING: // 128 //
            $typestr = 'E_COMPILE_WARNING';
            break;
        case E_USER_ERROR: // 256 //
            $typestr = 'E_USER_ERROR';
            break;
        case E_USER_WARNING: // 512 //
            $typestr = 'E_USER_WARNING';
            break;
        case E_USER_NOTICE: // 1024 //
            $typestr = 'E_USER_NOTICE';
            break;
        case E_STRICT: // 2048 //
            $typestr = 'E_STRICT';
            break;
        case E_RECOVERABLE_ERROR: // 4096 //
            $typestr = 'E_RECOVERABLE_ERROR';
            break;
        case E_DEPRECATED: // 8192 //
            $typestr = 'E_DEPRECATED';
            break;
        case E_USER_DEPRECATED: // 16384 //
            $typestr = 'E_USER_DEPRECATED';
            break;
    }
    if (!DEBUG) {
        //envia o e-mail de erro
        $mail = new \PHPMailer();
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'sitesoscasar@gmail.com';                 // SMTP username
        $mail->Password = 'gian080964';                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to
        $mail->setFrom('sitesoscasar@gmail.com', 'SOS Casar');

        $mail->addAddress('gianboschette@gmail.com', 'Gian Nunes');     // Add a recipient

        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = 'Erro '.$typestr;
        
        
        $message = " Error PHP <br/>"
                . "file => " . $errfile . " at line : " . $errline . "<br/>"
                . "type error => " . $typestr . " : " . $errstr . "<br/> ";
        $mail->Body = $message;
        $mail->send();
        ob_clean();
        ob_start();
        http_response_code(500);
        require ROOT.'/500.php';
        ob_end_flush();
        exit();
    }
}
