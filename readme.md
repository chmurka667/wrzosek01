Aplikacja do przechowywania i zarządzania notesem oraz listą rzeczy do zrobienia
Wymagane
Docker i Docker compose
Utworzenie projektu
Kopiujemy do katalogu projekt

git clone https://github.com/chmurka667/wrzosek01
W PhpStorm naciskamy open i otwieramy projekt

Instalacja
W terminalu, bedąc w ścieżce projektu wpisujemy

docker-compose build
Następnie uruchamiamy kontenery

docker-compose up -d
Potem, wchodzimy do kontenera dockera php

docker-compose exec php bash
i wydajemy polecenia

cd app
rm .gitkeep
git config --global user.email "you@example.com"
git config --global --add safe.directory /home/wwwroot/app
symfony new ../app --full --version=5.4
chown -R dev.dev *
rm -rf .git
Połącz sie z daną bazy dockera w pliku '.env'. Trzeba w pliku zmienić linie DATABASE_URL na:

DATABASE_URL=mysql://symfony:symfony@mysql:3306/symfony?serverVersion=5.7
Ostatecznie:

composer install
bin/console doctrine:migrations:migrate
bin/console doctrine:fixtures:load
Aby połączyć się ze symfony w przyglądarce i sprawdzić czy działa przechodzimy do

http://localhost:8000
