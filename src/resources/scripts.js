function openModal(modalId) {
    document.getElementById(modalId).style.display = "flex";
    if (modalId === "scanner-modal") {
        initScanner();  // Initialize the scanner when the modal opens
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = "none";
    document.getElementById("result").textContent = "Scanning...";

    // Clear input fields
    const inputs = modal.querySelectorAll('input[type="password"], input[type="email"]');
    inputs.forEach(input => input.value = '');

    // Clear error messages
    const errorMessages = modal.querySelectorAll('.error-message');
    errorMessages.forEach(error => error.textContent = '');
}

function switchToLogin() {
    const registerModal = document.getElementById('register-modal');
    const loginModal = document.getElementById('login-modal');

    // Hide the register modal and show the login modal
    registerModal.style.display = "none";
    loginModal.style.display = "flex";
}

function switchToRegister() {
    const loginModal = document.getElementById('login-modal');
    const registerModal = document.getElementById('register-modal');

    // Hide the login modal and show the register modal
    loginModal.style.display = "none";
    registerModal.style.display = "flex";
}


// Ensure no modal opens on page load
document.addEventListener("DOMContentLoaded", () => {
    closeModal('login-modal');
    closeModal('register-modal');
    closeModal('scanner-modal');
});

// Disable auto focus on modals
document.addEventListener("DOMContentLoaded", () => {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.display = "none";
    });
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


function toggleProfileMenu() {
    const menu = document.getElementById('profile-menu');
    menu.classList.toggle('show');
}

document.addEventListener('click', function(event) {
    const menu = document.getElementById('profile-menu');
    const profilePicMobile = document.querySelector('.profile-pic-mobile');
    const profilePicDesktop = document.querySelector('.profile-pic');

    if (
        !menu.contains(event.target) &&
        event.target !== profilePicMobile &&
        event.target !== profilePicDesktop
    ) {
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
            const currentPage = window.location.pathname;
            if (currentPage.includes('welcome.php') || currentPage.includes('SearchPage.php') || currentPage.includes('ProductPage.php')) {
                window.location.reload();
            } else {
                window.location.href = 'welcome.php';
            }
        } else {
            errorMessage.style.paddingBottom = '10px';
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
        errorMessage.style.paddingBottom = '10px';
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


function validateSearchInput(input) {
    const invalidCharacters = /[<>{};]/;
    if (invalidCharacters.test(input)) {
        showError("Invalid input detected. Please avoid using special characters.");
        return false;
    }
    return true;
}

function showError(message) {
    const errorPopup = document.getElementById("error-popup");
    const errorMessage = document.getElementById("error-message");

    errorMessage.textContent = message;
    errorPopup.style.display = "block";
}

function closeErrorPopup() {
    const errorPopup = document.getElementById("error-popup");
    errorPopup.style.display = "none";
}

document.getElementById("search-form").addEventListener("submit", function (event) {
    event.preventDefault();

    const searchInput = document.getElementById("search-input").value.trim();
    if (!validateSearchInput(searchInput)) {
        return;
    }
    this.submit();
});

let scanningInProgress = false; // Flag to ensure only one scan is processed at a time

function initScanner() {
    Quagga.init({
        inputStream: {
            type: "LiveStream",
            constraints: {
                width: 800,
                height: 400,
                facingMode: "environment" // Použiť zadnú kameru
            },
            target: document.getElementById("camera"),
        },
        decoder: {
            readers: ["code_128_reader", "ean_reader"] // Typy barcode
        },
        locate: true,
        multiple: false,
        numOfWorkers: 4,
        debug: {
            drawBoundingBox: true,
            showFrequency: true,
            drawScanline: true,
            showPattern: true
          },
        halfSample: false,
        patchSize: "medium",
        area: {
            top: "25%",    // start scanning 25% from the top
            right: "25%",  // 25% from the right
            left: "25%",   // 25% from the left
            bottom: "25%"  // 25% from the bottom
          }
    }, (err) => {
        if (err) {
            document.getElementById("result").textContent = "Camera was not found";
            console.error(err);
            return;
        }
        Quagga.start();
    });


    Quagga.onDetected((result) => {
        if (scanningInProgress) {
            console.log("Scan already in progress. Skipping this detection.");
            return; // Prevent multiple scans at once
        }
         scanningInProgress = true;

        // 1. Safeguard checks to ensure result and code exist
        if (!result || !result.codeResult || !result.codeResult.code) {
            scanningInProgress = false;
            return;
        }
    
        // 2. Calculate the average error of decoded characters
        const errors = result.codeResult.decodedCodes
            .filter((c) => c.error !== undefined)
            .map((c) => c.error);
    
        let avgError = 0;
        if (errors.length) {
            avgError = errors.reduce((sum, val) => sum + val, 0) / errors.length;
        }
    
        // 3. Confidence threshold (tweak this value to suit your use case)
        const errorThreshold = 0.15;
    
        // 4. If average error is too high, ignore this detection
        if (avgError > errorThreshold) {
            console.log("Ignoring low-confidence scan, avgError=", avgError);
            return;
        }
    
        // If we reach here, detection passed the confidence filter
        const barcode = result.codeResult.code;
    
        // Stop scanning once a confident result is found
        Quagga.stop();
    
        // Clear any old messages
        document.getElementById("result").textContent = ``;
        document.getElementById("camera").innerHTML = `Loading data ...`;


        $.ajax({
        url: 'check_login.php',
        method: 'GET',
        success: function (response) {
            const loggedIn = response === 'logged_in';
            // 5. Continue with your AJAX call and the rest of your logic
            $.ajax({
                url: "get_data.php",
                method: "GET",
                data: { barcode },
                success: function (response) {
                    const data = JSON.parse(response);
                    if (data.error) {
                        document.getElementById("camera").innerHTML = `<p>${data.error}</p>`;
                    } else {
                        let status = data.status === "defective" ? 1 : 0;
                        let product_id = data.data?.[0]?.id || null;
                        let product_link = `ProductPage.php?id=${product_id}`;  
        
        
        
                        if (data.status === "defective") {
                            const product = data.data[0]; // Access the first (and presumably only) product
                            const details = `
                            <div class="product-card">
                                <h2>Defective</h2>
                                <p><strong>Name:</strong> ${product.product_name}</p>
                                <p><strong>Barcode: </strong>${product.barcode} </p>
                                <p><strong>Info:</strong> ${product.product_info}</p>
                                <button id="see-details">See Details</button>
                                <div id="additional-info" style="display: none;">
                                    <p><strong>Alert Number:</strong> ${product.alert_number}</p>
                                    <p><strong>Case URL:</strong> ${product.case_url}</p>
                                    <p><strong>Product category:</strong> ${product.product_category}</p>
                                    <p><strong>Brand:</strong> ${product.brand}</p>
                                    <p><strong>Model type number:</strong> ${product.model_type_number}</p>
                                    <p><strong>Batch number:</strong> ${product.batch_number}</p>
                                    <p><strong>Company recall code:</strong> ${product.company_recall_code}</p>
                                    <p><strong>Risk type:</strong> ${product.risk_type}</p>
                                    <p><strong>Risk description:</strong> ${product.risk_info}</p>
                                    <p><strong>Measures:</strong> ${product.measures}</p>
                                    <p><strong>Company recall page:</strong> ${product.company_recall_page}</p>
                                    <p><strong>Description:</strong> ${product.product_description}</p>
                                    <p><strong>Production dates:</strong> ${product.production_dates}</p>
                                    <p><strong>Notifying country:</strong> ${product.notifying_country}</p>
                                    <p><strong>Country of origin:</strong> ${product.country_of_origin}</p>
                                    <p><strong>Type:</strong> ${product.type}</p>
                                    <p><strong>Level:</strong> ${product.level}</p>
                                    <p><strong>Images:</strong> ${product.images}</p>
                                </div>
                            </div>
                            `;
                            document.getElementById("camera").innerHTML = details;
        
                            // Toggle details visibility
                            document.getElementById("see-details").addEventListener("click", function () {
                                const additionalInfo = document.getElementById("additional-info");
                                if (additionalInfo.style.display === "none") {
                                    additionalInfo.style.display = "block";
                                    this.textContent = "Hide Details";
                                } else {
                                    additionalInfo.style.display = "none";
                                    this.textContent = "See Details";
                                }
                            });
                        }
                        else if (data.status === "exists_in_personalized") {
                            product_link = "personalized_list.php";
                            document.getElementById("camera").innerHTML = `<p>${data.message}</p>`;   
                        }
                        else if (data.status === "not_found") {
                                    if (loggedIn) {
                                        product_link = "";
                                        document.getElementById("camera").innerHTML = `
                                            <div class="product-card">
                                                <h3>Not Found</h3>
                                                <p>Barcode: ${barcode}</p>
                                                <form method="post" id="add-product-form">
                                                    <input type="text" id="name" name="name" placeholder="Name (mandatory)" required>
                                                    <input type="text" id="description" name="description" placeholder="Description (optional)">
                                                    <input type="text" id="brand" name="brand" placeholder="Brand (optional)">
                                                    <button id="add-product">Add to personalized list</button>
                                                    <button id="cancel" onclick="closeModal('scanner-modal')">Cancel</button>
                                                </form>
                                            </div>
                                        `;
                            $(document).ready(function () {
                                // Handle form submission
                                $('#add-product-form').on('submit', function (e) {
                                    e.preventDefault(); // Prevent the default form submission
                                    const nameValue = $('#name').val();
                                    // Collect form data
                                    var formData = {
                                        barcode: barcode,
                                        name: nameValue,
                                        description: $('#description').val(),
                                        brand: $('#brand').val()
                                    };
                                    // Send data to the server via AJAX POST
                                    $.ajax({
                                        url: 'add_product.php',
                                        type: 'POST',
                                        data: formData,
                                        success: function (response) {
                                            alert('Product has been added to your personalized list.');
                                            $('#camera').html('<p>Product added successfully.</p>');
                                            product_link = "personalized_list.php";
                                        },
                                        error: function (jqXHR, textStatus, errorThrown) {
                                            console.error('Error adding product:', textStatus, errorThrown);
                                            alert('There was an error adding the product. Please try again.');
                                        }
                                    });
                                });
                            });

                            } else {
                                document.getElementById("camera").innerHTML = `
                                    <div class="product-card">
                                        <h3>Not Found</h3>
                                        <p>Barcode: ${barcode}</p>
                                        <p>You need to be logged in to add products.</p>
                                    </div>
                                `;
                                }
                                document.getElementById("cancel").addEventListener("click", function () {
                                // You can add any cancel/close logic here
                                document.getElementById("result").textContent = "Scanning...";
                            });

                        }
                        if (loggedIn) {
                            insertScanToHistory(barcode, product_id, product_link, status);
                        };
                        
                    };
                    scanningInProgress = false;
                },
                error: function () {
                    document.getElementById("result").textContent = "Error loading data from the database.";
                    scanningInProgress = false;
                }
            });
            
        },
        error: function () {
            document.getElementById("result").textContent = "Error checking login status.";
            scanningInProgress = false;
        }
    });
        
    });
}

function insertScanToHistory(barcode, product_id, product_link, status) {
    $.ajax({
        url: "insert_scan.php",
        method: "POST",
        data: {
            barcode: barcode,
            product_id: product_id || null,
            product_link: product_link || "",
            status: status,
        },
        success: function (response) {
            console.log("Scan history updated:", response);
        },
        error: function () {
            console.error("Error inserting scan history.");
        }
    });
}

function handleForgotPassword() {
    const emailInput = document.getElementById('email').value;
    const messageDiv = document.getElementById('forgot-password-message');

    console.log('handleForgotPassword called');
    console.log('Email input:', emailInput);

    if (!emailInput) {
        messageDiv.style.display = 'block';
        messageDiv.style.color = 'red';
        messageDiv.textContent = 'Please enter your email address.';
        return;
    }

    fetch('forgotPassword.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: emailInput })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.style.display = 'block';
                messageDiv.style.color = 'green';
                messageDiv.textContent = 'Reset password link has been sent to your email.';
            } else {
                messageDiv.style.display = 'block';
                messageDiv.style.color = 'red';
                messageDiv.textContent = 'Email not found. Please try again.';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.style.display = 'block';
            messageDiv.style.color = 'red';
            messageDiv.textContent = 'An error occurred. Please try again later.';
        });
}
