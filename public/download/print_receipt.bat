@echo off
set /p filename=Enter the filename: 
set filepath=%userprofile%\Downloads\%filename%.txt
copy %filepath% COM3
exit