var CAMPAIGN = 'lv-P569-20210718';
var PROP = 569;

function findValues(txt, lang) {
	// txt = 'gfgdfdgfg 1867. gada 12. jūnijā fdgdfgdfg'console.log(lang)
	$.ajax({
		type: 'GET',
		url: 'monthnames.json',
		dataType: 'json',
		async: false,
	}).done(function (monthnames) {
		txt = txt.replace(/–|-|—/g, ' - ');
		digits = {
			'०': 0,
			'१': 1,
			'२': 2,
			'३': 3,
			'४': 4,
			'५': 5,
			'६': 6,
			'७': 7,
			'८': 8,
			'९': 9,
		};
		roman = {
			1: 'I',
			2: 'II',
			3: 'III',
			4: 'IV',
			5: 'V',
			6: 'VI',
			7: 'VII',
			8: 'VIII',
			9: 'IX',
			10: 'X',
			11: 'XI',
			12: 'XII',
		};
		$.each(digits, function (k, v) {
			r = new RegExp(k, 'g');
			txt = txt.replace(r, v);
		});

		// Japanese/Chinese/Korean
		r = new RegExp('(\\d{4})(年|年）|年[〈（(][^）〉)]+[〉|）|)]|년 )(\\d{1,2})(月|월 )(\\d{1,2})(日|일)', 'g');
		txt = txt.replace(
			r,
			"<span class='value' data-day='$5' data-month='$3' data-year='$1' title='$1-$3-$5'><span>$1</span>$2<span>$3</span>$4<span>$5</span>$6</span>"
		);
		r = new RegExp("(\\d{4})(年|年）|年[〈（(][^）〉)]+[〉|）|)]|년 )(\\d{1,2})(月|월 )([^-'<(])", 'g');
		txt = txt.replace(r, "<span class='value' data-month='$3' data-year='$1' title='$1-$3'><span>$1</span>$2<span>$3</span>$4</span>$5");
		r = new RegExp("(\\d{4})(年|年）|年[〈（(][^）〉)]+[〉|）|)]|년 )([^-'<(])", 'g');
		txt = txt.replace(r, "<span class='value' data-year='$1' title='$1'><span>$1</span>$2</span>$3");

		$.each(monthnames[lang] || {}, function (name, num) {
			//lv
			r = new RegExp('((\\d+)\\. *g(?:ada)?\\.? (\\d+)\\. *(' + name + '))', 'gi');
			txt = txt.replace(r, "<span class='value' data-day='$3' data-month='" + num + "' data-year='$2' title='$2-" + num + "-$3'>$1</span>");
			//lt
			//1912 m. sausio 15 d.
			r = new RegExp('((\\d+)\\.? *m\\.? *(' + name + ') (\\d+))', 'gi');
			txt = txt.replace(r, "<span class='value' data-day='$4' data-month='" + num + "' data-year='$2' title='$2-" + num + "-$3'>$1</span>");
			// day, month, year
			r = new RegExp("\\b(\\d{1,2})( |\\. |º |er | - an? de | de | d')?(" + name + ')(,| del?|, इ.स.| พ.ศ.)? (\\d{4})', 'gi');
			txt = txt.replace(
				r,
				"<span class='value' data-day='$1' data-month='" +
					num +
					"' data-year='$5' title='$5-" +
					num +
					"-$1'><span>$1</span>$2<span>$3</span>$4 <span>$5</span></span>"
			);
			// month, day, year
			r = new RegExp('(' + name + '|' + name.substr(0, 3) + ') (\\d{1,2})t?h?\\,? (\\d{4})', 'gi');
			txt = txt.replace(
				r,
				"<span class='value' data-day='$2' data-month='" +
					num +
					"' data-year='$3' title='$3-" +
					num +
					"-$2'><span>$1</span> <span>$2</span>, <span>$3</span></span>"
			);
			// year, month, day
			r = new RegExp('\\b(\\d{4})(e?ko|\\.|,)? (' + name + ')(aren)? (\\d{1,2})(a|ean|an)?', 'gi');
			txt = txt.replace(
				r,
				"<span class='value' data-day='$5' data-month='" +
					num +
					"' data-year='$1' title='$1-" +
					num +
					"-$5'><span>$1</span>$2 <span>$3</span>$4 <span>$5</span>$6</span>"
			);
			// month and year
			r = new RegExp('(' + name + '|' + name.substr(0, 3) + ") (\\d{4})([^-'</\\d年년])", 'gi');
			txt = txt.replace(
				r,
				"<span class='value' data-month='" + num + "' data-year='$2' title='$2-" + num + "'><span>$1</span> <span>$2</span></span>$3"
			);
		});

		for (var num = 1; num <= 12; num++) {
			// day, month (number), year
			r = new RegExp('(\\d{1,2})([. /]+| tháng )(0?' + num + '|' + roman[num] + ')([., /]+| năm )(\\d{4})', 'gi');
			txt = txt.replace(
				r,
				"<span class='value' data-day='$1' data-month='" +
					num +
					"' data-year='$5' title='$5-" +
					num +
					"-$1'><span>$1</span>$2<span>$3</span>$4<span>$5</span></span>"
			);
			// year, month (number), day
			r = new RegExp("[^'>](\\d{4})( - |/)(0?" + num + '|' + roman[num] + ')( - |/)(\\d{1,2})\\b', 'gi');
			txt = txt.replace(
				r,
				"<span class='value' data-day='$5' data-month='" +
					num +
					"' data-year='$1' title='$1-" +
					num +
					"-$5'><span>$1</span>$2<span>$3</span>$4<span>$5</span></span>"
			);
		}
		//only year
		txt = txt.replace(new RegExp("([^>])(\\d{4})([^-'<])", 'gi'), "$1<span class='value' data-year='$2' title='$2'>$2</span>$3");
	});
	return txt;
}

