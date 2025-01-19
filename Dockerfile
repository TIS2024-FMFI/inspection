FROM php:8.1.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libmariadb-dev \
    libmariadb-dev-compat \
    python3 \
    python3-pip \
    python3-venv \
    chromium \
    chromium-driver \
    build-essential \
    python3-dev && \
    docker-php-ext-install pdo_mysql && \
    a2enmod rewrite && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

COPY requirements.txt /tmp/requirements.txt
RUN pip install --no-cache-dir -r /tmp/requirements.txt && \
    rm /tmp/requirements.txt

COPY ./src/resources /var/www/html/
COPY ./src/resources /app/python/

RUN mkdir -p /app/python && \
    chown -R www-data:www-data /var/www/html /app /opt/venv && \
    chmod -R 755 /var/www/html

RUN mkdir -p /var/www/.cache/selenium \
    && chown -R www-data:www-data /var/www/.cache \
    && chmod -R 755 /var/www/.cache

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

USER www-data