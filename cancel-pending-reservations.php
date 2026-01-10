<?php
/**
 * Put in crontab
 *
 * /19 * * * * /usr/local/bin/php /home1/psklwqte/public_html/backend/cancel-pending-reservations.php >> ~/logs/cron.log 2>&1
 *
 * Hint you can use direct in crontab
 *
 *  * * * * /usr/bin/curl -s -X GET "https://api.shalehi.com/cancel-pending-reservations"
 *  * * * * /usr/bin/curl -s -X GET "https://api.shalehi.com/queue-work"
 *
 *
 */


for ($i = 0; $i < 4; $i++) {

    sleep(5*60*60);
    exec('/usr/local/bin/php /home1/psklwqte/public_html/backend/artisan app:cancel-pending-reservations');
}