function findValue1111(txt, lang) {
	txt = txt.replace(/–|-|—/g, ' - ');
	digits = {
		'०': 0,
		'१': 1,
		'२': 2,
		'३': 3,
		'४': 4,
		'५': 5,
		'६': 6,
		'७': 7,
		'८': 8,
		'९': 9,
	};
	roman = {
		1: 'I',
		2: 'II',
		3: 'III',
		4: 'IV',
		5: 'V',
		6: 'VI',
		7: 'VII',
		8: 'VIII',
		9: 'IX',
		10: 'X',
		11: 'XI',
		12: 'XII',
	};
	$.each(digits, function (k, v) {
		r = new RegExp(k, 'g');
		txt = txt.replace(r, v);
	});

	$.each(monthnames, function (name, num) {
		//(\d+)\. *g(ada)?\.? (\d+)\. *(jūlij[sā])

		r = new RegExp('\\b((\\d+)\\. *g(?:ada)?\\.? (\\d+)\\. *(' + name + '))', 'gi');
		txt = txt.replace(r, "<span class='value' data-day='$3' data-month='" + num + "' data-year='$2' title='$2-" + num + "-$3'>$1</span>");
	});

	//only year
	txt = txt.replace(new RegExp("([^>])(\\d{4})([^-'<])", 'gi'), "$1<span class='value' data-year='$2' title='$2'>$2</span>$3");

	return txt;
}

function loadNextItem() {
	//$('.box').fadeOut(300, function() {
	if ($('.active').next().length == 0) showItem($('#wd_items a:first'));
	else showItem($('.active').next());
	//});
}

var my_test_text =
	'hjhjk 2015. gads \
2015. gada 15. jūlijā \
2015. gada 15. jūlijs \
2015. gada 15.jūlijā \
2015. gada 15.jūlijs \
2015.gada 15. jūlijā \
2015.gada 15. jūlijs \
2015.gada 15.jūlijā \
2015.gada 15.jūlijs \
2015. g. 15. jūlijs \
2015.g 15. jūlijs \
2015.g. 15. jūlijs \
2015. g. 15.jūlijs \
2015.g 15.jūlijs \
2015.g. 15.jūlijs';

