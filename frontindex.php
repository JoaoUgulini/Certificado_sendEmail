<?php
global $conn;
require('conexao.php');

$eventos = $conn->query("SELECT id, nome, data_inicio FROM evento ORDER BY data_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerar Certificados</title>
</head>
<body>
<h2>Gerar Certificados por Evento</h2>

<form action="index.php" method="post">
    <label for="evento">Selecione o evento:</label><br>
    <select name="evento" id="evento" required>
        <option value="">-- Escolha um evento --</option>
        <?php foreach ($eventos as $e): ?>
            <option value="<?= $e['id'] ?>">
                <?= htmlspecialchars($e['nome']) ?> (<?= date('d/m/Y', strtotime($e['data_inicio'])) ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>
    <button type="submit">Gerar Certificados</button>
</form>
</body>
</html>
