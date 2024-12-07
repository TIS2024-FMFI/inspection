import os
import requests
from bs4 import BeautifulSoup

# Simplify seed.sql path handling
seed_file_path = os.path.join("src", "resources", "db", "seed.sql")

# Verify the resolved path
if not os.path.exists(seed_file_path):
    raise FileNotFoundError(f"Could not find seed.sql at {seed_file_path}. Please ensure the path exists.")

print(f"Using seed.sql at: {seed_file_path}")

# Base URL and main page URL for scraping
base_url = "https://www.soi.sk"
main_page_url = f"{base_url}/sk/Nebezpecne-vyrobky/Narodny-trh-SR.soi"

# Define functions (unchanged from previous script)
def extract_text_by_label(parent_div, label):
    ...

def extract_text_by_label_fallback(wrap_div, label):
    ...

def extract_text(parent_div, label):
    ...

# Process individual pages and generate SQL inserts
def process_page(page_url):
    response = requests.get(page_url)
    if response.status_code == 200:
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
        insert_statements = []
        for link in product_links:
            print(f"Scraping: {link}")
            product_response = requests.get(link)
            if product_response.status_code == 200:
                product_page = BeautifulSoup(product_response.text, 'html.parser')
                wrap_div = product_page.find('div', class_='wrap')
                if wrap_div:
                    # Extract details
                    product_title = wrap_div.find('h1').text.strip() if wrap_div.find('h1') else "Title not found"
                    category = extract_text(wrap_div, 'Kategória:')
                    date = extract_text(wrap_div, 'Dátum:')
                    country_of_origin = extract_text(wrap_div, 'Pôvod:')
                    product_description = extract_text(wrap_div, 'Identifikácia výrobku:')
                    risk_type = extract_text(wrap_div, 'Druh nebezpečnosti:')
                    causes_of_danger = extract_text(wrap_div, 'Príčiny nebezpečnosti:')
                    images = [img['src'] for img in wrap_div.find_all('img') if 'src' in img.attrs]

                    # Create SQL insert statement
                    images_string = ",".join(images)
                    sql = f"""
                    INSERT INTO defective_products (
                        product_name, product_category, published_on, country_of_origin, product_description, risk_type, risk_description, images
                    ) VALUES (
                        '{product_title}', '{category}', '{date}', '{country_of_origin}', '{product_description}', '{risk_type}', '{causes_of_danger}', '{images_string}'
                    );
                    """
                    insert_statements.append(sql)

        # Write to seed.sql
        with open(seed_file_path, "a", encoding="utf-8") as seed_file:
            seed_file.write("\n-- Inserted data from SOI website\n")
            seed_file.write("\n".join(insert_statements))
        return soup
    return None

# Handle pagination and scrape all pages
def scrape_all_pages():
    current_url = main_page_url
    while current_url:
        print(f"Processing page: {current_url}")
        soup = process_page(current_url)

        # Find the next page link
        next_button = soup.find('a', class_='AspNet-Pager-NextPage', rel='next')
        if next_button and 'href' in next_button.attrs:
            current_url = next_button['href']
            if not current_url.startswith("https://"):
                current_url = f"{base_url}{current_url}"
        else:
            print("No more pages to process.")
            current_url = None

# Start scraping
scrape_all_pages()
