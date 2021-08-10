require("../../main");

import { handleVoteSave, updateUserCount, voteButtonElement, votingButtons } from "../../main";

voteButtonElement.addEventListener("click", () => handleVoteSave("first"));

window.addEventListener("load", function() {
	updateUserCount(15);

	Array.from(votingButtons).forEach(element => {
		element.addEventListener("change", () => updateUserCount(15), false);
	});
});
