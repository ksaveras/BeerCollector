# Keliaujančio alaus surinkėjo užduotis
## Aprašymas
Jūs esate alaus fanatikas-milionierius. Atėjo savaitgalis. Jūsų bakūžė stovi LONG/LAT.
Turite idealų sraigtasparnį ir pakankamai kuro nuskristi 2000km.
Sugalvojote išbandyti kiek galima daugiau alaus rūšių tiesiai iš gamyklų…  
Sudarykite kelionės maršrutą kuris leistų parsiskraidinti daugiausiai skirtingų alaus rūšių.
 
Mūsų bakūžė 51.355468, 11.100790 - Bad Frankenhausen, Germany. 
Nesunkiai galima aplankyti 7 alaus daryklas ir grįžti atgal į bakūžę - be jokių optimizacijų.

[Originali užduotis](https://docs.google.com/document/d/1_WIUEpn18QugTxaaUbRcQ_VgyaO21OoCv39UlVElM7Q/edit#heading=h.irdfl0b9lep1)

## Duomenys
Alaus gamyklos, rūšys bei jų lokacijos pateiktos .csv failuose adresu https://github.com/brewdega/open-beer-database-dumps

## Programos naudojimas

Atsisiųsti composer paketus.
```
composer install --no-dev --optimize-autoloader
```

Programos vykdymas:

```
bin/console --lat LAT --lon LON
```
Kur LAT ir LON - ilguma ir platuma

Visas programos aprašas ir pagalba pateikiama iškvietus programą su --help parametru:
```
bin/console --help`
```
Dumps kataloge yra atsisiųsti csv failai ir papildyti keliomis alaus daryklų koordinatėmis.
