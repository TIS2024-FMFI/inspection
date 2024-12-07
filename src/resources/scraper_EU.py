from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import os
import xml.etree.ElementTree as ET

# Path to the seed SQL file
seed_file_path = "src/resources/db/seed.sql"  # Adjust this if your seed file is stored elsewhere

# Set up WebDriver
driver = webdriver.Chrome()

try:
    # Navigate to the page
    driver.get("https://ec.europa.eu/safety-gate-alerts/screen/search?resetSearch=true")
    
    # Wait for the page to load completely
    WebDriverWait(driver, 20).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
    print("Page loaded.")

    # Locate and click the popover toggle (e.g., the export button)
    popover_toggle = WebDriverWait(driver, 20).until(
        EC.element_to_be_clickable((By.XPATH, "//a[contains(@class, 'ecl-popover__toggle')]"))
    )
    popover_toggle.click()
    print("Popover toggle clicked.")

    # Wait for the popover content to appear
    WebDriverWait(driver, 10).until(
        EC.visibility_of_element_located((By.CLASS_NAME, "ecl-popover__content"))
    )
    print("Popover content is visible.")

    # Click the "Export to XML" link
    export_to_xml = WebDriverWait(driver, 20).until(
        EC.element_to_be_clickable((By.XPATH, "//span[text()='Export to XML']/parent::a"))
    )
    export_to_xml.click()
    print("Export to XML clicked.")

    # Wait for the download to complete
    time.sleep(10)

    # Process the latest XML file from downloads
    download_path = os.path.expanduser("~/Downloads")
    files = [os.path.join(download_path, f) for f in os.listdir(download_path) if f.endswith('.xml')]
    latest_file = max(files, key=os.path.getctime)
    print(f"Processing file: {latest_file}")

    # Parse the XML file
    tree = ET.parse(latest_file)
    root = tree.getroot()

    # Extract data from the XML
    notifications = root.findall(".//notifications")
    products_data = []
    for notification in notifications:
        data = {
            "case_number": notification.find("caseNumber").text if notification.find("caseNumber") is not None else None,
            "category": notification.find("category").text if notification.find("category") is not None else None,
            "product": notification.find("product").text if notification.find("product") is not None else None,
            "brand": notification.find("brand").text if notification.find("brand") is not None else None,
            "name": notification.find("name").text if notification.find("name") is not None else None,
            "model": notification.find("type_numberOfModel").text if notification.find("type_numberOfModel") is not None else None,
            "batch_number": notification.find("batchNumber").text if notification.find("batchNumber") is not None else None,
            "barcode": notification.find("barcode").text if notification.find("barcode") is not None else None,
            "risk_type": notification.find("riskType").text if notification.find("riskType") is not None else None,
            "danger": notification.find("danger").text if notification.find("danger") is not None else None,
            "measures": notification.find("measures").text if notification.find("measures") is not None else None,
            "notifying_country": notification.find("notifyingCountry").text if notification.find("notifyingCountry") is not None else None,
            "origin_country": notification.find("countryOfOrigin").text if notification.find("countryOfOrigin") is not None else None,
            "type": notification.find("type").text if notification.find("type") is not None else None,
            "level": notification.find("level").text if notification.find("level") is not None else None,
            "company_recall_code": notification.find("companyRecallCode").text if notification.find("companyRecallCode") is not None else None,
            "production_dates": notification.find("productionDates").text if notification.find("productionDates") is not None else None,
        }
        products_data.append(data)

    # Generate SQL INSERT statements
    insert_statements = []
    for product in products_data:
        sql = f"""
        INSERT INTO defective_products (
            alert_number, product_name, product_category, brand, product_description, type, country_of_origin, risk_type, risk_description, measures_authorities, notifying_country, company_recall_code, production_dates
        ) VALUES (
            '{product["case_number"]}', '{product["product"]}', '{product["category"]}', '{product["brand"]}', '{product["name"]}', '{product["type"]}', 
            '{product["origin_country"]}', '{product["risk_type"]}', '{product["danger"]}', '{product["measures"]}', '{product["notifying_country"]}', 
            '{product["company_recall_code"]}', '{product["production_dates"]}'
        );
        """
        insert_statements.append(sql)

    # Append the INSERT statements to the seed.sql file
    with open(seed_file_path, "a", encoding="utf-8") as seed_file:
        seed_file.write("\n-- Inserted data from XML\n")
        seed_file.write("\n".join(insert_statements))
    print(f"Inserted data appended to {seed_file_path}")

finally:
    # Close the browser
    driver.quit()
