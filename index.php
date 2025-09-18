<?php
require_once('src/PHPMailer.php');
require_once('src/SMTP.php');
require_once('src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//carregamento dos dados
$config = require 'config.php';

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $config['email'];
    $mail->Password = $config['senha'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom($config['email'], $config['nome_usuario']);
    $mail->addAddress($config['destinatario'], $config['nome_destinatario']);

    $mail->isHTML(true);
    $mail->Subject = 'Vencimento Cronotacógrafos';

    // Corpo em HTML
    $mail->Body = "
        <p>Prezado(a),</p>
        <p>Encaminho em anexo os cronotacógrafos que constam atualmente como vencidos, conforme verificação realizada em nosso sistema.</p>
        <p>Ficamos à disposição para esclarecer dúvidas ou realizar eventuais atualizações em nosso sistema.</p>
        <br>
        <p>Atenciosamente,<br><b>{$config['nome_usuario']}</b></p>
    ";

    // Versão alternativa em texto puro (para quem não aceita HTML)
    $mail->AltBody = "Prezado(a),\n\nEncaminho em anexo os cronotacógrafos que constam atualmente como vencidos, conforme verificação realizada em nosso sistema.\n\nFicamos à disposição para esclarecer dúvidas ou realizar eventuais atualizações em nosso sistema.\n\nAtenciosamente,\n{$config['nome_usuario']}";

    // Anexa o arquivo XLSX
    $mail->addAttachment('vencimentos.xlsx');

    if ($mail->send()) {
        echo 'Email enviado com sucesso';
    } else {
        echo 'Email não enviado';
    }
} catch (Exception $e) {
    echo "Erro ao enviar mensagem: {$mail->ErrorInfo}";
}
