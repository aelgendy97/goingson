<?php
include "connect.php";
$stmt = $con->prepare('SELECT * FROM events WHERE id = ?');
$stmt->execute(array($_GET["eventId"]));

$res = $stmt->fetch();

echo json_encode($res);
?>