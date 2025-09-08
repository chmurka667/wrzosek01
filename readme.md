# Aplikacja do tworzenia skrótów URL

Prosty projekt Symfony uruchamiany w kontenerach Docker/Docker Compose.

---

## Wymagania

* **Docker** oraz **Docker Compose**
* **PhpStorm** (opcjonalnie)

---

## Klonowanie projektu

```bash
git clone https://github.com/chmurka667/wrzosek01
```

W PhpStorm wybierz **Open** i wskaż sklonowany katalog.

---

## Budowanie i uruchamianie kontenerów

W terminalu, będąc w katalogu projektu:

```bash
docker-compose build
docker-compose up -d
```

Wejście do kontenera PHP:

```bash
docker-compose exec php bash
```

---

## Inicjalizacja aplikacji Symfony (wewnątrz kontenera)

```bash
cd app
rm .gitkeep
git config --global user.email "you@example.com"
git config --global --add safe.directory /home/wwwroot/app
symfony new ../app --version="6.4.*" --webapp
chown -R dev.dev *
rm -rf .git
```

> Uwaga: polecenie `symfony new ../app` tworzy projekt w katalogu nadrzędnym `app`.

---

## Konfiguracja bazy danych

W pliku `.env` zaktualizuj wartość `DATABASE_URL` tak, aby wskazywała na usługę MySQL z Dockera:

```env
DATABASE_URL="mysql://symfony:symfony@mysql:3306/symfony?serverVersion=5.7"
```

---

## Instalacja zależności i dane startowe

Wciąż wewnątrz kontenera:

```bash
composer install
bin/console doctrine:migrations:migrate
bin/console doctrine:fixtures:load
```

---

## Dostęp do aplikacji

Otwórz w przeglądarce:

```
http://localhost:8000
```

Jeśli wszystko przebiegło pomyślnie, zobaczysz stronę startową Symfony.
