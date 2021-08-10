import Toastify from 'toastify-js'
import "toastify-js/src/toastify.css"

const showToast = (msg, type, duration) => {
	Toastify({
		text: msg,
		duration,
		newWindow: true,
		close: true,
		gravity: "bottom",
		position: 'right',
		backgroundColor: type === 'error' ? '#f8d7da' : "#d4edda",
		stopOnFocus: true,
		onClick: function(){}
	  }).showToast();
}

export default showToast;
