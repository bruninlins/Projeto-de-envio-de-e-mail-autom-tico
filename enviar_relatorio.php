<?php
require 'vendor/autoload.php';
require_once('src/PHPMailer.php');
require_once('src/SMTP.php');
require_once('src/Exception.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Carrega configurações
$config = require 'config.php';

// ==== GERAR XLSX ==== //
try {
    // Conexão com banco
    $pdo = new PDO(
        "pgsql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']}",
        $config['db_user'],
        $config['db_pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta
    $stmt = $pdo->query("
        SELECT placa, renavam, vencimento
        FROM smartec_cronotacografo
        WHERE EXTRACT(MONTH FROM vencimento) = EXTRACT(MONTH FROM CURRENT_DATE)
        AND EXTRACT(YEAR FROM vencimento) = EXTRACT(YEAR FROM CURRENT_DATE);
    ");
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$dados) {
        die("Nenhuma placa encontrada para este mês.");
    }

    // Criar planilha
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Cabeçalho
    $sheet->setCellValue('A1', 'Placa');
    $sheet->setCellValue('B1', 'Renavam');
    $sheet->setCellValue('C1', 'Vencimento');

    // Estilo cabeçalho
    $sheet->getStyle('A1:C1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF'); // fonte branca
    $sheet->getStyle('A1:C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A1:C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('015958'); // cor de fundo

    // Inserir dados
    $rowNumber = 2;
    foreach ($dados as $row) {
        $sheet->setCellValue('A' . $rowNumber, $row['placa']);
        $sheet->setCellValue('B' . $rowNumber, $row['renavam']);
        $sheet->setCellValue('C' . $rowNumber, date('d/m/Y', strtotime($row['vencimento'])));

        // Centralizar e ajustar fonte
        $sheet->getStyle("A{$rowNumber}:C{$rowNumber}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$rowNumber}:C{$rowNumber}")
            ->getFont()->setSize(12);

        // Definir cor de fundo alternada (zebra)
        $fillColor = ($rowNumber % 2 == 0) ? '0FC2C0' : '008F8C'; // cor de fundo
        $fontColor = ($rowNumber % 2 == 0) ? '023535' : '023535'; // cor da fonte

        $sheet->getStyle("A{$rowNumber}:C{$rowNumber}")
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB($fillColor);

        $sheet->getStyle("A{$rowNumber}:C{$rowNumber}")->getFont()->getColor()->setRGB($fontColor);

        $rowNumber++;
    }

    // Ajustar largura das colunas
    foreach (range('A', 'C') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Salvar arquivo temporário
    $arquivo = __DIR__ . '/vencimentos.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($arquivo);

} catch (Exception $e) {
    die("Erro ao gerar planilha: " . $e->getMessage());
}

// ==== ENVIAR EMAIL ==== //
$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_OFF; // DEBUG_SERVER se quiser ver detalhes
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
    $mail->Body = "
        <p>Prezado(a),</p>
        <p>Encaminho em anexo os cronotacógrafos que constam atualmente como vencidos, conforme verificação realizada em nosso sistema.</p>
        <p>Ficamos à disposição para esclarecer dúvidas ou realizar eventuais atualizações em nosso sistema.</p>
        <br>
        <p>Atenciosamente,<br><b>{$config['nome_usuario']}</b></p>
    ";
    $mail->AltBody = "Prezado(a),\n\nEncaminho em anexo os cronotacógrafos vencidos.\n\nAtenciosamente,\n{$config['nome_usuario']}";

    $mail->addAttachment($arquivo);

    $mail->send();
    echo "Relatório enviado com sucesso!";
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
}
