import os
import time
import pymysql
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
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
            host='localhost',
            user='root',
            password='',
            database='safety_app',
            cursorclass=pymysql.cursors.DictCursor
        )
    except pymysql.MySQLError as e:
        print(f"Database connection failed: {e}")
        raise

# ------------------------------------------------------------------------------
# 2. Utility Functions
# ------------------------------------------------------------------------------
def sanitize(value):
    """Remove single quotes or other problematic characters from strings to avoid SQL injection or query errors."""
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
    if not os.path.exists(history_file_path):
        print("123")
        return []

    root = parse_xml_file(history_file_path)
    return extract_notifications(root)

def merge_data_with_history(new_data, history_data):
    existing_alert_numbers = {item["alert_number"] for item in history_data}
    
    merged_data = list(history_data)
    for item in new_data:
        if item["alert_number"] and (item["alert_number"] not in existing_alert_numbers):
            merged_data.append(item)
            existing_alert_numbers.add(item["alert_number"])
    
    return merged_data

def write_history_data_to_xml(all_data, history_file_path):
    root = etree.Element("historyRoot")

    for entry in all_data:
        notif_el = etree.SubElement(root, "notifications")
        etree.SubElement(notif_el, "typeOfAlert").text       = entry["type_of_alert"]
        etree.SubElement(notif_el, "caseNumber").text        = entry["alert_number"]
        etree.SubElement(notif_el, "submittedBy").text       = entry["alert_submitted_by"]
        etree.SubElement(notif_el, "countryOfOrigin").text   = entry["country_of_origin"]
        etree.SubElement(notif_el, "counterfeit").text       = entry["counterfeit"]
        etree.SubElement(notif_el, "riskType").text          = entry["risk_type"]
        etree.SubElement(notif_el, "riskLegalProvision").text= entry["risk_legal_provision"]
        etree.SubElement(notif_el, "product").text           = entry["product"]
        etree.SubElement(notif_el, "name").text              = entry["name"]
        etree.SubElement(notif_el, "brand").text             = entry["brand"]
        etree.SubElement(notif_el, "category").text          = entry["category"]
        etree.SubElement(notif_el, "typeNumberModel").text   = entry["type_model"]
        etree.SubElement(notif_el, "compulsoryMeasures").text= entry["compulsory_measures"]
        etree.SubElement(notif_el, "voluntaryMeasures").text = entry["voluntary_measures"]
        etree.SubElement(notif_el, "distributionCountries").text = entry["distribution_countries"]
        etree.SubElement(notif_el, "companyRecallPage").text = entry["company_recall_page"]
        etree.SubElement(notif_el, "urlOfCase").text         = entry["url_of_case"]
        etree.SubElement(notif_el, "barcode").text           = entry["barcode"]
        etree.SubElement(notif_el, "batchNumber").text       = entry["batch_number"]
        etree.SubElement(notif_el, "companyRecallCode").text = entry["company_recall_code"]
        etree.SubElement(notif_el, "productionDates").text   = entry["production_dates"]
        etree.SubElement(notif_el, "packagingDescription").text = entry["packaging_description"]

    tree = etree.ElementTree(root)
    tree.write(history_file_path, encoding="utf-8", xml_declaration=True, pretty_print=True)

def insert_data_into_database(data_list):
    connection = connect_to_db()
    try:
        with connection.cursor() as cursor:
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

            for item in data_list:
                cursor.execute(insert_query, item)

        connection.commit()
        print(f"{len(data_list)} records inserted/updated successfully.")
    except pymysql.MySQLError as e:
        connection.rollback()
        print(f"Database operation error: {e}")
    finally:
        connection.close()

# ------------------------------------------------------------------------------
# 3. Main Selenium + Download + Merge + Insert Flow
# ------------------------------------------------------------------------------
def main():
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

    driver = webdriver.Chrome(options=chrome_options)
    
    try:
        driver.get("https://ec.europa.eu/safety-gate-alerts/screen/search?resetSearch=true")
        WebDriverWait(driver, 20).until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        print("Page loaded.")

        popover_toggle = WebDriverWait(driver, 20).until(
            EC.element_to_be_clickable((By.XPATH, "//a[contains(@class, 'ecl-popover__toggle')]"))
        )
        popover_toggle.click()
        print("Popover toggle clicked.")


        WebDriverWait(driver, 10).until(
            EC.visibility_of_element_located((By.CLASS_NAME, "ecl-popover__content"))
        )
        print("Popover content is visible.")
        export_to_xml = WebDriverWait(driver, 20).until(
            EC.element_to_be_clickable((By.XPATH, "//span[text()='Export to XML']/parent::a"))
        )
        export_to_xml.click()
        print("Export to XML clicked.")
        time.sleep(10)

        download_path = os.path.expanduser("~/Downloads")
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
    # 4. Load existing history and merge
    # ------------------------------------------------------------------------------
    history_file_path = "C:\\xampp\\htdocs\\inspection\\inspection\\src\\resources\\db\\HistoryData.xml"
    history_data = load_history_data(history_file_path)
    print(f"Loaded {len(history_data)} records from HistoryData.xml")

    merged_data = merge_data_with_history(new_data, history_data)
    print(f"Merged dataset has {len(merged_data)} total records.")

    # ------------------------------------------------------------------------------
    # 5. Update the HistoryData.xml with the merged data
    # ------------------------------------------------------------------------------
    write_history_data_to_xml(merged_data, history_file_path)
    print(f"HistoryData.xml updated with any new records. Now contains {len(merged_data)} total records.")

    # ------------------------------------------------------------------------------
    # 6. Insert the merged data into the database
    # ------------------------------------------------------------------------------
    insert_data_into_database(merged_data)

# ------------------------------------------------------------------------------
# 7. Entry Point
# ------------------------------------------------------------------------------
if __name__ == "__main__":
    main()
