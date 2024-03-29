export function wp_parse_body(data = {}) {
	var form_data = new FormData();

	for (var key in data) {
		let value = data[key]
		if (typeof value === 'object') value = JSON.stringify(value)
		form_data.append(key, value);
	}

	return form_data;
}

export async function wp_fetch(action, data = {}, options = {}) {
	data.action = action;
	data._ajax_nonce = window.flightdeck.nonce;

	const defaultOptions = {
		method: 'POST',
		credentials: 'same-origin',
		body: wp_parse_body(data),
	}

	return fetch(window.flightdeck.ajax_url, { ...defaultOptions, ...options });
}

export async function wp_fetch_json(action, data = {}) {
	try {
		const resp = await wp_fetch(action, data);

		return await resp.json();
	}
	catch (exception) {
		console.error(exception);
	}
}

export const wp_rest_api = async function (endpoint, method = "GET", args = {}, opts = {}) {
	let url = new URL(`${window.flightdeck.rest_url + endpoint}`);

	let fetchOpts = {
		...opts,
		method: method,
		headers: new Headers({
			'X-WP-Nonce': window.flightdeck.rest_nonce
		})
	}

	if (method == "GET") {
		for (const key in args) {
			url.searchParams.append(key, args[key]);
		}
	}
	else {
		if (args instanceof FormData) {
			fetchOpts.body = args;
		}
		else {
			fetchOpts.headers.append('Content-Type', 'application/json');
			fetchOpts.body = JSON.stringify(args);
		}
	}

	console.log(fetchOpts);

	return fetch(url.href, fetchOpts)
}

export const wp_rest_api_json = async function (endpoint, method = "GET", args = {}) {
	return await wp_rest_api(endpoint, method, args).then(resp => resp.json())
}

window.wp_rest_api_json = wp_rest_api_json;

export const wp_fetch_new_tab = function (action, data = {}) {
	var form = document.createElement("form");
	form.action = window.flightdeck.rest_url + action;
	form.method = 'POST';
	form.target = "_blank";

	data._wpnonce = window.flightdeck.rest_nonce;

	for (var key in data) {
		var input = document.createElement("textarea");
		input.name = key;
		input.value = typeof data[key] === "object"
			? JSON.stringify(data[key])
			: data[key];
		form.appendChild(input);
	}

	form.style.display = 'none';
	document.body.appendChild(form);
	form.submit();
	document.body.removeChild(form);
}