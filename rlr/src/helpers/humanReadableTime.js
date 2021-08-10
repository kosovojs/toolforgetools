function pad(n){return n<10 ? '0'+n : n}

function human_readable_date(datetime) {
	const dateObject = new Date(Date.parse(datetime));

	return pad(dateObject.getDate())+'.'+pad(dateObject.getMonth()+1)+'.'+dateObject.getFullYear()//dateObject.toDateString();
}

export default human_readable_date;
