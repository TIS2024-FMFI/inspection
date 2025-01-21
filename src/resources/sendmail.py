import pymysql # type: ignore
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
EMAIL_PASSWORD = 'ajdb ucsa cjkv whfx'

SMTP_SERVER = 'smtp.gmail.com'
SMTP_PORT = 587

# -----------------------------------------------------------------------------
# 2. Connect to Database (using PyMySQL)
# -----------------------------------------------------------------------------
def get_db_connection():
    connection = pymysql.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASSWORD,
        db=DB_NAME
    )
    return connection

# -----------------------------------------------------------------------------
# 3. Fetch all users who have submitted defective products but have not been notified
# -----------------------------------------------------------------------------
def get_users_to_notify(connection):
    query = """
        SELECT DISTINCT u.id, u.email
        FROM users AS u
        JOIN user_submitted_products usp ON u.id = usp.user_id
        JOIN defective_products dp ON usp.barcode = dp.barcode
        WHERE u.notified = 0
    """
    with connection.cursor() as cursor:
        cursor.execute(query)
        results = cursor.fetchall()
    return results

# -----------------------------------------------------------------------------
# 4. Send Email Function
# -----------------------------------------------------------------------------
def send_notification_email(to_email):
    subject = "Important: Defective Product Notice"
    body = (
        "Dear user,\n\n"
        "One or more of the products you have submitted have been identified as defective.\n"
        "For safety and further instructions, please check your account.\n\n"
        "Regards,\n"
        "The Inspection Team"
    )
    
    msg = MIMEMultipart()
    msg['From'] = EMAIL_ADDRESS
    msg['To'] = to_email
    msg['Subject'] = subject
    msg.attach(MIMEText(body, 'plain'))

    with smtplib.SMTP(SMTP_SERVER, SMTP_PORT) as server:
        server.starttls()
        server.login(EMAIL_ADDRESS, EMAIL_PASSWORD)
        server.sendmail(EMAIL_ADDRESS, to_email, msg.as_string())

# -----------------------------------------------------------------------------
# 5. Mark user as notified
# -----------------------------------------------------------------------------
def mark_user_notified(connection, user_id):
    update_query = "UPDATE users SET notified = 1 WHERE id = %s"
    with connection.cursor() as cursor:
        cursor.execute(update_query, (user_id,))
    connection.commit()

# -----------------------------------------------------------------------------
# (Optional) Reset all users' notified status to 0
# -----------------------------------------------------------------------------
def reset_notified_status(connection):
    update_query = "UPDATE users SET notified = 0"
    with connection.cursor() as cursor:
        cursor.execute(update_query)
    connection.commit()

# -----------------------------------------------------------------------------
# 6. Main Flow
# -----------------------------------------------------------------------------
def main():
    conn = get_db_connection()
    
    users_to_notify = get_users_to_notify(conn)
    
    if not users_to_notify:
        print("No user submitted products found within the defective database")
        conn.close()
        return
    
    for user_id, email in users_to_notify:
        try:
            send_notification_email(email)
            print(f"Notification sent to {email}")
            mark_user_notified(conn, user_id)
        except Exception as e:
            print(f"Error sending email to {email}: {e}")
    
    # Reset user notified status after sending emails
    # reset_notified_status(conn)
    
    conn.close()

if __name__ == "__main__":
    main()
