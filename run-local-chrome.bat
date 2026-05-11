@echo off
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\serve-local-chrome.ps1" %*
