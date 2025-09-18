<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

// Configurações do banco de dados
/* $host = '10.10.1.3';
$port = 5432;
$dbname = 'cli_rigatrans';
$user = 'bruno';
$password = 'br#n@2025'; */

$config = require 'config.php';

$host = $config['db_host'];
$port = $config['db_port'];
$dbname = $config['db_name'];
$user = $config['db_user'];
$password = $config['db_pass'];

try {
    // Conexão com o banco
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta das placas vencendo neste mês
    $stmt = $pdo->query("
        SELECT placa, renavam, vencimento
        FROM smartec_cronotacografo
        WHERE EXTRACT(MONTH FROM vencimento) = EXTRACT(MONTH FROM CURRENT_DATE)
        AND EXTRACT(YEAR FROM vencimento) = EXTRACT(YEAR FROM CURRENT_DATE);
    ");
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$dados) {
        echo "Nenhuma placa encontrada.";
        exit;
    }

    // Criar planilha
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Cabeçalho
    $sheet->setCellValue('A1', 'Placa');
    $sheet->setCellValue('B1', 'Renavam');
    $sheet->setCellValue('C1', 'Vencimento');

    // Formatar cabeçalho
    $sheet->getStyle('A1:C1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1:C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Inserir dados
    $rowNumber = 2;
    foreach ($dados as $row) {
        $sheet->setCellValue('A' . $rowNumber, $row['placa']);
        $sheet->setCellValue('B' . $rowNumber, $row['renavam']);
        $sheet->setCellValue('C' . $rowNumber, date('d/m/Y', strtotime($row['vencimento'])));

        // Fonte e alinhamento das linhas
        $sheet->getStyle('A' . $rowNumber . ':C' . $rowNumber)->getFont()->setSize(12);
        $sheet->getStyle('A' . $rowNumber . ':C' . $rowNumber)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNumber++;
    }

    // Ajustar largura das colunas automaticamente
    foreach (range('A', 'C') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Salvar arquivo
    $arquivo = 'vencimentos.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($arquivo);

    echo "Arquivo $arquivo gerado com sucesso!";

} catch (PDOException $e) {
    echo "Erro ao conectar ao banco: " . $e->getMessage();
} catch (Exception $e) {
    echo "Erro ao gerar o Excel: " . $e->getMessage();
}
