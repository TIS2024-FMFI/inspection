# Kontrola
Verification of products

## Site URL
opensciencedata.eu

# Documentation

- [Testing Scenarios](docs/TestingScenarios.pdf)
- [Requirements Catalog](docs/requirementsCatalog.pdf)
- [System Design Inspection](docs/systemDesignInspection.pdf)

## Setup Instructions

### Prerequisites
- Docker Desktop installed on Windows

### Steps
1. Open a command line interface.
2. Run the following command to build and start the containers:
    ```sh
    docker-compose up --build -d
    ```
3. To shut down the containers, run:
    ```sh
    docker-compose down
    ```

## Modifying the Database

### Steps
1. Open Docker Desktop.
2. Navigate to the `inspection` container group.
3. Select the `inspection-db-1` container.
4. Open an Exec terminal.
5. Connect to the MySQL database by running:
    ```sh
    mysql -h db -u root -p
    ```
6. Enter the root password when prompted.
7. Switch to the `safety_app` database:
    ```sh
    USE safety_app;
    ```
8. You can now execute SQL commands to modify the database.



## Setup and Configuration Steps
1. Server Setup:
Adjusted files from the GitHub repository and deployed them on the server.

2. User Configuration:
Created a new sudo user for managing the server securely.

3. Database Setup and credentials:
Imported the historyData file into the database.
Created a database user and updated their permissions.
Updated all necessary usa cases for the user credentials

4. Library Installation:
Installed necessary libraries using apt and pip.

6. Routing Updates:
Redirected the index route to the welcome page.

7. Mails:
Updated the reset password link and the notification links.

8. Logging:
Added logs to monitor system and application activities.

9. SSL Certificate:
Set up a secure certificate to enable HTTPS.

10. Created a Google project for the google login

11. Installed neccesary libs (composer)


# User Manual

## Sign Up
A user can register in one of two ways:
- Clicking the **"Register Now"** button on the homepage.
- Clicking **"Sign in"**, then selecting **"Sign up"** in the pop-up window.

After entering a valid **email** and **password** and passing verification, the account will be created.

---

## Log In
To log into the system, the user must click **"Sign in"**, then select **"Log in"** from the pop-up window.

Users can log in using one of two methods:
1. Entering a valid **email** and **password**, then clicking **"Log in"**.
2. Using a **Google account** by clicking **"Continue with Google"**.

---

## Change Password
If the user forgets their password, they can:
1. Go to the **"Log in"** section.
2. Click **"Forgot password?"**.
3. Receive an email notification and follow the instructions to reset the password.

---

## Log Out
A user can log out as follows:
1. Click on the **profile icon**.
2. Select **"Log out"** from the pop-up menu.
3. The account session will end.

---

## Search
While on the **homepage**, the user can:
1. Enter a product name in the **"Search"** bar.
2. Press **Enter**.
3. See a list of products matching the query.

---

## Scan
While on the **homepage**, the user can press the **"Scan"** button to scan a product barcode.

After successful scanning, the user will receive a notification about the product's status. 
If the user is logged in and the product is **non-defective**, they can add it to the **Personalized List**.

---

## Personalized List
**Available only for logged-in users.**

### How to Access
1. Click on the **profile icon**.
2. Select **"Personalized List"** from the dropdown menu.

### Features
- **View** added products.
- **Choose display mode:**
  - **"Table View"** (table format).
  - **"Card View"** (card format).
- **Edit** product name, brand, and description by clicking **"Edit"**, then:
  - Save changes (**"Save"**).
  - Cancel changes (**"Cancel"**).
- **Delete** products:
  - By clicking the **cross icon** (in card view).
  - By clicking **"Delete"** (in table view).
- **Defective products** are highlighted in **red** and appear at the top of the list.

---

## History
**Available only for logged-in users.**

### How to Access
1. Click on the **profile icon**.
2. Select **" Scan History"** from the dropdown menu.

On this page, users can view **all scanned products**.

---

## Scrape Sites
**Available only for logged-in users with an administrator role.**

- On the homepage, click the **"Scrape Sites"** button.
- The system will gather information from websites and update the database.