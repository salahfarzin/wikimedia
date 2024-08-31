'use strict';

/**
 *  NOTE: Please do not use any third-party libraries to implement the
 *  following as we want to keep the JS payload as small as possible. You may
 *  use ES6. There is no need to support IE11.
 *
 *  TODO B: When typing in the "title" field, we want to auto-complete based on
 *  article titles that already exist. You may use the
 *  api.php?prefixsearch={search} endpoint for auto-completion. To avoid
 *  hitting the server endpoint excessively, please also add JavaScript code
 *  that ensures at least 200ms has passed between requests. Check the
 *  `design-spec/auto-complete-hover.png` file for the design spec.
 *  Also, you don't need to make the autocomplete list disappear when the input
 *  has lost focus in this TODO. That will be handled as part of TODO D.
 *
 *  TODO C: When the user selects an item from the auto-complete list, we want
 *  the textarea to populate with that article's contents. You may use the
 *  api.php?title={title} endpoint to get the article's contents. Check the
 *  `design-spec/auto-complete-select.png` file for the design spec.
 *
 *  TODO D: The autocomplete list should only be shown when the input receives
 *  focus. The list should be hidden after the user selects an item from the
 *  list or after the input loses focus.
 *
 *  TODO E: Figure out how to make multiple requests to the server as the user
 *  scrolls through the autocomplete list.
 *
 *  TODO F: Add error-handling requirements, such as displaying error messages
 *  to the user when API requests fail and provide a graceful degradation of
 *  functionality.
 */

class HttpClient {
	constructor(baseUrl) {
		this.baseUrl = baseUrl;
	}

	async request(url, options	= {}) {
		const headers = new Headers();
		headers.append('Accept', 'application/json');

		const defaultOptions = {
			headers: headers,
		};

		return await fetch(this.baseUrl + url, {...defaultOptions, ...options});
	}
}


class AutoComplete {
	timer;

	constructor(httpClient) {
		this.httpClient = httpClient;
	}

	init(event, timeout = 200) {
		const me = this;
		const input = event.target;

		clearTimeout(this.timer);
		this.timer = setTimeout(async () => {
			const res = await me.fetchRemote(event, input);

			document.querySelectorAll('.auto-complete-list li').forEach(el => el.remove());
			me.appendItemsToList(input, res);
		}, timeout);
	}

	async fetchRemote(event, input, fromDate)  {
		const url = '?prefixsearch=' + input.value + (fromDate ? '&fromDate=' + fromDate : '');
		const res = await this.httpClient.request(url);

		if (!res.ok) {
			throw new Error('whoops something went wrong: ' + res.statusText);
		}

		return await res.json();
	}

	async appendItemsToList(input, data)  {
		const me = this;
		if (!data.content.length) {
			return;
		}

		const containerElement = document.createElement('ul');
		containerElement.style.display = 'block';
		containerElement.classList.add('auto-complete-list');

		data.content.forEach((item) => {
			const listElement = document.createElement('li');
			listElement.innerText = item.title;
			listElement.dataset.modifiedAt = item.modifiedAt;
			containerElement.appendChild(listElement);
		});

		containerElement.addEventListener('scroll', event=> me.onListItemsScroll(event, input))
		input.closest('.form-group').appendChild(containerElement);
	}
}


const apiUrl ='/api.php';
const httpClient = new HttpClient(apiUrl);
const autoComplete = new AutoComplete(httpClient);

class Main {
	init() {
		this.initAutoComplete();
	}

	initAutoComplete() {
		document.querySelectorAll('input.auto-complete').forEach((input) => {
			input.addEventListener('keyup', (event) => autoComplete.init(event, 200));
		});
	}
}

const main = new Main();
main.init();
