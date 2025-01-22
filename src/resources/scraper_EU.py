import os
import time
import pymysql  # type: ignore
from selenium import webdriver  # type: ignore
from selenium.webdriver.common.by import By  # type: ignore
from selenium.webdriver.support.ui import WebDriverWait  # type: ignore
from selenium.webdriver.support import expected_conditions as EC  # type: ignore
from selenium.webdriver.chrome.options import Options  # type: ignore
from lxml import etree  # for XML parsing

# ------------------------------------------------------------------------------ 
# 1. Database Connection
# ------------------------------------------------------------------------------ 
def connect_to_db():
    """
    Establish a connection to the MySQL database.
    Replace host, user, password, and database with your actual credentials.
    """
    try:
        return pymysql.connect(
            host=os.environ.get('DB_HOST', 'db'),
            user=os.environ.get('DB_USER', 'root'),
            password=os.environ.get('DB_PASSWORD', 'rootpassword'),
            database=os.environ.get('DB_NAME', 'safety_app'),
            cursorclass=pymysql.cursors.DictCursor
        )
    except pymysql.MySQLError as e:
        print(f"Database connection failed: {e}")
        raise

# ------------------------------------------------------------------------------ 
# 2. Utility Functions
# ------------------------------------------------------------------------------ 
def sanitize(value):
    """Remove single quotes or other problematic characters from strings."""
    if isinstance(value, str):
        return value.replace("'", "''")
    return value

def parse_xml_file(file_path):
    """
    Parse an XML file using lxml with `recover=True` to handle minor syntax errors.
    Returns the root element.
    """
    parser = etree.XMLParser(recover=True)
    tree = etree.parse(file_path, parser)
    return tree.getroot()

def extract_notifications(xml_root):
    """
    Given the root of the parsed XML tree, extract 'notifications' elements
    and return a list of dictionaries (product data).
    Adjust the field names to match your XML.
    """
    notifications = xml_root.findall(".//notifications")
    products_data = []
    for notification in notifications:
        data = {
            "type_of_alert":       sanitize(notification.findtext("typeOfAlert") or ""),
            "alert_number":        sanitize(notification.findtext("caseNumber") or ""),
            "alert_submitted_by":  sanitize(notification.findtext("submittedBy") or ""),
            "country_of_origin":   sanitize(notification.findtext("countryOfOrigin") or ""),
            "counterfeit":         sanitize(notification.findtext("counterfeit") or ""),
            "risk_type":           sanitize(notification.findtext("riskType") or ""),
            "risk_legal_provision":sanitize(notification.findtext("riskLegalProvision") or ""),
            "product":             sanitize(notification.findtext("product") or ""),
            "name":                sanitize(notification.findtext("name") or ""),
            "brand":               sanitize(notification.findtext("brand") or ""),
            "category":            sanitize(notification.findtext("category") or ""),
            "type_model":          sanitize(notification.findtext("typeNumberModel") or ""),
            "compulsory_measures": sanitize(notification.findtext("compulsoryMeasures") or ""),
            "voluntary_measures":  sanitize(notification.findtext("voluntaryMeasures") or ""),
            "distribution_countries": sanitize(notification.findtext("distributionCountries") or ""),
            "company_recall_page": sanitize(notification.findtext("companyRecallPage") or ""),
            "url_of_case":         sanitize(notification.findtext("urlOfCase") or ""),
            "barcode":             sanitize(notification.findtext("barcode") or ""),
            "batch_number":        sanitize(notification.findtext("batchNumber") or ""),
            "company_recall_code": sanitize(notification.findtext("companyRecallCode") or ""),
            "production_dates":    sanitize(notification.findtext("productionDates") or ""),
            "packaging_description": sanitize(notification.findtext("packagingDescription") or ""),
        }
        products_data.append(data)
    return products_data

def load_history_data(history_file_path):
    """Load records from HistoryData.xml if it exists."""
    if not os.path.exists(history_file_path):
        print("No history file detected")
        return []
    print("Historical data found. Loading...")
    root = parse_xml_file(history_file_path)
    return extract_notifications(root)

