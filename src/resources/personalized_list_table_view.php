<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: welcome.php');
    exit;
}
require_once 'db/config.php';

try {
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT usp.*, dp.id AS defective_id
        FROM user_submitted_products usp
        LEFT JOIN defective_products dp ON usp.barcode = dp.barcode
        WHERE usp.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $userId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as &$product) {
        $product['is_defective'] = !is_null($product['defective_id']);
    }
    unset($product);

    usort($products, function ($a, $b) {
        return isset($b['defective_id']) - isset($a['defective_id']);
    });

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<style>

@media (max-width: 768px) {
    .products-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap; 
    }

    .products-table th, .products-table td {
        text-align: left;
        padding: 8px;
    }
}

.product-row.defective {
    background-color: #ffe6e6; 
    color: #b30000; 
    font-weight: bold;
}

.product-row.defective .exclamation {
    color: #b30000;
    font-weight: bold;
    margin-left: 5px;
}

.products-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  font-family: Arial, sans-serif;
}

.products-table thead {
  background-color: #f4f4f4;
}

.products-table th, 
.products-table td {
  padding: 10px;
  text-align: left;
  border: 1px solid #ddd;
}

.products-table th {
  font-weight: bold;
}

.products-table tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}

.products-table tbody tr:hover {
  background-color: #f1f1f1;
}

.actions {
    display: flex;
    gap: 10px;
}

.actions button {
    padding: 5px 10px;
    border: 1px;
    border-radius: 4px;
    cursor: pointer;
}

.actions .edit {
    background-color:#ddd;
    color: black;
}

.actions .delete {
    background-color: #ddd;
    color: black;
}

.actions button:hover {
    opacity: 0.9;
}

.save-table {
    padding: 8px 16px;
    background-color: #ddd;
    color: #333;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.cancel-table {
    padding: 8px 16px;
    background-color: #ddd;
    color: #333;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.edit-table {
    padding: 8px 16px;
    background-color: #ddd;
    color: #333;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: background-color 0.3s ease, color 0.3s ease;
}
.edit-table:hover {
    background-color: #ccc;
    font-weight: bold;
    color: #000;
}

.delete-table {
    padding: 8px 16px;
    background-color: #ddd;
    border: none;
    color: #333;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.delete-table:hover {
    background-color: #ccc;
    font-weight: bold;
    color: #000;
}
</style>

<table class="products-table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Barcode</th>
      <th>Brand</th>
      <th>Description</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
            <tr class="product-row <?php echo $product['is_defective'] ? 'defective' : ''; ?>" data-id="<?php echo htmlspecialchars($product['id']); ?>">
                    <td class="my-view-mode"><?php echo htmlspecialchars($product['name']); ?></td>
                    <td class="my-view-mode"><?php echo htmlspecialchars(isset($product['barcode']) ? $product['barcode'] : 'N/A'); ?></td>
                    <td class="my-view-mode"><?php echo htmlspecialchars(isset($product['brand']) ? $product['brand'] : 'N/A'); ?></td>
                    <td class="my-view-mode"><?php echo htmlspecialchars(isset($product['product_description']) ? $product['product_description'] : 'No description available.'); ?></td>
                    <td class="my-view-mode">
                        <div class="actions">
                            <button class="edit-table">Edit</button>
                            <button class="delete-table">Delete</button>
                        </div>
                    </td>
                
                
                    <td class="my-edit-mode hidden"><input type="text" class="edit-name" value="<?php echo htmlspecialchars($product['name']); ?>" /></td>
                    <td class="my-edit-mode hidden"><?php echo htmlspecialchars(isset($product['barcode']) ? $product['barcode'] : 'N/A'); ?></td>
                    <td class="my-edit-mode hidden"><input type="text" class="edit-brand" value="<?php echo htmlspecialchars($product['brand']); ?>" /></td>
                    <td class="my-edit-mode hidden"><textarea class="edit-description"><?php echo htmlspecialchars($product['product_description']); ?></textarea></td>
                    <td class="my-edit-mode hidden">
                        <div class="actions">
                            <button class="save-table">Save</button>
                            <button class="cancel-table">Cancel</button>
                        </div>
                    </td>
                
            
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <td colspan="5">No products found.</td>
    <?php endif; ?>

  </tbody>
</table>