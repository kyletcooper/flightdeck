import { useEffect, useState } from "react";
import DataStore from "../../DataStore";


export default function useAPILoading(endpoint) {
	const [isLoading, setIsLoading] = useState(true);

	useEffect(() => {
		DataStore[endpoint].loading(setIsLoading);

		return () => {
			DataStore[endpoint].unloading(setIsLoading);
		}
	}, [])

	return isLoading;
}