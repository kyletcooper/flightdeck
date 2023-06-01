import React, { useEffect, useState } from "react";

import DashboardSection from "../DashboardSection";
import IconButton from "../IconButton";
import { wp_rest_api } from "../../helpers/wpajax";

function humanFileSize(bytes, dp = 1) {
	const thresh = 1000;

	if (Math.abs(bytes) < thresh) {
		return bytes + ' B';
	}

	const units = ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
	let u = -1;
	const r = 10 ** dp;

	do {
		bytes /= thresh;
		++u;
	} while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);


	return bytes.toFixed(dp) + ' ' + units[u];
}

export default function SectionLogs() {
	const [page, setPage] = useState(1);
	const [maxPage, setMaxPage] = useState(1);
	const [logs, setLogs] = useState([]);
	const [isLoading, setIsLoading] = useState(true);

	useEffect(() => {
		const getLogs = async () => {
			setIsLoading(true)

			const resp = await wp_rest_api('flightdeck/v1/logs', 'GET', {
				page
			})

			setIsLoading(false)
			setMaxPage(resp.headers.get("X-WP-TotalPages"))
			setLogs(await resp.json())
		}

		getLogs()

		document.addEventListener('flightdeck/sync-done', getLogs);

		return () => {
			document.removeEventListener('flightdeck/sync-done', getLogs);
		}
	}, [page])

	const handleNavigate = (change) => {
		setPage(Math.max(page + change, 0))
	}

	const hasPrevPage = page > 1;
	const hasNextPage = page < maxPage;

	return (
		<DashboardSection title="History">
			<div className="flex items-center gap-2">
				<p className="mr-auto">
					View the logs from previous departures and arrivals.
				</p>

				<IconButton icon="chevron_left" onClick={() => handleNavigate(-1)} disabled={!hasPrevPage || isLoading} />

				<IconButton icon="chevron_right" onClick={() => handleNavigate(1)} disabled={!hasNextPage || isLoading} />
			</div>

			<table className={"w-full mt-4 transition-opacity " + (isLoading ? "opacity-50 h-[400px]" : "")}>
				<tbody>
					{logs.length > 0 && logs.map(log => (
						<tr key={log.name}>
							<td className="py-2">
								<a download className="font-semibold transition-colors text-blue-500 hover:text-blue-600 focus:text-blue-600" href={log.url}>
									{log.name}
								</a>
							</td>

							<td className="py-2 text-gray-500">
								{humanFileSize(log.size)}
							</td>

							<td className="py-2 text-gray-500 text-right">
								{new Date(log.lastmod * 1000).toLocaleDateString('en-gb', { year: "numeric", month: "short", day: "numeric", hour: "numeric", minute: "numeric" })}
							</td>
						</tr>
					))}
				</tbody>
			</table>
		</DashboardSection>
	)
}