def write_history_data_to_xml(all_data, history_file_path):
    """
    Write given data to the XML history file.
    Data is expected to be a list of dictionaries.
    """
    root = etree.Element("historyRoot")
    for entry in all_data:
        notif_el = etree.SubElement(root, "notifications")
        etree.SubElement(notif_el, "typeOfAlert").text        = entry["type_of_alert"]
        etree.SubElement(notif_el, "caseNumber").text         = entry["alert_number"]
        etree.SubElement(notif_el, "submittedBy").text        = entry["alert_submitted_by"]
        etree.SubElement(notif_el, "countryOfOrigin").text    = entry["country_of_origin"]
        etree.SubElement(notif_el, "counterfeit").text        = entry["counterfeit"]
        etree.SubElement(notif_el, "riskType").text           = entry["risk_type"]
        etree.SubElement(notif_el, "riskLegalProvision").text = entry["risk_legal_provision"]
        etree.SubElement(notif_el, "product").text            = entry["product"]
        etree.SubElement(notif_el, "name").text               = entry["name"]
        etree.SubElement(notif_el, "brand").text              = entry["brand"]
        etree.SubElement(notif_el, "category").text           = entry["category"]
        etree.SubElement(notif_el, "typeNumberModel").text    = entry["type_model"]
        etree.SubElement(notif_el, "compulsoryMeasures").text = entry["compulsory_measures"]
        etree.SubElement(notif_el, "voluntaryMeasures").text  = entry["voluntary_measures"]
        etree.SubElement(notif_el, "distributionCountries").text = entry["distribution_countries"]
        etree.SubElement(notif_el, "companyRecallPage").text  = entry["company_recall_page"]
        etree.SubElement(notif_el, "urlOfCase").text          = entry["url_of_case"]
        etree.SubElement(notif_el, "barcode").text            = entry["barcode"]
        etree.SubElement(notif_el, "batchNumber").text        = entry["batch_number"]
        etree.SubElement(notif_el, "companyRecallCode").text  = entry["company_recall_code"]
        etree.SubElement(notif_el, "productionDates").text    = entry["production_dates"]
        etree.SubElement(notif_el, "packagingDescription").text = entry["packaging_description"]

    tree = etree.ElementTree(root)
    tree.write(history_file_path, encoding="utf-8", xml_declaration=True, pretty_print=True)

# ------------------------------------------------------------------------------ 
# 3. Database CRUD Functions and Field Cleaning
# ------------------------------------------------------------------------------ 
def record_exists_in_db(connection, alert_number):
    """
    Check if a record with the given alert_number exists in the database.
    """
    with connection.cursor() as cursor:
        query = "SELECT COUNT(*) AS cnt FROM defective_products WHERE alert_number = %s"
        cursor.execute(query, (alert_number,))
        result = cursor.fetchone()
        return result["cnt"] > 0

def clean_field(value, max_length):
    """
    Attempt to clean a field by stripping spaces and removing single quotes.
    If the cleaned value still exceeds max_length, return None.
    """
    if not isinstance(value, str):
        return value

    # Remove single quotes and excess whitespace
    cleaned = value.strip().replace("'", "").replace("  ", " ")
    if len(cleaned) > max_length:
        return None
    return cleaned

def prepare_record_for_insertion(record):
    """
    Based on a mapping of record fields to max allowed lengths,
    clean each field. If any field cannot be cleaned to acceptable length,
    return None to signal skipping the record.
    """
    # Mapping: key in record -> max length allowed (according to your table schema)
    column_limits = {
        "alert_number": 100,
        "type_of_alert": 100,
        "alert_submitted_by": 100,
        "country_of_origin": 1023,
        "risk_type": 100,
        "product": 1023,
        "brand": 100,
        "category": 1023,
        "type_model": 100,
        "company_recall_page": 255,
        "url_of_case": 255,
        "barcode": 255,
        "batch_number": 1023,
        "company_recall_code": 100,
        "production_dates": 255,
    }

    # Work on a copy so as not to mutate the original
    record_cleaned = record.copy()

    for key, max_len in column_limits.items():
        if key in record_cleaned:
            cleaned_value = clean_field(record_cleaned[key], max_len)
            if cleaned_value is None:
                print(f"Skipping record {record.get('alert_number', 'N/A')} because field '{key}' exceeds allowed length even after cleaning.")
                return None
            record_cleaned[key] = cleaned_value

    return record_cleaned

