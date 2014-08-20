<?php

/**
 * Example of crontab.class.php
 *
 * @author ICT & MEDIA, s.r.o.
 * @author Jan Lipovský <janlipovsky@gmail.com>
 * @link www.ictmedia.cz
 * @copyright (c) 2014, ICT & MEDIA, s.r.o.
 * @license BSD 3-Clause (LICENSE file is located in the root folder of this project)
 * 
 * @file index.php
 * @brief Example file
 * @details Example of crontab.class.php
 * 
 * @version 0.1
 * @date 2014-07-02
 */
require_once '../crontab.class.php';

//Initialize crontab
$cron = new crontab();

//Sets path to crontab
//$cron = new crontab('/usr/bin/');
//or
//$cron->setCrontabPath('/usr/bin/');

//Clear (empty) cron table
$cron->clearCronTable();
//Load cron table to memory
$cron->load();

//Add cron job to the crontab (as apache user)
$cron->addCronJob('1 * * * * /path/to/script1.sh');
//Append cron job to the crontab (as apache user)
$cron->appendCronJob('2 * * * * /path/to/script2.sh');
//Save cron job to the crontab at position 0
$cron->saveCronJob('3 * * * * /path/to/script3.sh', 0);

//Get cron table as array
$cron_table = $cron->getCronTable();
if ($cron_table !== false) {
    print_r($cron_table);
    echo "<br/><br/>";
}

//Saves cron table from array (each line is one crontab line)
$cron->saveCronTable($cron_table);


//Get position of string
$pos = $cron->getCronJobNumber('script1.sh');
if ($pos !== false) {
    echo "Cron job position of script1.sh is: " . $pos;
    echo "<br/>";

    //Get cron job line at position
    $job = $cron->getCronJobAt($pos);
    echo "Cron job at position [$pos]: " . $job;
    echo "<br/>";

    //Get cron job line containing
    $job = $cron->getCronJob('script1.sh');
    echo "Cron job containing 'script1.sh': " . $job;
    echo "<br/><br/>";

    //Delete cron job at line
    $cron->deleteCronJob($pos);
}

//Parse cron job to get an array ['min','hour','day','month','dotw','command']
$cronjob = $cron->parseCronJob($cron_table[0]);

echo "Cron job setting: <br/>";
echo "Minutes: " . $cronjob['min'] . " <br/>";
echo "Hours: " . $cronjob['hour'] . " <br/>";
echo "Days: " . $cronjob['day'] . " <br/>";
echo "Months: " . $cronjob['month'] . " <br/>";
echo "Day of the week: " . $cronjob['dotw'] . " <br/>";
echo "Command to run: " . $cronjob['command'];

echo "<br/><br/>";

//Get cron table as text (with parameter "<br/>" as delimiter)
$cron_tableTXT = $cron->getCronTableTXT("<br/>");
if ($cron_tableTXT !== false) {
    echo $cron_tableTXT;
}
