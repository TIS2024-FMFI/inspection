<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
// Database connection
$host = 'localhost';
$dbname = 'safety_app';
$username_db = 'root';
$password_db = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("SELECT * FROM user_submitted_products WHERE user_id = :user_id");
    $stmt->execute(['user_id' => 1]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalized List</title>
    <link rel="stylesheet" href="personalizedlist_styles.css">
</head>
<body>
<header>
    <img src="images/logo.png" alt="Logo" class="logo">

    <!-- Контейнер для заголовка и мобильной иконки -->
    <div class="homepage-container">
        <h2 class="homepage-title personalized-list-title">Personalized List</h2>
        <!-- Мобильная версия иконки -->
        <img src="images/profile-pic.png" alt="Profile Picture" class="profile-pic-mobile" onclick="toggleProfileMenu()">
    </div>

    <!-- Десктопная версия иконки -->
    <div class="profile-header profile-menu-container">
        <img src="images/profile-pic.png"
             alt="Profile Picture"
             class="profile-pic"
             onclick="toggleProfileMenu()">

        <!-- Меню профиля -->
        <div class="profile-menu" id="profile-menu">
            <p class="profile-username">
                <?php echo htmlspecialchars($_SESSION['username'] ?? 'No username'); ?>
            </p>
            <a href="index.php" class="profile-menu-item">Home</a>
            <a href="history.php" class="profile-menu-item">Scan History</a>
            <a href="logout.php" class="profile-menu-item">Logout</a>
        </div>
    </div>
</header>
<main>
<div class="view-toggle">
        <style>
            .view-toggle {
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 15px 0; 
                border: 1px solid #ccc;
                border-radius: 8px;
                overflow: hidden;
                width: 300px;
                margin-left: auto; 
                margin-right: auto;
            }
            
            .toggle-option {
                flex: 1;
                text-align: center;
                padding: 10px 15px;
                cursor: pointer;
                background-color: #f0f0f0;
                color: #333;
                font-weight: bold;
                transition: background-color 0.3s, color 0.3s;
                position: relative;
            }
            .toggle-option:not(:last-child) {
                border-right: 1px solid #ccc; 
            }
            
            .toggle-option.active {
                background-color: #333;
                color: #fff;
            }
            
            .toggle-option:hover:not(.active) {
                background-color: #e0e0e0;
            }
        </style>
        <div id="cards-view" class="toggle-option active" onclick="switchView('cards')">Cards View</div>
        <div id="table-view" class="toggle-option" onclick="switchView('table')">Table View</div>
    </div>
    <div class="content" id="my-list">
        
    </div>

    <div class="content hidden" id="history">
        <p>History tab content goes here.</p>
    </div>
</main>


<!-- switch between cards view and table view -->
<script>
    function switchView(view) {
    const container = document.getElementById('my-list');
    const cardsView = document.getElementById('cards-view');
    const tableView = document.getElementById('table-view');

    cardsView.classList.remove('active');
    tableView.classList.remove('active');

    if (view === 'cards') {
        cardsView.classList.add('active');
    } else if (view === 'table') {
        tableView.classList.add('active');
    }

    fetch(view === 'cards' ? 'personalized_list_cards_view.php' : 'personalized_list_table_view.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load view');
        }
        return response.text();
    })
    .then(html => {
        container.innerHTML = html;
    })
    .catch(error => {
        console.error('Error loading view:', error);
        container.innerHTML = '<p>Error loading view. Please try again later.</p>';
    });
}
</script>    

<!-- delete for table view -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('my-list');
    container.addEventListener('click', function (event) {
        if (event.target && event.target.classList.contains('delete-table')) {
            const row = event.target.closest('.product-row');
            const productId = row.getAttribute('data-id'); 

                fetch('delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json' 
                    },
                    body: JSON.stringify({ id: productId }) 
                })
                .then(response => response.json()) 
                .then(data => {
                    if (data.success) {
                        row.remove(); 
                    } else {
                        alert('Failed to delete the product: ' + data.message); 
                    }
                })
                .catch(error => console.error('Error:', error)); 
        }
    });
});
</script>

