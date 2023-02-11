# Piac szimuláció

Piaci folyamat szimuláció.


## WEB SITE 
[http://piac.infora.hu](http://piac.infora.hu)

## Tulajdonságok

- PHP, MYSQL backend, vue frontend,
- bootstrap, fontawesome,
- MVC struktúra,
- login/logout/regist rendszer, Google és Facebook login támogatása,
- usergroups rendszer,
- SEO url támogatása,
- egszerű telepíthetőség, nem szükséges konzol hozzáférés,



## Célkitűzés
	Általában apriori, axiomatikus tételként tekintünk arra az
	állításra, hogy a piaci folyamatok (ideális esetben) a
	kereslet/kínálat egyensúlyához és az optimális erőforrás
	allokációhoz vezetnek. Arra gondoltam ennek az állításnak a
	bizonyítására, szemléltetésére vagy esetleg cáfolatára
	készítenék egy szimulációs programot. 

Müködés leírást lásd: [http://piac.infora.hu](http://piac.infora.hu)

### A programot mindenki csak saját felelősségére használhatja.
						
## Lecensz

GNU v3

## Információk informatikusok számára      

## Szükséges sw környezet
### futtatáshoz
- web szerver   .htacces és rewrite támogatással
- php 7+ (mysqli kiegészítéssel)
- mysql 5+
### fejlesztéshez
- phpunit (unit test futtatáshoz)
- doxygen (php dokumentáció előállításhoz)
- nodejs (js unittesthez)
- php és js szintaxist támogató forrás szerkesztő vagy IDE

## Telepítés

- adatbázis létrehozása (utf8, magyar rendezéssel),
- config.php elkészítése a a config-example.php alapján,
- a views/impressum, policy, policy2, policy3 fájlok szükség szerinti módosítása
- fájlok és könyvtárak feltöltése a szerverre,
- az images könyvtár legyen irható a web szerver számára, a többi csak olvasható legyen,
- többfelhasználós üzemmód esetén; a program "Regisztrálás" menüpontjában hozzuk létre a
  a system adminisztrátor fiokot (a config.php -ban beállított bejelentkezési névvel).
- adatbázis táblák és kezdeti tartalom létrehozása a doc/create-db.sql segitségével

Könyvtár szerkezet a futtató web szerveren:
```
[document_root]
  [images]
     kép fájlok (alkönyvtárak is lehetnek)
  [includes]
    [controllers]
      kontrollerek php fájlok
    [models]
      adat modellek php fájlok
    [views]
      viewer templates  spec. html fájlok. vue elemeket tartalmaznak
  [vendor]
    keretrendszer fájlok és harmadik féltől származó fájlok (több alkönyvtárat is tartalmaz)
  index.php  - fő program
  config.php - konfigurációs adatok
  style.css  - megjelenés
  files.txt  - a telepített fájlok felsorolása, az upgrade folyamat használja

```  
index.php paraméter nélküli hívással a "home.show" taskal indul a program.

index.php?task=upgrade1&version=vx.x&branch=xxxx hívással a github megadott branch -et használva  
is tesztelhető/használható az upgrade folyamat.

