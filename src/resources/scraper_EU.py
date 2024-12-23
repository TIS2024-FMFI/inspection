from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from lxml import etree  # Using lxml for tolerant XML parsing
import time
import os

# Path to the seed SQL file
seed_file_path = "src/resources/db/seed.sql"  # Adjust this if your seed file is stored elsewhere

def sanitize(value):
    """Remove single quotes from strings to avoid SQL issues."""
    return value.replace("'", "") if isinstance(value, str) else value

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

    # Parse the XML file using lxml (tolerant parser)
    try:
        parser = etree.XMLParser(recover=True)
        tree = etree.parse(latest_file, parser)
        root = tree.getroot()
        print("XML parsing completed successfully using lxml.")
    except etree.XMLSyntaxError as e:
        print(f"XML parsing error with lxml: {e}")
        raise

    # Extract data from the XML
    notifications = root.findall(".//notifications")
    products_data = []
    for notification in notifications:
        data = {
            "type_of_alert": sanitize(notification.findtext("typeOfAlert")),
            "alert_number": sanitize(notification.findtext("caseNumber")),
            "alert_submitted_by": sanitize(notification.findtext("submittedBy")),
            "country_of_origin": sanitize(notification.findtext("countryOfOrigin")),
            "counterfeit": sanitize(notification.findtext("counterfeit")),
            "risk_type": sanitize(notification.findtext("riskType")),
            "risk_legal_provision": sanitize(notification.findtext("riskLegalProvision")),
            "product": sanitize(notification.findtext("product")),
            "name": sanitize(notification.findtext("name")),
            "brand": sanitize(notification.findtext("brand")),
            "category": sanitize(notification.findtext("category")),
            "type_model": sanitize(notification.findtext("typeNumberModel")),
            "compulsory_measures": sanitize(notification.findtext("compulsoryMeasures")),
            "voluntary_measures": sanitize(notification.findtext("voluntaryMeasures")),
            "distribution_countries": sanitize(notification.findtext("distributionCountries")),
            "company_recall_page": sanitize(notification.findtext("companyRecallPage")),
            "url_of_case": sanitize(notification.findtext("urlOfCase")),
            "barcode": sanitize(notification.findtext("barcode")),
            "batch_number": sanitize(notification.findtext("batchNumber")),
            "company_recall_code": sanitize(notification.findtext("companyRecallCode")),
            "production_dates": sanitize(notification.findtext("productionDates")),
            "packaging_description": sanitize(notification.findtext("packagingDescription")),
        }
        products_data.append(data)

    # Generate SQL INSERT statements
    insert_statements = []
    for product in products_data:
        sql = f"""
        INSERT INTO defective_products (
            type_of_alert, alert_number, alert_submitted_by, country_of_origin, counterfeit,
            risk_type, risk_legal_provision, product_name, product_description, brand, product_category,
            model_type_number, compulsory_measures, voluntary_measures, found_and_measures_taken_in,
            company_recall_page, case_url, barcode, batch_number, company_recall_code,
            production_dates, packaging_description
        ) VALUES (
            '{product["type_of_alert"]}', '{product["alert_number"]}', '{product["alert_submitted_by"]}', 
            '{product["country_of_origin"]}', '{product["counterfeit"]}', '{product["risk_type"]}', 
            '{product["risk_legal_provision"]}', '{product["product"]}', '{product["name"]}', 
            '{product["brand"]}', '{product["category"]}', '{product["type_model"]}', 
            '{product["compulsory_measures"]}', '{product["voluntary_measures"]}', 
            '{product["distribution_countries"]}', '{product["company_recall_page"]}', 
            '{product["url_of_case"]}', '{product["barcode"]}', '{product["batch_number"]}', 
            '{product["company_recall_code"]}', '{product["production_dates"]}', 
            '{product["packaging_description"]}'
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
