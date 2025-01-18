import os
from sys import stderr
import requests
from bs4 import BeautifulSoup
import pymysql
from datetime import datetime

print("Starting scraping process of the Slovak website, please stand tight...")

def connect_to_db():
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

try:
    # Establish database connection
    pdo = connect_to_db()

    with pdo.cursor() as cursor:
        # Delete rows in the child table first
        cursor.execute("DELETE FROM product_history WHERE product_id IN (SELECT id FROM defective_products)")
        pdo.commit()

        # Now delete rows in the parent table
        cursor.execute("DELETE FROM defective_products")
        pdo.commit()
    print("Successfully cleared defective_products and related rows in product_history.")
except Exception as e:
    print(f"Error clearing defective_products or related rows: {e}", file=stderr)
    pdo.rollback()

# Base URL and main page URL for scraping
base_url = "https://www.soi.sk"
main_page_url = f"{base_url}/sk/Nebezpecne-vyrobky/Narodny-trh-SR.soi"

# Define a function to extract text based on labels (original method)
def extract_text_by_label(parent_div, label):
    all_elements = parent_div.find_all(['div', 'span', 'p', 'u', 'strong'])
    for element in all_elements:
        if label in element.text:
            label_position = element.text.find(label)
            if label_position != -1:
                extracted_text = element.text[label_position + len(label):].strip()
                if extracted_text and label not in extracted_text:
                    return extracted_text
            sibling = element.find_next_sibling()
            if sibling and sibling.text.strip():
                return sibling.text.strip()
            nested_text = element.find_next(string=True)
            if nested_text and nested_text.strip():
                return nested_text.strip()
    return None

# Define a fallback function for tougher cases
def extract_text_by_label_fallback(wrap_div, label):
    label_element = wrap_div.find(['u', 'strong'], string=lambda text: text and label in text)
    if label_element:
        parent = label_element.find_parent()
        if parent:
            next_sibling = parent.find_next_sibling()
            if next_sibling and next_sibling.text.strip():
                return next_sibling.text.strip()
            label_position = parent.text.find(label)
            if label_position != -1:
                extracted_text = parent.text[label_position + len(label):].strip()
                if extracted_text and label not in extracted_text:
                    return extracted_text
    for sibling in wrap_div.find_all(['div', 'span', 'p']):
        if label in sibling.text:
            nearby_text = sibling.text.replace(label, '').strip()
            if nearby_text:
                return nearby_text
    return None

# Wrapper function to try both methods
def extract_text(parent_div, label):
    text = extract_text_by_label(parent_div, label)
    if text is None:
        text = extract_text_by_label_fallback(parent_div, label)
    if text and label in text:
        text = None
    return text

def parse_date(date_string):
    try:
        # Remove extra spaces and parse date
        date_string = date_string.replace(" ", "")
        parsed_date = datetime.strptime(date_string, "%d.%m.%Y")
        return parsed_date  # Return as a datetime object
    except (ValueError, TypeError):
        print(f"Invalid date format: {date_string}", file=stderr)
        return None    

# Process individual pages and insert into database
def process_page(page_url):
    response = requests.get(page_url)
    if response.status_code == 200:
        # Explicitly set encoding
        response.encoding = 'utf-8'
        soup = BeautifulSoup(response.text, 'html.parser')

        # Find all product links
        articles = soup.find_all('div', class_='item')
        product_links = []
        for article in articles:
            link_tag = article.find('a', class_='inside')
            if link_tag and 'href' in link_tag.attrs:
                href = link_tag['href']
                full_url = href if href.startswith("https://") else f"{base_url}{href}"
                product_links.append(full_url)

        # Process each product
        for link in product_links:
            print(f"Scraping: {link}", file=stderr)
            product_response = requests.get(link)
            if product_response.status_code == 200:
                # Fix encoding for product page
                product_response.encoding = 'utf-8'
                product_page = BeautifulSoup(product_response.text, 'html.parser')
                wrap_div = product_page.find('div', class_='wrap')
                if wrap_div:
                    # Extract details
                    product_title = wrap_div.find('h1').text.strip() if wrap_div.find('h1') else "Title not found"
                    category = extract_text(wrap_div, 'Kategória:')
                    date = parse_date(extract_text(wrap_div, 'Dátum:'))
                    print( f"Date: {date}", file=stderr)
                    country_of_origin = extract_text(wrap_div, 'Pôvod:')
                    product_description = extract_text(wrap_div, 'Identifikácia výrobku:')
                    risk_type = extract_text(wrap_div, 'Druh nebezpečnosti:')
                    causes_of_danger = extract_text(wrap_div, 'Príčiny nebezpečnosti:')
                    images = [img['src'] for img in wrap_div.find_all('img') if 'src' in img.attrs]

                    # Use the first image only
                    image_url = images[0] if images else None

                    # Insert data into the database
                    try:
                        with pdo.cursor() as cursor:
                            sql = """
                            INSERT INTO defective_products (
                                product_name, product_category, published_on, country_of_origin, 
                                product_description, risk_type, risk_description, images, case_url
                            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s);
                            """
                            cursor.execute(sql, (
                                product_title, category, date, country_of_origin,
                                product_description, risk_type, causes_of_danger, image_url, link
                            ))
                            pdo.commit()
                        print(f"Inserted: {product_title}", file=stderr)
                    except Exception as e:
                        print(f"Error inserting data for {product_title}: {e}", file=stderr)
    return soup

def scrape_all_pages():
    current_url = main_page_url  # Ensure main_page_url is correctly defined
    while current_url:
        try:
            print(f"Processing page: {current_url}", file=stderr)
            soup = process_page(current_url)  # Ensure process_page returns a valid soup object

            # Find the next page link
            next_button = soup.find('a', class_='AspNet-Pager-NextPage', rel='next')
            if next_button and 'href' in next_button.attrs:
                current_url = next_button['href']
                if not current_url.startswith("https://"):
                    current_url = f"{base_url}{current_url}"  # Ensure base_url is correctly defined
            else:
                print("No more pages to process.")
                current_url = None

        except Exception as e:
            print(f"Error processing page {current_url}: {e}")
            current_url = None  # Stop the loop if an error occurs

# Start scraping

scrape_all_pages()