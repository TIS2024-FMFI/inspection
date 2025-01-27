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
