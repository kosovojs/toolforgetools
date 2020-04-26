var last_update = '2018. gada 13. maijā';

Array.prototype.unique = function() {
  return this.filter(function (value, index, self) { 
    return self.indexOf(value) === index;
  });
}

function push_to_arr(arr,key,val) {
	if (key in arr) {
		arr[key].push(val);
	} else {
		arr[key] = [val];
	}
	
	return arr
}

projects= {
	'Grèce_antique':['Senā Grieķija','Senā Gr.'],
	'URSS':['PSRS','PSRS'],
	'Empire_ottoman':['Osmaņu impērija','Osmaņi'],
	'Empire_autrichien':['Austrijas impērija','Austr. imp.'],
	'Albanie':['Albānija','ALB'],
	'Arménie':['Armēnija','ARM'],
	'Autriche':['Austrija','AUT'],
	'Innsbruck':['Insbruka','Insb'],
	'Vienne_(Autriche)':['Vīne','Wien'],
	'Azerbaïdjan':['Azerbaidžāna','AZE'],
	'Bakou':['Baku','Baku'],
	'Biélorussie':['Baltkrievija','BLR'],
	'Bosnie-Herzégovine':['Bosnija-Hercogovina','Bosn'],
	'Bulgarie':['Bulgārija','BUL'],
	'République_tchèque':['Čehija','CZE'],
	'Tchécoslovaquie':['Čehoslovākija','TCH'],
	'Prague':['Prāga','Prāga'],
	'Géorgie_(pays)':['Gruzija','GEO'],
	'Croatie':['Horvātija','CRO'],
	'Macédoine':['Maķedonija','MCD'],
	'Skopje':['Skopje','Skopje'],
	'Moldavie':['Moldāvija','Mold'],
	'Roumanie':['Rumānija','ROU'],
	'Bucarest':['Bukareste','Bukar'],
	'Serbie':['Serbija','SRB'],
	'Belgrade':['Belgrada','Belgr.'],
	'Slovaquie':['Slovākija','SVK'],
	'Bratislava':['Bratislava','Brat'],
	'Košice':['Košice','Košice'],
	'Hongrie':['Ungārija','HUN'],
	'Budapest':['Budapešta','Budap'],
	'Grèce':['Grieķija','GRE'],
	'Athènes':['Atēnas','Atēn'],
	'Crète':['Krēta','Krēta'],
	'Kazakhstan':['Kazahstāna','KAZ'],
	'Chypre':['Kipra','Kipra'],
	'Kosovo':['Kosova','Kosova'],
	'Monténégro':['Melnkalne','Melnk'],
	'Slovénie':['Slovēnija','SLO'],
	'Turquie':['Turcija','TUR'],
	'Istanbul':['Stambula','Stambul'],
	'Estonie':['Igaunija','EST'],
	'Tallinn':['Tallina','Tall.'],
	'Lituanie':['Lietuva','LTU'],
	'Pologne':['Polija','POL'],
	'Varsovie':['Varšava','Var.'],
	'Cracovie':['Krakova','Krak.'],
	'Ukraine':['Ukraina','UKR'],
	'Russie':['Krievija','RUS'],
	'Sibérie':['Sibīrija','Sib'],
	'Moscou':['Maskava','Mask'],
	'Oblast_de_Novossibirsk':['Novosibirskas apgabals','Novos.'],
	'Saint-Pétersbourg':['Sanktpēterburga','Sanktp'],
	'Sotchi':['Soči','Soči'],
}
