import { wp_rest_api_json } from "./helpers/wpajax";

const createStore = (endpoint, includeSetter = true) => {
	return {
		subscribers: [],
		isLoading: true,
		loaders: [],

		store: false,

		_callCallbacks: function (array, ...args) {
			array.forEach(callback => typeof callback === 'function' && callback(...args))
		},

		_setLoading: function (setTo) {
			this.isLoading = setTo;
			this._callCallbacks(this.loaders, setTo)
		},

		get: function (refresh = false) {
			if (this.store === false || refresh) {
				this.store = new Promise(async (resolve, reject) => {

					this._setLoading(true);
					const resp = await wp_rest_api_json(endpoint, 'GET')
					this._setLoading(false);

					this.store.then(old_data => {
						this._callCallbacks(this.subscribers, resp, old_data)
					})

					resolve(resp)
				})
			}

			return this.store
		},

		set: async function (data) {
			if (!includeSetter) {
				throw new Error("Called set() on a store that does not include a setter.")
			}

			this._setLoading(true);
			const resp = await wp_rest_api_json(endpoint, 'POST', data)

			if (resp.hasOwnProperty('code')) {
				return new Promise((resolve, reject) => {
					this._setLoading(false);
					reject(resp);
				});
			}

			this.store.then(old_data => {
				this.store = new Promise((resolve, reject) => {
					resolve(resp);
				});

				this._callCallbacks(this.subscribers, resp, old_data)
			})

			this._setLoading(false);

			return this.store;
		},

		subscribe: function (callback) {
			this.subscribers.push(callback)
			this.get().then(callback);
		},

		unsubscribe: function (callback) {
			this.subscribers = this.subscribers.filter(x => x === callback)
		},

		loading: function (callback) {
			this.loaders.push(callback)
			callback(this.isLoading);
		},

		unloading: function (callback) {
			this.loaders = this.loaders.filter(x => x === callback)
		},
	}
}

const DataStore = {
	settings: createStore('flightdeck/v1/settings'),
	connection: createStore('flightdeck/v1/connection', false),
	tables: createStore('flightdeck/v1/tables', false),
	logs: createStore('flightdeck/v1/logs', false)
};

DataStore.settings.subscribe((settings, prevSettings) => {
	DataStore.connection.get(true)
});

export default DataStore;