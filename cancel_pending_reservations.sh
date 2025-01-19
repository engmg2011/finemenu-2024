#!/bin/bash
# Navigate to the project directory
cd /var/www/menuai/backend

# Run the CancelPendingReservations command using Sail
./vendor/bin/sail artisan app:cancel-pending-reservations

