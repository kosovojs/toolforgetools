require("../../main");

import { handleVoteSave, updateUserCount, voteButtonElement, votingButtons } from "../../main";

voteButtonElement.addEventListener("click", () => handleVoteSave("newbie"));

window.addEventListener("load", function() {
	updateUserCount(3);

	Array.from(votingButtons).forEach(element => {
		element.addEventListener("change", () => updateUserCount(3), false);
	});
});
