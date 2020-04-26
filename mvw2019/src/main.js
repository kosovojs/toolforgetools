import './style.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import checkAuth from './auth';
import toast from './toast';

import { API_BASE } from "./config";

const votingButtons = document.getElementsByClassName("votingCheckbox");
const userCountElement = document.getElementById("currentUserCount");
const voteButtonElement = document.getElementById("voteButton");

const updateUserCount = (maxUserCount) => {
	const numberOfSelectedUsers = document.querySelectorAll("input[type=checkbox]:checked").length;

	userCountElement.innerHTML = `Pašlaik izvēlētais dalībnieku skaits: ${numberOfSelectedUsers}`;
	userCountElement.classList.toggle("warning", numberOfSelectedUsers > maxUserCount);
	if (numberOfSelectedUsers > maxUserCount || numberOfSelectedUsers == 0) {
		voteButtonElement.setAttribute("disabled", "");
	} else if (voteButtonElement.hasAttribute("disabled")) {
		voteButtonElement.removeAttribute("disabled");
	}
};

const resetCheckboxes = () => {
	Array.from(votingButtons).forEach(element => {
		if (element.type === "checkbox") {
			element.checked = false;
		}
	});
};

const handleVoteSave = (voteType) => {
	const allSelectedUsers = document.querySelectorAll("input[type=checkbox]:checked");
	const userList = [];

	Array.from(allSelectedUsers).forEach(element => {
		userList.push(element.getAttribute("data-user"));
	});

	fetch(API_BASE, {
		method: "post",
		body: JSON.stringify({
			vote: voteType,
			users: userList
		})
	})
		.then(resp => resp.json())
		.then(res => {
			if (res.status === "error") {
				toast(`Notika kļūda: ${res.msg}`, "error", 5000);
			} else {
				toast(`Paldies, balsojums pieņemts!`, "ok", 2500);
			}
		})
		.catch(err => {
			toast(`Notika kļūda: ${err}`, "error", 5000);
		});
};

window.addEventListener("load", function() {
	resetCheckboxes();
	checkAuth();
});

export {
	handleVoteSave,
	updateUserCount,
	voteButtonElement,
	votingButtons
}
