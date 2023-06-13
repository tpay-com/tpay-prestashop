# TPAY. INSTRUKCJA INSTALACJI MODUŁU PŁATNOŚCI

## WYMAGANIA

- Serwer PHP w wersji co najmniej 7.1
- PrestaShop w minimalnej wersji 1.7.7.5
- Dostępna waluta PLN (dla przelewów bankowych online)**

## DEVELOPER

### Wymagane
```
npm
composer
```

### Instalacja
Zainstaluj wszystkie zależności.

```
npm install
composer install
```

#### Generowanie zależności php
Przejdź do katalogu modułu, następnie wywołaj polecenie:
```
composer install # środowisko deweloperskie
composer build:prod # środowisko produkcyjne
```
#### Generowanie zależności js/ts
Przejdź do katalogu _dev w głównym katalogu modułu, wykonaj polecenie npm i aby wywołać instalacje zależności.

#### Generowanie css/js
Po instalacji należy wykonać polecenie:
```
npm run watch # środowisko deweloperskie z podglądem zmian)
npm run build # środowisko produkcyjne, minifikacja plików)
```

## INSTALACJA MODUŁU

- Pobierz moduł na dysk twardy i zaloguj się do panelu administracyjnego Prestashop
- Przejdź do Sekcji Ulepszenia -> Moduły -> Menedżer modułów i wybierz opcję załaduj moduł
- Wybierz pobrany plik i zainstaluj

## KONFIGURACJA MODUŁU

### Główne ustawienia
1. ID Klienta: Twój identyfikator sprzedawcy w systemie Tpay.com
2. Sekretny Klucz - hasło do wygenerowanego klucza API
3. Powiadomienia klucz bezpieczeństwa - Kod zabezpieczający jest dostępny w Panelu sprzedawcy w zakładce Ustawienia -> Powiadomienia -> bezpieczeństwo
4. Użyj środowiska testowego
5. Email dotyczączy powiadomień
6. Dopłata za korzystanie z płatności - Tworzy produkt o określonej dopłacie która jest dodawana do zamówienia jako osobna pozycja
   1. Rodzaj rozliczenia kwota, procent
   2. Wartość dopłaty
7. Widoczność kanałów płatności 
8. Inne ustawienia są opcjonalne i zależą od konkretnych wymagań
    Kliknij przycisk Zapisz

### Widoczność kanałów płatności
- Wybierz kanały płatności które mają się wyświetlać na głównej liście

### Karty kredytowe
1. Pokaż karty kredytowe w głównym widoku listy płatności
2. RSA key - Kod zabezpieczający jest dostępny w Panelu sprzedawcy w zakładce Płatności kartami -> API -> Klucz publiczny RSA


### Statusy płatności
1. Użyj statusów zdefiniowanych przez tpay
2. Dostępne statusy dla płatności 
- opłacona płatność
- oczekująca płatność
- niepoprawna niepoprawna
