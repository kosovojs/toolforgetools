function generateContent(inputData) {
	var updDate = inputData['time'];
	var articles = inputData['articles'].map(formatOneEntry);

	var finalOutput = '<ul>' + articles.join('') + '</ul>';

	$('#articleList').html(finalOutput);
	$('#updateID').html(updDate);
}

function formatOneEntry(entry) {
	var article = entry[0];
	var iws = entry[1];
	return '<li><a href="https://en.wikipedia.org/wiki/' + encodeURIComponent(article) + '" target="_blank">' + article + '</a> — ' + iws + '</li>';
}

$.ajax({
	url: "https://tools.wmflabs.org/edgars/wam2019/api.php",
	type: 'GET',
	dataType: 'json',
	success: function (res) {
		generateContent(res);
	}
});