#!/usr/bin/env powershell
# Install FFmpeg (static build) and configure project to use it
# Non-interactive script. MUST be run as Administrator.
# Usage (PowerShell as Administrator):
# Set-ExecutionPolicy Bypass -Scope Process -Force; .\install_ffmpeg.ps1

Try {
    $isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
} Catch {
    $isAdmin = $false
}
if (-not $isAdmin) {
    Write-Error "Ce script doit être exécuté en tant qu'administrateur. Ouvrez PowerShell en mode administrateur et relancez le script."
    exit 1
}

$zipUrl = 'https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-essentials.zip'
$tempZip = "$env:TEMP\\ffmpeg.zip"
$extractDir = "$env:TEMP\\ffmpeg_extracted"
$destDir = 'C:\ffmpeg\\bin'

Write-Host "Téléchargement de FFmpeg depuis : $zipUrl"
Invoke-WebRequest -Uri $zipUrl -OutFile $tempZip -UseBasicParsing

Write-Host "Extraction dans : $extractDir"
if (Test-Path $extractDir) { Remove-Item -Recurse -Force $extractDir }
Expand-Archive -LiteralPath $tempZip -DestinationPath $extractDir -Force

# Rechercher ffmpeg.exe dans l'archive
$ffmpegExe = Get-ChildItem -Path $extractDir -Recurse -Filter 'ffmpeg.exe' -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $ffmpegExe) {
    Write-Error "ffmpeg.exe introuvable dans l'archive. Extraction échouée."
    exit 1
}

Write-Host "ffmpeg trouvé : $($ffmpegExe.FullName)"

# Créer le dossier de destination
if (-not (Test-Path $destDir)) { New-Item -ItemType Directory -Path $destDir -Force | Out-Null }

# Copier ffmpeg.exe et les DLL associées
Copy-Item -Path $ffmpegExe.FullName -Destination $destDir -Force
$binDir = Split-Path $ffmpegExe.FullName -Parent
Get-ChildItem -Path $binDir -Filter '*.dll' -ErrorAction SilentlyContinue | ForEach-Object {
    Copy-Item -Path $_.FullName -Destination $destDir -Force
}

# Ajouter C:\ffmpeg\bin à la variable PATH système si nécessaire
$currentPath = [Environment]::GetEnvironmentVariable('Path', [EnvironmentVariableTarget]::Machine)
$addPath = 'C:\ffmpeg\\bin'
if (-not $currentPath.Split(';') -contains $addPath) {
    $newPath = $currentPath + ';' + $addPath
    [Environment]::SetEnvironmentVariable('Path', $newPath, [EnvironmentVariableTarget]::Machine)
    Write-Host "Ajout de $addPath à la variable PATH système."
} else {
    Write-Host "$addPath est déjà présent dans PATH système."
}

# Mettre à jour includes/config.php
$configPath = Join-Path $PSScriptRoot 'includes\\config.php'
$phpContent = "<?php`n// Configuration automatique créée par install_ffmpeg.ps1`n$CONFIG = [`n    'ffmpeg_cmd' => 'C:\\\\ffmpeg\\\\bin\\\\ffmpeg.exe'`n];`n"
Set-Content -Path $configPath -Value $phpContent -Encoding UTF8

Write-Host "FFmpeg installé dans : $destDir"
Write-Host "Fichier de configuration mis à jour : $configPath"
Write-Host "Redémarrez Apache (XAMPP) et éventuellement votre session Windows pour prendre en compte PATH, puis testez l'extraction." 