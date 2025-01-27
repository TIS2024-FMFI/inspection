#!/usr/bin/env python3

import sys
import json
import pymysql  # type: ignore
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
import os
import secrets
from datetime import datetime, timedelta

# Configurations
DB_HOST = os.getenv('DB_HOST', 'localhost')
DB_USER = os.getenv('DB_USER', 'safety_app_user')
DB_PASSWORD = os.getenv('DB_PASSWORD', 'safety_app_password')
DB_NAME = os.getenv('DB_NAME', 'safety_app')

EMAIL_ADDRESS = 'safety.inspection.team@gmail.com'
EMAIL_PASSWORD = 'yaap hdkr fmmf wbev'  # **Important**: Use environment variables for sensitive data
SMTP_SERVER = 'smtp.gmail.com'
SMTP_PORT = 587

RESET_LINK_TEMPLATE = "http://194.182.84.121//reset_password.php?token={token}"

# Database connection
def get_db_connection():
    connection = pymysql.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASSWORD,
        db=DB_NAME,
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )
    return connection

# Generate reset token and save it to the database
def create_password_reset_token(connection, email):
    query = "SELECT id FROM users WHERE email = %s"
    with connection.cursor() as cursor:
        cursor.execute(query, (email,))
        user = cursor.fetchone()

    if not user:
        return None  # User doesn't exist

    user_id = user['id']
    token = secrets.token_urlsafe(32)
    expires_at = datetime.now() + timedelta(minutes=15)

    query = "INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (%s, %s, %s)"
    with connection.cursor() as cursor:
        cursor.execute(query, (user_id, token, expires_at))
        connection.commit()

    return token

# Send reset email
def send_reset_email(email, token):
    reset_link = RESET_LINK_TEMPLATE.format(token=token)
    subject = "Password Reset Request"
    body = (
        "Dear User,\n\n"
        "We received a request to reset your password. If this was you, click the link below to reset your password:\n\n"
        f"{reset_link}\n\n"
        "This link will expire in 15 minutes. If you did not request this, please ignore this email.\n\n"
        "Regards,\n"
        "The Safety App Team"
    )

    msg = MIMEMultipart()
    msg['From'] = EMAIL_ADDRESS
    msg['To'] = email
    msg['Subject'] = subject
    msg.attach(MIMEText(body, 'plain'))

    try:
        with smtplib.SMTP(SMTP_SERVER, SMTP_PORT) as server:
            server.starttls()
            server.login(EMAIL_ADDRESS, EMAIL_PASSWORD)
            server.sendmail(EMAIL_ADDRESS, email, msg.as_string())
    except Exception as e:
        # Output JSON error message
        print(json.dumps({"success": False, "message": f"Failed to send email: {str(e)}"}))
        sys.exit(1)

# Main forgot password function
def handle_forgot_password(email):
    connection = get_db_connection()
    try:
        token = create_password_reset_token(connection, email)
        if token:
            send_reset_email(email, token)
            return True
        else:
            return False
    finally:
        connection.close()

def main():
    if len(sys.argv) != 2:
        print(json.dumps({"success": False, "message": "Invalid number of arguments. Expected email."}))
        sys.exit(1)

    email = sys.argv[1]

    # Basic email validation
    if not email or '@' not in email:
        print(json.dumps({"success": False, "message": "Invalid email format."}))
        sys.exit(1)

    try:
        success = handle_forgot_password(email)
        if success:
            print(json.dumps({"success": True, "message": "Password reset email sent."}))
        else:
            print(json.dumps({"success": False, "message": "Email not found."}))
    except Exception as e:
        print(json.dumps({"success": False, "message": f"An error occurred: {str(e)}"}))
        sys.exit(1)

if __name__ == "__main__":
    main()
