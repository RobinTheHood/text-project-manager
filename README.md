# Dokumentation

Ein Projekt besteht aus folgenden Komponenten:
- Aufgaben (Tasks)
- Bearbeiter (User)
- Berichte (Reports)


## Aufgabe (Task)

Ein Projekt besteht aus einer oder mehreren Aufgaben (Task). Eine Aufgabe fängt immer mit einem `#` Zeichen an. Hier ein Beispiel:

```markdown
# Aufgabe 1

# Aufgabe 2
```

## Bearbeiter (User)

Ein Aufgabe kann von unterschiedlichen Bearbeitern (User) bearbeitet werden. Der Name eines Bearbeiter fängt immer mit einem `@` Zeichen an. Hier ein Beispiel:

```markdown
# Aufgabe 1
@ Max

# Aufgabe 2
@ Max
@ Lena
```


## Bericht (Report)

Zu jedem Bearbeiter (User) je Aufgabe (Task) können Zeiten oder eine Menge in Form eines Berichts (Report) protokolliert werden. Ein Bericht (Report) fängt immer mit einem `-` oder einem `+` Zeichen an. Ein Breicht, der mit einem `-` beginnt, wird nicht in Rechnung gestellt. Ein Breicht der mit `+` beginnt, wird in Rechnung gestellt. Hier ein Beispiel:

```markdown
# Aufgabe 1
@ Max
+ 1.1.2023; 10min; Report 1 - Bild auf Webseite ausgetauscht

# Aufgabe 2
@ Max
+ 2.1.2023; 20min; Report 2 - Design

@ Lena
- 3.1.2023; 1h; Report 3 - Programmierung - Fehler behoben
```

Ein Bricht (Report) ist wie folgt aufgebaut:

```
(+ | -) ; DATUM ; (DAUER | MENGE) ; BESCHREIBUNG ; [EXTERNE-KOSTEN] ; [INTERNE-KOSTEN]
````

Jeder Teil eines Berichts (Report) wird durch ein `;` Zeichen getrennt. `(A | B)` wird als `A` oder `B` gelesen und `[C]` wird als optional `C` gelsen. Ein Bericht (Report) ist aus folgenden Teilen aufgebaut. Ein Bericht startet mit `+` oder `-`, gefolgt von einem Datum, gefolgt von einer Dauer oder einer Menge, gefolgt von einer Beschreibung. Optional gefolgt von externen Kosten, die optional von internen Kosten gefolgt werden können.

Für die einzelenen Teile können wie folgt verwendet werden:

- `+` Ein Report, der in Rechnung gestellt werden kann
- `-` Ein Report, der nicht in Rechnung gestellt werden kann
- `DATUM` Datum in Tag.Monat.Jahr oder Wochentag, oder heute oder gestern z. B. `1.1.2023` oder `montag` oder `heute`
- `DAUER` Möglich sind Minuten `min` und Stunden `h` z. B. `10min` oder `2h`
- `MENGE` z. B. `2x` oder `1x / Monat` 
- `BESCHREIBUNG` Eine Beschreibung
- `EXTERNE-KOSTEN` Externe Kosten pro Einheit in Euro z. B. `80,00€`
- `INTERNE-KOSTEN` Interne Kosten pro Einheit in Euro z. B. `30,00€`


## Vorlagen (Templates)

Hier ein Beispiel:

```markdown
> Hosting
```