#!/bin/bash

# Создаем директорию для сборки
mkdir -p build/HomeAssistant.app/Contents/MacOS
mkdir -p build/HomeAssistant.app/Contents/Resources

# Копируем файлы
cp run.py build/HomeAssistant.app/Contents/MacOS/
cp Info.plist build/HomeAssistant.app/Contents/
cp -r templates build/HomeAssistant.app/Contents/Resources/
cp -r static build/HomeAssistant.app/Contents/Resources/
cp requirements.txt build/HomeAssistant.app/Contents/Resources/

# Делаем скрипт исполняемым
chmod +x build/HomeAssistant.app/Contents/MacOS/run.py

# Создаем символическую ссылку на Python
ln -s /usr/bin/python3 build/HomeAssistant.app/Contents/MacOS/python

echo "Сборка завершена. Приложение находится в папке build/" 