<!-- delete for card view -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('my-list').addEventListener('click', function (event) {
        if (event.target && event.target.classList.contains('delete-btn')) {
            const card = event.target.closest('.product-card'); 
            const productId = card.getAttribute('data-id'); 

                fetch('delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: productId }) 
                })
                .then(response => response.json()) 
                .then(data => {
                    if (data.success) {
                        card.remove(); 
                    } else {
                        alert('Failed to delete the product: ' + data.message); 
                    }
                })
                .catch(error => console.error('Error:', error)); 
        }
    });
});
</script>

<!-- edit for table view -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('my-list');

    container.addEventListener('click', function (event) {
        if (event.target && event.target.classList.contains('edit-table')) {
            const row = event.target.closest('.product-row');
            row.querySelectorAll('.my-view-mode').forEach(cell => cell.classList.add('hidden'));
            row.querySelectorAll('.my-edit-mode').forEach(cell => cell.classList.remove('hidden'));
        }

        if (event.target && event.target.classList.contains('save-table')) {
            const row = event.target.closest('.product-row');
            const id = row.dataset.id;
            const name = row.querySelector('.edit-name').value;
            const brand = row.querySelector('.edit-brand').value;
            const description = row.querySelector('.edit-description').value;

            fetch('update_product.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, name, brand, description })
            })
                .then(response => {
                    if (response.ok) {
                        row.querySelectorAll('.my-view-mode')[0].textContent = name;
                        row.querySelectorAll('.my-view-mode')[2].textContent = brand;
                        row.querySelectorAll('.my-view-mode')[3].textContent = description;

                        row.querySelectorAll('.my-edit-mode').forEach(cell => cell.classList.add('hidden'));
                        row.querySelectorAll('.my-view-mode').forEach(cell => cell.classList.remove('hidden'));
                    } else {
                        alert('Failed to save changes.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to save changes.');
                });
        }

        if (event.target && event.target.classList.contains('cancel-table')) {
            const row = event.target.closest('.product-row');
            row.querySelectorAll('.my-edit-mode').forEach(cell => cell.classList.add('hidden'));
            row.querySelectorAll('.my-view-mode').forEach(cell => cell.classList.remove('hidden'));
        }
    });
});
</script>


<!-- edit for card viwe -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('my-list').addEventListener('click', function (event) {
        if (event.target && event.target.classList.contains('edit-btn')) {
            const card = event.target.closest('.product-card');
            card.querySelector('.view-mode').classList.add('hidden');
            card.querySelector('.edit-mode').classList.remove('hidden');
        }
    });

    document.getElementById('my-list').addEventListener('click', function (event) {
        if (event.target && event.target.classList.contains('save-btn')) {
            const card = event.target.closest('.product-card');
            const id = card.dataset.id;
            const name = card.querySelector('.edit-name').value;
            const brand = card.querySelector('.edit-brand').value;
            const description = card.querySelector('.edit-description').value;


            fetch('update_product.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, name, brand, description })
            })
            .then(response => {
                if (response.ok) {
                    card.querySelector('h3').textContent = name;
                    card.querySelector('.view-mode p:nth-child(4)').innerHTML = `<strong>Brand:</strong> ${brand}`;
                    card.querySelector('.view-mode p:nth-child(5)').innerHTML = `<strong>Description:</strong> ${description}`;
                    

                    card.querySelector('.view-mode').classList.remove('hidden');
                    card.querySelector('.edit-mode').classList.add('hidden');
                } else {
                    alert('Failed to save changes.');
                }
            })
            .catch(error => {
                console.error('Error saving changes:', error);
                alert('Failed to save changes.');
            });
        }
    });

    document.getElementById('my-list').addEventListener('click', function (event) {
        if (event.target && event.target.classList.contains('cancel-btn')) {
            const card = event.target.closest('.product-card');
            card.querySelector('.view-mode').classList.remove('hidden');
            card.querySelector('.edit-mode').classList.add('hidden');
        }
    });
});
</script>

<script src="scripts.js"></script>
</body>
</html>

