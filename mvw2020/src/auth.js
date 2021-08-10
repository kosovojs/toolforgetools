import toast from "./toast";
import { API_BASE_EDGARS } from "./config";

const checkAuth = () => {
	const usernameElement = document.getElementById("username");
	const loginElement = document.getElementById("login");
	const authErrorElement = document.getElementById("auth-notification");

	fetch(`${API_BASE_EDGARS}?action=userinfo`)
		.then(resp => resp.json())
		.then(res => {
			if ("error" in res) {
				usernameElement.innerHTML = "";
				authErrorElement.innerHTML =
					'<div class="alert alert-danger" role="alert">Lai nobalsotu, ir nepieciešams <a href="https://edgars.toolforge.org/?action=authorize" target="_parent">autorizēties</a>!</div>';
				loginElement.innerHTML =
					'<a href="https://edgars.toolforge.org/?action=authorize" target="_parent">ienākt</a>';
			} else {
				const username = res["query"]["userinfo"]["name"];
				usernameElement.innerHTML = `Sveiks, ${username}!`;
				loginElement.innerHTML =
					'<a href="https://edgars.toolforge.org/?action=logout" target="_parent">iziet</a>';
			}
		})
		.catch(err => {
			toast(`Nevar nolasīt autentifikācijas statusu`, "error", 5000);
		});
};

export default checkAuth;
