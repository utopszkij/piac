# Célkitüzés

Piaci mechanizmus szimuláció

## objektumok

### konfigurációs beállítások

- pénznem
- kezdeti órabér 
- kezdeti inaktív járandóság
- adó
- products
- markers és workers csoportok

### Products
termékek, szolgáltatások  (köztük a "munkaóra" nevű is)

- id
- megnevezés
- mérték egység
- kategória (fogyasztási cikk/termelő eszköz)meghetározza a fogyaszt
- napi minimális szükséglet egy ember számára (csak a fogyastási cikkeknél)
- piaci ár (egy egység)
- egy egység előállításához szükséges erőforrások [{productId, quantity},....]

### markers
termelő csoportok

- id
- megnevezés
- létszám
- termékek [{productId, capacity, realized}, ...]
- dolgozóknak fizetett napi bér
- életszinvonal (a szükséglethez képest ennyit akar fogyasztani)
- napi kivett nyereség 
- folyószámla
- stratégia 

### workers 
dolgozó csoportok

- id
- megnevezés
- létszám
- életszinvonal (a szükséglethez képest ennyit akar fogyasztani)
- folyószámla 

### inactivers

- id
- létszám
- életszinvonal (a szükséglethez képest ennyit akar fogyasztani)
- folyószámla
- havi járandóság 


## Müködés
Inditáskor beállítjuk a "products", "markers", "workers", "incativers" táblákat egy kezdeti értékkel.

Egyszerüsített modell, egy marker csoporttal  és egy worker csoporttal számol

A program napi ciklusokban végzi el a következőket:

- fizetőképes fogyasztási cikk kereslet (dolgozók + tőkések kivett nyeresége)
- fogyasztási cikk szükségletek termékenként természetes mértékegységben és piaci értékben amrkereknél is átlag fogyasztással számolva (szükséglet)
- fogyasztási cikk szükségletek termékenként természetes mértékegységben és piaci értékben amrkereknél luxus fogyasztással számolva (igény)
- kinálatok árúcikkenként természetes mértékegységben és piaci értéken
- keresletek árúcikkenként természetes mértékegységben és piaci értéken (termelő eszközöknél rekrzió!)
- összesített fogyasztási cikk kinálat piaci áron
- ha az összesített fogyasztási cikk kinálat > fizetőképes kereslet akkor **tultermelés kezelés** 
- adó fizetés (dolgozók bére és markerek kivett nyeresége után)
- tranzakciók végrehajtása (folyószámla változások: munkabérek, fogyasztási cikk értékesitések, termelő eszköz értékesitések, inaktivak juttatásai)
- **nap kiértékelése**
- **markerek gazdasági döntése** 
- **állam gazdasági döntése** 

### Túltermelés kezelés

fogyasztási cikk túltermelés kezelés
a túltermelés arányában csökkenti az egyes fogyasztási cikkeknél a **realized** értéket.

### Markerek gazdasági döntései

nyereség számítás

- ha a realized < capacity akkor a termék capacity csökkentése a realized értékre
- ha egy árucikknél a kinálat < kereslet akkor capacity növelés és ár emelés
- ha egy árucikknél a kinálat > kereslet akkor capacity csökkentés és ár csökkentés

### Állam gazdasági döntése

- ha az állami folyószámla minuszba megy akkor adó emelés
- ha az állami folyószámla pluszos akkor adó csökkentés

## Nap kiértékelése

- a fogyasztási cikk kinálat fedezi a szükségleteket (a markereknél is csak az átlag fogyasztással számolva)?
- a fogyasztási cikk kinálat fedezi az igényeket (a markereknél luxus fogyasztással számolva) ?
- a termelő eszköz kinálat fedezi az igényeket?
- a fizető képes kereslet fedezi a szükségleteket?
- a fizető képes kereslet fedezi az igényeket?
- inaktivak juttatásaira van fedezet?
- grafikon: fogyasztási cikk kinálat piaci áron, fogyasztási cikk igény piaci áron összesítve 
- grafikon fogyasztási cikkenként: kinálat / igény / szükséglet 
- grafokon szereplőnként folyószámlák alakulása