function addDate(q, article, site, year, month, day, nodob, raw, campaign) {
	var year = year != '' ? parseInt(year) : '';

	month = typeof month !== 'undefined' ? month : '00';
	day = typeof day !== 'undefined' ? day : '00';
	if (String(month).length == 1) month = '0' + month;
	if (day.length == 1) day = '0' + day;
	precision = 9;
	if (month != '00') precision = 10;
	if (month != '00' && day != '00') precision = 11;
	//console.log('were here');

	let campaignName = $('.active').attr('data-random') && $('.active').attr('data-random') == 'yes' ? 'ranom' : CAMPAIGN;

	$.ajax({
		type: 'POST',
		url: 'save.php',
		data: {
			property: PROP,
			q: q,
			site: site,
			article: article,
			year: year,
			month: month,
			day: day,
			prec: precision,
			nodob: nodob,
			raw: raw,
			campaign: campaignName,
		},
		success: function (response) {
			$('#display_info').html(response);
		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			alert('Status: ' + textStatus);
			alert('Error: ' + errorThrown);
		},
	});
}

function removeLinks(text) {
	text = text.replace(/<!--[\s\S]*?-->/g, '');
	text = text.replace(/&#160;/g, ' ');
	return text.replace(/<\/?a[^>]*>/g, '');
}

function getFullWikipediapage(site, lang, title, q) {
	//site = 'lvwiki';
	//lang = 'lv';
	site = lang + 'wiki';
	$.getJSON(
		'https://' + lang + '.wikipedia.org/w/api.php?callback=?',
		{
			action: 'parse',
			page: title,
			disableeditsection: 1,
			disabletoc: 1,
			format: 'json',
		},
		function (data) {
			const thiscontent = findValues(removeLinks(data['parse']['text']['*']), lang) + '</div>';
			$('#wikitext').html(
				`<a href="https://${lang}.wikipedia.org/w/index.php?title=${title}">lvwiki</a> | <a href="https://www.wikidata.org/wiki/${q}">wd</a><hr />${thiscontent}`
			); // + '</div>');

			var categories = '';
			for (c in data['parse']['categories']) {
				if (!('hidden' in data['parse']['categories'][c])) {
					categories += data['parse']['categories'][c]['*'].replace(/_/g, ' ') + ' | ';
				}
			}
			$('#wikitext').append('<br />Categories: ' + findValues(categories, lang));
		}
	);
}

function getWikipediapage(lvwikititle, q, lang) {
	site = lang + 'wiki';
	$.getJSON(
		'https://' + lang + '.wikipedia.org/w/api.php?callback=?',
		{
			action: 'query',
			prop: 'extracts',
			exintro: 1,
			explaintext: 1,
			titles: lvwikititle,
			format: 'json',
		},
		function (data) {
			if (q == $('.active').attr('data-q')) {
				//console.log(q);
				for (m in data['query']['pages']) {
					// lang = 'lv';
					//console.log(m);
					extract = findValues(data['query']['pages'][m]['extract'], lang);
					if (extract.indexOf('data-day') > -1 || extract.indexOf('data-value') > -1) {
						$('#wikitext').html(
							`<a href="https://${lang}.wikipedia.org/w/index.php?title=${lvwikititle}">lvwiki</a> | <a href="https://www.wikidata.org/wiki/${q}">wd</a><hr />${extract}`
						); // + '</div>');
						$('#wikitext').append(
							'<br /><br /><a href="#" class="fulltext" data-site="' +
								site +
								'" data-lang="' +
								lang +
								'" data-title="' +
								lvwikititle +
								'">get full article</a>'
						);
						console.log('here1jhjghj111');
					} else {
						getFullWikipediapage(site, lang, lvwikititle, q);
					}
				}
			}
		}
	);
}

function checkitem() {
	$('#warnings').html(``);
	const wdItem = $('.active').attr('data-q');

	const params = {
		action: 'wbgetentities',
		format: 'json',
		origin: '*',
		ids: wdItem,
		props: 'claims',
		formatversion: '2',
	};
	const baseurl = 'https://www.wikidata.org/w/api.php?';
	const qs = new URLSearchParams(params).toString();

	fetch(`${baseurl}${qs}`)
		.then((resp) => resp.json())
		.then((res) => {
			const data = res.entities[Object.keys(res.entities)[0]].claims;

			if (data && `P${PROP}` in data) {
				$('#warnings').html(`ALREADY HAS  P${PROP}`);
			}

			const p31Values = data?.P31.map((stmt) => {
				return stmt.mainsnak?.datavalue?.value?.id;
			}).filter(Boolean);

			if (p31Values.length === 0 || !p31Values.includes('Q5')) {
				$('#warnings').html(`NOT HUMAN`);
			}
			console.log(p31Values);

			console.log(data);
		});
}

function additems() {
	$('#wd_items').html('');

	fetch('//edgars.toolforge.org/wd-test/list.php?campaign=' + CAMPAIGN)
		.then((resp) => resp.json())
		.then((data) => {
			/* let dataR = [
				{
					article: 'Талантов, Пётр Валентинович',
					wikidata: 'Q65156117',
					wiki: 'ru',
				},
				{
					article: 'Arch Manning',
					wikidata: 'Q99925317',
					wiki: 'de',
				},
				{
					article: 'Arch Manning',
					wikidata: 'Q99925317',
					wiki: 'de',
				},
				{
					article: 'Кесслер, Алиса и Эллен',
					wikidata: 'Q563255',
					wiki: 'ru',
				},
			]; */

			let outt = [];

			data.map((entry) => {
				const { article, wikidata, wiki } = entry;
				outt.push('<a href="#" data-random="yes" data-q="' + wikidata + '" data-site="' + wiki + '" target="_parent">' + article + '</a>');
			});

			$('#wd_items').append(outt.join('\n'));
		});
}

function loadFromPetscan() {
	$('#wd_items').html('');
	const petscanId = $('#input-petscan').val();
	const wikilanguage = $('#input-lang').val();

	fetch(`https://petscan.wmflabs.org/?psid=${petscanId}&doit=Do it!&format=json`)
		.then((resp) => resp.json())
		.then((data) => {
			const articles = data['*'][0]['a']['*'];

			let outt = [];

			articles.map((entry) => {
				const { q, title } = entry;
				outt.push('<a href="#" data-random="yes" data-q="' + q + '" data-site="' + wikilanguage + '" target="_parent">' + title + '</a>');
			});

			$('#wd_items').append(outt.join('\n'));
		});
}

function showItem(item) {
	$('#wd_items a').removeClass('active');
	$(item).addClass('active');

	$('#wikipediapage').html('');

	var q = $(item).attr('data-q');
	var site = $(item).attr('data-site');
	var name = $(item).text();

	checkitem();

	getWikipediapage(name, q, site);
}

$(document).ready(function () {
	$('#wd_items').on('click', 'a', function (e) {
		e.preventDefault();
		window.scrollTo(0, 0);
		showItem($(this));
	});

	$('#btn-nodob').on('click', function (e) {
		e.preventDefault();
		//console.log('clicked');
		addDate($('.active').attr('data-q'), $('.active').text(), $('.active').attr('data-site'), '', '', '', 'y', '', 'dod-lvwiki1');
		if ($('.active').next().length == 0) {
			showItem($('#wd_items a:first'));
		} else {
			showItem($('.active').next());
		}
	});

	$('#btn-yes').on('click', function (e) {
		e.preventDefault();
		//console.log($('.btn-raw').val());
		addDate($('.active').attr('data-q'), $('.active').text(), $('.active').attr('data-site'), '', '', '', '', $('#btn-raw').val(), 'dod-lvwiki1');
	});

	$('#btn-load').on('click', function (e) {
		e.preventDefault();
		loadFromPetscan();
	});

	additems();

	$('#wikitext').on('click', '.value', function (e) {
		e.preventDefault();
		//console.log('clicked');
		addDate(
			$('.active').attr('data-q'),
			$('.active').text(),
			$('.active').attr('data-site'),
			$(this).attr('data-year'),
			$(this).attr('data-month'),
			$(this).attr('data-day'),
			'',
			'',
			'dod-lvwiki1'
		);

		if ($('.active').next().length == 0) {
			showItem($('#wd_items a:first'));
		} else {
			showItem($('.active').next());
		}
	});

	$('#wikitext').on('click', '.fulltext', function (e) {
		e.preventDefault();
		getFullWikipediapage($(this).attr('data-site'), $(this).attr('data-lang'), $(this).attr('data-title'), $(this).attr('data-q'));
	});

	$('body').on('click', 'a.button', function (e) {
		e.preventDefault();
		if ($(this).text() == 'next') {
			if ($('.active').next().length == 0) showItem($('#wd_items a:first'));
			else showItem($('.active').next());
		} else if ($(this).text() == 'previous') {
			if ($('.active').prev().length == 0) showItem($('#wd_items a:last'));
			else showItem($('.active').prev());
		}
	});
});
