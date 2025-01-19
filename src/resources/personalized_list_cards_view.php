<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'db/config.php';

try {
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM user_submitted_products WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<style>
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.product-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: relative;
    padding-bottom: 50px;
}

.delete-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: none;
    border: none;
    color: #333;
    font-size: 18px;
    cursor: pointer;
}

.delete-btn:hover {
    font-weight: bold;
    color: #000;
}

.delete-btn .tooltip {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    top: -20px; 
    right: 50%;
    transform: translateX(50%);
    background-color: #333;
    color: #fff;
    text-align: center;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    z-index: 10;
}

.delete-btn:hover .tooltip {
    visibility: visible;
    opacity: 1;
}

.product-card h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.product-card p {
    font-size: 14px;
    margin-bottom: 5px;
}

.edit-btn {
    padding: 8px 16px;
    background-color: #ddd;
    color: #333;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    position: absolute;
    bottom: 10px;
    right: 10px;
    transition: background-color 0.3s ease, color 0.3s ease;
}
.edit-btn:hover {
    background-color: #ccc;
    font-weight: bold;
    color: #000;
}

.save-btn {
    padding: 8px 16px;
    background-color: #ddd;
    color: #333;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    bottom: 10px;
    right: 10px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.cancel-btn {
    padding: 8px 16px;
    background-color: #ddd;
    color: #333;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    bottom: 10px;
    right: 10px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.edit-name,
.edit-brand,
.edit-description {
    width: 90%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.edit-name:focus,
.edit-brand:focus,
.edit-description:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 8px rgba(76, 175, 80, 0.5);
    outline: none;
}

textarea.edit-description {
    height: 100px;
    resize: none;
}
</style>

<div class="product-grid">
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card" data-id="<?php echo htmlspecialchars($product['id']); ?>">
                <div class="view-mode">
                    <button class="delete-btn">
                        &times;
                        <span class="tooltip">Delete</span>
                    </button>
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p><strong>Barcode:</strong> <?php echo htmlspecialchars(isset($product['barcode']) ? $product['barcode'] : 'N/A'); ?></p>
                    <p><strong>Brand:</strong> <?php echo htmlspecialchars(isset($product['brand']) ? $product['brand'] : 'N/A'); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars(isset($product['product_description']) ? $product['product_description'] : 'No description available.'); ?></p>
                    <button class="edit-btn">Edit</button>
                </div>
                <div class="edit-mode hidden">
                    <h3> Name:</h3>
                    <input type="text" class="edit-name" value="<?php echo htmlspecialchars($product['name']); ?>" />
                    <p><strong>Brand:</strong></p>
                    <input type="text" class="edit-brand" value="<?php echo htmlspecialchars($product['brand']); ?>" />
                    <p><strong>Description:</strong></p>
                    <textarea class="edit-description"><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                    <button class="save-btn">Save</button>
                    <button class="cancel-btn">Cancel</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>



<!-- <script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const card = button.closest('.product-card'); // Найти карточку, в которой находится кнопка
            const productId = card.getAttribute('data-id'); // Получить ID продукта из data-id

            // if (confirm('Are you sure you want to delete this product?')) {
                fetch('delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json' // Сообщаем серверу, что данные в формате JSON
                    },
                    body: JSON.stringify({ id: productId }) // Отправляем ID продукта на сервер
                })
                .then(response => response.json()) // Обрабатываем JSON-ответ от сервера
                .then(data => {
                    if (data.success) {
                        card.remove(); // Если удаление успешно, убираем карточку из DOM
                    } else {
                        alert('Failed to delete the product: ' + data.message); // Показываем ошибку
                    }
                })
                .catch(error => console.error('Error:', error)); // Обрабатываем ошибки
            // }
        });
    });
});
</script>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                const card = button.closest('.product-card');
                card.querySelector('.view-mode').classList.add('hidden');
                card.querySelector('.edit-mode').classList.remove('hidden');
            });
        });

        document.querySelectorAll('.save-btn').forEach(button => {
            button.addEventListener('click', async () => {
                const card = button.closest('.product-card');
                const id = card.dataset.id;
                const name = card.querySelector('.edit-name').value;
                const brand = card.querySelector('.edit-brand').value;
                const description = card.querySelector('.edit-description').value;

                // Send the data to the server to update
                const response = await fetch('update_product.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, name, brand, description })
                });

                if (response.ok) {
                    // Update the UI with the new values
                    card.querySelector('h3').textContent = name;
                    card.querySelector('.view-mode p:nth-child(3)').innerHTML = `<strong>Brand:</strong> ${brand}`;
                    card.querySelector('.view-mode p:nth-child(4)').innerHTML = `<strong>Description:</strong> ${description}`;
                    card.querySelector('.view-mode').classList.remove('hidden');
                    card.querySelector('.edit-mode').classList.add('hidden');
                } else {
                    alert('Failed to save changes.');
                }
            });
        });

        document.querySelectorAll('.cancel-btn').forEach(button => {
            button.addEventListener('click', () => {
                const card = button.closest('.product-card');
                card.querySelector('.view-mode').classList.remove('hidden');
                card.querySelector('.edit-mode').classList.add('hidden');
            });
        });
    });
</script>
<script src="scripts.js"></script> -->