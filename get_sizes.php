<?php
if (!isset($_GET['shoe_id'], $_GET['shoename'], $_GET['shoetype'])) {
    exit('Missing parameters');
}

$pdo = new PDO("mysql:host=mysql-highdreams.alwaysdata.net;dbname=highdreams_1", "439165", "Skyworth23");

$query = "SELECT s36, s37, s38, s39, s40, s41, s42, s43, s44, s45 FROM inventory
          WHERE id = ? AND shoe_name = ? AND shoe_type = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$_GET['shoe_id'], $_GET['shoename'], $_GET['shoetype']]);
$stock_row = $stmt->fetch(PDO::FETCH_ASSOC);

$sizes = [
    "US 5 / EU 36" => "s36",
    "US 6 / EU 37" => "s37",
    "US 7 / EU 38" => "s38",
    "US 7.5 / EU 39" => "s39",
    "US 8 / EU 40" => "s40",
    "US 8.5 / EU 41" => "s41",
    "US 9 / EU 42" => "s42",
    "US 9.5 / EU 43" => "s43",
    "US 10 / EU 44" => "s44",
    "US 11 / EU 45" => "s45"
];

echo '<select name="size" required>';
echo '<option value="" disabled selected>Select Shoe Size</option>';

foreach ($sizes as $label => $column) {
    $stock = isset($stock_row[$column]) ? intval($stock_row[$column]) : 0;
    $disabled = $stock <= 0 ? 'disabled' : '';
    echo '<option value="' . htmlspecialchars($column) . '" ' . $disabled .
         ' data-stock="' . $stock . '">' .
         htmlspecialchars($label) . '  ' .
         ($stock > 0 ? "$stock in stock" : 'Out of Stock') .
         '</option>';
}
echo '</select>';
?>
