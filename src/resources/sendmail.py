import pymysql  # type: ignore
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
import os

# -----------------------------------------------------------------------------
# 1. Configure Database and Email
# -----------------------------------------------------------------------------
DB_HOST = os.getenv('DB_HOST', 'localhost')
DB_USER = os.getenv('DB_USER', 'root')
DB_PASSWORD = os.getenv('DB_PASSWORD', '')
DB_NAME = os.getenv('DB_NAME', 'safety_app')

EMAIL_ADDRESS = 'safety.inspection.team@gmail.com'
EMAIL_PASSWORD = 'yaap hdkr fmmf wbev'

SMTP_SERVER = 'smtp.gmail.com'
SMTP_PORT = 587

PRODUCT_PAGE_URL_TEMPLATE = "http://194.182.84.121/ProductPage.php?id={id}"

# -----------------------------------------------------------------------------
# 2. Connect to Database (using PyMySQL)
# -----------------------------------------------------------------------------
def get_db_connection():
    connection = pymysql.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASSWORD,
        db=DB_NAME,
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor  # To get results as dictionaries
    )
    return connection

# -----------------------------------------------------------------------------
# 3. Fetch All Users Who Have Submitted Products
# -----------------------------------------------------------------------------
def get_all_users(connection):
    query = "SELECT id, email FROM users"
    with connection.cursor() as cursor:
        cursor.execute(query)
        users = cursor.fetchall()
    return users

# -----------------------------------------------------------------------------
# 4. Fetch Defective Products with Their IDs
# -----------------------------------------------------------------------------
def get_defective_products(connection):
    """
    Returns a dictionary mapping barcode to defective product id.
    """
    query = "SELECT id, barcode FROM defective_products"
    with connection.cursor() as cursor:
        cursor.execute(query)
        rows = cursor.fetchall()
    # Assuming barcodes are unique. If not, you might need to handle multiple IDs per barcode.
    defective_products = {row['barcode']: row['id'] for row in rows}
    return defective_products

# -----------------------------------------------------------------------------
# 5. Fetch User's Submitted Products
# -----------------------------------------------------------------------------
def get_user_submitted_products(connection, user_id):
    query = "SELECT id, barcode FROM user_submitted_products WHERE user_id = %s"
    with connection.cursor() as cursor:
        cursor.execute(query, (user_id,))
        products = cursor.fetchall()
    return products

# -----------------------------------------------------------------------------
# 6. Send Email Function
# -----------------------------------------------------------------------------
def send_notification_email(to_email, defective_product_ids):
    subject = "Important: Defective Product Notice"

    # Create list of product references
    product_links = [
        PRODUCT_PAGE_URL_TEMPLATE.format(id=product_id)
        for product_id in defective_product_ids
    ]

    # Create the email body with product links
    body = (
        "Dear User,\n\n"
        "We have identified the following products you submitted as defective:\n\n"
    )
    for link in product_links:
        body += f"- {link}\n"

    body += (
        "\nPlease check your account for more details and further instructions.\n\n"
        "Regards,\n"
        "The Inspection Team"
    )

    # Create MIME message
    msg = MIMEMultipart()
    msg['From'] = EMAIL_ADDRESS
    msg['To'] = to_email
    msg['Subject'] = subject
    msg.attach(MIMEText(body, 'plain'))

    # Send the email
    try:
        with smtplib.SMTP(SMTP_SERVER, SMTP_PORT) as server:
            server.starttls()
            server.login(EMAIL_ADDRESS, EMAIL_PASSWORD)
            server.sendmail(EMAIL_ADDRESS, to_email, msg.as_string())
    except Exception as e:
        print(f"Failed to send email to {to_email}: {e}")
        raise  # Re-raise exception to handle it in the main flow if necessary

# -----------------------------------------------------------------------------
# 7. Mark User as Notified
# -----------------------------------------------------------------------------
def mark_user_notified(connection, user_id):
    update_query = "UPDATE users SET notified = 1 WHERE id = %s"
    with connection.cursor() as cursor:
        cursor.execute(update_query, (user_id,))
    connection.commit()

# -----------------------------------------------------------------------------
# 8. Main Flow
# -----------------------------------------------------------------------------
def main():
    try:
        conn = get_db_connection()
    except pymysql.MySQLError as e:
        print(f"Error connecting to the database: {e}")
        return

    try:
        users = get_all_users(conn)
        if not users:
            print("No users found in the database.")
            return

        defective_products_mapping = get_defective_products(conn)
        if not defective_products_mapping:
            print("No defective products found in the database.")
            return

        for user in users:
            user_id = user['id']
            email = user['email']
            try:
                submitted_products = get_user_submitted_products(conn, user_id)
                if not submitted_products:
                    print(f"User {email} has no submitted products.")
                    continue

                # Identify defective products for the user based on barcode mapping
                defective_product_ids = [
                    defective_id
                    for product in submitted_products
                    if (defective_id := defective_products_mapping.get(product['barcode']))
                ]

                if defective_product_ids:
                    # Remove duplicates if a user has multiple submissions mapping to the same defective product
                    unique_defective_product_ids = list(set(defective_product_ids))

                    # Send notification email with defective product links
                    send_notification_email(email, unique_defective_product_ids)
                    # print(f"Notification sent to {email} for products: {unique_defective_product_ids}")

                    # Mark user as notified
                    #mark_user_notified(conn, user_id)
                #else:
                    #print(f"No defective products found for user {email}.")

            except Exception as e:
                #print(f"Error processing user {email}: {e}")
                ...

    finally:
        print("Notifications processed, closing the database connection...")
        conn.close()

if __name__ == "__main__":
    main()
