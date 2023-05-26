import { useEffect, useState } from "react";
import DataStore from "../../DataStore";

export default function useAPI(endpoint, query = {}) {
	const [data, setData] = useState({});

	const setAPIData = (newData) => {
		return DataStore[endpoint].set(newData)
	}

	useEffect(() => {
		const setDataHandler = (data) => {
			setData(data);
		}

		DataStore[endpoint].subscribe(setDataHandler);

		return () => {
			DataStore[endpoint].unsubscribe(setDataHandler);
		}
	}, [query])

	return [data, setAPIData];
}