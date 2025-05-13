<?php

for ($i = 0; $i < 12; $i++) {
    exec('/usr/local/bin/php /home1/psklwqte/public_html/backend/artisan queue:work');
    sleep(5);
}
