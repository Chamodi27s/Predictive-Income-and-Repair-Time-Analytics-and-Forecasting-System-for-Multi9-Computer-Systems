<?php
include 'db_config.php';

if(isset($_POST['item_code']) && isset($_POST['quantity'])){
    $item_code = $_POST['item_code'];
    $quantity = intval($_POST['quantity']);
    $status = $quantity > 0 ? 'In Stock' : 'Out Stock';

    $stmt = $conn->prepare("UPDATE stock SET quantity=?, status=? WHERE item_code=?");
    $stmt->bind_param("iss", $quantity, $status, $item_code);

    if($stmt->execute()){
        $totalQty = $conn->query("SELECT SUM(quantity) as total_qty FROM stock")->fetch_assoc()['total_qty'];
        $inStock = $conn->query("SELECT COUNT(*) as total FROM stock WHERE quantity > 0")->fetch_assoc()['total'];
        $outStock = $conn->query("SELECT COUNT(*) as total FROM stock WHERE quantity = 0")->fetch_assoc()['total'];
        $totalValue = $conn->query("SELECT SUM(quantity*unit_price) as total_value FROM stock")->fetch_assoc()['total_value'];

        echo json_encode([
            'status'=>'success',
            'newStatus'=>$status,
            'totalQty'=>$totalQty,
            'inStock'=>$inStock,
            'outStock'=>$outStock,
            'totalValue'=>number_format($totalValue,2)
        ]);
    } else {
        echo json_encode(['status'=>'error','message'=>$stmt->error]);
    }
}
?>
