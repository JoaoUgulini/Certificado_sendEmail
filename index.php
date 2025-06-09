<?php
global $conn;
require('conexao.php');
require('fpdf/fpdf.php');
require('PHPMailer/src/PHPMailer.php');
require('PHPMailer/src/SMTP.php');
require('PHPMailer/src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_POST['evento']) || !is_numeric($_POST['evento'])) {
    die("❌ Evento inválido.");
}

$id_evento = (int) $_POST['evento'];

$stmtEvento = $conn->prepare("SELECT nome, organizacao, data_inicio FROM evento WHERE id = ?");
$stmtEvento->execute([$id_evento]);
$evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);

if (!$evento) {
    die("❌ Evento não encontrado.");
}

$nomeEvento = $evento['nome'];
$data = date('d/m/Y', strtotime($evento['data_inicio']));
$organizacao = $evento['organizacao'];

$sql = "
SELECT u.nome, u.email
FROM usuario_atividade ua
JOIN usuario u ON u.id = ua.id_usuario
JOIN atividade a ON a.id = ua.id_atividade
WHERE ua.presenca = 1 AND a.id_evento = ?
GROUP BY u.id
HAVING COUNT(ua.id_atividade) >= 3
";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_evento]);
$participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($participantes as $p) {
    $nome = $p['nome'];
    $email = $p['email'];

    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetDrawColor(0, 102, 204);
    $pdf->SetLineWidth(1.5);
    $pdf->Rect(10, 10, 277, 190, 'D');
    $pdf->SetFont('Arial', 'B', 32);
    $pdf->SetTextColor(0, 102, 204);
    $pdf->Cell(0, 40, utf8_decode('CERTIFICADO'), 0, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 18);
    $pdf->Ln(10);
    $pdf->MultiCell(0, 10, utf8_decode("Certificamos que"), 0, 'C');
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->MultiCell(0, 15, utf8_decode($nome), 0, 'C');
    $pdf->SetFont('Arial', '', 18);
    $pdf->Ln(2);
    $pdf->MultiCell(0, 10, utf8_decode("participou do evento \"$nomeEvento\", realizado em $data, com carga horária total de 8 horas."), 0, 'C');
    $pdf->Ln(30);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, '_________________________________________', 0, 1, 'C');
    $pdf->Cell(0, 10, utf8_decode($organizacao), 0, 1, 'C');
    $pdfData = $pdf->Output('S');

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'eventqiffar@gmail.com';
        $mail->Password = 'qrod aiyp rxnx bzru';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('eventqiffar@gmail.com', $organizacao);
        $mail->addAddress($email, $nome);
        $mail->isHTML(true);
        $mail->Subject = "Certificado de participação no $nomeEvento";
        $mail->Body = "
            Olá <b>$nome</b>,<br><br>
            Em anexo está seu certificado de participação no evento <b>$nomeEvento</b>.<br><br>
            Atenciosamente,<br>
            <b>$organizacao</b>
        ";
        $mail->addStringAttachment($pdfData, 'certificado.pdf');
        $mail->send();

        echo "✅ Certificado enviado para: $nome &lt;$email&gt;<br>";
    } catch (Exception $e) {
        echo "❌ Erro ao enviar para $email: {$mail->ErrorInfo}<br>";
    }
}
