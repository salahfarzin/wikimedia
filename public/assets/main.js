'use strict';

/**
 *  NOTE: Please do not use any third-party libraries to implement the
 *  following as we want to keep the JS payload as small as possible. You may
 *  use ES6. There is no need to support IE11.
 *
 *  TODO F: Add error-handling requirements, such as displaying error messages
 *  to the user when API requests fail and provide a graceful degradation of
 *  functionality.
 */

class HttpClient {
	constructor(baseUrl) {
		this.baseUrl = baseUrl;
	}

	/**
	 * Send request to a remote API endpoint
	 * by default some headers adjusted
	 *
	 * @param url
	 * @param options
	 * @returns {Promise<Response>}
	 */
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

	/**
	 * Initialize auto complete, timeout can be adjusted to avoid overwhelming server
	 *
	 * @param event
	 * @param timeout
	 */
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

	/**
	 * Fetch Data from remote API
	 *
	 * @param event
	 * @param input
	 * @param fromDate
	 * @returns {Promise<any>}
	 */
	async fetchRemote(event, input, fromDate)  {
		const url = '?search=' + input.value + (fromDate ? '&from-date=' + fromDate : '');
		const res = await this.httpClient.request(url);

		if (!res.ok) {
			throw new Error('whoops something went wrong: ' + res.statusText);
		}

		return await res.json();
	}

	/**
	 * Append data to the auto complete list, can be select by click on each item
	 * @param input
	 * @param data
	 * @returns {Promise<void>}
	 */
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
			listElement.addEventListener(
				'click',
				async event => await me.onListItemClick(event, item.title)
			);
			containerElement.appendChild(listElement);
		});

		containerElement.addEventListener('scroll', event=> me.onListItemsScroll(event, input))
		input.closest('.form-group').appendChild(containerElement);
	}


	/**
	 * Catch click on the list item after auto complete list emerge
	 *
	 * @param event
	 * @param title
	 */
	async onListItemClick(event, title) {
		const el = event.target;

		const res = await this.httpClient.request('?title=' + title);
		if (!res.ok) {
			throw new Error('whoops something went wrong: ' + res.statusText);
		}

		const data = await res.json();
		if (!data.content) {
			return;
		}

		el.closest('.form-group').querySelector('input[name=title]').value = title;
		document.querySelector('textarea[name=body]').value = data.content;
		el.closest('.auto-complete-list').remove();
	}

	/**
	 * Handle auto complete to load more by scrolling each time
	 * it uses the last date to load for last certain days
	 * it might not work correct, the time wasn't enough
	 *
	 * @param event
	 * @param input
	 * @returns {Promise<void>}
	 */
	async onListItemsScroll(event, input	) {
		const element = event.target;
		if (element.scrollTop + element.clientHeight >= element.scrollHeight - 20) {
			const lastListItem = document.querySelector('.auto-complete-list li:last-child');
			const fromDate = lastListItem.dataset.modifiedAt;

			const res = await this.fetchRemote(event, input, fromDate);
			this.appendItemsToList(input, res);
		}
	}
}


const apiUrl ='/api.php';
const httpClient = new HttpClient(apiUrl);
const autoComplete = new AutoComplete(httpClient);

class Main {
	init() {
		this.initAutoComplete();
		this.fetchArticlesWordCount();
		this.fetchArticles();
	}

	/**
	 * Initialize and control auto complete for
	 * forms with inputs that have class .auto-complete
	 */
	initAutoComplete() {
		document.querySelectorAll('input.auto-complete').forEach((input) => {
			input.addEventListener('keyup', (event) => autoComplete.init(event, 200));
			input.addEventListener('blur', (event) => {
				if (event.explicitOriginalTarget
					&& event.explicitOriginalTarget.closest('.auto-complete-list')
				) {
					return;
				}
				document.querySelectorAll('.auto-complete-list').forEach(el => el.remove());
			});
		});
	}

	/**
	 * Fetch word count of articles from remote API
	 */
	async fetchArticlesWordCount() {
		const res =  await httpClient.request('?word-count=1');
		if (!res.ok) {
			throw new Error('whoops something went wrong: ' + res.statusText);
		}

		const data = await res.json();
		if (!data.content) {
			return;
		}

		document.querySelector('.articles-word-count').innerText = data.content;
	}

	/**
	 * Fetch articles list from remote API
	 */
	async fetchArticles() {
		const articleContainer = document.querySelector('.article-list-container');
		const lastListItem = articleContainer.querySelector('li:last-child');

		let fromDate = '?from-date=';
		if (lastListItem) {
			fromDate += lastListItem.dataset.modifiedAt ? lastListItem.dataset.modifiedAt : '';
		}

		const res =  await httpClient.request(fromDate);
		if (!res.ok) {
			throw new Error('whoops something went wrong: ' + res.statusText);
		}

		const data = await res.json();
		if (!data.content.length) {
			return;
		}

		const articlesHolderElement = document.createElement('ul');
		data.content.forEach((item) => {
			const listElement = document.createElement('li');
			const anchorElement = document.createElement('a');

			anchorElement.href = 'index.php?title=' +  item.title;
			anchorElement.innerText = item.title;
			anchorElement.dataset.modifiedAt = item.modifiedAt;

			listElement.appendChild(anchorElement);
			articlesHolderElement.appendChild(listElement);
		});
		articleContainer.appendChild(articlesHolderElement)
	}
}

const main = new Main();
main.init();
