#!/usr/bin/env python3
import os
import sys
import subprocess

def install_requirements():
    subprocess.check_call([sys.executable, "-m", "pip", "install", "-r", "requirements.txt"])

def main():
    # Устанавливаем зависимости
    install_requirements()
    
    # Запускаем Flask приложение
    os.environ['FLASK_APP'] = 'app.py'
    os.environ['FLASK_ENV'] = 'development'
    subprocess.check_call([sys.executable, "-m", "flask", "run", "--host=0.0.0.0"])

if __name__ == "__main__":
    main() 