function openModal(modalId) {
    document.getElementById(modalId).style.display = "flex";
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
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
    window.open("../scanner.html", "Scanner", "width=800,height=400");
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
