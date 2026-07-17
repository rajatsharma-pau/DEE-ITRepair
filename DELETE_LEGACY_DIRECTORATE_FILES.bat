@echo off
REM Optional only. Run this from Laravel project root ONLY after confirming no file uses App\Directorate.
REM Search command:
REM   findstr /S /I "App\Directorate Directorate:: directorate_id" *.php
REM If no active code uses it, you may delete this compatibility model:
REM   del app\Directorate.php

echo This patch removes the directorates TABLE. app\Directorate.php is now only a compatibility alias to colleges.
echo Do not delete app\Directorate.php unless you are sure no old code uses it.
