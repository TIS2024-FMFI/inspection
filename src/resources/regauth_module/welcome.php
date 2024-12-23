<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="welcome_styles.css">
</head>
<body>
<header>
    <img src="images/logo.png" alt="Logo" class="logo">
    <button class="sign-in-button" onclick="location.href='login.php'">Sign In</button>
</header>
<main>
    <h1>WELCOME</h1>
    <div class="content-container">
        <img src="images/welcome-placeholder.png" alt="Placeholder Image" class="welcome-image">
        <div class="welcome-text">
            <p>
                This website is part of a school project designed to help users identify defective products.
                You can search for products or scan their barcodes to check if they meet safety and quality standards.
            </p>
            <p>
                By registering an account, you can unlock additional features:
            </p>
            <ul>
                <li>View your search history.</li>
                <li>Add products to a personalized list for easier tracking.</li>
            </ul>
            <p>
                Or continue without registering with access to searching and scanning without additional features.
                <strong>Your safety is our priority.</strong>
            </p>
        </div>
    </div>
    <div class="button-container">
        <button class="register-button" onclick="location.href='register.php'">Register Now</button>
        <button class="continue-button" onclick="location.href='index.php'">Continue without registering</button>
    </div>
</main>
</body>
</html>

