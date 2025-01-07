function openModal(modalId) {
    document.getElementById(modalId).style.display = "flex";
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = "none";

    // Clear input fields
    const inputs = modal.querySelectorAll('input[type="password"], input[type="email"]');
    inputs.forEach(input => input.value = '');

    // Clear error messages
    const errorMessages = modal.querySelectorAll('.error-message');
    errorMessages.forEach(error => error.textContent = '');
}

function switchToLogin() {
    closeModal('register-modal');
    openModal('login-modal');
}

function switchToRegister() {
    closeModal('login-modal');
    openModal('register-modal');
}

// Ensure no modal opens on page load
document.addEventListener("DOMContentLoaded", () => {
    closeModal('login-modal');
    closeModal('register-modal');
});

// Highlight 'My List' button when on personalized_list.php
document.addEventListener("DOMContentLoaded", () => {
    const url = window.location.href;
    if (url.includes("personalized_list.php")) {
        document.querySelector('.my-list-button').classList.add('active');
    }
});

function searchProduct() {
    const query = document.getElementById("search-input").value.trim();
    if (!query) {
        alert("Please enter a search query!");
        return;
    }

    fetch(`search.php?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultContainer = document.getElementById("result-container");
            resultContainer.innerHTML = "";

            if (data.length === 0) {
                resultContainer.innerHTML = "<p>No products found.</p>";
                return;
            }

            data.forEach(product => {
                const productDiv = document.createElement("div");
                productDiv.className = "product-result";
                productDiv.innerHTML = `
                    <h3>${product.name}</h3>
                    <p><strong>Barcode:</strong> ${product.barcode || "N/A"}</p>
                    <p><strong>Brand:</strong> ${product.brand || "N/A"}</p>
                    <p><strong>Description:</strong> ${product.description || "No description available."}</p>
                `;
                resultContainer.appendChild(productDiv);
            });
        })
        .catch(error => console.error("Error fetching search results:", error));
}

function startScan() {
    alert("Barcode scanning is not yet implemented!");
}


document.getElementById("scan-button").addEventListener("click", () => {
    window.open("scanner.html", "Scanner", "width=800,height=400");
});

function toggleProfileMenu() {
    const menu = document.getElementById('profile-menu');
    menu.classList.toggle('show');
}

document.addEventListener('click', function(event) {
    const menu = document.getElementById('profile-menu');
    const profilePic = document.querySelector('.profile-pic');
    if (!menu.contains(event.target) && !profilePic.contains(event.target)) {
        menu.classList.remove('show');
    }
});

function handleLogin(event) {
    event.preventDefault();
    const form = document.getElementById('login-form');
    const formData = new FormData(form);
    const errorMessage = form.querySelector('.error-message');

    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php';
        } else {
            errorMessage.style.display = 'block';
            errorMessage.textContent = data.error;
            form.querySelector('#password').value = '';
        }
    })
    .catch(error => {
        errorMessage.style.display = 'block';
        errorMessage.textContent = data.error;
        form.querySelector('#password').value = '';
    });
}

function handleRegister(event) {
    event.preventDefault();
    const form = document.getElementById('register-form');
    const formData = new FormData(form);
    const errorMessage = form.querySelector('.error-message');

    fetch('register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        errorMessage.style.display = 'block';
        errorMessage.textContent = data.error;
        if (data.success) {
            errorMessage.style.color = 'green';
            form.querySelector('#email').value = '';
        } else {
            errorMessage.style.color = 'red';
        }
        form.querySelector('#password').value = '';
        form.querySelector('#confirm-password').value = '';
    })
    .catch(_ => {
        errorMessage.style.display = 'block';
        errorMessage.textContent = 'An error occurred. Please try again.';
        form.querySelector('#password').value = '';
        form.querySelector('#confirm-password').value = '';
    });
}
