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
        return value.replace("'", "")
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
    Given the root of the parsed XML tree, extract the <notifications> elements,
    mapping fields from the EU XML to our database schema.
    Returns a list of dictionaries with the corresponding field names.
    """
    notifications = xml_root.findall(".//notifications")
    notifications_data = []

def extract_notifications(xml_root):
    """
    Given the root of the parsed XML tree, extract the <notifications> elements,
    mapping fields from the EU XML to our database schema.
    Returns a list of dictionaries with the corresponding field names.
    """
    notifications = xml_root.findall(".//notifications")
    notifications_data = []

    for notification in notifications:
        # Retrieve text content from elements; if an element is nil or missing, use an empty string.
        def get_text(tag):
            # Using xpath to support attributes like xsi:nil
            element = notification.find(tag)
            if element is not None and element.get("{http://www.w3.org/2001/XMLSchema-instance}nil") != "true":
                return sanitize(element.text or "")
            return ""

        # For pictures, we handle multiple <picture> items under <pictures>.
        image_url = ""
        pictures_el = notification.find("pictures")
        if pictures_el is not None:
            picture_elements = pictures_el.findall("picture")
            if picture_elements:
                # Only take the first picture URL
                image_url = sanitize(picture_elements[0].text or "")

        # Map XML fields to our table schema
        data = {
            # Basic identifiers
            "alert_number":       get_text("caseNumber"),   # EU: caseNumber
            "case_url":           get_text("reference"),    # EU: reference

            # Product information
            "product_name":       get_text("name"),         # EU: name
            "product_info":       get_text("product"),      # EU: product
            "product_category":   get_text("category"),     # EU: category
            "brand":              get_text("brand"),        # brand
            "model_type_number":  get_text("type_numberOfModel"),  # EU: type_numberOfModel

            # Batch and codes
            "batch_number":       get_text("batchNumber"),       # batchNumber
            "barcode":            get_text("barcode"),           # barcode
            "company_recall_code":get_text("companyRecallCode"), # companyRecallCode

            # Risk and measures information
            "risk_type":          get_text("riskType"),      # EU: riskType
            "risk_info":          get_text("danger"),        # EU: danger (risk info)
            "measures":           get_text("measures"),      # EU: measures

            # Additional recall and production info
            "company_recall_page":get_text("URLrecall"),     # EU: URLrecall
            "product_description":get_text("description"),   # EU: description
            "production_dates":   get_text("productionDates"),  # productionDates

            # Country and origin info
            "notifying_country":  get_text("notifyingCountry"), # EU: notifyingCountry
            "country_of_origin":  get_text("countryOfOrigin"),  # EU: countryOfOrigin

            # Other fields
            "type":               get_text("type"),          # EU: type
            "level":              get_text("level"),         # EU: level

            # Media: Set to the first picture URL retrieved from <pictures>
            "images":             image_url,
        }
        notifications_data.append(data)
    return notifications_data


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

        etree.SubElement(notif_el, "caseNumber").text         = entry["alert_number"]
        etree.SubElement(notif_el, "reference").text          = entry["case_url"]

        etree.SubElement(notif_el, "name").text               = entry["product_name"]
        etree.SubElement(notif_el, "product").text            = entry["product_info"]
        etree.SubElement(notif_el, "category").text           = entry["product_category"]
        etree.SubElement(notif_el, "brand").text              = entry["brand"]
        etree.SubElement(notif_el, "type_numberOfModel").text   = entry["model_type_number"]

        etree.SubElement(notif_el, "batchNumber").text        = entry["batch_number"]
        etree.SubElement(notif_el, "barcode").text            = entry["barcode"]
        etree.SubElement(notif_el, "companyRecallCode").text  = entry["company_recall_code"]

        etree.SubElement(notif_el, "riskType").text           = entry["risk_type"]
        etree.SubElement(notif_el, "danger").text             = entry["risk_info"]
        etree.SubElement(notif_el, "measures").text           = entry["measures"]

        etree.SubElement(notif_el, "URLrecall").text          = entry["company_recall_page"]
        etree.SubElement(notif_el, "description").text        = entry["product_description"]
        etree.SubElement(notif_el, "productionDates").text    = entry["production_dates"]

        etree.SubElement(notif_el, "notifyingCountry").text   = entry["notifying_country"]
        etree.SubElement(notif_el, "countryOfOrigin").text    = entry["country_of_origin"]

        etree.SubElement(notif_el, "type").text               = entry["type"]
        etree.SubElement(notif_el, "level").text              = entry["level"]

        # Write images inside a pictures element
        pictures_el = etree.SubElement(notif_el, "pictures")
        etree.SubElement(pictures_el, "picture").text         = entry["images"]

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
    cleaned = value.strip().replace("'", "").replace("  ", " ")
    if len(cleaned) > max_length:
        return None
    return cleaned

def prepare_record_for_insertion(record):
    """
    Clean record fields based on a mapping of record keys to max allowed lengths.
    If any field cannot be cleaned to an acceptable length,
    return None to signal skipping the record.
    """
    column_limits = {
        "alert_number": 100,
        "case_url": 255,
        "product_name": 255,
        "product_info": 255,
        "product_category": 255,
        "brand": 100,
        "model_type_number": 100,
        "batch_number": 255,
        "barcode": 255,
        "company_recall_code": 100,
        "risk_type": 255,
        "company_recall_page": 255,
        "production_dates": 255,
        "notifying_country": 100,
        "country_of_origin": 255,
        "type": 100,
        "level": 100,
    }

    record_cleaned = record.copy()
    for key, max_len in column_limits.items():
        if key in record_cleaned:
            cleaned_value = clean_field(record_cleaned[key], max_len)
            if cleaned_value is None:
                return None
            record_cleaned[key] = cleaned_value
    return record_cleaned

def insert_record_into_db(connection, record):
    """
    Insert a single record into the defective_products table.
    If any field is too long (even after cleaning), the record will be skipped.
    """
    record_prepared = prepare_record_for_insertion(record)
    if record_prepared is None:
        return  # Skip this record

    # Switched info and name in the query to match the schema
    insert_query = """
        INSERT INTO defective_products (
            alert_number,
            case_url,
            product_name, 
            product_info,
            product_category,
            brand,
            model_type_number,
            batch_number,
            barcode,
            company_recall_code,
            risk_type,
            risk_info,
            measures,
            company_recall_page,
            product_description,
            production_dates,
            notifying_country,
            country_of_origin,
            type,
            level,
            images
        )
        VALUES (
            %(alert_number)s,
            %(case_url)s,
            %(product_name)s,
            %(product_info)s,
            %(product_category)s,
            %(brand)s,
            %(model_type_number)s,
            %(batch_number)s,
            %(barcode)s,
            %(company_recall_code)s,
            %(risk_type)s,
            %(risk_info)s,
            %(measures)s,
            %(company_recall_page)s,
            %(product_description)s,
            %(production_dates)s,
            %(notifying_country)s,
            %(country_of_origin)s,
            %(type)s,
            %(level)s,
            %(images)s
        )
    """
    with connection.cursor() as cursor:
        cursor.execute(insert_query, record_prepared)
    connection.commit()

# ------------------------------------------------------------------------------
# 4. Main Selenium + Download + Merge + Insert Flow
# ------------------------------------------------------------------------------
def main():
    # Set up Chrome for headless file download
    chrome_options = Options()
    chrome_options.add_argument("--headless")
    chrome_options.add_argument("--disable-gpu")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    # Ensure safe browsing and download preferences are set
    download_path = "/app/downloads"
    prefs = {
        "download.default_directory": download_path,
        "download.prompt_for_download": False,
        "download.directory_upgrade": True,
        "safebrowsing.enabled": True,
    }
    chrome_options.add_experimental_option("prefs", prefs)

    driver = webdriver.Chrome(options=chrome_options)
    driver.set_window_size(1920, 1080)

    try:
        driver.get("https://ec.europa.eu/safety-gate-alerts/screen/search?resetSearch=true")
        WebDriverWait(driver, 20).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        print("Page loaded.")
        print(f"Driver title: {driver.title}")

        try:
            # Click the export/download icon then select the export option
            WebDriverWait(driver, 20).until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, ".ecl-link--standalone > .eui-icon-svg"))
            ).click()
            WebDriverWait(driver, 20).until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, ".ecl-popover__item:nth-child(1) .ecl-link__label"))
            ).click()
        except Exception as e:
            print("Error: Element not found", e)
            return

        time.sleep(10)  # Wait for the download to complete

        # Look for .xml files in the download folder
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
    # 5. Merge with History Data
    # ------------------------------------------------------------------------------
    history_file_path = "db/HistoryData.xml"
    history_dir = os.path.dirname(history_file_path)
    if not os.path.exists(history_dir):
        os.makedirs(history_dir)
    history_data = load_history_data(history_file_path)
    # Create a set for fast lookup using the alert number
    history_alerts = {item["alert_number"] for item in history_data if item["alert_number"]}

    # Process each new record to update history (only add if not already present)
    for record in new_data:
        if record["alert_number"] and (record["alert_number"] not in history_alerts):
            history_data.append(record)
            history_alerts.add(record["alert_number"])

    # Save updated history data back to the XML file
    write_history_data_to_xml(history_data, history_file_path)
    print(f"HistoryData.xml updated; it now contains {len(history_data)} records.")

    # ------------------------------------------------------------------------------
    # 6. Insert Records into Database
    # ------------------------------------------------------------------------------
    connection = connect_to_db()
    try:
        for record in history_data:
            # Only process records with a valid alert_number
            if not record["alert_number"]:
                continue

            if not record_exists_in_db(connection, record["alert_number"]):
                insert_record_into_db(connection, record)
    except Exception as e:
        print("Database operation error:", e)
    finally:
        connection.close()

# ------------------------------------------------------------------------------
# 7. Entry Point
# ------------------------------------------------------------------------------
if __name__ == "__main__":
    main()