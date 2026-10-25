# Persoonlijke financiële administratie

Een kleine, zelfgebouwde PHP-applicatie om financiële transacties lokaal te bekijken en te beheren. Het doel is om zelf grip te houden op transacties, categorieën en de eigen financiële administratie, zonder afhankelijk te zijn van een externe budgetteringsdienst.

## Functionaliteit

- Financiële transacties importeren op basis van CSV-exports van ASN Bank.
- Transacties per jaar organiseren.
- Transacties en categorieën bekijken en beheren.
- Financiële gegevens lokaal opslaan en naar eigen inzicht organiseren.

## Technologie

- PHP
- HTML, CSS en een enkel JavaScript
- CSV-bestanden voor gegevensopslag
- Lokale ontwikkelomgeving (bijvoorbeeld MAMP)

## Gegevens importeren

De applicatie werkt met CSV-exports van ASN Bank. De transacties worden per kalenderjaar in een afzonderlijk CSV-bestand bewaard in de map `csv/`.

Voorbeeld van de mappenstructuur:

```text
csv/
├── 2024.csv
├── 2025.csv
└── 2026.csv
```

### Werkwijze

1. Categorisering wordt beheerd in de ASN omgeving.
2. Exporteer je transacties bij ASN Bank als CSV-bestand.
3. Plaats het bestand in de map `csv/`.
4. Gebruik één CSV-bestand per kalenderjaar.
5. Plaats spatie-gescheiden tags in het Notitie-veld voor verder overzicht.
6. Loop na importeren de transacties van bijvoorbeeld Tikkie, Paypal en Betaalverzoek na, die zijn vaak verkeerd gecategoriseerd.

De exacte bestandsnamen en CSV-indeling moeten overeenkomen met de manier waarop de applicatie de bestanden verwerkt.

**Let op:** CSV-bestanden bevatten financiële gegevens. Neem ze niet op in een GitHub-repository en deel ze niet zonder passende beveiliging.