def insert_record_into_db(connection, record):
    """
    Insert a single record into the defective_products table.
    If any field is too long (even after cleaning), the record will be skipped.
    """
    # Clean record fields based on predetermined limits.
    record_prepared = prepare_record_for_insertion(record)
    if record_prepared is None:
        print(f"Record with alert_number {record.get('alert_number', 'N/A')} was skipped due to field length issues.")
        return  # Skip this record

    insert_query = """
        INSERT INTO defective_products (
            type_of_alert,
            alert_number,
            alert_submitted_by,
            country_of_origin,
            counterfeit,
            risk_type,
            risk_legal_provision,
            product_name,
            product_description,
            brand,
            product_category,
            model_type_number,
            compulsory_measures,
            voluntary_measures,
            found_and_measures_taken_in,
            company_recall_page,
            case_url,
            barcode,
            batch_number,
            company_recall_code,
            production_dates,
            packaging_description
        )
        VALUES (
            %(type_of_alert)s,
            %(alert_number)s,
            %(alert_submitted_by)s,
            %(country_of_origin)s,
            %(counterfeit)s,
            %(risk_type)s,
            %(risk_legal_provision)s,
            %(product)s,
            %(name)s,
            %(brand)s,
            %(category)s,
            %(type_model)s,
            %(compulsory_measures)s,
            %(voluntary_measures)s,
            %(distribution_countries)s,
            %(company_recall_page)s,
            %(url_of_case)s,
            %(barcode)s,
            %(batch_number)s,
            %(company_recall_code)s,
            %(production_dates)s,
            %(packaging_description)s
        )
    """
    # Make sure 'counterfeit' is set to a valid value.
    if not record_prepared.get("counterfeit"):
        record_prepared["counterfeit"] = 0

    with connection.cursor() as cursor:
        cursor.execute(insert_query, record_prepared)
    connection.commit()
    print(f"Record with alert_number {record_prepared['alert_number']} inserted.")

# ------------------------------------------------------------------------------ 
# 4. Main Selenium + Download + Individual Merge + Insert Flow
# ------------------------------------------------------------------------------ 
def main():
    # Set up Chrome for headless file download
    chrome_options = Options()
    chrome_options.add_argument("--headless")
    chrome_options.add_argument("--disable-gpu")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_experimental_option(
        "prefs",
        {
            "safebrowsing.enabled": True,
        }
    )

    download_path = "/app/downloads"
    chrome_options.add_experimental_option(
        "prefs",
        {
            "download.default_directory": download_path,
            "download.prompt_for_download": False,
            "download.directory_upgrade": True,
            "safebrowsing.enabled": True
        }
    )

    driver = webdriver.Chrome(options=chrome_options)
    driver.set_window_size(1920, 1080)

    try:
        driver.get("https://ec.europa.eu/safety-gate-alerts/screen/search?resetSearch=true")
        WebDriverWait(driver, 20).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        print("Page loaded.")
        print(f"Driver title: {driver.title}")

        try:
            WebDriverWait(driver, 20).until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, ".ecl-link--standalone > .eui-icon-svg"))
            ).click()
            WebDriverWait(driver, 20).until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, ".ecl-popover__item:nth-child(1) .ecl-link__label"))
            ).click()
        except Exception as e:
            print("Error: Element not found", e)
            return

        time.sleep(10)  # Wait for download to complete

        files = [os.path.join(download_path, f) for f in os.listdir(download_path) if f.endswith('.xml')]
        if not files:
            print("No XML files found in the downloads folder.")
            return

        latest_file = max(files, key=os.path.getctime)
        print(f"Processing file: {latest_file}")

        root_new = parse_xml_file(latest_file)
        new_data = extract_notifications(root_new)
        print(f"Extracted {len(new_data)} new notifications from downloaded XML.")

    finally:
        driver.quit()
        print("Browser closed.")

    # ------------------------------------------------------------------------------ 
    # 5. Load existing history data and add new records one by one if not already present
    # ------------------------------------------------------------------------------ 
    history_file_path = "db/HistoryData.xml"
    history_dir = os.path.dirname(history_file_path)
    if not os.path.exists(history_dir):
        os.makedirs(history_dir)
    history_data = load_history_data(history_file_path)
    # Create a set for fast lookup using the alert number
    history_alerts = {item["alert_number"] for item in history_data if item["alert_number"]}

    # Process each new record against history
    for record in new_data:
        if record["alert_number"] and (record["alert_number"] not in history_alerts):
            history_data.append(record)
            history_alerts.add(record["alert_number"])
            print(f"Alert {record['alert_number']} added to history.")
        else:
            print(f"Alert {record['alert_number']} is already in history.")

    # Save the updated history data back to the XML file.
    write_history_data_to_xml(history_data, history_file_path)
    print(f"HistoryData.xml updated; it now contains {len(history_data)} records.")

    # ------------------------------------------------------------------------------ 
    # 6. For each record in history, check the DB individually and insert if needed
    # ------------------------------------------------------------------------------ 
    connection = connect_to_db()
    try:
        for record in history_data:
            # Only process records with a valid alert_number
            if not record["alert_number"]:
                continue

            if not record_exists_in_db(connection, record["alert_number"]):
                insert_record_into_db(connection, record)
            else:
                print(f"Record with alert_number {record['alert_number']} already exists in the database.")
    except Exception as e:
        print("Database operation error:", e)
    finally:
        connection.close()

# ------------------------------------------------------------------------------ 
# 7. Entry Point
# ------------------------------------------------------------------------------ 
if __name__ == "__main__":
    main()