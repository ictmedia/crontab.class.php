Crontab class PHP
=================
PHP Class for easy maintenance of cron tables using crontab command on Unix/Linux OS.


Requirements
------------
1. PHP version >= PHP 4
2. OS Linux
3. Shell commands:
  1. `crontab`
  2. `which`
  3. `whoami`

---

Class usage
===========

How to use crontab.class.php. Working example is located in example/index.php file.

### Initialize
```PHP
require_once 'crontab.class.php';
$cron = new crontab();
```

### Clear (empty) cron table
```php
$cron->clearCronTable();
```

### Adding cron jobs to the crontab (as apache user)
Adds cron job
```php
$cron->addCronJob('1 * * * * /path/to/script1.sh');
```

Append cron job
```php
$cron->appendCronJob('2 * * * * /path/to/script2.sh');
```

Save cron job to position x
```php
$cron->saveCronJob('3 * * * * /path/to/script3.sh', 0);
```


### Get cron table
Get cron table as an array. One cron job per line.
```php
$cron_table = $cron->getCronTable();
```

Get cron table as text (with parameter "<br/>" as a delimiter)
```php
$cron_tableTXT = $cron->getCronTableTXT("<br/>");
```

### Save cron table to the crontab (as apache user)
```php
$cron->saveCronTable($cron_table);
```

### Cron jobs
Get number of the 'script1.sh' cron job
```php
$pos = $cron->getCronJobNumber('script1.sh');
```

Get cron job at position $pos
```php
$job = $cron->getCronJobAt($pos);
```

Get cron job with the 'script1.sh' string
```php
$job = $cron->getCronJob('script1.sh');
```

Delete cron job at line
```php
$cron->deleteCronJob($pos);
```

Parse cron job
```php
$cronjob = $cron->parseCronJob($job);
echo "Cron job setting: <br/>";
echo "Minutes: " . $cronjob['min'] . " <br/>";
echo "Hours: " . $cronjob['hour'] . " <br/>";
echo "Days: " . $cronjob['day'] . " <br/>";
echo "Months: " . $cronjob['month'] . " <br/>";
echo "Day of the week: " . $cronjob['dotw'] . " <br/>";
echo "Command to run: " . $cronjob['command'];
```


(c) 2014 ICT & MEDIA, s.r.o.
